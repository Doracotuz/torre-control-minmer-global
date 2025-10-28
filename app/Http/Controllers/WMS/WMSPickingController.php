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
    public function generate(SalesOrder $salesOrder)
    {
        if ($salesOrder->status !== 'Pending') {
            return back()->with('error', 'Esta orden ya está siendo procesada o ya tiene una Pick List.');
        }

        DB::beginTransaction();
        try {
            $pickListItems = [];
            foreach ($salesOrder->lines as $line) {
                $palletItem = \App\Models\WMS\PalletItem::where('product_id', $line->product_id)
                    ->where('quantity', '>=', $line->quantity_ordered)
                    ->orderBy('created_at')
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

    public function show(PickList $pickList)
    {
        $pickList->load([
            'salesOrder', 
            'items.product', 
            'items.location',
            'items.quality', 
            'items.pallet.purchaseOrder',
            'items.pallet.location'
        ]);

        $stagingLocations = Location::where('type', 'shipping')
                                    ->orderBy('code')
                                    ->get();

        return view('wms.picking.show', compact('pickList', 'stagingLocations'));
    }

    public function confirmItem(Request $request, PickListItem $pickListItem)
    {
        $validated = $request->validate([
            'scanned_location_code' => 'required|string',
            'scanned_sku' => 'required|string',
            'scanned_quantity' => 'required|integer|min:1',
            'scanned_lpn' => 'required|string',
        ]);

        $pickListItem->load(['product', 'location', 'pallet', 'quality']);

        DB::beginTransaction();
        try {
            $scannedLocation = Location::where('code', $validated['scanned_location_code'])->first();

            if (!$scannedLocation || $scannedLocation->id !== $pickListItem->location_id) {
                $expectedLocationCode = $pickListItem->location->code ?? 'N/A';
                throw new \Exception("Ubicación escaneada ({$validated['scanned_location_code']}) no coincide con la esperada ({$expectedLocationCode}).");
            }

            if ($validated['scanned_sku'] !== $pickListItem->product->sku) {
                 throw new \Exception("SKU escaneado ({$validated['scanned_sku']}) no coincide con el esperado ({$pickListItem->product->sku}).");
            }

            if ($validated['scanned_quantity'] != $pickListItem->quantity_to_pick) {
                 throw new \Exception("Cantidad escaneada ({$validated['scanned_quantity']}) no coincide con la esperada ({$pickListItem->quantity_to_pick}).");
            }

            if ($validated['scanned_lpn'] !== $pickListItem->pallet->lpn) {
                 throw new \Exception("LPN escaneado ({$validated['scanned_lpn']}) no coincide con el esperado ({$pickListItem->pallet->lpn}).");
            }

            $pickListItem->update([
                'is_picked' => true,
                'quantity_picked' => $validated['scanned_quantity'],
                'picked_at' => now(),
            ]);

            $palletItem = PalletItem::where('pallet_id', $pickListItem->pallet_id)
                                    ->where('product_id', $pickListItem->product_id)
                                    ->where('quality_id', $pickListItem->quality_id)
                                    ->first();

            if ($palletItem) {
                 if ($palletItem->quantity < $pickListItem->quantity_to_pick) {
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
                 throw new \Exception("No se encontró el PalletItem correspondiente (PalletID: {$pickListItem->pallet_id}, ProdID: {$pickListItem->product_id}, QID: {$pickListItem->quality_id}). No se pudo descontar inventario.");
            }

            $generalStock = InventoryStock::where('product_id', $pickListItem->product_id)
                                ->where('location_id', $pickListItem->location_id)
                                ->where('quality_id', $pickListItem->quality_id)
                                ->first();

            if ($generalStock) {
                if ($generalStock->quantity < $pickListItem->quantity_to_pick) {
                     Log::warning("Stock general ({$generalStock->quantity}) insuficiente para SKU {$pickListItem->product->sku} en ubicación {$pickListItem->location->code} al confirmar item de picking #{$pickListItem->pick_list_id}. Se necesitaban {$pickListItem->quantity_to_pick}.");
                }

                $newGeneralQuantity = max(0, $generalStock->quantity - $pickListItem->quantity_to_pick);
                $newGeneralCommitted = 0;
                if (isset($generalStock->committed_quantity)) {
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
                    'quantity' => -$pickListItem->quantity_to_pick,
                    'movement_type' => 'SALIDA-PICKING',
                    'source_id' => $pickListItem->id,
                    'source_type' => \App\Models\WMS\PickListItem::class,
                ]);

            } else {
                 Log::error("No se encontró registro de stock general para SKU {$pickListItem->product->sku} (ProdID: {$pickListItem->product_id}), LocID: {$pickListItem->location_id}, QID: {$pickListItem->quality_id} al confirmar item de picking #{$pickListItem->pick_list_id}.");
            }

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Item confirmado correctamente.']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422); // 422 Unprocessable Entity
        }
    }

    public function completePicking(Request $request, PickList $pickList)
    {
        $validated = $request->validate([
            'staging_location_id' => 'required|exists:locations,id',
        ]);

        $pickList->load('items');

        $allItemsPicked = $pickList->items->every(fn($item) => $item->is_picked);

        if (!$allItemsPicked) {
            return back()->with('error', 'No se puede completar el picking. Faltan items por confirmar.');
        }

        DB::beginTransaction();
        try {
            $pickList->update([
                'status' => 'Completed',
                'picker_id' => Auth::id(),
                'picked_at' => now(),
            ]);

            $pickList->salesOrder()->update(['status' => 'Packed']);

            DB::commit();

            return redirect()->route('wms.sales-orders.show', $pickList->sales_order_id)
                           ->with('success', 'Picking completado exitosamente. La orden ha pasado a estado Empacado.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al completar picking #{$pickList->id}: " . $e->getMessage());
            return back()->with('error', 'Ocurrió un error al intentar completar el picking: ' . $e->getMessage());
        }
    }


    public function generatePickListPdf(PickList $pickList)
    {
        $pickList->load([
            'salesOrder',
            'items.product',
            'items.location',
            'items.quality',
            'items.pallet.purchaseOrder'
        ]);

        $qrCodeUrl = route('wms.picking.show', $pickList);

        try {
        $builder = new Builder(
            data: $qrCodeUrl,
            writer: new PngWriter(),
            writerOptions: [],
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::Low,
            size: 100,
            margin: 5,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
            validateResult: false
            );

            $qrResult = $builder->build();

            $qrCodeDataUri = $qrResult->getDataUri();

        } catch (\Exception $e) {
            Log::error("Error generando QR Code v5 para PickList {$pickList->id}: " . $e->getMessage());
            $qrCodeDataUri = null;
        }
        $logoBase64 = null;
        $logoPath = 'LogoAzul.png'; $disk = 's3';
         if (Storage::disk($disk)->exists($logoPath)) {
             try {
                 $logoContent = Storage::disk($disk)->get($logoPath);
                 $mimeType = 'image/' . pathinfo($logoPath, PATHINFO_EXTENSION);
                 if (pathinfo($logoPath, PATHINFO_EXTENSION) === 'svg') { $mimeType = 'image/svg+xml'; }
                 $logoBase64 = 'data:' . $mimeType . ';base64,' . base64_encode($logoContent);
             } catch (\Exception $e) { Log::error("Error cargando logo PDF: ".$e->getMessage()); }
        }


        $data = [
            'pickList' => $pickList,
            'logoBase64' => $logoBase64,
            'qrCodeDataUri' => $qrCodeDataUri
        ];

        $pdf = Pdf::loadView('wms.picking.pdf', $data);

        $fileName = 'PickList-' . $pickList->salesOrder->so_number . '-' . $pickList->id . '.pdf';

        return $pdf->stream($fileName);
    }
}