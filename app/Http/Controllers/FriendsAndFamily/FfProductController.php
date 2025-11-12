<?php

namespace App\Http\Controllers\FriendsAndFamily;

use App\Http\Controllers\Controller;
use App\Models\ffProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use ZipArchive;

class FfProductController extends Controller
{
    public function index()
    {
        $products = ffProduct::orderBy('description')->get();
        return view('friends-and-family.catalog.index', compact('products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sku' => 'required|string|max:255|unique:ff_products,sku',
            'description' => 'required|string|max:500',
            'type' => 'nullable|string|max:255',
            'brand' => 'nullable|string|max:255',
            'price' => 'required|numeric|min:0',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048', 
        ]);

        $data = $validated;
        $data['is_active'] = $request->boolean('is_active', true);

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('ff_catalog_photos', 's3');
            $data['photo_path'] = $path;
        }

        $product = ffProduct::create($data);

        return response()->json($product->fresh(), 201); 
    }

    public function update(Request $request, ffProduct $catalog)
    {
        $validated = $request->validate([
            'sku' => ['required', 'string', 'max:255', Rule::unique('ff_products')->ignore($catalog->id)],
            'description' => 'required|string|max:500',
            'type' => 'nullable|string|max:255',
            'brand' => 'nullable|string|max:255',
            'price' => 'required|numeric|min:0',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
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

        return response()->json($catalog->fresh());
    }

    public function destroy(ffProduct $catalog)
    {
        $catalog->delete();

        return response()->json(['message' => 'Producto eliminado permanentemente'], 204);
    }

    public function downloadTemplate()
    {
        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="plantilla_productos_ff.csv"',
        ];

        $callback = function() {
            $file = fopen('php://output', 'w');
            $columns = [
                'SKU', 
                'Description', 
                'Type', 
                'Brand', 
                'Price', 
                'Photo_Filename (Ej: foto1.jpg)'
            ];
            fputcsv($file, $columns);
            $example = [
                'SKU-001', 
                'Taza Mágica Minmer', 
                'Taza', 
                'Minmer', 
                '150.00',
                'taza_minmer.jpg'
            ];
            fputcsv($file, $example);
            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }


    public function import(Request $request)
    {
        $request->validate([
            'product_file' => 'required|file|mimes:csv,txt',
            'image_zip'    => 'required|file|mimes:zip',
        ]);

        $csvPath = $request->file('product_file')->getPathname();
        $zipPath = $request->file('image_zip')->getPathname();

        $tempZipDir = storage_path('app/temp/' . uniqid('zip_'));
        if (!mkdir($tempZipDir, 0777, true)) {
            return response()->json(['message' => 'Error de sistema: No se pudo crear directorio temporal.'], 500);
        }

        $missingPhotos = [];

        try {
            $zip = new ZipArchive;
            if ($zip->open($zipPath) !== TRUE) {
                throw new \Exception('No se pudo abrir el archivo ZIP.');
            }
            $zip->extractTo($tempZipDir);
            $zip->close();

            if (($handle = fopen($csvPath, 'r')) === FALSE) {
                throw new \Exception('No se pudo abrir el archivo CSV.');
            }

            fgetcsv($handle);

            $importCount = 0;
            DB::beginTransaction();

            while (($row = fgetcsv($handle)) !== FALSE) {
                $sku = mb_convert_encoding(trim($row[0] ?? ''), 'UTF-8', 'ISO-8859-1');
                if (empty($sku)) continue;

                $productData = [
                    'description' => mb_convert_encoding(trim($row[1] ?? ''), 'UTF-8', 'ISO-8859-1'),
                    'type'        => mb_convert_encoding(trim($row[2] ?? ''), 'UTF-8', 'ISO-8859-1'),
                    'brand'       => mb_convert_encoding(trim($row[3] ?? ''), 'UTF-8', 'ISO-8859-1'),
                    'price'       => (float)($row[4] ?? 0.00),
                    'is_active'   => true,
                ];

                $photoFilename = trim($row[5] ?? '');
                
                if (!empty($photoFilename)) {
                    $foundPath = $this->findFileRecursively($tempZipDir, $photoFilename);

                    if ($foundPath) {
                        $s3Path = Storage::disk('s3')->putFileAs(
                            'ff_catalog_photos',
                            $foundPath,
                            $sku . '.' . pathinfo($foundPath, PATHINFO_EXTENSION)
                        );
                        $productData['photo_path'] = $s3Path;
                    } else {
                        $missingPhotos[] = "SKU: $sku (Archivo buscado: '$photoFilename')";
                    }
                }

                ffProduct::updateOrCreate(
                    ['sku' => $sku],
                    $productData
                );
                $importCount++;
            }
            
            DB::commit();
            fclose($handle);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error importación FF: ' . $e->getMessage());
            $this->cleanupTempDir($tempZipDir);
            return response()->json(['message' => 'Error crítico: ' . $e->getMessage()], 500);
        }

        $this->cleanupTempDir($tempZipDir);

        $message = "¡Éxito! Se procesaron $importCount productos.";
        if (count($missingPhotos) > 0) {
            $message .= " Pero NO se encontraron las fotos para: " . implode(', ', $missingPhotos);
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
}