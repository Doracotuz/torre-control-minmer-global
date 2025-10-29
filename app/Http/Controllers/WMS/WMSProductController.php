<?php

namespace App\Http\Controllers\WMS;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Brand;
use App\Models\ProductType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class WMSProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['brand', 'productType'])->latest();

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('sku', 'like', "%{$searchTerm}%")
                  ->orWhere('name', 'like', "%{$searchTerm}%")
                  ->orWhere('upc', 'like', "%{$searchTerm}%");
            });
        }

        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }

        if ($request->filled('product_type_id')) {
            $query->where('product_type_id', $request->product_type_id);
        }

        $products = $query->paginate(15)->withQueryString();

        $brands = Brand::orderBy('name')->get();
        $productTypes = ProductType::orderBy('name')->get();

        return view('wms.products.index', compact('products', 'brands', 'productTypes'));
    }

    public function create()
    {
        $brands = Brand::orderBy('name')->get();
        $productTypes = ProductType::orderBy('name')->get();
        return view('wms.products.create', compact('brands', 'productTypes'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'sku' => 'required|string|max:255|unique:products,sku',
            'name' => 'required|string|max:255',
            'pieces_per_case' => 'nullable|integer|min:1',
            'brand_id' => 'nullable|exists:brands,id',
            'product_type_id' => 'nullable|exists:product_types,id',
            'unit_of_measure' => 'required|string|max:50',
            'length' => 'nullable|numeric|min:0',
            'width' => 'nullable|numeric|min:0',
            'height' => 'nullable|numeric|min:0',
            'weight' => 'nullable|numeric|min:0',
            'upc' => 'nullable|string|max:255|unique:products,upc',
        ]);

        Product::create($validatedData);

        return redirect()->route('wms.products.index')
                                ->with('success', 'Producto creado exitosamente.');
    }

    public function edit(Product $product)
    {
        $brands = Brand::orderBy('name')->get();
        $productTypes = ProductType::orderBy('name')->get();
        return view('wms.products.edit', compact('product', 'brands', 'productTypes'));
    }

    public function update(Request $request, Product $product)
    {
        $validatedData = $request->validate([
            'sku' => 'required|string|max:255|unique:products,sku,' . $product->id,
            'name' => 'required|string|max:255',
            'pieces_per_case' => 'nullable|integer|min:1',
            'brand_id' => 'nullable|exists:brands,id',
            'product_type_id' => 'nullable|exists:product_types,id',
            'unit_of_measure' => 'required|string|max:50',
            'length' => 'nullable|numeric|min:0',
            'width' => 'nullable|numeric|min:0',
            'height' => 'nullable|numeric|min:0',
            'weight' => 'nullable|numeric|min:0',
            'upc' => 'nullable|string|max:255|unique:products,upc,' . $product->id,
        ]);

        $product->update($validatedData);

        return redirect()->route('wms.products.index')
                                ->with('success', 'Producto actualizado exitosamente.');
    }

    public function destroy(Product $product)
    {
        try {
            $product->delete();
            return redirect()->route('wms.products.index')
                             ->with('success', 'Producto eliminado exitosamente.');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->route('wms.products.index')
                             ->with('error', 'No se puede eliminar el producto porque tiene inventario o está en un pedido.');
        }
    }

    public function downloadTemplate()
    {
        $headers = [
            'Content-type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename=plantilla_productos.csv',
        ];

        $columns = [
            'sku (requerido)', 'nombre_producto (requerido)', 'upc', 
            'nombre_marca', 'nombre_tipo_producto', 
            'unidad_de_empaque (ej: Caja, Pieza)', 'piezas_por_caja',
            'largo_cm', 'ancho_cm', 'alto_cm', 'peso_kg'
        ];

        $callback = function() use ($columns) {
            $file = fopen('php://output', 'w');
            
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($file, $columns);
            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }

    public function importCsv(Request $request)
    {
        $request->validate(['file' => 'required|mimes:csv,txt']);

        $file = $request->file('file');
        
        $content = file_get_contents($file->getRealPath());
        $utf8Content = mb_convert_encoding($content, 'UTF-8', mb_detect_encoding($content, 'UTF-8, ISO-8859-1', true));
        $lines = preg_split('/(\r\n|\n|\r)/', $utf8Content, -1, PREG_SPLIT_NO_EMPTY);
        
        if (count($lines) < 2) {
            return back()->with('error', 'El archivo está vacío o solo contiene encabezados.');
        }

        $headerLine = array_shift($lines);
        $headerLine = str_replace('ï»¿', '', $headerLine);
        
        $delimiter = str_contains($headerLine, ';') ? ';' : ','; 
        $csvHeaders = str_getcsv($headerLine, $delimiter);
        
        $headers = array_map(function($h) {
            $parts = explode('(', $h, 2); $h = $parts[0]; 
            $h = trim($h); $h = strtolower($h);
            $h = str_replace([' ', '.'], '_', $h);
            return $h;
        }, $csvHeaders);

        $countSuccess = 0;
        $errors = [];
        $warnings = [];
        $existingSkus = Product::pluck('sku')->toArray();
        $skusInFile = []; 

        DB::beginTransaction();
        try {
            foreach ($lines as $index => $row) {
                $lineNumber = $index + 2;
                if (empty(trim($row))) continue; 

                $data = str_getcsv($row, $delimiter);
                
                if (empty(trim(implode('', $data)))) continue;

                if (count($headers) !== count($data)) {
                    $warnings[] = "Línea $lineNumber: Se omitió (número de columnas incorrecto: ".count($data)." encontradas, ".count($headers)." esperadas).";
                    continue;
                }

                $trimmedData = array_map('trim', $data);
                
                try {
                    $rowData = array_combine($headers, $trimmedData);
                } catch (\Exception $e) {
                     $errors[] = "Error en la línea $lineNumber: Falla al combinar encabezados y datos.";
                     continue;
                }

                if (empty($rowData['sku']) && empty($rowData['nombre_producto'])) {
                    continue;
                }

                $validator = Validator::make($rowData, [
                    'sku' => 'required|string',
                    'nombre_producto' => 'required|string',
                ]);

                if ($validator->fails()) {
                    $errors[] = "Error en la línea $lineNumber: ".$validator->errors()->first();
                    continue;
                }
                
                $sku = $rowData['sku']; 
                if (in_array($sku, $existingSkus)) {
                    $errors[] = "Error en la línea $lineNumber: El SKU '$sku' ya existe en la base de datos.";
                    continue;
                }
                if (in_array($sku, $skusInFile)) {
                    $errors[] = "Error en la línea $lineNumber: El SKU '$sku' está duplicado en este mismo archivo.";
                    continue;
                }

                $brand = null;
                if (!empty($rowData['nombre_marca'])) {
                    $brand = Brand::firstOrCreate(['name' => $rowData['nombre_marca']]);
                }
                $productType = null;
                if (!empty($rowData['nombre_tipo_producto'])) {
                    $productType = ProductType::firstOrCreate(['name' => $rowData['nombre_tipo_producto']]);
                }

                Product::create([
                    'sku' => $sku,
                    'name' => $rowData['nombre_producto'],
                    'upc' => $rowData['upc'] ?? null,
                    'brand_id' => $brand ? $brand->id : null,
                    'product_type_id' => $productType ? $productType->id : null,
                    'unit_of_measure' => $rowData['unidad_de_empaque'] ?? 'Pieza',
                    'pieces_per_case' => (int)($rowData['piezas_por_caja'] ?? 1) ?: 1,
                    'length' => (float)($rowData['largo_cm'] ?? 0) ?: null,
                    'width' => (float)($rowData['ancho_cm'] ?? 0) ?: null,
                    'height' => (float)($rowData['alto_cm'] ?? 0) ?: null,
                    'weight' => (float)($rowData['peso_kg'] ?? 0) ?: null,
                ]);

                $existingSkus[] = $sku;
                $skusInFile[] = $sku;
                $countSuccess++;
            }

            if (!empty($errors)) {
                DB::rollBack();
                return back()
                    ->with('error', "La importación falló. Se encontraron " . count($errors) . " errores de datos. No se guardó ningún producto.")
                    ->with('import_errors', $errors)
                    ->with('import_warnings', $warnings);
            }

            DB::commit();
            
            $successMessage = "$countSuccess productos nuevos fueron importados exitosamente.";

            if (!empty($warnings)) {
                return redirect()->route('wms.products.index')
                                ->with('success', $successMessage)
                                ->with('warning', "Se omitieron " . count($warnings) . " líneas por problemas de formato (ej. líneas en blanco al final).");
            }
            
            return redirect()->route('wms.products.index')
                            ->with('success', $successMessage);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Ocurrió un error inesperado durante la importación: ' . $e->getMessage());
        }
    }

    public function exportCsv(Request $request)
    {
        $fileName = 'reporte_productos_' . date('Y-m-d') . '.csv';

        $query = Product::with(['brand', 'productType'])->latest();

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('sku', 'like', "%{$searchTerm}%")
                  ->orWhere('name', 'like', "%{$searchTerm}%")
                  ->orWhere('upc', 'like', "%{$searchTerm}%");
            });
        }
        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }
        if ($request->filled('product_type_id')) {
            $query->where('product_type_id', $request->product_type_id);
        }

        $products = $query->get();

        $headers = [
            'Content-type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=$fileName",
        ];

        $callback = function() use ($products) {
            $file = fopen('php://output', 'w');
            
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, [
                'SKU', 'Nombre', 'UPC', 'Marca', 'Tipo de Producto',
                'Unidad de Empaque', 'Piezas por Caja',
                'Largo (cm)', 'Ancho (cm)', 'Alto (cm)', 'Peso (kg)', 'Volumen (m³)'
            ]);

            foreach ($products as $product) {
                fputcsv($file, [
                    $product->sku,
                    $product->name,
                    $product->upc ?? '',
                    $product->brand->name ?? 'N/A',
                    $product->productType->name ?? 'N/A',
                    $product->unit_of_measure,
                    $product->pieces_per_case,
                    $product->length,
                    $product->width,
                    $product->height,
                    $product->weight,
                    $product->volume
                ]);
            }
            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }

}