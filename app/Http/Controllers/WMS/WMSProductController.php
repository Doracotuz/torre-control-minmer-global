<?php

namespace App\Http\Controllers\WMS;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Brand;
use App\Models\ProductType;
use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class WMSProductController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = Auth::user();
            $routeName = $request->route()->getName();

            if ($request->routeIs('wms.products.index') || $request->routeIs('wms.products.export') || $request->routeIs('wms.products.template') || $request->routeIs('wms.products.catalogs')) {
                if (!$user->hasFfPermission('wms.products.view')) {
                    abort(403, 'No tienes permiso para ver productos WMS.');
                }
            } elseif ($request->routeIs('wms.products.create') || $request->routeIs('wms.products.store') || $request->routeIs('wms.products.import')) {
                if (!$user->hasFfPermission('wms.products.create')) {
                    abort(403, 'No tienes permiso para crear productos WMS.');
                }
            } elseif ($request->routeIs('wms.products.edit') || $request->routeIs('wms.products.update')) {
                if (!$user->hasFfPermission('wms.products.edit')) {
                    abort(403, 'No tienes permiso para editar productos WMS.');
                }
            } elseif ($request->routeIs('wms.products.destroy')) {
                if (!$user->hasFfPermission('wms.products.delete')) {
                    abort(403, 'No tienes permiso para eliminar productos WMS.');
                }
            }

            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $query = Product::with(['brand', 'productType', 'area'])->latest();

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

        if ($request->filled('area_id')) {
            $query->where('area_id', $request->area_id);
        }        

        $products = $query->paginate(15)->withQueryString();

        $brands = Brand::orderBy('name')->get();
        $productTypes = ProductType::orderBy('name')->get();
        $areas = Area::orderBy('name')->get();

        return view('wms.products.index', compact('products', 'brands', 'productTypes', 'areas'));
    }

    public function create()
    {
        $brands = Brand::orderBy('name')->get();
        $productTypes = ProductType::orderBy('name')->get();
        $areas = Area::orderBy('name')->get();
        return view('wms.products.create', compact('brands', 'productTypes', 'areas'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'sku' => 'required|string|max:255|unique:products,sku',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'pieces_per_case' => 'nullable|integer|min:1',
            'brand_id' => 'nullable|exists:brands,id',
            'product_type_id' => 'nullable|exists:product_types,id',
            'area_id' => 'required|exists:areas,id',
            'unit_of_measure' => 'required|string|max:50',
            'length' => 'nullable|numeric|min:0',
            'width' => 'nullable|numeric|min:0',
            'height' => 'nullable|numeric|min:0',
            'weight' => 'nullable|numeric|min:0',
            'upc' => 'nullable|string|max:255|unique:products,upc',
        ]);

        $validatedData['pieces_per_case'] = $validatedData['pieces_per_case'] ?? 1;
        Product::create($validatedData);

        return redirect()->route('wms.products.index')
                                ->with('success', 'Producto creado exitosamente.');
    }

    public function edit(Product $product)
    {
        $brands = Brand::orderBy('name')->get();
        $productTypes = ProductType::orderBy('name')->get();
        $areas = Area::orderBy('name')->get();
        return view('wms.products.edit', compact('product', 'brands', 'productTypes', 'areas'));
    }

    public function update(Request $request, Product $product)
    {
        $validatedData = $request->validate([
            'sku' => 'required|string|max:255|unique:products,sku,' . $product->id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'pieces_per_case' => 'nullable|integer|min:1',
            'brand_id' => 'nullable|exists:brands,id',
            'product_type_id' => 'nullable|exists:product_types,id',
            'area_id' => 'required|exists:areas,id',
            'unit_of_measure' => 'required|string|max:50',
            'length' => 'nullable|numeric|min:0',
            'width' => 'nullable|numeric|min:0',
            'height' => 'nullable|numeric|min:0',
            'weight' => 'nullable|numeric|min:0',
            'upc' => 'nullable|string|max:255|unique:products,upc,' . $product->id,
        ]);

        $validatedData['pieces_per_case'] = $validatedData['pieces_per_case'] ?? 1;
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
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=plantilla_productos.csv',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $columns = [
            'sku', 
            'name', 
            'description', 
            'brand_name', 
            'type_name', 
            'unit_of_measure', 
            'upc', 
            'length', 
            'width', 
            'height', 
            'weight', 
            'pieces_per_case'
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
        $request->validate([
            'file' => 'required|mimes:csv,txt',
            'area_id' => 'required|exists:areas,id',
        ]);

        $file = $request->file('file');
        $areaId = $request->input('area_id');
        
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
                    'area_id' => $areaId,
                    'unit_of_measure' => $rowData['unidad_de_empaque'] ?? 'Pieza',
                    'pieces_per_case' => (int)($rowData['piezas_por_caja'] ?? 1) ?: 1,
                    'length' => (float)($rowData['largo_cm'] ?? 0) ?: null,
                    'width' => (float)($rowData['ancho_cm'] ?? 0) ?: null,
                    'height' => (float)($rowData['alto_cm'] ?? 0) ?: null,
                    'weight' => (float)($rowData['peso_kg'] ?? 0) ?: null,
                    'description' => $rowData['descripcion'] ?? null,
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
            
            $successMessage = "$countSuccess productos nuevos fueron importados exitosamente para el cliente seleccionado.";

            if (!empty($warnings)) {
                return redirect()->route('wms.products.index')
                                ->with('success', $successMessage)
                                ->with('warning', "Se omitieron " . count($warnings) . " líneas por problemas de formato.");
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
        $query = Product::with(['brand', 'productType', 'area'])->latest();

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

        if ($request->filled('area_id')) {
            $query->where('area_id', $request->area_id);
        }

        $products = $query->get();

        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=productos_export_' . date('Y-m-d') . '.csv',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function() use ($products) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, [
                'Area/Cliente', 'SKU', 'Nombre', 'Descripción', 'Marca', 'Tipo', 
                'UPC', 'Unidad', 'Largo', 'Ancho', 'Alto', 'Peso', 'Piezas x Caja'
            ]);

            foreach ($products as $product) {
                fputcsv($file, [
                    $product->area->name ?? 'N/A',
                    $product->sku,
                    $product->name,
                    $product->description,
                    $product->brand->name ?? '',
                    $product->productType->name ?? '',
                    $product->upc,
                    $product->unit_of_measure,
                    $product->length,
                    $product->width,
                    $product->height,
                    $product->weight,
                    $product->pieces_per_case
                ]);
            }
            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }

    public function getCatalogsByArea(Request $request)
    {
        $areaId = $request->area_id;
        
        if (!$areaId) {
            return response()->json(['brands' => [], 'types' => []]);
        }

        $brands = Brand::where('area_id', $areaId)->orderBy('name')->get(['id', 'name']);
        $types = ProductType::where('area_id', $areaId)->orderBy('name')->get(['id', 'name']);

        return response()->json([
            'brands' => $brands,
            'types' => $types
        ]);
    }    

}