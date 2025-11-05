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
        $movements = ffInventoryMovement::where('quantity', '<', 0)
            ->with('product', 'user')
            ->orderBy('created_at', 'desc')
            ->paginate(50); 
            
        return view('friends-and-family.reports.transactions', compact('movements'));
    }
    
    public function reprintReceipt(ffInventoryMovement $movement)
    {
        if ($movement->quantity >= 0) {
            abort(404);
        }
        
        $product = ffProduct::find($movement->ff_product_id);

        $productPrice = $product ? $product->price : 0;
        $quantitySold = abs($movement->quantity);
        $userName = $movement->user ? $movement->user->name : 'N/A';

        $pdfData = [
            'items' => [[
                'description' => $product ? $product->description : 'Producto Eliminado (ID: ' . $movement->ff_product_id . ')',
                'quantity'    => $quantitySold,
                'unit_price'  => $productPrice,
                'total_price' => $productPrice * $quantitySold,
            ]],
            'grandTotal' => $productPrice * $quantitySold,
            'date' => $movement->created_at->format('d/m/Y H:i A'),
            'user' => $userName,
            'transaction_id' => $movement->id, 
        ];
        
        $pdfView = view('friends-and-family.sales.pdf', $pdfData); 
        $dompdf = new Dompdf();
        $dompdf->loadHtml($pdfView->render());
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        return response(
            $dompdf->output(), 
            200, 
            ['Content-Type' => 'application/pdf']
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
            ->with(['product:id,sku,description', 'user:id,name'])
            ->orderBy('created_at', 'desc')
            ->limit($limit);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $movements = $query->get();

        $data = $movements->map(function ($mov) {
            $productSku = $mov->product->sku ?? 'N/A';
            $quantity = abs($mov->quantity);
            $reason = 'Venta';
            
            $price = $mov->product->price ?? 0;
            $value = $price * $quantity;

            return [
                'time' => $mov->created_at->diffForHumans(),
                'value' => '$' . number_format($value, 2),
                'detail' => "$reason: $productSku ({$quantity} u.)",
                'icon' => 'money-bill',
            ];
        });

        return response()->json($data);
    }    

}