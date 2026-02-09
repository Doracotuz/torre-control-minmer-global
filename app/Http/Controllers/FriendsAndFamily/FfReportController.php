<?php

namespace App\Http\Controllers\FriendsAndFamily;

use App\Http\Controllers\Controller;
use App\Models\ffProduct;
use App\Models\ffInventoryMovement;
use App\Models\User;
use App\Models\Area;
use App\Models\FfWarehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Dompdf\Dompdf;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use App\Models\FfClient;
use Illuminate\Support\Str;

class FfReportController extends Controller
{
    public function index(Request $request)
    {
        if (!Auth::user()->isSuperAdmin() && !Auth::user()->hasFfPermission('reports.view')) {
            abort(403, 'No tienes permiso para ver el dashboard de reportes.');
        }

        $userIdFilter = $request->input('user_id');

        $areas = [];
        if (Auth::user()->isSuperAdmin()) {
            $areas = Area::orderBy('name')->get();
        }

        $warehousesQuery = FfWarehouse::where('is_active', true);
        if (!Auth::user()->isSuperAdmin()) {
            $warehousesQuery->where('area_id', Auth::user()->area_id);
        } elseif ($request->filled('area_id')) {
            $warehousesQuery->where('area_id', $request->input('area_id'));
        }
        $warehouses = $warehousesQuery->orderBy('description')->get();

        $baseQuery = ffInventoryMovement::where('ff_inventory_movements.quantity', '<', 0);
        
        if (!Auth::user()->isSuperAdmin()) {
            $baseQuery->where('ff_inventory_movements.area_id', Auth::user()->area_id);
        }

        if (Auth::user()->isSuperAdmin() && $request->filled('area_id')) {
            $baseQuery->where('ff_inventory_movements.area_id', $request->input('area_id'));
        }

        if ($request->filled('warehouse_id')) {
            $baseQuery->where('ff_inventory_movements.ff_warehouse_id', $request->input('warehouse_id'));
        }

        if ($userIdFilter) {
            $baseQuery->where('ff_inventory_movements.user_id', $userIdFilter);
        }

        $ventasCompletas = (clone $baseQuery)
            ->join('ff_products', 'ff_inventory_movements.ff_product_id', '=', 'ff_products.id')
            ->select(
                DB::raw('SUM(ABS(ff_inventory_movements.quantity) * ff_products.unit_price) as valor_total_vendido'),
                DB::raw('SUM(ABS(ff_inventory_movements.quantity)) as total_unidades_vendidas')
            )
            ->first();

        $totalUnidadesVendidas = (int) ($ventasCompletas->total_unidades_vendidas ?? 0);
        $valorTotalVendido = (float) ($ventasCompletas->valor_total_vendido ?? 0);

        $topProductos = (clone $baseQuery)
            ->select('ff_product_id', DB::raw('SUM(ABS(quantity)) as total_vendido'))
            ->groupBy('ff_product_id')
            ->orderByDesc('total_vendido')
            ->limit(5)
            ->with('product')
            ->get();
        
        $chartTopProductos = [
            'series' => [['data' => $topProductos->pluck('total_vendido')->toArray()]],
            'categories' => $topProductos->pluck('product.sku')->toArray(),
        ];

        $ventasPorVendedor = (clone $baseQuery)
            ->select('user_id', DB::raw('SUM(ABS(quantity)) as total_vendido'))
            ->groupBy('user_id')
            ->orderByDesc('total_vendido')
            ->with('user')
            ->get();
            
        $chartVentasVendedor = [
            'series' => $ventasPorVendedor->pluck('total_vendido')->toArray(),
            'labels' => $ventasPorVendedor->map(fn ($v) => $v->user->name ?? 'Usuario Eliminado')->toArray(),
        ];
        
        $warehouseId = $request->input('warehouse_id');
        $productsQuery = ffProduct::withSum(['movements' => function($q) use ($warehouseId) {
            if ($warehouseId) {
                $q->where('ff_warehouse_id', $warehouseId);
            }
        }], 'quantity');
        
        if (!Auth::user()->isSuperAdmin()) {
            $productsQuery->where('area_id', Auth::user()->area_id);
        }

        if (Auth::user()->isSuperAdmin() && $request->filled('area_id')) {
            $productsQuery->where('area_id', $request->input('area_id'));
        }

        $stockAgotadoCount = $productsQuery->get()
            ->filter(fn ($p) => ($p->movements_sum_quantity ?? 0) <= 0)
            ->count();
            
        $durationDays = 2; 
        $startDate = Carbon::parse('2025-11-13')->startOfDay();
        $endDate = $startDate->copy()->addDays($durationDays); 
        $now = now();

        $timerState = 'after';
        if ($now->lessThan($startDate)) {
            $timerState = 'before';
        } elseif ($now->between($startDate, $endDate)) {
            $timerState = 'during';
        }
        
        $startDateIso = $startDate->toIso8601String();
        $endDateIso = $endDate->toIso8601String();
        
        $vendedoresQuery = User::whereHas('movements', function ($query) use ($request) {
            $query->where('quantity', '<', 0);
            if (!Auth::user()->isSuperAdmin()) {
                $query->where('area_id', Auth::user()->area_id);
            }
            if (Auth::user()->isSuperAdmin() && $request->filled('area_id')) {
                $query->where('area_id', $request->input('area_id'));
            }
            if ($request->filled('warehouse_id')) {
                $query->where('ff_warehouse_id', $request->input('warehouse_id'));
            }
        })->orderBy('name');

        if (!Auth::user()->isSuperAdmin()) {
            $vendedoresQuery->where('area_id', Auth::user()->area_id);
        }

        if (Auth::user()->isSuperAdmin() && $request->filled('area_id')) {
            $vendedoresQuery->where('area_id', $request->input('area_id'));
        }

        $vendedores = $vendedoresQuery->get(['id', 'name']);
        
        return view('friends-and-family.reports.index', compact(
            'totalUnidadesVendidas',
            'valorTotalVendido',
            'stockAgotadoCount',
            'timerState',
            'startDateIso',
            'endDateIso',
            'vendedores',
            'userIdFilter',
            'chartTopProductos',
            'chartVentasVendedor',
            'areas',
            'warehouses'
        ));
    }

    public function transactions(Request $request)
    {
        if (!Auth::user()->isSuperAdmin() && !Auth::user()->hasFfPermission('reports.transactions')) {
            abort(403, 'No tienes permiso para ver transacciones.');
        }

        $vendedoresQuery = User::whereHas('movements', function ($query) use ($request) {
            $query->where('quantity', '<', 0);
            if (!Auth::user()->isSuperAdmin()) {
                $query->where('area_id', Auth::user()->area_id);
            }
            if (Auth::user()->isSuperAdmin() && $request->filled('area_id')) {
                $query->where('area_id', $request->input('area_id'));
            }
            if ($request->filled('warehouse_id')) {
                $query->where('ff_warehouse_id', $request->input('warehouse_id'));
            }
        })->orderBy('name');

        if (!Auth::user()->isSuperAdmin()) {
            $vendedoresQuery->where('area_id', Auth::user()->area_id);
        }

        if (Auth::user()->isSuperAdmin() && $request->filled('area_id')) {
            $vendedoresQuery->where('area_id', $request->input('area_id'));
        }
        
        $vendedores = $vendedoresQuery->get();

        $userIdFilter = $request->input('vendedor_id');
        $search = $request->input('search');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $query = ffInventoryMovement::where('ff_inventory_movements.quantity', '<', 0)
            ->join('ff_products', 'ff_inventory_movements.ff_product_id', '=', 'ff_products.id')
            ->select(
                'ff_inventory_movements.folio',
                'ff_inventory_movements.user_id',
                'ff_inventory_movements.client_name',
                'ff_inventory_movements.surtidor_name',
                'ff_inventory_movements.area_id',
                'ff_inventory_movements.ff_warehouse_id',
                DB::raw('MAX(ff_inventory_movements.created_at) as created_at'),
                DB::raw('COUNT(ff_inventory_movements.id) as total_items'),
                DB::raw('SUM(ABS(ff_inventory_movements.quantity)) as total_units'),
                DB::raw('SUM(ABS(ff_inventory_movements.quantity) * ff_products.unit_price) as total_value')
            )
            ->groupBy(
                'ff_inventory_movements.folio', 
                'ff_inventory_movements.user_id', 
                'ff_inventory_movements.client_name', 
                'ff_inventory_movements.surtidor_name',
                'ff_inventory_movements.area_id',
                'ff_inventory_movements.ff_warehouse_id'
            );

        if (!Auth::user()->isSuperAdmin()) {
            $query->where('ff_inventory_movements.area_id', Auth::user()->area_id);
        }

        if (Auth::user()->isSuperAdmin() && $request->filled('area_id')) {
            $query->where('ff_inventory_movements.area_id', $request->input('area_id'));
        }

        if ($request->filled('warehouse_id')) {
            $query->where('ff_inventory_movements.ff_warehouse_id', $request->input('warehouse_id'));
        }

        if ($userIdFilter) {
            $query->where('ff_inventory_movements.user_id', $userIdFilter);
        }

        if ($startDate) {
            $query->whereDate('ff_inventory_movements.created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('ff_inventory_movements.created_at', '<=', $endDate);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                if (is_numeric($search)) {
                    $q->orWhere('ff_inventory_movements.folio', (int)$search);
                }
                $q->orWhere('ff_inventory_movements.client_name', 'like', "%{$search}%")
                  ->orWhere('ff_inventory_movements.surtidor_name', 'like', "%{$search}%");
            });
        }

        $sales = $query->orderBy('created_at', 'desc')
            ->with(['user', 'warehouse'])
            ->paginate(50)
            ->withQueryString();
            
        $areas = [];
        if (Auth::user()->isSuperAdmin()) {
            $areas = Area::orderBy('name')->get();
        }

        $warehousesQuery = FfWarehouse::where('is_active', true);
        if (!Auth::user()->isSuperAdmin()) {
            $warehousesQuery->where('area_id', Auth::user()->area_id);
        } elseif ($request->filled('area_id')) {
            $warehousesQuery->where('area_id', $request->input('area_id'));
        }
        $warehouses = $warehousesQuery->orderBy('description')->get();

        return view('friends-and-family.reports.transactions', compact(
            'sales', 
            'vendedores', 
            'userIdFilter', 
            'search', 
            'startDate', 
            'endDate',
            'areas',
            'warehouses'
        ));
    }
    
    public function reprintReceipt(ffInventoryMovement $movement)
    {
        if (!Auth::user()->isSuperAdmin() && !Auth::user()->hasFfPermission('reports.transactions')) {
            abort(403, 'No tienes permiso para reimprimir recibos.');
        }

        if (!Auth::user()->isSuperAdmin() && $movement->area_id !== Auth::user()->area_id) {
            abort(403);
        }

        $saleMovements = ffInventoryMovement::where('folio', $movement->folio)
            ->where('quantity', '<', 0)
            ->with(['product', 'user', 'warehouse'])
            ->get();

        if ($saleMovements->isEmpty()) {
            abort(404, 'No se encontraron movimientos para reimprimir el recibo.');
        }

        $firstMovement = $saleMovements->first();
        $user = $firstMovement->user;
        
        $logoUrl = Storage::disk('s3')->url('LogoAzulm.PNG');
        $area = Area::find($firstMovement->area_id);

        if ($area && $area->icon_path) {
            $logoUrl = Storage::disk('s3')->url($area->icon_path);
        } elseif ($user && $user->area && $user->area->icon_path) {
            $logoUrl = Storage::disk('s3')->url($user->area->icon_path);
        }

        $warehouseName = 'N/A';
        if ($firstMovement->warehouse) {
            $warehouseName = $firstMovement->warehouse->code . ' - ' . $firstMovement->warehouse->description;
        }

        $pdfData = [
            'items' => [],
            'grandTotal' => 0,
            'copies' => ['Original'],
            'folio' => $firstMovement->folio,
            'date' => $firstMovement->created_at->format('d/m/Y'),
            'client_name' => $firstMovement->client_name,
            'company_name' => $firstMovement->company_name,
            'client_phone' => $firstMovement->client_phone,
            'address' => $firstMovement->address,
            'locality' => $firstMovement->locality,
            'delivery_date' => $firstMovement->delivery_date ? $firstMovement->delivery_date->format('d/m/Y H:i') : 'N/A',
            'observations' => $firstMovement->observations,
            'surtidor_name' => $firstMovement->surtidor_name,
            'vendedor_name' => $user->name ?? 'N/A',
            'logo_url' => $logoUrl,
            'warehouse_name' => $warehouseName,
        ];

        foreach ($saleMovements as $item) {
            $product = $item->product;
            $quantity = abs($item->quantity);
            $price = $product->unit_price ?? 0;
            $totalItem = $price * $quantity;

            $pdfData['items'][] = [
                'sku' => $product->sku,
                'description' => $product->description,
                'quantity' => $quantity,
                'unit_price' => $price,
                'total_price' => $totalItem,
            ];
            $pdfData['grandTotal'] += $totalItem;
        }

        $dompdf = new Dompdf();
        $options = $dompdf->getOptions();
        $options->set('isRemoteEnabled', true);
        $dompdf->setOptions($options);        
        
        $pdfView = view('friends-and-family.sales.pdf', $pdfData);
        
        $dompdf->loadHtml($pdfView->render());
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return response(
            $dompdf->output(),
            200,
            [
                'Content-Type' => 'application/pdf',
                'X-Venta-Folio' => $firstMovement->folio,
            ]
        );
    }
    
    public function inventoryAnalysis(Request $request)
    {
        if (!Auth::user()->isSuperAdmin() && !Auth::user()->hasFfPermission('reports.inventory_analysis')) {
            abort(403, 'No tienes permiso para ver análisis de inventario.');
        }

        $movementQuery = ffInventoryMovement::query();
        
        if (!Auth::user()->isSuperAdmin()) {
            $movementQuery->where('area_id', Auth::user()->area_id);
        }

        if (Auth::user()->isSuperAdmin() && $request->filled('area_id')) {
            $movementQuery->where('area_id', $request->input('area_id'));
        }

        if ($request->filled('warehouse_id')) {
            $movementQuery->where('ff_warehouse_id', $request->input('warehouse_id'));
        }

        $movementReasons = $movementQuery->select(
                'reason', 
                DB::raw('SUM(CASE WHEN quantity > 0 THEN quantity ELSE 0 END) as entradas'),
                DB::raw('SUM(CASE WHEN quantity < 0 THEN ABS(quantity) ELSE 0 END) as salidas')
            )
            ->groupBy('reason')
            ->get();
            
        $reasons = $movementReasons->pluck('reason')->toArray();
        $chartMovementReasons = [
            'series' => [
                ['name' => 'Entradas', 'data' => $movementReasons->pluck('entradas')->toArray()],
                ['name' => 'Salidas', 'data' => $movementReasons->pluck('salidas')->toArray()],
            ],
            'categories' => $reasons,
        ];
        
        $productsQuery = ffProduct::query();
        
        if (!Auth::user()->isSuperAdmin()) {
            $productsQuery->where('ff_products.area_id', Auth::user()->area_id);
        }

        if (Auth::user()->isSuperAdmin() && $request->filled('area_id')) {
            $productsQuery->where('ff_products.area_id', $request->input('area_id'));
        }

        $rotationProducts = $productsQuery->join('ff_inventory_movements', function($join) use ($request) {
                $join->on('ff_products.id', '=', 'ff_inventory_movements.ff_product_id');
                if ($request->filled('warehouse_id')) {
                    $join->where('ff_inventory_movements.ff_warehouse_id', $request->input('warehouse_id'));
                }
            })
            ->select(
                'ff_products.id',
                'ff_products.sku',
                'ff_products.unit_price',
                DB::raw('SUM(ff_inventory_movements.quantity) as total_stock'),
                DB::raw('SUM(CASE WHEN ff_inventory_movements.quantity < 0 THEN ABS(ff_inventory_movements.quantity) ELSE 0 END) as total_vendido')
            )
            ->groupBy('ff_products.id', 'ff_products.sku', 'ff_products.unit_price')
            ->having('total_vendido', '>', 0)
            ->get();
            
        $chartRotation = [
            'series' => [[
                'name' => 'Rotación',
                'data' => $rotationProducts->map(function($p) {
                    return [
                        'x' => (float) $p->unit_price,
                        'y' => (int) $p->total_stock,
                        'z' => (int) $p->total_vendido,
                        'label' => $p->sku,
                    ];
                })->toArray()
            ]]
        ];

        $areas = [];
        if (Auth::user()->isSuperAdmin()) {
            $areas = Area::orderBy('name')->get();
        }

        $warehousesQuery = FfWarehouse::where('is_active', true);
        if (!Auth::user()->isSuperAdmin()) {
            $warehousesQuery->where('area_id', Auth::user()->area_id);
        } elseif ($request->filled('area_id')) {
            $warehousesQuery->where('area_id', $request->input('area_id'));
        }
        $warehouses = $warehousesQuery->orderBy('description')->get();

        return view('friends-and-family.reports.inventory-analysis', compact(
            'chartMovementReasons',
            'chartRotation',
            'areas',
            'warehouses'
        ));
    }

    public function stockAvailability(Request $request)
    {
        if (!Auth::user()->isSuperAdmin() && !Auth::user()->hasFfPermission('reports.stock_availability')) {
            abort(403, 'No tienes permiso para ver disponibilidad de stock.');
        }

        $search = $request->input('search');
        $warehouseId = $request->input('warehouse_id');

        $query = ffProduct::where('is_active', true)
            ->withSum(['movements' => function($q) use ($warehouseId) {
                if ($warehouseId) {
                    $q->where('ff_warehouse_id', $warehouseId);
                }
            }], 'quantity')
            ->withSum(['cartItems' => function($q) use ($warehouseId) {
                if ($warehouseId) {
                    $q->where('ff_warehouse_id', $warehouseId);
                }
            }], 'quantity');

        if (!Auth::user()->isSuperAdmin()) {
            $query->where('area_id', Auth::user()->area_id);
        }

        if (Auth::user()->isSuperAdmin() && $request->filled('area_id')) {
            $query->where('area_id', $request->input('area_id'));
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('sku', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $allProducts = $query->get()->map(function($product) {
            $totalStock = (int) ($product->movements_sum_quantity ?? 0);
            $totalReserved = (int) ($product->cart_items_sum_quantity ?? 0);
            $available = $totalStock - $totalReserved;

            return [
                'sku' => $product->sku,
                'description' => $product->description,
                'total_stock' => $totalStock,
                'total_reserved' => $totalReserved,
                'available' => $available,
            ];
        });

        $lowStockAlerts = $allProducts->filter(fn ($p) => $p['available'] < 10)->sortBy('available');

        $chartData = $allProducts->sortByDesc('total_stock')->take(15);
        $chartStockVsReserved = [
            'series' => [
                ['name' => 'Stock Disponible', 'data' => $chartData->pluck('available')->toArray()],
                ['name' => 'Reservado (Carrito)', 'data' => $chartData->pluck('total_reserved')->toArray()],
            ],
            'categories' => $chartData->pluck('sku')->toArray(),
        ];

        $sortedCollection = $allProducts->sortBy('available');
        
        $currentPage = \Illuminate\Pagination\Paginator::resolveCurrentPage();
        $perPage = 50;
        $currentPageItems = $sortedCollection->slice(($currentPage - 1) * $perPage, $perPage)->all();
        
        $paginatedData = new \Illuminate\Pagination\LengthAwarePaginator(
            $currentPageItems, 
            $sortedCollection->count(), 
            $perPage, 
            $currentPage, 
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $areas = [];
        if (Auth::user()->isSuperAdmin()) {
            $areas = Area::orderBy('name')->get();
        }

        $warehousesQuery = FfWarehouse::where('is_active', true);
        if (!Auth::user()->isSuperAdmin()) {
            $warehousesQuery->where('area_id', Auth::user()->area_id);
        } elseif ($request->filled('area_id')) {
            $warehousesQuery->where('area_id', $request->input('area_id'));
        }
        $warehouses = $warehousesQuery->orderBy('description')->get();

        return view('friends-and-family.reports.stock-availability', compact(
            'paginatedData',
            'chartStockVsReserved',
            'lowStockAlerts',
            'search',
            'areas',
            'warehouses'
        ));
    }

    public function catalogAnalysis(Request $request)
    {
        if (!Auth::user()->isSuperAdmin() && !Auth::user()->hasFfPermission('reports.catalog_analysis')) {
            abort(403, 'No tienes permiso para ver análisis de catálogo.');
        }

        $query = ffProduct::select('unit_price', 'brand', 'type', 'is_active');

        if (!Auth::user()->isSuperAdmin()) {
            $query->where('area_id', Auth::user()->area_id);
        }

        if (Auth::user()->isSuperAdmin() && $request->filled('area_id')) {
            $query->where('area_id', $request->input('area_id'));
        }

        $products = $query->get();
        
        $priceRanges = [
            '0-100' => $products->whereBetween('unit_price', [0, 100])->count(),
            '101-300' => $products->whereBetween('unit_price', [101, 300])->count(),
            '301-500' => $products->whereBetween('unit_price', [301, 500])->count(),
            '501+' => $products->where('unit_price', '>', 500)->count(),
        ];
        
        $chartPriceDistribution = [
            'series' => array_values($priceRanges),
            'categories' => array_keys($priceRanges),
        ];

        $activeCount = $products->where('is_active', true)->count();
        $inactiveCount = $products->where('is_active', false)->count();

        $chartActiveInactive = [
            'series' => [$activeCount, $inactiveCount],
            'labels' => ['Activos', 'Inactivos'],
        ];

        $brandDistribution = $products->groupBy('brand')->map->count()->sortDesc()->take(10);
        
        $chartBrand = [
            'series' => $brandDistribution->values()->toArray(),
            'labels' => $brandDistribution->keys()->toArray(),
        ];

        $areas = [];
        if (Auth::user()->isSuperAdmin()) {
            $areas = Area::orderBy('name')->get();
        }

        $warehousesQuery = FfWarehouse::where('is_active', true);
        if (!Auth::user()->isSuperAdmin()) {
            $warehousesQuery->where('area_id', Auth::user()->area_id);
        } elseif ($request->filled('area_id')) {
            $warehousesQuery->where('area_id', $request->input('area_id'));
        }
        $warehouses = $warehousesQuery->orderBy('description')->get();

        return view('friends-and-family.reports.catalog-analysis', compact(
            'chartPriceDistribution',
            'chartActiveInactive',
            'chartBrand',
            'areas',
            'warehouses'
        ));
    }

    public function sellerPerformance(Request $request)
    {
        if (!Auth::user()->isSuperAdmin() && !Auth::user()->hasFfPermission('reports.seller_performance')) {
            abort(403, 'No tienes permiso para ver desempeño de vendedores.');
        }

        $vendedoresQuery = User::select('id', 'name')->where(function($q) use ($request) {
            $q->whereHas('cartItems', function($query) use ($request) {
                if (!Auth::user()->isSuperAdmin()) {
                    $query->whereHas('product', function($q) {
                        $q->where('area_id', Auth::user()->area_id);
                    });
                }
                if (Auth::user()->isSuperAdmin() && $request->filled('area_id')) {
                    $query->whereHas('product', function($q) use ($request) {
                        $q->where('area_id', $request->input('area_id'));
                    });
                }
                if ($request->filled('warehouse_id')) {
                    $query->where('ff_warehouse_id', $request->input('warehouse_id'));
                }
            })->orWhereHas('movements', function($query) use ($request) {
                if (!Auth::user()->isSuperAdmin()) {
                    $query->where('area_id', Auth::user()->area_id);
                }
                if (Auth::user()->isSuperAdmin() && $request->filled('area_id')) {
                    $query->where('area_id', $request->input('area_id'));
                }
                if ($request->filled('warehouse_id')) {
                    $query->where('ff_warehouse_id', $request->input('warehouse_id'));
                }
            });
        });

        if (!Auth::user()->isSuperAdmin()) {
            $vendedoresQuery->where('area_id', Auth::user()->area_id);
        }
        
        if (Auth::user()->isSuperAdmin() && $request->filled('area_id')) {
            $vendedoresQuery->where('area_id', $request->input('area_id'));
        }

        $vendedores = $vendedoresQuery->get();
        
        $sellerPerformanceData = $vendedores->map(function ($user) use ($request) {
            
            $query = ffInventoryMovement::where('user_id', $user->id)
                ->where('quantity', '<', 0);

            if (!Auth::user()->isSuperAdmin()) {
                $query->where('ff_inventory_movements.area_id', Auth::user()->area_id);
            }

            if (Auth::user()->isSuperAdmin() && $request->filled('area_id')) {
                $query->where('ff_inventory_movements.area_id', $request->input('area_id'));
            }

            if ($request->filled('warehouse_id')) {
                $query->where('ff_warehouse_id', $request->input('warehouse_id'));
            }

            $salesData = $query->join('ff_products', 'ff_inventory_movements.ff_product_id', '=', 'ff_products.id')
                ->select(
                    DB::raw('COUNT(ff_inventory_movements.id) as total_pedidos'),
                    DB::raw('SUM(ABS(ff_inventory_movements.quantity)) as total_unidades'),
                    DB::raw('SUM(ABS(ff_inventory_movements.quantity) * ff_products.unit_price) as valor_total'),
                    DB::raw('COUNT(DISTINCT ff_inventory_movements.ff_product_id) as skus_unicos')
                )
                ->first();

            $totalPedidos = (int) ($salesData->total_pedidos ?? 0);
            $valorTotal = (float) ($salesData->valor_total ?? 0);

            return [
                'name' => $user->name,
                'total_pedidos' => $totalPedidos,
                'total_unidades' => (int) ($salesData->total_unidades ?? 0),
                'valor_total' => $valorTotal,
                'skus_unicos' => (int) ($salesData->skus_unicos ?? 0),
                'ticket_promedio' => $totalPedidos > 0 ? $valorTotal / $totalPedidos : 0,
            ];
        })->filter(fn ($s) => $s['total_pedidos'] > 0)->sortByDesc('valor_total');
        
        $chartValorVendedor = [
            'series' => $sellerPerformanceData->pluck('valor_total')->toArray(),
            'labels' => $sellerPerformanceData->pluck('name')->toArray(),
        ];

        $areas = [];
        if (Auth::user()->isSuperAdmin()) {
            $areas = Area::orderBy('name')->get();
        }

        $warehousesQuery = FfWarehouse::where('is_active', true);
        if (!Auth::user()->isSuperAdmin()) {
            $warehousesQuery->where('area_id', Auth::user()->area_id);
        } elseif ($request->filled('area_id')) {
            $warehousesQuery->where('area_id', $request->input('area_id'));
        }
        $warehouses = $warehousesQuery->orderBy('description')->get();

        return view('friends-and-family.reports.seller-performance', compact(
            'sellerPerformanceData',
            'chartValorVendedor',
            'areas',
            'warehouses'
        ));
    }

    public function apiGetRecentMovements(Request $request)
    {
        if (!Auth::user()->isSuperAdmin() && !Auth::user()->hasFfPermission('reports.view')) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $userId = $request->input('user_id');
        $limit = $request->input('limit', 10);

        $query = ffInventoryMovement::where('quantity', '<', 0);

        if (!Auth::user()->isSuperAdmin()) {
            $query->where('ff_inventory_movements.area_id', Auth::user()->area_id);
        }

        if (Auth::user()->isSuperAdmin() && $request->filled('area_id')) {
            $query->where('ff_inventory_movements.area_id', $request->input('area_id'));
        }

        if ($request->filled('warehouse_id')) {
            $query->where('ff_inventory_movements.ff_warehouse_id', $request->input('warehouse_id'));
        }

        $query->join('ff_products', 'ff_inventory_movements.ff_product_id', '=', 'ff_products.id')
            ->select(
                'ff_inventory_movements.folio',
                'ff_inventory_movements.user_id',
                'ff_inventory_movements.created_at',
                DB::raw('COUNT(ff_inventory_movements.id) as total_items'),
                DB::raw('SUM(ABS(ff_inventory_movements.quantity)) as total_units'),
                DB::raw('SUM(ABS(ff_inventory_movements.quantity) * ff_products.unit_price) as total_value')
            )
            ->groupBy('ff_inventory_movements.folio', 'ff_inventory_movements.user_id', 'ff_inventory_movements.created_at');

        if ($userId) {
            $query->where('ff_inventory_movements.user_id', $userId);
        }

        $recentSales = $query->orderBy('ff_inventory_movements.created_at', 'desc')
                        ->with('user:id,name')
                        ->limit($limit)
                        ->get();
        
        $data = $recentSales->map(function ($sale) {
            $userName = $sale->user->name ?? 'N/A';
            
            return [
                'time' => $sale->created_at->diffForHumans(),
                'value' => '$' . number_format($sale->total_value, 2),
                'detail' => "Venta Folio #{$sale->folio}: {$sale->total_units} unids. en {$sale->total_items} ítems.",
                'icon' => 'receipt',
                'user' => $userName,
            ];
        });

        return response()->json($data);
    }

    public function apiGetSaleDetails(Request $request, $folio)
    {
        if (!Auth::user()->isSuperAdmin() && !Auth::user()->hasFfPermission('reports.transactions')) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $query = ffInventoryMovement::where('folio', $folio)
            ->where('quantity', '<', 0);

        if (!Auth::user()->isSuperAdmin()) {
            $query->where('area_id', Auth::user()->area_id);
        }

        if (Auth::user()->isSuperAdmin() && $request->filled('area_id')) {
            $query->where('area_id', $request->input('area_id'));
        }

        $movements = $query->with('product:id,sku,description,unit_price')->get();

        if ($movements->isEmpty()) {
            return response()->json(['error' => 'Pedido no encontrado o sin permisos'], 404);
        }

        $items = $movements->map(function ($mov) {
            $product = $mov->product;
            $quantity = abs($mov->quantity);
            $price = $product->unit_price ?? 0;
            
            return [
                'sku' => $product->sku ?? 'N/A',
                'description' => $product->description ?? 'Producto Eliminado',
                'quantity' => $quantity,
                'unit_price' => '$' . number_format($price, 2),
                'total_price' => '$' . number_format($price * $quantity, 2),
            ];
        });

        return response()->json([
            'folio' => $folio,
            'items' => $items,
        ]);
    }

    public function generateExecutiveReport(Request $request)
    {
        if (!Auth::user()->isSuperAdmin() && !Auth::user()->hasFfPermission('reports.executive')) {
            abort(403, 'No tienes permiso para generar reporte ejecutivo.');
        }

        $userIdFilter = $request->input('user_id');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $areaFilterId = $request->input('area_id');
        $warehouseId = $request->input('warehouse_id');
        
        $data = [];

        try {
            $user = Auth::user();
            $iconPath = 'LogoAzulm.PNG';

            if ($user->isSuperAdmin()) {
                if ($areaFilterId) {
                    $selectedArea = Area::find($areaFilterId);
                    if ($selectedArea && $selectedArea->icon_path) {
                        $iconPath = $selectedArea->icon_path;
                    }
                }
            } else {
                if ($user->area && $user->area->icon_path) {
                    $iconPath = $user->area->icon_path;
                }
            }

            try {
                if (Storage::disk('s3')->exists($iconPath)) {
                    $logoContent = Storage::disk('s3')->get($iconPath);
                    $data['logo_base_64'] = 'data:image/png;base64,' . base64_encode($logoContent);
                } else {
                    $data['logo_base_64'] = null;
                }
            } catch (\Exception $e) {
                $data['logo_base_64'] = null; 
            }

            $data = array_merge($data, $this->gatherReportData($userIdFilter, $startDate, $endDate));
            
            $data['user_filter_name'] = $userIdFilter ? User::find($userIdFilter)->name : 'Todos';
            
            if ($areaFilterId) {
                $data['area_name'] = Area::find($areaFilterId)->name ?? 'Global';
            } elseif ($user->area) {
                $data['area_name'] = $user->area->name;
            } else {
                $data['area_name'] = 'Global';
            }

            if ($warehouseId) {
                $wh = FfWarehouse::find($warehouseId);
                $data['warehouse_name'] = $wh ? ($wh->code . ' - ' . $wh->description) : 'Almacén Desconocido';
            } else {
                $data['warehouse_name'] = 'Inventario Global (Todos)';
            }
            
            if ($startDate && $endDate) {
                $start = Carbon::parse($startDate)->isoFormat('D MMM YYYY');
                $end = Carbon::parse($endDate)->isoFormat('D MMM YYYY');
                $data['date_range'] = "$start - $end";
            } else {
                $data['date_range'] = "Histórico Completo";
            }

            $data['report_date'] = Carbon::now()->isoFormat('D MMMM, YYYY H:mm');
            $data['diagnosis'] = $this->generateDiagnosis($data);
            $data['final_summary'] = $this->generateFinalSummary($data);
            
            $pdf = Pdf::loadView('friends-and-family.reports.executive-pdf', $data);
            
            $filterName = $userIdFilter ? User::find($userIdFilter)->name : 'Global';
            $filename = 'FF_Reporte_' . $filterName . '_' . Carbon::now()->format('Ymd_His') . '.pdf';
            
            return $pdf->stream($filename);

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al generar el PDF: ' . $e->getMessage());
        }
    }

    private function generateDiagnosis(array $data): array
    {
        $kpis = $data['kpis'];
        $waffle = $data['waffleChart'];
        
        $level = 'success';
        $message = "La operación comercial fluye de manera estable, con ventas activas y rotación de inventario constante.";

        if ($kpis['valorTotalVendido'] == 0) {
            $level = 'warning';
            $message = "ADVERTENCIA: No se registran transacciones en el periodo seleccionado. El reporte no contiene datos de flujo.";
            return ['level' => $level, 'message' => $message];
        }

        if ($waffle['percent_agotado'] > 10) {
            $level = 'danger';
            $message = "ALERTA DE STOCK: El inventario requiere reabastecimiento urgente. Más del 10% del catálogo ({$waffle['agotado']} SKUs) se encuentra agotado.";
        } elseif ($waffle['percent_agotado'] > 0 || $waffle['percent_bajo'] > 20) {
            $level = 'warning';
            $message = "ATENCIÓN REQUERIDA: El inventario muestra signos de presión. Hay {$waffle['agotado']} SKUs agotados y {$waffle['bajo']} en nivel crítico de reorden.";
        }
        
        $activos = $data['pictogramChart']['activos'];
        $total = $data['pictogramChart']['total'];
        if ($activos > 0 && ($activos / $total) < 0.25) {
             $message .= " Se detecta una baja participación de la fuerza de ventas ({$activos} de {$total} usuarios activos en el periodo).";
        }

        return ['level' => $level, 'message' => $message];
    }

    private function generateFinalSummary(array $data): string
    {
        $trivial = $data['trivial'];
        $kpis = $data['kpis'];
        
        if ($kpis['valorTotalVendido'] == 0) {
            return "No hay suficientes datos transaccionales para generar conclusiones operativas en este periodo.";
        }

        $summary = "El periodo analizado cierra con un ingreso bruto acumulado de $" . number_format($kpis['valorTotalVendido'], 2) . ". ";
        
        if ($trivial['productoEstrella']) {
            $summary .= "La demanda se ha polarizado hacia el SKU {$trivial['productoEstrella']->sku}, que lidera el volumen de salida. ";
        }
        
        if ($trivial['mejorVendedor']) {
            $summary .= "En el rendimiento individual, {$trivial['mejorVendedor']->user_name} destaca como el operador comercial más efectivo con una facturación de $" . number_format($trivial['mejorVendedor']->valor_total, 2) . ". ";
        }

        if ($trivial['ventasPorDia']->count() > 1) {
            $summary .= "El flujo de transacciones alcanzó su punto máximo el {$trivial['diaPico']->dia_formateado}, marcando la tendencia alta del periodo. ";
        } elseif ($trivial['diaPico']) {
            $summary .= "La operatividad se concentró puntualmente el día {$trivial['diaPico']->dia_formateado}. ";
        }

        $summary .= "Se recomienda revisar los niveles de stock crítico para asegurar la continuidad operativa.";
        
        return $summary;
    }

    private function gatherReportData($userIdFilter, $startDate = null, $endDate = null): array
    {
        $data = [];
        Carbon::setLocale('es');

        $baseQuery = ffInventoryMovement::where('ff_inventory_movements.quantity', '<', 0);
        
        if (!Auth::user()->isSuperAdmin()) {
            $baseQuery->where('ff_inventory_movements.area_id', Auth::user()->area_id);
        }
        
        if (Auth::user()->isSuperAdmin() && request()->filled('area_id')) {
             $baseQuery->where('ff_inventory_movements.area_id', request()->input('area_id'));
        }

        if (request()->filled('warehouse_id')) {
            $baseQuery->where('ff_inventory_movements.ff_warehouse_id', request()->input('warehouse_id'));
        }

        if ($userIdFilter) {
            $baseQuery->where('ff_inventory_movements.user_id', $userIdFilter);
        }

        if ($startDate && $endDate) {
            $baseQuery->whereBetween('ff_inventory_movements.created_at', [
                Carbon::parse($startDate)->startOfDay(), 
                Carbon::parse($endDate)->endOfDay()
            ]);
        }

        $ventasCompletas = (clone $baseQuery)
            ->join('ff_products', 'ff_inventory_movements.ff_product_id', '=', 'ff_products.id')
            ->select(
                DB::raw('SUM(ABS(ff_inventory_movements.quantity) * ff_products.unit_price) as valor'),
                DB::raw('SUM(ABS(ff_inventory_movements.quantity)) as unidades'),
                DB::raw('COUNT(DISTINCT ff_inventory_movements.folio) as total_ventas')
            )
            ->first();
        
        $data['kpis'] = [
            'valorTotalVendido' => (float) ($ventasCompletas->valor ?? 0),
            'totalUnidadesVendidas' => (int) ($ventasCompletas->unidades ?? 0),
            'totalVentas' => (int) ($ventasCompletas->total_ventas ?? 0),
            'ticketPromedio' => ($ventasCompletas->total_ventas ?? 0) > 0 ? $ventasCompletas->valor / $ventasCompletas->total_ventas : 0,
            'unidadesPorVenta' => ($ventasCompletas->total_ventas ?? 0) > 0 ? $ventasCompletas->unidades / $ventasCompletas->total_ventas : 0,
        ];
        
        $data['topProductos'] = (clone $baseQuery)
            ->join('ff_products', 'ff_inventory_movements.ff_product_id', '=', 'ff_products.id')
            ->select('ff_products.sku', 'ff_products.description', DB::raw('SUM(ABS(ff_inventory_movements.quantity)) as total_vendido'))
            ->groupBy('ff_products.sku', 'ff_products.description')
            ->orderByDesc('total_vendido')
            ->get();

        $ventasPorVendedorQuery = ffInventoryMovement::where('ff_inventory_movements.quantity', '<', 0)
            ->join('ff_products', 'ff_inventory_movements.ff_product_id', '=', 'ff_products.id')
            ->join('users', 'ff_inventory_movements.user_id', '=', 'users.id');

        if (!Auth::user()->isSuperAdmin()) {
            $ventasPorVendedorQuery->where('ff_inventory_movements.area_id', Auth::user()->area_id);
        }
        
        if (Auth::user()->isSuperAdmin() && request()->filled('area_id')) {
             $ventasPorVendedorQuery->where('ff_inventory_movements.area_id', request()->input('area_id'));
        }

        if (request()->filled('warehouse_id')) {
            $ventasPorVendedorQuery->where('ff_inventory_movements.ff_warehouse_id', request()->input('warehouse_id'));
        }

        if ($userIdFilter) {
            $ventasPorVendedorQuery->where('ff_inventory_movements.user_id', $userIdFilter);
        }
        
        if ($startDate && $endDate) {
            $ventasPorVendedorQuery->whereBetween('ff_inventory_movements.created_at', [
                Carbon::parse($startDate)->startOfDay(), 
                Carbon::parse($endDate)->endOfDay()
            ]);
        }

        $data['ventasPorVendedor'] = $ventasPorVendedorQuery->select(
                'users.name as user_name',
                DB::raw('SUM(ABS(ff_inventory_movements.quantity) * ff_products.unit_price) as valor_total'),
                DB::raw('SUM(ABS(ff_inventory_movements.quantity)) as total_unidades'),
                DB::raw('COUNT(DISTINCT ff_inventory_movements.folio) as total_pedidos')
            )
            ->groupBy('ff_inventory_movements.user_id', 'users.name')
            ->orderByDesc('valor_total')
            ->get();

        $productsCatalogQuery = ffProduct::select('unit_price');
        if (!Auth::user()->isSuperAdmin()) {
            $productsCatalogQuery->where('area_id', Auth::user()->area_id);
        }
        if (Auth::user()->isSuperAdmin() && request()->filled('area_id')) {
            $productsCatalogQuery->where('area_id', request()->input('area_id'));
        }

        $productsCatalog = $productsCatalogQuery->get();

        $priceRangesRaw = [
            '0-100' => $productsCatalog->whereBetween('unit_price', [0, 100])->count(),
            '101-300' => $productsCatalog->whereBetween('unit_price', [101, 300])->count(),
            '301-500' => $productsCatalog->whereBetween('unit_price', [301, 500])->count(),
            '501+' => $productsCatalog->where('unit_price', '>', 500)->count(),
        ];
        $totalProductos = max(1, array_sum($priceRangesRaw));
        $data['priceRanges'] = collect($priceRangesRaw)->mapWithKeys(function ($count, $range) use ($totalProductos) {
            return [$range => ['count' => $count, 'percent' => ($count / $totalProductos) * 100]];
        });

        $warehouseId = request()->input('warehouse_id');
        $allProductsQuery = ffProduct::withSum(['movements' => function($q) use ($warehouseId) {
            if ($warehouseId) {
                $q->where('ff_warehouse_id', $warehouseId);
            }
        }], 'quantity')->withSum(['cartItems' => function($q) use ($warehouseId) {
            if ($warehouseId) {
                $q->where('ff_warehouse_id', $warehouseId);
            }
        }], 'quantity');

        if (!Auth::user()->isSuperAdmin()) {
            $allProductsQuery->where('area_id', Auth::user()->area_id);
        }
        if (Auth::user()->isSuperAdmin() && request()->filled('area_id')) {
            $allProductsQuery->where('area_id', request()->input('area_id'));
        }
        $allProducts = $allProductsQuery->get();

        $totalSKUs = $allProducts->count();
        $stockAgotado = 0;
        $stockBajo = 0;
        $stockSaludable = 0;
        $lowStockList = [];

        foreach ($allProducts as $product) {
            $totalStock = (int) ($product->movements_sum_quantity ?? 0);
            $totalReserved = (int) ($product->cart_items_sum_quantity ?? 0);
            $available = $totalStock - $totalReserved;
            
            if ($available <= 0) {
                $stockAgotado++;
            } elseif ($available < 10) {
                $stockBajo++;
                $product->available = $available;
                $product->total_stock = $totalStock;
                $product->total_reserved = $totalReserved;
                $lowStockList[] = $product;
            } else {
                $stockSaludable++;
            }
        }

        $data['waffleChart'] = [
            'total' => $totalSKUs,
            'agotado' => $stockAgotado,
            'bajo' => $stockBajo,
            'saludable' => $stockSaludable,
            'percent_agotado' => $totalSKUs > 0 ? round(($stockAgotado / $totalSKUs) * 100) : 0,
            'percent_bajo' => $totalSKUs > 0 ? round(($stockBajo / $totalSKUs) * 100) : 0,
            'percent_saludable' => $totalSKUs > 0 ? round(($stockSaludable / $totalSKUs) * 100) : 0,
        ];
        $data['lowStockAlerts'] = collect($lowStockList)->sortBy('available');
        $data['kpis']['stockAgotadoCount'] = $stockAgotado;
        $data['kpis']['lowStockAlertsCount'] = $stockBajo;

        $vendedoresActivos = $data['ventasPorVendedor']->count();
        
        $totalVendedoresQuery = User::query();
        if (!Auth::user()->isSuperAdmin()) {
            $totalVendedoresQuery->where('area_id', Auth::user()->area_id);
        }
        if (Auth::user()->isSuperAdmin() && request()->filled('area_id')) {
            $totalVendedoresQuery->where('area_id', request()->input('area_id'));
        }
        $totalVendedores = $totalVendedoresQuery->count();
        
        if ($totalVendedores == 0) { $totalVendedores = max($vendedoresActivos, 1); }
        
        $data['pictogramChart'] = [
            'total' => $totalVendedores,
            'activos' => $vendedoresActivos,
            'percent_activos' => $totalVendedores > 0 ? round(($vendedoresActivos / $totalVendedores) * 100) : 0,
        ];

        $ventasPorDia = (clone $baseQuery)
            ->join('ff_products', 'ff_inventory_movements.ff_product_id', '=', 'ff_products.id')
            ->select(DB::raw('DATE(ff_inventory_movements.created_at) as dia'), 
                    DB::raw('SUM(ABS(ff_inventory_movements.quantity) * ff_products.unit_price) as total_dia'))
            ->groupBy('dia')->orderBy('dia', 'asc')->get();
        
        $diaPico = $ventasPorDia->sortByDesc('total_dia')->first();
        if($diaPico) { $diaPico->dia_formateado = Carbon::parse($diaPico->dia)->isoFormat('dddd D \d\e MMMM'); }

        $data['trivial'] = [
            'mejorVendedor' => $data['ventasPorVendedor']->first(),
            'productoEstrella' => $data['topProductos']->first(),
            'diaPico' => $diaPico,
            'ventasPorDia' => $ventasPorDia,
        ];
        
        return $data;
    }

    public function clientAnalysis(Request $request)
    {
        if (!Auth::user()->isSuperAdmin() && !Auth::user()->hasFfPermission('reports.client_analysis')) {
            abort(403, 'No tienes permiso para ver análisis de clientes.');
        }

        $search = $request->input('search');
        $areaId = $request->input('area_id');
        $warehouseId = $request->input('warehouse_id');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $baseQuery = ffInventoryMovement::query()
            ->where('quantity', '<', 0)
            ->join('ff_products', 'ff_inventory_movements.ff_product_id', '=', 'ff_products.id');

        if (!Auth::user()->isSuperAdmin()) {
            $baseQuery->where('ff_inventory_movements.area_id', Auth::user()->area_id);
        }
        if (Auth::user()->isSuperAdmin() && $areaId) {
            $baseQuery->where('ff_inventory_movements.area_id', $areaId);
        }
        if ($warehouseId) {
            $baseQuery->where('ff_inventory_movements.ff_warehouse_id', $warehouseId);
        }
        if ($startDate && $endDate) {
            $baseQuery->whereBetween('ff_inventory_movements.created_at', [
                Carbon::parse($startDate)->startOfDay(), 
                Carbon::parse($endDate)->endOfDay()
            ]);
        }
        
        if ($search) {
            $baseQuery->where('ff_inventory_movements.client_name', 'like', "%{$search}%");
        }

        $clientSuggestionsQuery = \App\Models\FfClient::select('name')
            ->where('is_active', true);

        if (!Auth::user()->isSuperAdmin()) {
            $clientSuggestionsQuery->where('area_id', Auth::user()->area_id);
        }
        
        $clientSuggestions = $clientSuggestionsQuery->orderBy('name')->pluck('name');

        $sixMonthsAgo = Carbon::now()->subMonths(6)->startOfMonth();
        $historyQuery = (clone $baseQuery);
        if (!$startDate) {
            $historyQuery->where('ff_inventory_movements.created_at', '>=', $sixMonthsAgo);
        }
        
        $salesHistory = $historyQuery->select(
                DB::raw("DATE_FORMAT(ff_inventory_movements.created_at, '%Y-%m') as mes"),
                DB::raw('SUM(ABS(ff_inventory_movements.quantity) * ff_products.unit_price) as total')
            )
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();

        $chartHistory = [
            'categories' => $salesHistory->pluck('mes')->map(fn($m) => Carbon::parse($m . '-01')->translatedFormat('M Y'))->values()->toArray(),
            'data' => $salesHistory->pluck('total')->map(fn($val) => (float)$val)->values()->toArray(),
        ];

        $daysData = (clone $baseQuery)
            ->select(
                DB::raw('DAYOFWEEK(ff_inventory_movements.created_at) as dia_num'),
                DB::raw('COUNT(*) as transacciones'),
                DB::raw('SUM(ABS(ff_inventory_movements.quantity) * ff_products.unit_price) as total_valor')
            )
            ->groupBy('dia_num')
            ->get();
        
        $weekMap = [1=>'Dom', 2=>'Lun', 3=>'Mar', 4=>'Mié', 5=>'Jue', 6=>'Vie', 7=>'Sáb'];
        $chartDays = [
            'labels' => array_values($weekMap),
            'transactions' => array_fill(0, 7, 0),
            'values' => array_fill(0, 7, 0),
        ];
        foreach($daysData as $d) {
            $idx = $d->dia_num - 1; 
            $chartDays['transactions'][$idx] = $d->transacciones;
            $chartDays['values'][$idx] = $d->total_valor;
        }

        $topProducts = (clone $baseQuery)
            ->select('ff_products.description', 'ff_products.sku', DB::raw('SUM(ABS(ff_inventory_movements.quantity)) as qty'))
            ->groupBy('ff_products.description', 'ff_products.sku')
            ->orderByDesc('qty')
            ->limit(10)
            ->get();

        $topBranches = (clone $baseQuery)
            ->join('ff_client_branches', 'ff_inventory_movements.ff_client_branch_id', '=', 'ff_client_branches.id')
            ->select('ff_client_branches.name', DB::raw('SUM(ABS(ff_inventory_movements.quantity) * ff_products.unit_price) as total'))
            ->groupBy('ff_client_branches.name')
            ->orderByDesc('total')
            ->limit(10)
            ->get();
            
        $salesChannels = (clone $baseQuery)
            ->join('ff_sales_channels', 'ff_inventory_movements.ff_sales_channel_id', '=', 'ff_sales_channels.id')
            ->select('ff_sales_channels.name', DB::raw('SUM(ABS(ff_inventory_movements.quantity) * ff_products.unit_price) as total'))
            ->groupBy('ff_sales_channels.name')
            ->get();
            
        $chartChannels = [
            'labels' => $salesChannels->pluck('name')->values()->toArray(),
            'series' => $salesChannels->pluck('total')->map(fn($val) => (float)$val)->values()->toArray(),
        ];

        $clientsQuery = (clone $baseQuery)
            ->select(
                'ff_inventory_movements.client_name',
                DB::raw('COUNT(DISTINCT ff_inventory_movements.folio) as frecuencia'),
                DB::raw('SUM(ABS(ff_inventory_movements.quantity)) as piezas'),
                DB::raw('SUM(ABS(ff_inventory_movements.quantity) * ff_products.unit_price) as valor'),
                DB::raw('MAX(ff_inventory_movements.created_at) as ultima_compra'),
                DB::raw('DATEDIFF(NOW(), MAX(ff_inventory_movements.created_at)) as dias_inactivo')
            )
            ->groupBy('ff_inventory_movements.client_name')
            ->having('valor', '>', 0)
            ->orderByDesc('valor');

        $clientsData = $clientsQuery->get()->map(function($client) {
            return [
                'name' => $client->client_name ?: 'Venta Mostrador',
                'frecuencia' => $client->frecuencia,
                'piezas' => $client->piezas,
                'valor' => $client->valor,
                'ticket_promedio' => $client->frecuencia > 0 ? $client->valor / $client->frecuencia : 0,
                'ultima_compra' => Carbon::parse($client->ultima_compra)->format('Y-m-d'),
                'dias_inactivo' => $client->dias_inactivo,
                'status_riesgo' => $client->dias_inactivo > 60 ? 'Alto' : ($client->dias_inactivo > 30 ? 'Medio' : 'Bajo'),
            ];
        });

        $kpis = [
            'total_clientes' => $clientsData->count(),
            'ventas_totales' => $clientsData->sum('valor'),
            'ticket_promedio_global' => $clientsData->count() > 0 ? $clientsData->avg('ticket_promedio') : 0,
            'mejor_cliente' => $clientsData->first(),
            'cliente_riesgo' => $clientsData->sortByDesc('dias_inactivo')->first(),
        ];

        $areas = Auth::user()->isSuperAdmin() ? Area::orderBy('name')->get() : [];
        $warehouses = FfWarehouse::where('is_active', true)
            ->when(!Auth::user()->isSuperAdmin(), fn($q) => $q->where('area_id', Auth::user()->area_id))
            ->get();

        return view('friends-and-family.reports.client-analysis', compact(
            'clientsData', 
            'kpis', 
            'chartHistory', 
            'chartDays', 
            'chartChannels',
            'topProducts', 
            'topBranches', 
            'areas', 
            'warehouses', 
            'search',
            'startDate',
            'endDate',
            'clientSuggestions'
        ));
    }

    public function generateClientPdf(Request $request)
    {
        if (!Auth::user()->isSuperAdmin() && !Auth::user()->hasFfPermission('reports.client_analysis')) {
            abort(403, 'No tienes permiso para generar PDF de clientes.');
        }

        Carbon::setLocale('es');
        $search = $request->input('search');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $areaId = $request->input('area_id');
        $warehouseId = $request->input('warehouse_id');

        $query = ffInventoryMovement::where('quantity', '<', 0)
            ->join('ff_products', 'ff_inventory_movements.ff_product_id', '=', 'ff_products.id');

        if (!Auth::user()->isSuperAdmin()) {
            $query->where('ff_inventory_movements.area_id', Auth::user()->area_id);
        }
        if (Auth::user()->isSuperAdmin() && $areaId) {
            $query->where('ff_inventory_movements.area_id', $areaId);
        }
        if ($warehouseId) {
            $query->where('ff_inventory_movements.ff_warehouse_id', $warehouseId);
        }
        if ($startDate && $endDate) {
            $query->whereBetween('ff_inventory_movements.created_at', [
                Carbon::parse($startDate)->startOfDay(), 
                Carbon::parse($endDate)->endOfDay()
            ]);
        }
        
        $clientName = $search ?: "Reporte General de Cartera";
        if (!empty($search)) {
            $query->where('ff_inventory_movements.client_name', 'like', "%{$search}%");
        }

        $baseMetrics = (clone $query)->select(
            DB::raw('SUM(ABS(ff_inventory_movements.quantity) * ff_products.unit_price) as total_valor'),
            DB::raw('SUM(ABS(ff_inventory_movements.quantity)) as total_unidades'),
            DB::raw('COUNT(DISTINCT ff_inventory_movements.folio) as total_transacciones'),
            DB::raw('MAX(ff_inventory_movements.created_at) as ultima_compra'),
            DB::raw('MIN(ff_inventory_movements.created_at) as primera_compra'),
            DB::raw('AVG(ABS(ff_inventory_movements.quantity) * ff_products.unit_price) as ticket_promedio')
        )->first();

        $diasInactivo = ($baseMetrics && $baseMetrics->ultima_compra)
            ? intval(Carbon::parse($baseMetrics->ultima_compra)->diffInDays(now())) 
            : 0;
            
        $antiguedad = ($baseMetrics && $baseMetrics->primera_compra)
            ? Carbon::parse($baseMetrics->primera_compra)->diffForHumans(null, true)
            : 'N/A';

        $svgTrend = null;
        $historyData = (clone $query)->select(
            DB::raw("DATE_FORMAT(ff_inventory_movements.created_at, '%Y-%m') as mes_sort"),
            DB::raw("DATE_FORMAT(ff_inventory_movements.created_at, '%b') as mes_label"),
            DB::raw('SUM(ABS(ff_inventory_movements.quantity) * ff_products.unit_price) as total')
        )->groupBy('mes_sort', 'mes_label')->orderBy('mes_sort', 'asc')->limit(12)->get();

        if ($historyData->count() > 0) {
            if ($historyData->count() === 1) {
                $firstItem = $historyData->first();
                $prevDate = Carbon::createFromFormat('Y-m', $firstItem->mes_sort)->subMonth();
                $historyData->prepend((object)[
                    'mes_sort' => $prevDate->format('Y-m'),
                    'mes_label' => $prevDate->format('M'),
                    'total' => 0
                ]);
            }

            $w = 800; $h = 250; 
            $maxVal = $historyData->max('total') * 1.1 ?: 1;
            $points = [];
            $step = $w / max(1, $historyData->count() - 1);
            
            foreach ($historyData->values() as $i => $d) {
                $x = number_format($i * $step, 2, '.', '');
                $y = number_format($h - (($d->total / $maxVal) * $h), 2, '.', ''); 
                $points[] = "$x,$y";
            }
            
            $polyPoints = implode(' ', $points);
            $areaPoints = "0,$h $polyPoints $w,$h";
            
            $rawSvgTrend = '<svg width="'.$w.'" height="'.$h.'" viewBox="0 0 '.$w.' '.$h.'" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <linearGradient id="grad" x1="0%" y1="0%" x2="0%" y2="100%">
                        <stop offset="0%" style="stop-color:#f59e0b;stop-opacity:0.3" />
                        <stop offset="100%" style="stop-color:#f59e0b;stop-opacity:0.0" />
                    </linearGradient>
                </defs>
                <polygon points="'.$areaPoints.'" fill="url(#grad)" stroke="none" />
                <polyline fill="none" stroke="#f59e0b" stroke-width="3" points="'.$polyPoints.'" stroke-linejoin="round" stroke-linecap="round" />
                <circle cx="'.explode(',', end($points))[0].'" cy="'.explode(',', end($points))[1].'" r="5" fill="#1e293b" stroke="#f59e0b" stroke-width="2" />
            </svg>';
            $svgTrend = 'data:image/svg+xml;base64,' . base64_encode($rawSvgTrend);
        }

        $svgPie = null;
        $rawLocs = (clone $query)->leftJoin('ff_client_branches', 'ff_inventory_movements.ff_client_branch_id', '=', 'ff_client_branches.id')
            ->select(DB::raw('COALESCE(ff_client_branches.name, ff_inventory_movements.locality, "General") as location'))
            ->get();
            
        $locData = $rawLocs->groupBy('location')->map->count()->sortDesc()->take(5);
        $totalLoc = $locData->sum();
        
        if ($totalLoc > 0) {
            $cx = 100; $cy = 100; $r = 90;
            $cumulative = 0; $paths = [];
            $colors = ['#1e293b', '#f59e0b', '#334155', '#d97706', '#94a3b8'];
            $idx = 0;
            foreach($locData as $name => $val) {
                $percent = $val / $totalLoc;
                if($percent > 0.999) {
                    $paths[] = '<circle cx="'.$cx.'" cy="'.$cy.'" r="'.$r.'" fill="'.$colors[0].'" />'; break;
                }
                $start = 2 * pi() * $cumulative;
                $end = 2 * pi() * ($cumulative + $percent);
                $x1 = $cx + $r * sin($start); $y1 = $cy - $r * cos($start);
                $x2 = $cx + $r * sin($end);   $y2 = $cy - $r * cos($end);
                $large = $percent > 0.5 ? 1 : 0;
                $paths[] = '<path d="M'.$cx.','.$cy.' L'.$x1.','.$y1.' A'.$r.','.$r.' 0 '.$large.',1 '.$x2.','.$y2.' Z" fill="'.$colors[$idx % 5].'" stroke="#ffffff" stroke-width="3"/>';
                $cumulative += $percent; $idx++;
            }
            $rawSvgPie = '<svg width="200" height="200" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">'.implode('', $paths).'
                <circle cx="100" cy="100" r="45" fill="#ffffff"/>
                <text x="100" y="105" text-anchor="middle" font-family="Arial" font-size="14" fill="#1e293b" font-weight="bold">'.number_format($totalLoc).'</text>
            </svg>';
            $svgPie = 'data:image/svg+xml;base64,' . base64_encode($rawSvgPie);
        }

        $topProducts = (clone $query)->select(
            'ff_products.sku', 'ff_products.description',
            DB::raw('SUM(ABS(ff_inventory_movements.quantity)) as cantidad'),
            DB::raw('SUM(ABS(ff_inventory_movements.quantity) * ff_products.unit_price) as monto')
        )->groupBy('ff_products.sku', 'ff_products.description')->orderByDesc('monto')->limit(5)->get();
        
        $maxProdVal = $topProducts->max('monto') ?: 1;
        foreach($topProducts as $p) $p->percent = ($p->monto / $maxProdVal) * 100;

        $daysData = (clone $query)->select(DB::raw('DAYOFWEEK(ff_inventory_movements.created_at) as dia'), DB::raw('COUNT(*) as total'))
            ->groupBy('dia')->pluck('total', 'dia')->toArray();
            
        $chartWeek = [];
        $maxDay = max($daysData) ?: 1;
        $labels = [1=>'Dom', 2=>'Lun', 3=>'Mar', 4=>'Mié', 5=>'Jue', 6=>'Vie', 7=>'Sáb'];
        for($i=2; $i<=7; $i++) $labels[$i-1] = $labels[$i]; $labels[7] = 'Dom';
        
        $mapDays = [1=>7, 2=>1, 3=>2, 4=>3, 5=>4, 6=>5, 7=>6]; 

        for($i=1; $i<=7; $i++) {
            $dbDay = array_search($i, $mapDays);
            $val = $daysData[$dbDay] ?? 0;
            $intensity = $maxDay > 0 ? ($val / $maxDay) : 0;
            $colorBg = $val > 0 ? "rgba(245, 158, 11, " . (0.15 + ($intensity * 0.85)) . ")" : "#f1f5f9";
            $colorTxt = $intensity > 0.6 ? "#ffffff" : "#334155";
            $chartWeek[] = ['label' => $labels[$i], 'val' => $val, 'color_bg' => $colorBg, 'color_txt' => $colorTxt];
        }

        $logoBase64 = null;
        try {
            $iconPath = Auth::user()->area->icon_path ?? 'LogoAzulm.PNG';
            if (Storage::disk('s3')->exists($iconPath)) {
                $logoBase64 = 'data:image/png;base64,' . base64_encode(Storage::disk('s3')->get($iconPath));
            }
        } catch (\Exception $e) {}

        $data = compact('clientName','baseMetrics','diasInactivo','antiguedad','svgTrend','svgPie','locData','topProducts','chartWeek','logoBase64','totalLoc');
        $data['report_date'] = now()->format('d F, Y');

        $pdf = Pdf::loadView('friends-and-family.reports.client-pdf', $data);
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOption('isRemoteEnabled', true); 
        $pdf->setOption('dpi', 120);
        
        return $pdf->stream("Executive_Report_" . Str::slug($clientName) . ".pdf");
    }

}