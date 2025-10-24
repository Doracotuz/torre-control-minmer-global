<?php

namespace App\Http\Controllers\WMS;

use App\Http\Controllers\Controller;
use App\Models\WMS\SalesOrder;
use App\Models\WMS\PickList;
use App\Models\WMS\InventoryStock;
use App\Models\WMS\PalletItem;
use App\Models\WMS\PickListItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Models\Location;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Label\Label;
use Endroid\QrCode\Logo\Logo;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use App\Models\WMS\StockMovement;

class WMSPickingController extends Controller
{
    /**
     * Genera una Pick List a partir de una Orden de Venta.
     */
    public function generate(SalesOrder $salesOrder)
    {
        if ($salesOrder->status !== 'Pending') {
            return back()->with('error', 'Esta orden ya está siendo procesada o ya tiene una Pick List.');
        }

        DB::beginTransaction();
        try {
            $pickListItems = [];
            foreach ($salesOrder->lines as $line) {
                // Buscamos un PalletItem específico que cumpla las condiciones
                $palletItem = \App\Models\WMS\PalletItem::where('product_id', $line->product_id)
                    ->where('quantity', '>=', $line->quantity_ordered)
                    ->orderBy('created_at') // FIFO básico
                    ->first();

                if (!$palletItem) {
                    throw new \Exception('No hay un pallet con stock suficiente para el producto: ' . $line->product->sku);
                }

                $pickListItems[] = [
                    'product_id' => $line->product_id,
                    'pallet_id' => $palletItem->pallet_id,
                    'location_id' => $palletItem->pallet->location_id,
                    'quantity_to_pick' => $line->quantity_ordered,
                    'quality_id' => $palletItem->quality_id,
                ];
            }

            $pickList = \App\Models\WMS\PickList::create([
                'sales_order_id' => $salesOrder->id,
                'user_id' => Auth::id(),
                'status' => 'Generated',
            ]);

            $pickList->items()->createMany($pickListItems);
            $salesOrder->update(['status' => 'Picking']);

            DB::commit();
            return redirect()->route('wms.picking.show', $pickList)->with('success', 'Pick List generada exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al generar Pick List: ' . $e->getMessage());
        }
    }

    /**
     * Muestra la interfaz de picking para el operario.
     */
    public function show(PickList $pickList)
    {
        // Carga relaciones necesarias, incluyendo las nuevas para validación
        $pickList->load([
            'salesOrder', 
            'items.product', 
            'items.location', // Ubicación ESPERADA
            'items.quality', 
            'items.pallet.purchaseOrder', // Pallet ESPERADO y su PO
            'items.pallet.location' // Ubicación REAL del pallet (podría diferir)
        ]);

        // Obtener ubicaciones de tipo 'Picking' o 'Staging' para el dropdown
        $stagingLocations = Location::where('type', 'shipping') // O el tipo que uses para staging/empaque
                                    ->orderBy('code')
                                    ->get();

        // Pasar las ubicaciones a la vista
        return view('wms.picking.show', compact('pickList', 'stagingLocations'));
    }

/**
     * Confirma UN SOLO item de la Pick List después de la validación.
     */
    public function confirmItem(Request $request, PickListItem $pickListItem) // <--- $pickListItem es la variable
    {
        // Valida los datos escaneados/ingresados
        $validated = $request->validate([
            'scanned_location_code' => 'required|string',
            'scanned_sku' => 'required|string',
            'scanned_quantity' => 'required|integer|min:1',
            'scanned_lpn' => 'required|string',
        ]);

        // Carga las relaciones necesarias del item para comparar
        // Asegúrate de cargar 'quality' si lo usas más abajo
        $pickListItem->load(['product', 'location', 'pallet', 'quality']);

        DB::beginTransaction();
        try {
            // 1. Validar Ubicación
            $scannedLocation = Location::where('code', $validated['scanned_location_code'])->first();

            // Compara IDs para seguridad
            if (!$scannedLocation || $scannedLocation->id !== $pickListItem->location_id) {
                // --- INICIO DE LA CORRECCIÓN ---
                // Obtén el código esperado de forma segura ANTES de la cadena
                $expectedLocationCode = $pickListItem->location->code ?? 'N/A';
                // Lanza la excepción usando la variable separada
                throw new \Exception("Ubicación escaneada ({$validated['scanned_location_code']}) no coincide con la esperada ({$expectedLocationCode}).");
                // --- FIN DE LA CORRECCIÓN ---
            }

            // 2. Validar SKU
            if ($validated['scanned_sku'] !== $pickListItem->product->sku) {
                 throw new \Exception("SKU escaneado ({$validated['scanned_sku']}) no coincide con el esperado ({$pickListItem->product->sku}).");
            }

            // 3. Validar Cantidad
            if ($validated['scanned_quantity'] != $pickListItem->quantity_to_pick) {
                 throw new \Exception("Cantidad escaneada ({$validated['scanned_quantity']}) no coincide con la esperada ({$pickListItem->quantity_to_pick}).");
            }

            // 4. Validar LPN
            if ($validated['scanned_lpn'] !== $pickListItem->pallet->lpn) {
                 throw new \Exception("LPN escaneado ({$validated['scanned_lpn']}) no coincide con el esperado ({$pickListItem->pallet->lpn}).");
            }

            // --- Si todas las validaciones pasan ---

            // 5. Marcar item como surtido
            $pickListItem->update([
                'is_picked' => true,
                'quantity_picked' => $validated['scanned_quantity'],
                'picked_at' => now(),
            ]);

            // 6. --- CORRECCIÓN: Buscar PalletItem usando los IDs correctos ---
            // Buscamos el PalletItem usando la combinación única
            $palletItem = PalletItem::where('pallet_id', $pickListItem->pallet_id)
                                    ->where('product_id', $pickListItem->product_id)
                                    ->where('quality_id', $pickListItem->quality_id)
                                    ->first(); // Solo debe haber uno

            if ($palletItem) {
                 // Validar stock físico ANTES de descontar (doble check)
                 if ($palletItem->quantity < $pickListItem->quantity_to_pick) {
                     // Obtenemos el LPN del pallet asociado al PalletItem encontrado
                     $lpnActual = $palletItem->load('pallet')->pallet->lpn ?? 'DESCONOCIDO';
                     throw new \Exception("Stock físico insuficiente ({$palletItem->quantity}) en LPN {$lpnActual} al intentar confirmar item.");
                 }
                $newQuantity = max(0, $palletItem->quantity - $pickListItem->quantity_to_pick);
                $newCommitted = max(0, $palletItem->committed_quantity - $pickListItem->quantity_to_pick);
                $palletItem->update([
                    'quantity' => $newQuantity,
                    'committed_quantity' => $newCommitted,
                ]);
                $palletItem->loadMissing('pallet'); 
                if ($palletItem->pallet) {
                    $palletItem->pallet->update([
                        'last_action' => 'Picking Item (SO: ' . $pickListItem->pickList->salesOrder->so_number . ')',
                        'user_id' => Auth::id()
                    ]);
                }                
            } else {
                 // Si no se encuentra, hay una inconsistencia grave
                 throw new \Exception("No se encontró el PalletItem correspondiente (PalletID: {$pickListItem->pallet_id}, ProdID: {$pickListItem->product_id}, QID: {$pickListItem->quality_id}). No se pudo descontar inventario.");
            }

            // 7. (Opcional) Actualizar InventoryStock si lo usas
            //    --- CORRECCIÓN: Usar $pickListItem en lugar de $item ---
            $generalStock = InventoryStock::where('product_id', $pickListItem->product_id) // <- Corregido
                                ->where('location_id', $pickListItem->location_id)
                                ->where('quality_id', $pickListItem->quality_id)
                                ->first();

            if ($generalStock) {
                if ($generalStock->quantity < $pickListItem->quantity_to_pick) {
                     Log::warning("Stock general ({$generalStock->quantity}) insuficiente para SKU {$pickListItem->product->sku} en ubicación {$pickListItem->location->code} al confirmar item de picking #{$pickListItem->pick_list_id}. Se necesitaban {$pickListItem->quantity_to_pick}.");
                }

                $newGeneralQuantity = max(0, $generalStock->quantity - $pickListItem->quantity_to_pick);
                $newGeneralCommitted = 0;
                if (isset($generalStock->committed_quantity)) { // Verifica si la columna existe
                   $newGeneralCommitted = max(0, $generalStock->committed_quantity - $pickListItem->quantity_to_pick);
                }

                $generalStock->update([
                    'quantity' => $newGeneralQuantity,
                    'committed_quantity' => $newGeneralCommitted
                ]);

                StockMovement::create([
                    'user_id' => Auth::id(),
                    'product_id' => $pickListItem->product_id,
                    'location_id' => $pickListItem->location_id,
                    'pallet_item_id' => $palletItem->id,
                    'quantity' => -$pickListItem->quantity_to_pick, // Negativo para salida
                    'movement_type' => 'SALIDA-PICKING',
                    'source_id' => $pickListItem->id, // Fuente es el PickListItem
                    'source_type' => \App\Models\WMS\PickListItem::class,
                ]);

            } else {
                 Log::error("No se encontró registro de stock general para SKU {$pickListItem->product->sku} (ProdID: {$pickListItem->product_id}), LocID: {$pickListItem->location_id}, QID: {$pickListItem->quality_id} al confirmar item de picking #{$pickListItem->pick_list_id}.");
            }

            DB::commit();

            // Devuelve éxito en JSON para Alpine
            return response()->json(['success' => true, 'message' => 'Item confirmado correctamente.']);

        } catch (\Exception $e) {
            DB::rollBack();
            // Devuelve error en JSON para Alpine
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422); // 422 Unprocessable Entity
        }
    }

    /**
     * Marca toda la Pick List como completada y actualiza el estado de la SO.
     */
    public function completePicking(Request $request, PickList $pickList)
    {
        // Valida que se haya enviado una ubicación de staging
        $validated = $request->validate([
            'staging_location_id' => 'required|exists:locations,id',
        ]);

        // Carga los items para verificar que todos estén surtidos
        $pickList->load('items');

        // Verifica si todos los items tienen is_picked = true
        $allItemsPicked = $pickList->items->every(fn($item) => $item->is_picked);

        if (!$allItemsPicked) {
            return back()->with('error', 'No se puede completar el picking. Faltan items por confirmar.');
        }

        DB::beginTransaction();
        try {
            // Actualiza estado de PickList, asigna picker y fecha
            $pickList->update([
                'status' => 'Completed',
                'picker_id' => Auth::id(),
                'picked_at' => now(),
                // Podrías añadir una columna 'staging_location_id' a pick_lists si necesitas guardar dónde se dejó
            ]);

            // Actualiza estado de SalesOrder a 'Packed'
            $pickList->salesOrder()->update(['status' => 'Packed']);

            DB::commit();

            // Redirige a la vista de la Orden de Venta
            return redirect()->route('wms.sales-orders.show', $pickList->sales_order_id)
                           ->with('success', 'Picking completado exitosamente. La orden ha pasado a estado Empacado.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al completar picking #{$pickList->id}: " . $e->getMessage());
            return back()->with('error', 'Ocurrió un error al intentar completar el picking: ' . $e->getMessage());
        }
    }


    /**
     * Genera y muestra el PDF de la Pick List (AHORA CON QR).
     */
    public function generatePickListPdf(PickList $pickList)
    {
        // 1. Carga las relaciones necesarias para el PDF
        $pickList->load([
            'salesOrder',
            'items.product',
            'items.location',
            'items.quality',
            'items.pallet.purchaseOrder'
        ]);

        // 2. Genera la URL para el QR Code
        $qrCodeUrl = route('wms.picking.show', $pickList);

        // --- INICIO CÓDIGO QR CON ENDROID v5 (Como en VisitController) ---
        try {
            // 3. Instancia el Builder directamente
        $builder = new Builder(
            data: $qrCodeUrl,
            writer: new PngWriter(),
            writerOptions: [],
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::Low,
            size: 100,
            margin: 5,
            roundBlockSizeMode: RoundBlockSizeMode::Margin, // <-- CORREGIDO: Usa la constante
            validateResult: false
            );

            // 4. Construye el resultado
            $qrResult = $builder->build();

            // 5. Obtener el QR como data URI
            $qrCodeDataUri = $qrResult->getDataUri();

        } catch (\Exception $e) {
            Log::error("Error generando QR Code v5 para PickList {$pickList->id}: " . $e->getMessage());
            $qrCodeDataUri = null; // Continúa sin QR si falla
        }
        // --- FIN CÓDIGO QR CON ENDROID v5 ---


        // 6. Carga el logo de la empresa (como antes)
        $logoBase64 = null;
        // ... (Tu lógica para cargar el logo principal del PDF) ...
        $logoPath = 'LogoAzul.png'; $disk = 's3';
         if (Storage::disk($disk)->exists($logoPath)) {
             try {
                 $logoContent = Storage::disk($disk)->get($logoPath);
                 $mimeType = 'image/' . pathinfo($logoPath, PATHINFO_EXTENSION);
                 if (pathinfo($logoPath, PATHINFO_EXTENSION) === 'svg') { $mimeType = 'image/svg+xml'; }
                 $logoBase64 = 'data:' . $mimeType . ';base64,' . base64_encode($logoContent);
             } catch (\Exception $e) { \Log::error("Error cargando logo PDF: ".$e->getMessage()); }
        }


        // 7. Prepara los datos para la vista Blade del PDF
        $data = [
            'pickList' => $pickList,
            'logoBase64' => $logoBase64,
            'qrCodeDataUri' => $qrCodeDataUri
        ];

        // 8. Carga la vista Blade
        $pdf = Pdf::loadView('wms.picking.pdf', $data);

        // 9. Define el nombre del archivo PDF
        $fileName = 'PickList-' . $pickList->salesOrder->so_number . '-' . $pickList->id . '.pdf';

        // 10. Devuelve el PDF al navegador
        return $pdf->stream($fileName);
    }
}