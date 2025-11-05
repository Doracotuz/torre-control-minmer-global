<?php

namespace App\Http\Controllers\FriendsAndFamily;

use App\Http\Controllers\Controller;
use App\Models\ffProduct;
use App\Models\ffInventoryMovement;
use App\Models\ffCartItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Dompdf\Dompdf;
use Illuminate\Support\Carbon;

class FfReportController extends Controller
{
    public function index(Request $request)
    {
        $userIdFilter = $request->input('user_id');

        $baseQuery = ffInventoryMovement::where('quantity', '<', 0);
        
        if ($userIdFilter) {
            $baseQuery->where('user_id', $userIdFilter);
        }

        $ventasCompletas = (clone $baseQuery)
            ->join('ff_products', 'ff_inventory_movements.ff_product_id', '=', 'ff_products.id')
            ->select(
                DB::raw('SUM(ABS(ff_inventory_movements.quantity) * ff_products.price) as valor_total_vendido'),
                DB::raw('SUM(ABS(ff_inventory_movements.quantity)) as total_unidades_vendidas')
            )
            ->first();

        $totalUnidadesVendidas = (int) $ventasCompletas->total_unidades_vendidas;
        $valorTotalVendido = (float) $ventasCompletas->valor_total_vendido;

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
        
        $stockAgotadoCount = ffProduct::withSum('movements', 'quantity')
            ->get()
            ->filter(fn ($p) => ($p->movements_sum_quantity ?? 0) <= 0)
            ->count();
            
        $durationDays = 2;
        $firstMovement = ffInventoryMovement::min('created_at');
        
        $startDate = Carbon::parse('2025-11-14')->startOfDay();
        $endDate = $startDate->copy()->addDays($durationDays);
        
        $diff = now()->diff($endDate);

        $daysRemainingFormatted = '0 Días';
        if (now()->lessThan($endDate)) {
            $days = $diff->days;
            $hours = $diff->h;
            $minutes = $diff->i;
            $daysRemainingFormatted = "{$days}d {$hours}h {$minutes}m";
        } elseif (now()->greaterThan($endDate)) {
            $daysRemainingFormatted = "FINALIZADO";
        }


        $vendedores = User::whereHas('movements', function ($query) {
                $query->where('quantity', '<', 0);
            })->orderBy('name')->get(['id', 'name']);
        
        return view('friends-and-family.reports.index', compact(
            'totalUnidadesVendidas',
            'valorTotalVendido',
            'stockAgotadoCount',
            'daysRemainingFormatted',
            'vendedores',
            'userIdFilter',
            'chartTopProductos',
            'chartVentasVendedor'
        ));
    }

    public function transactions(Request $request)
    {
        $vendedores = \App\Models\User::whereHas('movements', function ($query) {
            $query->where('quantity', '<', 0);
        })->orderBy('name')->get();

        $userIdFilter = $request->input('vendedor_id');
        $search = $request->input('search');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $query = ffInventoryMovement::where('quantity', '<', 0)
            ->join('ff_products', 'ff_inventory_movements.ff_product_id', '=', 'ff_products.id')
            ->select(
                'ff_inventory_movements.folio',
                'ff_inventory_movements.user_id',
                'ff_inventory_movements.client_name',
                'ff_inventory_movements.surtidor_name',
                DB::raw('MAX(ff_inventory_movements.created_at) as created_at'),
                DB::raw('COUNT(ff_inventory_movements.id) as total_items'),
                DB::raw('SUM(ABS(ff_inventory_movements.quantity)) as total_units'),
                DB::raw('SUM(ABS(ff_inventory_movements.quantity) * ff_products.price) as total_value')
            )
            ->groupBy(
                'ff_inventory_movements.folio', 
                'ff_inventory_movements.user_id', 
                'ff_inventory_movements.client_name', 
                'ff_inventory_movements.surtidor_name'
            );

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
            ->with('user')
            ->paginate(50)
            ->withQueryString();

        return view('friends-and-family.reports.transactions', compact(
            'sales', 
            'vendedores', 
            'userIdFilter', 
            'search', 
            'startDate', 
            'endDate'
        ));
    }
    
    public function reprintReceipt(ffInventoryMovement $movement)
    {
        $saleMovements = ffInventoryMovement::where('folio', $movement->folio)
            ->where('quantity', '<', 0)
            ->with(['product', 'user'])
            ->get();

        if ($saleMovements->isEmpty()) {
            abort(404, 'No se encontraron movimientos para reimprimir el recibo.');
        }

        $firstMovement = $saleMovements->first();
        $user = $firstMovement->user;

        $pdfData = [
            'items' => [],
            'grandTotal' => 0,
            'copies' => ['Original', 'Copia Cliente', 'Copia Almacén'],
            'folio' => $firstMovement->folio,
        ];

        foreach ($saleMovements as $item) {
            $product = $item->product;
            $quantity = abs($item->quantity);
            $totalItem = $product->price * $quantity;

            $pdfData['items'][] = [
                'sku' => $product->sku,
                'description' => $product->description,
                'quantity' => $quantity,
                'unit_price' => $product->price,
                'total_price' => $totalItem,
            ];
            $pdfData['grandTotal'] += $totalItem;
        }
        
        $pdfData['date'] = $firstMovement->created_at->format('d/m/Y H:i:s');
        $pdfData['client_name'] = $firstMovement->client_name;
        $pdfData['surtidor_name'] = $firstMovement->surtidor_name;
        $pdfData['vendedor_name'] = $user->name ?? 'N/A';

        $dompdf = new Dompdf();
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
    
    public function inventoryAnalysis()
    {
        $movementReasons = ffInventoryMovement::select(
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
        
        $rotationProducts = ffProduct::withSum('movements as total_stock', 'quantity')
            ->join('ff_inventory_movements', 'ff_products.id', '=', 'ff_inventory_movements.ff_product_id')
            ->select(
                'ff_products.sku',
                'ff_products.price',
                DB::raw('SUM(CASE WHEN ff_inventory_movements.quantity < 0 THEN ABS(ff_inventory_movements.quantity) ELSE 0 END) as total_vendido')
            )
            ->groupBy('ff_products.id', 'ff_products.sku', 'ff_products.price')
            ->having('total_vendido', '>', 0)
            ->get();
            
        $chartRotation = [
            'series' => [[
                'name' => 'Rotación',
                'data' => $rotationProducts->map(function($p) {
                    return [
                        'x' => (float) $p->price,
                        'y' => (int) $p->total_stock,
                        'z' => (int) $p->total_vendido,
                        'label' => $p->sku,
                    ];
                })->toArray()
            ]]
        ];

        return view('friends-and-family.reports.inventory-analysis', compact(
            'chartMovementReasons',
            'chartRotation'
        ));
    }

    public function stockAvailability()
    {
        $products = ffProduct::where('is_active', true)
            ->withSum('movements', 'quantity')
            ->withSum('cartItems', 'quantity')
            ->get();
            
        $data = $products->map(function($product) {
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
        })->sortByDesc('total_stock');

        $categories = $data->pluck('sku')->take(10)->toArray();
        $stockData = $data->pluck('total_stock')->take(10)->toArray();
        $reservedData = $data->pluck('total_reserved')->take(10)->toArray();
        $availableData = $data->pluck('available')->take(10)->toArray();

        $chartStockVsReserved = [
            'series' => [
                ['name' => 'Stock Disponible', 'data' => $availableData],
                ['name' => 'Reservado (Carrito)', 'data' => $reservedData],
            ],
            'categories' => $categories,
        ];
        
        $lowStockAlerts = $data->filter(fn ($p) => $p['available'] > 0 && $p['available'] < 10);


        return view('friends-and-family.reports.stock-availability', compact(
            'data',
            'chartStockVsReserved',
            'lowStockAlerts'
        ));
    }

    public function catalogAnalysis()
    {
        $products = ffProduct::select('price', 'brand', 'type', 'is_active')->get();
        
        $priceRanges = [
            '0-100' => $products->whereBetween('price', [0, 100])->count(),
            '101-300' => $products->whereBetween('price', [101, 300])->count(),
            '301-500' => $products->whereBetween('price', [301, 500])->count(),
            '501+' => $products->where('price', '>', 500)->count(),
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


        return view('friends-and-family.reports.catalog-analysis', compact(
            'chartPriceDistribution',
            'chartActiveInactive',
            'chartBrand'
        ));
    }

    public function sellerPerformance()
    {
        $vendedores = User::select('id', 'name')->whereHas('cartItems')->orWhereHas('movements')->get();
        
        $sellerPerformanceData = $vendedores->map(function ($user) {
            
            $salesData = ffInventoryMovement::where('user_id', $user->id)
                ->where('quantity', '<', 0)
                ->join('ff_products', 'ff_inventory_movements.ff_product_id', '=', 'ff_products.id')
                ->select(
                    DB::raw('COUNT(ff_inventory_movements.id) as total_pedidos'),
                    DB::raw('SUM(ABS(ff_inventory_movements.quantity)) as total_unidades'),
                    DB::raw('SUM(ABS(ff_inventory_movements.quantity) * ff_products.price) as valor_total'),
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


        return view('friends-and-family.reports.seller-performance', compact(
            'sellerPerformanceData',
            'chartValorVendedor'
        ));
    }

    public function apiGetRecentMovements(Request $request)
    {
        $userId = $request->input('user_id');
        $limit = $request->input('limit', 10);

        $query = ffInventoryMovement::where('quantity', '<', 0)
            ->join('ff_products', 'ff_inventory_movements.ff_product_id', '=', 'ff_products.id')
            ->select(
                'ff_inventory_movements.folio',
                'ff_inventory_movements.user_id',
                'ff_inventory_movements.created_at',
                DB::raw('COUNT(ff_inventory_movements.id) as total_items'),
                DB::raw('SUM(ABS(ff_inventory_movements.quantity)) as total_units'),
                DB::raw('SUM(ABS(ff_inventory_movements.quantity) * ff_products.price) as total_value')
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
            $firstMovement = ffInventoryMovement::where('folio', $sale->folio)->first();
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
        $movements = ffInventoryMovement::where('folio', $folio)
            ->where('quantity', '<', 0)
            ->with('product:id,sku,description,price')
            ->get();

        $items = $movements->map(function ($mov) {
            $product = $mov->product;
            $quantity = abs($mov->quantity);
            $price = $product->price ?? 0;
            
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

}