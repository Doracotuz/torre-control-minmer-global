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
        if (!Auth::user()->isSuperAdmin() && !Auth::user()->hasFfPermission('catalog.view')) {
            abort(403, 'No tienes permiso para ver el catálogo.');
        }

        $channelsQuery = FfSalesChannel::where('is_active', true)->orderBy('name');
        $productQuery = ffProduct::query();
        
        if (!Auth::user()->isSuperAdmin()) {    
            $channelsQuery->where('area_id', Auth::user()->area_id);
            $productQuery->where('area_id', Auth::user()->area_id);
        }
        
        $channels = $channelsQuery->get();

        $brands = (clone $productQuery)
            ->whereNotNull('brand')
            ->where('brand', '!=', '')
            ->distinct()
            ->orderBy('brand')
            ->pluck('brand');

        $types = (clone $productQuery)
            ->whereNotNull('type')
            ->where('type', '!=', '')
            ->distinct()
            ->orderBy('type')
            ->pluck('type');        
        
        $areas = [];
        if (Auth::user()->isSuperAdmin()) {
            $areas = Area::orderBy('name')->get();
        }

        return view('friends-and-family.catalog.index', [
            'products' => [],
            'channels' => $channels,
            'areas' => $areas,
            'brands' => $brands,
            'types' => $types
        ]);
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

        if ($request->filled('area_id')) {
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
        return Storage::disk('s3')->url('LogoAzulm.PNG');
    }

    public function store(Request $request)
    {
        if (!Auth::user()->isSuperAdmin() && !Auth::user()->hasFfPermission('catalog.create')) {
            return response()->json(['message' => 'No tienes permiso para crear productos.'], 403);
        }

        $user = Auth::user();
        
        $targetAreaId = $user->area_id;
        if ($user->isSuperAdmin() && $request->filled('area_id')) {
            $targetAreaId = $request->input('area_id');
        }

        $validated = $request->validate([
            'sku' => [
                'required', 
                'string', 
                'max:255', 
                Rule::unique('ff_products')->where(function ($query) use ($targetAreaId) {
                    return $query->where('area_id', $targetAreaId);
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
            'master_box_weight' => 'nullable|numeric|min:0',
            'upc' => 'nullable|string|max:255',
            'channels' => 'nullable|array',
            'channels.*' => [
                'exists:ff_sales_channels,id',
                function ($attribute, $value, $fail) use ($user, $targetAreaId) {
                    if (!$user->isSuperAdmin()) {
                        $exists = FfSalesChannel::where('id', $value)->where('area_id', $targetAreaId)->exists();
                        if (!$exists) $fail('El canal de venta seleccionado no es válido para su área.');
                    }
                }
            ],
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:20048',
            'area_id' => 'nullable|exists:areas,id',
        ]);

        $data = $validated;
        $data['is_active'] = $request->boolean('is_active', true);
        $data['area_id'] = $targetAreaId;

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
        if (!Auth::user()->isSuperAdmin() && !Auth::user()->hasFfPermission('catalog.edit')) {
            return response()->json(['message' => 'No tienes permiso para editar productos.'], 403);
        }

        $user = Auth::user();

        if (!$user->isSuperAdmin() && $catalog->area_id !== $user->area_id) {
            abort(403, 'No tienes permiso para editar este producto.');
        }

        $targetAreaId = $catalog->area_id; 
        if ($user->isSuperAdmin() && $request->filled('area_id')) {
            $targetAreaId = $request->input('area_id');
        }

        $validated = $request->validate([
            'sku' => [
                'required', 
                'string', 
                'max:255', 
                Rule::unique('ff_products')->ignore($catalog->id)->where(function ($query) use ($targetAreaId) {
                    return $query->where('area_id', $targetAreaId);
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
            'master_box_weight' => 'nullable|numeric|min:0',
            'upc' => 'nullable|string|max:255',
            'channels' => 'nullable|array',
            'channels.*' => [
                'exists:ff_sales_channels,id',
                function ($attribute, $value, $fail) use ($user, $targetAreaId) {
                    if (!$user->isSuperAdmin()) {
                        $exists = FfSalesChannel::where('id', $value)->where('area_id', $targetAreaId)->exists();
                        if (!$exists) $fail('El canal de venta seleccionado no es válido.');
                    }
                }
            ],
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:20048',
            'is_active' => 'required|boolean',
            'area_id' => 'nullable|exists:areas,id',
            'delete_photo' => 'nullable|boolean',
        ]);

        $data = $validated;
        $data['is_active'] = $request->boolean('is_active');
        $data['area_id'] = $targetAreaId;

        if ($request->boolean('delete_photo')) {
            if ($catalog->photo_path) {
                Storage::disk('s3')->delete($catalog->photo_path);
            }
            $data['photo_path'] = null;
        }        

        if ($request->hasFile('photo')) {
            if (!$request->boolean('delete_photo') && $catalog->photo_path) {
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
        if (!Auth::user()->isSuperAdmin() && !Auth::user()->hasFfPermission('catalog.delete')) {
            return response()->json(['message' => 'No tienes permiso para eliminar productos.'], 403);
        }

        if (!Auth::user()->isSuperAdmin() && $catalog->area_id !== Auth::user()->area_id) {
            abort(403, 'No tienes permiso para eliminar este producto.');
        }

        $catalog->delete();

        return response()->json(['message' => 'Producto eliminado permanentemente'], 204);
    }

    public function downloadTemplate()
    {
        if (!Auth::user()->isSuperAdmin() && !Auth::user()->hasFfPermission('catalog.import')) {
            abort(403, 'No tienes permiso para importar/descargar plantilla.');
        }

        $query = ffProduct::orderBy('sku')->with('channels');

        if (!Auth::user()->isSuperAdmin()) {
            $query->where('area_id', Auth::user()->area_id);
        }

        $products = $query->get();

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="plantilla_gestion_catalogo_'.date('Y-m-d').'.csv"',
        ];

        $callback = function() use ($products) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF");
            
            fputcsv($file, [
                'SKU (No cambiar)', 'Descripción', 'Precio Unitario', 'Marca', 'Tipo', 
                'Piezas por Caja', 'Largo', 'Ancho', 'Alto', 'Peso Caja Master', 'UPC', 
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
                    $product->master_box_weight,
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
        if (!Auth::user()->isSuperAdmin() && !Auth::user()->hasFfPermission('catalog.import')) {
            return response()->json(['message' => 'No tienes permiso para importar productos.'], 403);
        }

        $request->validate([
            'product_file' => 'required|file|mimes:csv,txt',
            'image_zip'    => 'nullable|file|mimetypes:application/zip,application/x-zip-compressed,application/x-zip,application/octet-stream,multipart/x-zip',
            'area_id'      => 'nullable|exists:areas,id',
        ]);

        $targetAreaId = Auth::user()->area_id;
        
        if (Auth::user()->isSuperAdmin() && $request->filled('area_id')) {
            $targetAreaId = $request->input('area_id');
        }

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
                $sku = $this->cleanCsvValue($row[0] ?? '');
                if (empty($sku)) continue;

                $channelsStr = $this->cleanCsvValue($row[12] ?? '');
                $channelIds = [];
                if (!empty($channelsStr)) {
                    $channelNames = preg_split('/[,|]/', $channelsStr); 
                    foreach($channelNames as $name) {
                        $name = trim($name);
                        if(empty($name)) continue;
                        
                        $channel = FfSalesChannel::firstOrCreate(
                            ['name' => $name, 'area_id' => $targetAreaId],
                            ['is_active' => true]
                        );
                        $channelIds[] = $channel->id;
                    }
                }

                $productData = [
                    'description'    => $this->cleanCsvValue($row[1] ?? ''),
                    'unit_price'     => (float)($row[2] ?? 0.00),
                    'brand'          => $this->cleanCsvValue($row[3] ?? ''),
                    'type'           => $this->cleanCsvValue($row[4] ?? ''),
                    'pieces_per_box' => !empty($row[5]) ? (int)$row[5] : null,
                    'length'         => !empty($row[6]) ? (float)$row[6] : null,
                    'width'          => !empty($row[7]) ? (float)$row[7] : null,
                    'height'         => !empty($row[8]) ? (float)$row[8] : null,
                    'master_box_weight' => !empty($row[9]) ? (float)$row[9] : null,
                    'upc'            => $this->cleanCsvValue($row[10] ?? ''),
                    'is_active'      => true,
                    'area_id'        => $targetAreaId
                ];

                $photoFilename = trim($row[11] ?? '');
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

                $product = ffProduct::updateOrCreate(
                    [
                        'sku' => $sku, 
                        'area_id' => $targetAreaId
                    ], 
                    $productData
                );
                
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

        $areaName = Area::find($targetAreaId)->name ?? 'Área desconocida';
        $message = "¡Éxito! Se actualizaron $importCount productos en el área: $areaName.";
        
        if (count($missingPhotos) > 0) {
            $message .= " (Faltaron " . count($missingPhotos) . " fotos)";
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

    public function bulkDestroy(Request $request)
    {
        if (!Auth::user()->isSuperAdmin() && !Auth::user()->hasFfPermission('catalog.delete')) {
            return response()->json(['message' => 'No tienes permiso para eliminar productos.'], 403);
        }

        if (!Auth::user()->isSuperAdmin()) {
            abort(403, 'No tienes permiso para realizar esta acción.');
        }

        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return response()->json(['message' => 'No se seleccionaron productos.'], 422);
        }

        $products = ffProduct::whereIn('id', $ids)->get();

        foreach ($products as $product) {
            $product->delete();
        }

        return response()->json(['message' => 'Productos eliminados correctamente.'], 200);
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
        if (!Auth::user()->isSuperAdmin() && !Auth::user()->hasFfPermission('catalog.export')) {
            abort(403, 'No tienes permiso para exportar el catálogo.');
        }

        $query = ffProduct::with('channels')->orderBy('brand')->orderBy('description');

        $query = $this->applyFilters($query, $request);

        if (!Auth::user()->isSuperAdmin()) {
            $query->where('area_id', Auth::user()->area_id);
        }

        $products = $query->get();
        
        $filename = 'catalogo_filtrado_ff_' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($products) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF");
            
            fputcsv($file, ['SKU', 'Descripción', 'Precio Unitario', 'Marca', 'Tipo', 'Pzas/Caja', 'Largo', 'Ancho', 'Alto', 'Peso Caja Master', 'UPC', 'Canales', 'Estado', 'URL Imagen']);

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
                    $product->master_box_weight,
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
        Log::info('Inicio generacion PDF FF');
        $startTime = microtime(true);

        if (!Auth::user()->isSuperAdmin() && !Auth::user()->hasFfPermission('catalog.export')) {
            abort(403, 'No tienes permiso para exportar el catálogo.');
        }

        set_time_limit(0);
        ini_set('memory_limit', '1024M');

        $percentage = $request->input('percentage', 0);

        $query = ffProduct::orderBy('brand')->orderBy('description');

        $query = $this->applyFilters($query, $request);

        if (!Auth::user()->isSuperAdmin()) {
            $query->where('area_id', Auth::user()->area_id);
        }

        $products = $query->get();
        Log::info('Productos consultados: ' . $products->count() . ' - Tiempo: ' . round(microtime(true) - $startTime, 2) . 's');

        $cacheDir = storage_path('app/public/ff_catalog_cache');
        if (!file_exists($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }

        foreach ($products as $product) {
            if ($percentage > 0) {
                $increase = $product->unit_price * ($percentage / 100);
                $product->unit_price += $increase;
            }

            $product->temp_photo_path = null; 

            if ($product->photo_path) {
                // Generar nombre de archivo único basado en el path original de la foto
                // Esto asegura que si la foto cambia (nuevo path en S3), se genere un nuevo archivo cache
                $cacheKey = $product->id . '_' . md5($product->photo_path);
                $localPath = $cacheDir . '/' . $cacheKey . '.jpg';
                
                $localPath = str_replace('\\', '/', $localPath);

                if (file_exists($localPath)) {
                    $product->temp_photo_path = $localPath;
                } else {
                    try {
                        if (Storage::disk('s3')->exists($product->photo_path)) {
                            $imageContent = Storage::disk('s3')->get($product->photo_path);
                            $sourceImage = @imagecreatefromstring($imageContent);
                            
                            if ($sourceImage) {
                                $width = imagesx($sourceImage);
                                $newWidth = 150;
                                $newHeight = floor(imagesy($sourceImage) * ($newWidth / $width));
                                
                                $virtualImage = imagecreatetruecolor($newWidth, $newHeight);
                                
                                imagealphablending($virtualImage, false);
                                imagesavealpha($virtualImage, true);
                                
                                imagecopyresampled($virtualImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, imagesy($sourceImage));
                                
                                imagejpeg($virtualImage, $localPath, 60);
                                
                                $product->temp_photo_path = $localPath;

                                imagedestroy($virtualImage);
                                imagedestroy($sourceImage);
                            }
                        }
                    } catch (\Exception $e) {
                         Log::error("Error procesando imagen producto {$product->id}: " . $e->getMessage());
                    }
                }
            }
            
            if ($product->temp_photo_path) {
                $product->photo_url = $product->temp_photo_path;
            } else {
                $product->photo_url = null; 
            }
        }
        
        Log::info('Imagenes procesadas - Tiempo: ' . round(microtime(true) - $startTime, 2) . 's');

        $targetAreaId = Auth::user()->isSuperAdmin() && $request->filled('area_id') 
            ? $request->input('area_id') 
            : Auth::user()->area_id;

        $logoKey = 'LogoAzulm.PNG';
        
        if ($targetAreaId) {
            $area = Area::find($targetAreaId);
            if ($area && $area->icon_path) {
                $logoKey = $area->icon_path;
            }
        }

        $localLogoPath = null;
        try {
            $logoCachePath = $cacheDir . '/logo_' . ($targetAreaId ?? 'default') . '.png';
            $logoCachePath = str_replace('\\', '/', $logoCachePath);
            
            if (file_exists($logoCachePath)) {
                $localLogoPath = $logoCachePath;
            } else {
                if (Storage::disk('s3')->exists($logoKey)) {
                    $logoContent = Storage::disk('s3')->get($logoKey);
                    file_put_contents($logoCachePath, $logoContent);
                    $localLogoPath = $logoCachePath;
                }
            }
        } catch (\Exception $e) {
            Log::error("Error procesando logo: " . $e->getMessage());
        }

        $finalLogo = $localLogoPath;

        $data = [
            'products' => $products,
            'date' => now()->format('d/m/Y'),
            'logo_url' => $finalLogo,
            'percentage_text' => $percentage > 0 ? " (Precios +{$percentage}%)" : ""
        ];

        Log::info('Iniciando renderizado DOMPDF...');

        $pdf = Pdf::loadView('friends-and-family.catalog.pdf', $data);
        
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOption('isRemoteEnabled', false);
        $pdf->setOption('dpi', 96);
        $pdf->setOption('isHtml5ParserEnabled', true);
        
        return $pdf->stream('Catalogo_FF_'.now()->format('Ymd').'.pdf');
    }

    public function generateTechnicalSheet(Request $request, ffProduct $product)
    {
        if (!Auth::user()->isSuperAdmin() && !Auth::user()->hasFfPermission('catalog.technical_sheet')) {
            abort(403, 'No tienes permiso para generar ficha técnica.');
        }

        if (!Auth::user()->isSuperAdmin() && $product->area_id !== Auth::user()->area_id) {
            abort(403);
        }

        $request->validate([
            'alcohol_vol' => 'nullable|string',
            'boxes_per_layer' => 'nullable|string',
            'layers_per_pallet' => 'nullable|string',
            'master_box_weight' => 'nullable|string',
            'primary_color' => 'nullable|string|max:7',
            'accent_color' => 'nullable|string|max:7',
        ]);

        $logoUrl = $this->getLogoUrl($product->area_id);

        $colors = [
            'primary' => $request->input('primary_color') ?? '#00683f',
            'accent'  => $request->input('accent_color')  ?? '#f77b33',
        ];        
        
        $data = [
            'product' => $product,
            'extra' => $request->all(),
            'logo_url' => $logoUrl,
            'colors' => $colors,

        ];

        $pdf = Pdf::loadView('friends-and-family.catalog.technical-sheet', $data);
        
        $pdf->setPaper('A5', 'portrait'); 
        
        $pdf->setOption('isRemoteEnabled', true);
        $pdf->setOption('dpi', 150);
        
        return $pdf->stream('Ficha_Tecnica_'.$product->sku.'.pdf');
    }

    public function exportInventoryPdf(Request $request)
    {
        if (!Auth::user()->isSuperAdmin() && !Auth::user()->hasFfPermission('catalog.export')) {
            abort(403, 'No tienes permiso para exportar el catálogo/inventario.');
        }

        set_time_limit(0); 
        ini_set('max_execution_time', 600);
        ini_set('memory_limit', '1024M');

        $query = ffProduct::orderBy('brand')->orderBy('description');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('sku', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%")
                  ->orWhere('upc', 'like', "%{$search}%");
            });
        }
        if ($request->filled('brand')) $query->where('brand', $request->input('brand'));
        if ($request->filled('type')) $query->where('type', $request->input('type'));
        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('is_active', $request->input('status') === 'active');
        }
        if ($request->filled('channel')) {
            $query->whereHas('channels', fn($q) => $q->where('id', $request->input('channel')));
        }
        
        $targetAreaId = Auth::user()->isSuperAdmin() && $request->filled('area_id') 
            ? $request->input('area_id') 
            : Auth::user()->area_id;

        if ($targetAreaId) {
            $query->where('area_id', $targetAreaId);
        }

        $products = $query->get();

        $tempDir = storage_path('app/public/temp_pdf_' . uniqid());
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0777, true);
        }

        foreach ($products as $product) {
            $product->local_photo_path = null;
            if ($product->photo_path) {
                try {
                    if (Storage::disk('s3')->exists($product->photo_path)) {
                        $imageContent = Storage::disk('s3')->get($product->photo_path);
                        $sourceImage = @imagecreatefromstring($imageContent);
                        if ($sourceImage) {
                            $width = imagesx($sourceImage);
                            $newWidth = 150;
                            $newHeight = floor(imagesy($sourceImage) * ($newWidth / $width));
                            $virtualImage = imagecreatetruecolor($newWidth, $newHeight);
                            imagealphablending($virtualImage, false);
                            imagesavealpha($virtualImage, true);
                            imagecopyresampled($virtualImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, imagesy($sourceImage));
                            
                            $localPath = $tempDir . '/' . $product->id . '.jpg';
                            imagejpeg($virtualImage, $localPath, 60); 
                            $product->local_photo_path = $localPath;

                            imagedestroy($virtualImage);
                            imagedestroy($sourceImage);
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning("Error img producto {$product->sku}: " . $e->getMessage());
                }
            }
        }
        
        $logoKey = 'LogoAzulm.PNG'; 
        
        if ($targetAreaId) {
            $area = Area::find($targetAreaId);
            if ($area && $area->icon_path) {
                $logoKey = $area->icon_path;
            }
        }

        $localLogoPath = null;
        try {
            if (Storage::disk('s3')->exists($logoKey)) {
                $logoContent = Storage::disk('s3')->get($logoKey);
                $localLogoPath = $tempDir . '/logo_header.png';
                file_put_contents($localLogoPath, $logoContent);
            }
        } catch (\Exception $e) {
            Log::error("Error descargando logo para PDF inventario: " . $e->getMessage());
        }

        $finalLogo = $localLogoPath ?? $this->getLogoUrl($targetAreaId);
        

        $data = [
            'products' => $products,
            'logo_url' => $finalLogo,
            'print_date' => now()->format('d/m/Y H:i'), 
        ];

        $pdf = Pdf::loadView('friends-and-family.catalog.inventory-pdf', $data);
        $pdf->setPaper('A4', 'portrait');
        $output = $pdf->output();

        $files = glob($tempDir . '/*'); 
        foreach($files as $file){ if(is_file($file)) unlink($file); }
        rmdir($tempDir);

        return response()->streamDownload(
            fn () => print($output),
            'Toma_Inventario_FF_'.date('Y-m-d').'.pdf'
        );
    }

    public function searchProducts(Request $request)
    {
        $query = ffProduct::with('channels')->orderBy('brand')->orderBy('description');
        $query = $this->applyFilters($query, $request);

        if (!Auth::user()->isSuperAdmin()) {
            $query->where('area_id', Auth::user()->area_id);
        }

        $products = $query->paginate(12);

        $areaId = null;
        if (Auth::user()->isSuperAdmin()) {
            $areaId = $request->input('area_id'); 
        } else {
            $areaId = Auth::user()->area_id;
        }

        $metaQuery = ffProduct::query();
        $channelQuery = FfSalesChannel::query()->where('is_active', true);

        if ($areaId) {
            $metaQuery->where('area_id', $areaId);
            $channelQuery->where('area_id', $areaId);
        }

        $availableBrands = (clone $metaQuery)
            ->whereNotNull('brand')->where('brand', '!=', '')
            ->distinct()->orderBy('brand')->pluck('brand');

        $availableTypes = (clone $metaQuery)
            ->whereNotNull('type')->where('type', '!=', '')
            ->distinct()->orderBy('type')->pluck('type');

        $availableChannels = $channelQuery->orderBy('name')->get();

        return response()->json([
            'products' => $products,
            'filters' => [
                'brands' => $availableBrands,
                'types' => $availableTypes,
                'channels' => $availableChannels
            ]
        ]);
    }

    private function cleanCsvValue($value)
    {
        if (!$value) return '';
        $value = trim($value);

        if (mb_check_encoding($value, 'UTF-8')) {
            return preg_replace('/^\xEF\xBB\xBF/', '', $value);
        }

        return mb_convert_encoding($value, 'UTF-8', 'Windows-1252');
    }

}