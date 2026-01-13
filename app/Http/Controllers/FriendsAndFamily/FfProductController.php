<?php

namespace App\Http\Controllers\FriendsAndFamily;

use App\Http\Controllers\Controller;
use App\Models\ffProduct;
use App\Models\FfSalesChannel;
use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use ZipArchive;
use Barryvdh\DomPDF\Facade\Pdf;

class FfProductController extends Controller
{
    public function index()
    {
        $products = ffProduct::with('channels')->orderBy('description')->get();
        $channels = FfSalesChannel::where('is_active', true)->orderBy('name')->get();
        
        $areas = [];
        if (Auth::user()->isSuperAdmin()) { 
            $areas = Area::orderBy('name')->get();
        }

        return view('friends-and-family.catalog.index', compact('products', 'channels', 'areas'));
    }

    private function applyFilters($query, Request $request)
    {
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('sku', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%")
                  ->orWhere('upc', 'like', "%{$search}%");
            });
        }

        if ($request->filled('brand')) {
            $query->where('brand', $request->input('brand'));
        }
        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $isActive = $request->input('status') === 'active';
            $query->where('is_active', $isActive);
        }

        if ($request->filled('channel')) {
            $query->whereHas('channels', function($q) use ($request) {
                $q->where('ff_sales_channels.id', $request->input('channel'));
            });
        }

        if (Auth::user()->isSuperAdmin() && $request->filled('area_id')) {
            $query->where('area_id', $request->input('area_id'));
        }

        return $query;
    }

    private function getLogoUrl($areaId)
    {
        if ($areaId) {
            $area = Area::find($areaId);
            if ($area && $area->icon_path) {
                return Storage::disk('s3')->url($area->icon_path);
            }
        }
        return Storage::disk('s3')->url('logoConsorcioMonter.png');
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'sku' => [
                'required', 
                'string', 
                'max:255', 
                Rule::unique('ff_products')->where(function ($query) use ($user) {
                    return $query->where('area_id', $user->area_id);
                })
            ],
            'description' => 'required|string|max:500',
            'unit_price' => 'required|numeric|min:0',
            'brand' => 'nullable|string|max:255',
            'type' => 'nullable|string|max:255',
            'pieces_per_box' => 'nullable|integer|min:0',
            'length' => 'nullable|numeric|min:0',
            'width' => 'nullable|numeric|min:0',
            'height' => 'nullable|numeric|min:0',
            'upc' => 'nullable|string|max:255',
            'channels' => 'nullable|array',
            'channels.*' => 'exists:ff_sales_channels,id',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:20048',
        ]);

        $data = $validated;
        $data['is_active'] = $request->boolean('is_active', true);

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('ff_catalog_photos', 's3');
            $data['photo_path'] = $path;
        }

        $product = ffProduct::create($data);
        
        if (!empty($request->channels)) {
            $product->channels()->sync($request->channels);
        }
        
        $product->load('channels');

        return response()->json($product->fresh(), 201); 
    }

    public function update(Request $request, ffProduct $catalog)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'sku' => [
                'required', 
                'string', 
                'max:255', 
                Rule::unique('ff_products')->ignore($catalog->id)->where(function ($query) use ($user) {
                    return $query->where('area_id', $user->area_id);
                })
            ],
            'description' => 'required|string|max:500',
            'unit_price' => 'required|numeric|min:0',
            'brand' => 'nullable|string|max:255',
            'type' => 'nullable|string|max:255',
            'pieces_per_box' => 'nullable|integer|min:0',
            'length' => 'nullable|numeric|min:0',
            'width' => 'nullable|numeric|min:0',
            'height' => 'nullable|numeric|min:0',
            'upc' => 'nullable|string|max:255',
            'channels' => 'nullable|array',
            'channels.*' => 'exists:ff_sales_channels,id',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:20048',
            'is_active' => 'required|boolean',
        ]);

        $data = $validated;
        $data['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('photo')) {
            if ($catalog->photo_path) {
                Storage::disk('s3')->delete($catalog->photo_path);
            }
            $path = $request->file('photo')->store('ff_catalog_photos', 's3');
            $data['photo_path'] = $path;
        }

        $catalog->update($data);

        if (isset($request->channels)) {
            $catalog->channels()->sync($request->channels);
        }
        
        $catalog->load('channels');

        return response()->json($catalog->fresh());
    }

    public function destroy(ffProduct $catalog)
    {
        $catalog->delete();

        return response()->json(['message' => 'Producto eliminado permanentemente'], 204);
    }

    public function downloadTemplate()
    {
        $products = ffProduct::orderBy('sku')->with('channels')->get();

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="plantilla_gestion_catalogo_'.date('Y-m-d').'.csv"',
        ];

        $callback = function() use ($products) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF");
            
            fputcsv($file, [
                'SKU (No cambiar)', 'Descripción', 'Precio Unitario', 'Marca', 'Tipo', 
                'Piezas por Caja', 'Largo', 'Ancho', 'Alto', 'UPC', 
                'Nombre Archivo Foto', 'Canales de Venta (Separar con |)'
            ]);

            foreach ($products as $product) {
                $channelsStr = $product->channels->pluck('name')->implode('|');
                
                fputcsv($file, [
                    $product->sku,
                    $product->description,
                    $product->unit_price,
                    $product->brand,
                    $product->type,
                    $product->pieces_per_box,
                    $product->length,
                    $product->width,
                    $product->height,
                    $product->upc,
                    '',
                    $channelsStr
                ]);
            }
            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }

    public function import(Request $request)
    {
        $request->validate([
            'product_file' => 'required|file|mimes:csv,txt',
            'image_zip'    => 'nullable|file|mimetypes:application/zip,application/x-zip-compressed,application/x-zip,application/octet-stream,multipart/x-zip',
        ]);

        $csvPath = $request->file('product_file')->getPathname();
        $tempZipDir = null;
        
        if ($request->hasFile('image_zip')) {
            $zipPath = $request->file('image_zip')->getPathname();
            $tempZipDir = storage_path('app/temp/' . uniqid('zip_'));
            if (!mkdir($tempZipDir, 0777, true)) {
                return response()->json(['message' => 'Error: No se pudo crear directorio temporal.'], 500);
            }
            
            $zip = new \ZipArchive;
            if ($zip->open($zipPath) === TRUE) {
                $zip->extractTo($tempZipDir);
                $zip->close();
            } else {
                return response()->json(['message' => 'Error: El archivo ZIP es inválido.'], 422);
            }
        }

        $missingPhotos = [];
        $importCount = 0;

        try {
            if (($handle = fopen($csvPath, 'r')) === FALSE) {
                throw new \Exception('No se pudo abrir el archivo CSV.');
            }

            fgetcsv($handle);

            DB::beginTransaction();

            while (($row = fgetcsv($handle)) !== FALSE) {
                $sku = mb_convert_encoding(trim($row[0] ?? ''), 'UTF-8', 'ISO-8859-1');
                if (empty($sku)) continue;

                $channelsStr = mb_convert_encoding(trim($row[11] ?? ''), 'UTF-8', 'ISO-8859-1');
                $channelIds = [];
                
                if (!empty($channelsStr)) {
                    $channelNames = preg_split('/[,|]/', $channelsStr); 
                    foreach($channelNames as $name) {
                        $name = trim($name);
                        if(empty($name)) continue;
                        
                        $channel = FfSalesChannel::firstOrCreate(
                            ['name' => $name],
                            ['is_active' => true]
                        );
                        $channelIds[] = $channel->id;
                    }
                }

                $productData = [
                    'description'    => mb_convert_encoding(trim($row[1] ?? ''), 'UTF-8', 'ISO-8859-1'),
                    'unit_price'     => (float)($row[2] ?? 0.00),
                    'brand'          => mb_convert_encoding(trim($row[3] ?? ''), 'UTF-8', 'ISO-8859-1'),
                    'type'           => mb_convert_encoding(trim($row[4] ?? ''), 'UTF-8', 'ISO-8859-1'),
                    'pieces_per_box' => !empty($row[5]) ? (int)$row[5] : null,
                    'length'         => !empty($row[6]) ? (float)$row[6] : null,
                    'width'          => !empty($row[7]) ? (float)$row[7] : null,
                    'height'         => !empty($row[8]) ? (float)$row[8] : null,
                    'upc'            => mb_convert_encoding(trim($row[9] ?? ''), 'UTF-8', 'ISO-8859-1'),
                    'is_active'      => true,
                ];

                $photoFilename = trim($row[10] ?? '');
                if (!empty($photoFilename) && $tempZipDir) {
                    $foundPath = $this->findFileRecursively($tempZipDir, $photoFilename);
                    if ($foundPath) {
                        $s3Path = Storage::disk('s3')->putFileAs(
                            'ff_catalog_photos',
                            $foundPath,
                            $sku . '.' . pathinfo($foundPath, PATHINFO_EXTENSION)
                        );
                        $productData['photo_path'] = $s3Path;
                    } else {
                        $missingPhotos[] = "$sku ($photoFilename)";
                    }
                }

                $product = ffProduct::updateOrCreate(['sku' => $sku], $productData);
                
                if (!empty($channelIds)) {
                    $product->channels()->sync($channelIds);
                }

                $importCount++;
            }
            
            DB::commit();
            fclose($handle);

        } catch (\Exception $e) {
            DB::rollBack();
            if ($tempZipDir) $this->cleanupTempDir($tempZipDir);
            return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
        }

        if ($tempZipDir) $this->cleanupTempDir($tempZipDir);

        $message = "¡Éxito! Se actualizaron/crearon $importCount productos.";
        if (count($missingPhotos) > 0) {
            $message .= " (Nota: No se encontraron fotos para: " . implode(', ', $missingPhotos) . ")";
        }

        return response()->json(['message' => $message], 200);
    }

    private function findFileRecursively($rootDir, $fileName)
    {
        $directory = new \RecursiveDirectoryIterator($rootDir);
        $iterator = new \RecursiveIteratorIterator($directory);

        foreach ($iterator as $info) {
            if ($info->isFile() && $info->getFilename() === $fileName) {
                return $info->getPathname();
            }
        }
        return null;
    }

    private function cleanupTempDir(string $tempZipDir): void
    {
        try {
            if (is_dir($tempZipDir)) {
                $files = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($tempZipDir, \RecursiveDirectoryIterator::SKIP_DOTS),
                    \RecursiveIteratorIterator::CHILD_FIRST
                );
                foreach ($files as $fileinfo) {
                    $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
                    $todo($fileinfo->getRealPath());
                }
                rmdir($tempZipDir);
            }
        } catch (\Exception $e) {
            Log::error('Error al limpiar directorio temporal de importación: ' . $e->getMessage());
        }
    }

    public function exportCsv(Request $request)
    {
        $query = ffProduct::with('channels')->orderBy('brand')->orderBy('description');

        $query = $this->applyFilters($query, $request);

        $products = $query->get();
        
        $filename = 'catalogo_filtrado_ff_' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($products) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF"); 
            fputcsv($file, ['SKU', 'Descripción', 'Precio Unitario', 'Marca', 'Tipo', 'Pzas/Caja', 'Largo', 'Ancho', 'Alto', 'UPC', 'Canales', 'Estado', 'URL Imagen']);

            foreach ($products as $product) {
                $channelsStr = $product->channels->pluck('name')->implode(', ');
                
                fputcsv($file, [
                    $product->sku,
                    $product->description,
                    $product->unit_price,
                    $product->brand,
                    $product->type,
                    $product->pieces_per_box,
                    $product->length,
                    $product->width,
                    $product->height,
                    $product->upc,
                    $channelsStr,
                    $product->is_active ? 'Activo' : 'Inactivo',
                    $product->photo_url
                ]);
            }
            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }

    public function exportPdf(Request $request)
    {
        ini_set('max_execution_time', 300);
        ini_set('memory_limit', '512M');

        $percentage = $request->input('percentage', 0);

        $query = ffProduct::orderBy('brand')->orderBy('description');

        $query = $this->applyFilters($query, $request);

        $products = $query->get();

        if ($percentage > 0) {
            foreach ($products as $product) {
                $increase = $product->unit_price * ($percentage / 100);
                $product->unit_price += $increase;
            }
        }
        
        $logoUrl = $this->getLogoUrl(Auth::user()->area_id);

        $data = [
            'products' => $products,
            'date' => now()->format('d/m/Y'),
            'logo_url' => $logoUrl,
            'percentage_text' => $percentage > 0 ? " (Precios +{$percentage}%)" : ""
        ];

        $pdf = Pdf::loadView('friends-and-family.catalog.pdf', $data);
        
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOption('isRemoteEnabled', true);
        $pdf->setOption('dpi', 150);
        
        return $pdf->stream('Catalogo_FF_'.now()->format('Ymd').'.pdf');
    }

    public function generateTechnicalSheet(Request $request, ffProduct $product)
    {
        $request->validate([
            'alcohol_vol' => 'nullable|string',
            'boxes_per_layer' => 'nullable|string',
            'layers_per_pallet' => 'nullable|string',
            'master_box_weight' => 'nullable|string',
        ]);

        $logoUrl = $this->getLogoUrl(Auth::user()->area_id);
        
        $data = [
            'product' => $product,
            'extra' => $request->all(),
            'logo_url' => $logoUrl,
        ];

        $pdf = Pdf::loadView('friends-and-family.catalog.technical-sheet', $data);
        
        $pdf->setPaper('A5', 'portrait'); 
        
        $pdf->setOption('isRemoteEnabled', true);
        $pdf->setOption('dpi', 150);
        
        return $pdf->stream('Ficha_Tecnica_'.$product->sku.'.pdf');
    }

    public function exportInventoryPdf(Request $request)    
    {
        ini_set('max_execution_time', 300);
        ini_set('memory_limit', '512M');

        $query = ffProduct::orderBy('brand')
                    ->orderBy('description');

        $query = $this->applyFilters($query, $request);

        $products = $query->get();
        
        $logoUrl = Storage::disk('s3')->url('LogoAzulm.PNG');

        $data = [
            'products' => $products,
            'logo_url' => $logoUrl,
            'print_date' => now()->format('d/m/Y H:i'), 
        ];

        $pdf = Pdf::loadView('friends-and-family.catalog.inventory-pdf', $data);
        
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOption('isRemoteEnabled', true);
        $pdf->setOption('dpi', 150);
        
        return $pdf->stream('Toma_Inventario_FF_'.date('Y-m-d').'.pdf');
    }

}