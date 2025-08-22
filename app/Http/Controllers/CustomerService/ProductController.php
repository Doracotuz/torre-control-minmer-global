<?php

namespace App\Http\Controllers\CustomerService;

use App\Http\Controllers\Controller;
use App\Models\CsProduct;
use App\Models\CsBrand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProductController extends Controller
{
    public function index()
    {
        $products = $this->getFilteredProducts(new Request())->paginate(25);
        $brands = CsBrand::orderBy('name')->get();
        return view('customer-service.products.index', compact('products', 'brands'));
    }

    public function filter(Request $request)
    {
        $products = $this->getFilteredProducts($request)->paginate(25);
        return response()->json([
            'table' => view('customer-service.products.partials.table', compact('products'))->render(),
        ]);
    }

    public function search(Request $request)
    {
        $searchTerm = $request->query('term', '');

        if (strlen($searchTerm) < 2) {
            return response()->json([]);
        }

        $products = \App\Models\CsProduct::where('sku', 'like', "%{$searchTerm}%")
            ->orWhere('description', 'like', "%{$searchTerm}%")
            ->limit(10) // Devolvemos solo los primeros 10 resultados para ser rápidos
            ->get(['id', 'sku', 'description']);

        return response()->json($products);
    }       

    public function create()
    {
        $brands = CsBrand::orderBy('name')->get();
        return view('customer-service.products.create', compact('brands'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'sku' => 'required|string|max:255|unique:cs_products,sku',
            'description' => 'required|string',
            'packaging_factor' => 'required|integer|min:1',
            'cs_brand_id' => 'required|string|max:255',
        ]);

        $brandIdentifier = $validatedData['cs_brand_id'];
        if (is_numeric($brandIdentifier)) {
            $brandId = $brandIdentifier;
        } else {
            // Si no es numérico, es un nombre nuevo. Lo creamos.
            $newBrand = CsBrand::firstOrCreate(['name' => $brandIdentifier]);
            $brandId = $newBrand->id;
        }


        CsProduct::create([
            'sku' => $validatedData['sku'],
            'description' => $validatedData['description'],
            'packaging_factor' => $validatedData['packaging_factor'],
            'cs_brand_id' => $brandId,
            'type' => $this->getProductType($validatedData['sku']),
            'created_by_user_id' => Auth::id(),
        ]);

        return redirect()->route('customer-service.products.index')->with('success', 'Producto creado exitosamente.');
    }

    public function edit(CsProduct $product)
    {
        $brands = CsBrand::orderBy('name')->get();
        return view('customer-service.products.edit', compact('product', 'brands'));
    }

    public function update(Request $request, CsProduct $product)
    {
        $validatedData = $request->validate([
            'sku' => ['required', 'string', 'max:255', Rule::unique('cs_products')->ignore($product->id)],
            'description' => 'required|string',
            'packaging_factor' => 'required|integer|min:1',
            'cs_brand_id' => 'required|string|max:255', // Se cambia a string
        ]);

        $brandIdentifier = $validatedData['cs_brand_id'];
        if (is_numeric($brandIdentifier)) {
            $brandId = $brandIdentifier;
        } else {
            $newBrand = CsBrand::firstOrCreate(['name' => $brandIdentifier]);
            $brandId = $newBrand->id;
        }        

        $product->update([
            'sku' => $validatedData['sku'],
            'description' => $validatedData['description'],
            'packaging_factor' => $validatedData['packaging_factor'],
            'cs_brand_id' => $brandId, // Se usa el ID final
            'type' => $this->getProductType($validatedData['sku']),
            'updated_by_user_id' => Auth::id(),
        ]);

        return redirect()->route('customer-service.products.index')->with('success', 'Producto actualizado exitosamente.');
    }

    public function destroy(CsProduct $product)
    {
        $product->delete();
        return redirect()->route('customer-service.products.index')->with('success', 'Producto eliminado exitosamente.');
    }

    public function exportCsv(Request $request)
    {
        $products = $this->getFilteredProducts($request)->get();
        
        $fileName = "export_productos_" . date('Y-m-d') . ".csv";
        $headers = [ "Content-type" => "text/csv", "Content-Disposition" => "attachment; filename=$fileName", "Pragma" => "no-cache", "Cache-Control" => "must-revalidate, post-check=0, pre-check=0", "Expires" => "0" ];

        $callback = function() use ($products) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['SKU', 'Descripcion', 'F Empaque', 'Marca', 'Tipo', 'Creado por', 'Fecha Creacion']);
            foreach ($products as $product) {
                fputcsv($file, [
                    $product->sku, $product->description, $product->packaging_factor,
                    $product->brand->name, $product->type,
                    $product->createdBy->name, $product->created_at->format('Y-m-d')
                ]);
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }

    private function getProductType(string $sku): string
    {
        $skuUpper = strtoupper($sku);
        if (str_starts_with($skuUpper, '5') || str_starts_with($skuUpper, '2')) {
            return 'Promocional';
        }
        return 'Producto';
    }

    public function importCsv(Request $request)
    {
        $request->validate(['csv_file' => 'required|mimes:csv,txt']);

        $path = $request->file('csv_file')->getRealPath();

        // --- INICIA CORRECCIÓN: Se convierte el archivo a UTF-8 ---
        $fileContent = file_get_contents($path);
        // Detecta la codificación actual y la convierte a UTF-8
        $utf8Content = mb_convert_encoding($fileContent, 'UTF-8', mb_detect_encoding($fileContent, 'UTF-8, ISO-8859-1', true));
        
        // Se crea un archivo temporal en memoria con el contenido corregido
        $file = fopen("php://memory", 'r+');
        fwrite($file, $utf8Content);
        rewind($file);
        // --- TERMINA CORRECCIÓN ---

        fgetcsv($file); // Omitir la cabecera

        $brands = CsBrand::pluck('id', 'name')->all();

        while (($row = fgetcsv($file, 1000, ",")) !== FALSE) {
            // Se asegura de que la fila no esté completamente vacía
            if (count(array_filter($row)) == 0) continue;

            $brandName = trim($row[3]);
            if (!isset($brands[$brandName])) continue;

            $sku = trim($row[0]);
            CsProduct::updateOrCreate(
                ['sku' => $sku],
                [
                    'description' => trim($row[1]),
                    'packaging_factor' => (int)trim($row[2]),
                    'cs_brand_id' => $brands[$brandName],
                    'type' => $this->getProductType($sku),
                    'created_by_user_id' => Auth::id(),
                ]
            );
        }
        fclose($file);
        return redirect()->route('customer-service.products.index')->with('success', 'Archivo CSV importado exitosamente.');
    }


    private function getFilteredProducts(Request $request)
    {
        $query = CsProduct::with('brand', 'createdBy');
        if ($request->filled('search') && strlen($request->search) > 1) {
            $searchTerm = '%' . $request->search . '%';
            $query->where(fn($q) => $q->where('sku', 'like', $searchTerm)->orWhere('description', 'like', $searchTerm));
        }
        if ($request->filled('brand_id')) { $query->where('cs_brand_id', $request->brand_id); }
        if ($request->filled('type')) { $query->where('type', $request->type); }
        return $query->orderBy('sku', 'asc');
    }

    public function dashboard()
    {
        // Gráfico 1: Productos por Tipo
        $productsByType = CsProduct::select('type', DB::raw('count(*) as total'))
            ->groupBy('type')
            ->pluck('total', 'type');
        
        // Gráfico 2: Productos por Marca (Top 10)
        $productsByBrand = CsProduct::join('cs_brands', 'cs_products.cs_brand_id', '=', 'cs_brands.id')
            ->select('cs_brands.name', DB::raw('count(cs_products.id) as total'))
            ->groupBy('cs_brands.name')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->pluck('total', 'name');

        // Gráfico 3: Productos creados en los últimos 30 días
        $recentProducts = CsProduct::where('created_at', '>=', Carbon::now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get([
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            ]);
        
        // Gráfico 4: Top 5 Marcas con más productos
        $topBrands = $productsByBrand->take(5);

        $chartData = [
            'productsByType' => [
                'labels' => $productsByType->keys(),
                'data' => $productsByType->values(),
            ],
            'productsByBrand' => [
                'labels' => $productsByBrand->keys(),
                'data' => $productsByBrand->values(),
            ],
            'recentProducts' => [
                'labels' => $recentProducts->map(fn($item) => Carbon::parse($item->date)->format('d M')),
                'data' => $recentProducts->pluck('count'),
            ],
            'topBrands' => [
                'labels' => $topBrands->keys(),
                'data' => $topBrands->values(),
            ]
        ];

        return view('customer-service.products.dashboard', compact('chartData'));
    }

    public function downloadTemplate()
    {
        $headers = [ "Content-type" => "text/csv", "Content-Disposition" => "attachment; filename=plantilla_productos.csv" ];
        $callback = function() {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['SKU', 'Descripcion', 'F Empaque', 'Marca']);
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }


}
