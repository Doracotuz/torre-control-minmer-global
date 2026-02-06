<?php

namespace App\Services\Sync;

use App\Models\Product;
use App\Models\ffProduct;
use App\Models\WMS\Quality;
use App\Models\FfQuality;
use App\Models\Warehouse;
use App\Models\FfWarehouse;
use App\Models\WMS\PurchaseOrder;
use App\Models\ffInventoryMovement;
use App\Models\WMS\InventoryAdjustment;
use App\Models\SyncNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WmsToFnFService
{
    /**
     * Sync a WMS Product to FnF Product
     */
    public function syncProduct(Product $product)
    {
        try {
            DB::beginTransaction();

            // Find or create FnF Product by SKU
            // Find or create FnF Product by SKU
            $ffProduct = ffProduct::withoutEvents(function () use ($product) {
                return ffProduct::updateOrCreate(
                    ['sku' => $product->sku],
                    [
                        'description' => $product->name,
                        'brand' => $product->brand ? $product->brand->name : null,
                        'type' => $product->productType ? $product->productType->name : null,
                        'pieces_per_box' => $product->pieces_per_case ?? 1,
                        'length' => $product->length,
                        'width' => $product->width,
                        'height' => $product->height,
                        'upc' => $product->upc,
                        'area_id' => $product->area_id,
                    ]
                );
            });

            DB::commit();
            DB::commit();

            $warnings = $this->checkProductCompleteness($ffProduct);
            if (!empty($warnings)) {
                $this->logTransaction('Product Sync Warning', "Product {$product->sku} synced to FnF but has missing fields: " . implode(', ', $warnings), ['sku' => $product->sku, 'missing' => $warnings]);
            } else {
                $this->logTransaction('Product Sync Success', "Product {$product->sku} synced to FnF", ['sku' => $product->sku]);
            }
            
            return $ffProduct;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError('Product Sync Error', $e->getMessage(), ['sku' => $product->sku]);
            return null;
        }
    }

    /**
     * Sync WMS Quality to FnF Quality
     */
    public function syncQuality(Quality $quality)
    {
        try {
            $ffQuality = FfQuality::withoutEvents(function () use ($quality) {
                 return FfQuality::updateOrCreate(
                    ['name' => $quality->name],
                    [
                        'is_active' => $quality->is_available ?? true,
                        'area_id' => $quality->area_id,
                    ]
                );
            });
            $this->logTransaction('Quality Sync Success', "Quality {$quality->name} synced to FnF", ['quality' => $quality->name]);
            return $ffQuality;
        } catch (\Exception $e) {
            $this->logError('Quality Sync Error', $e->getMessage(), ['quality_name' => $quality->name]);
            return null;
        }
    }

    /**
     * Sync WMS Warehouse to FnF Warehouse
     */
    public function syncWarehouse(Warehouse $warehouse)
    {
        try {
                return ffWarehouse::updateOrCreate(
                    ['code' => $warehouse->code], 
                    [
                        'description' => $warehouse->name,
                        'address' => $warehouse->address ?? 'Dirección Pendiente',
                        'is_active' => true,
                        'phone' => 'S/T', // Mandatory in FnF, missing in WMS
                        'area_id' => 1, // Default area as WMS warehouses are global
                    ]
                );
            
            $warnings = $this->checkWarehouseCompleteness($ffWarehouse);
            if (!empty($warnings)) {
                $this->logTransaction('Warehouse Sync Warning', "Warehouse {$warehouse->code} synced to FnF but has missing fields: " . implode(', ', $warnings), ['warehouse' => $warehouse->code, 'missing' => $warnings]);
            } else {
                $this->logTransaction('Warehouse Sync Success', "Warehouse {$warehouse->code} synced to FnF", ['warehouse' => $warehouse->code]);
            }
            return $ffWarehouse;
        } catch (\Exception $e) {
            $this->logError('Warehouse Sync Error', $e->getMessage(), ['warehouse_code' => $warehouse->code]);
            return null;
        }
    }

    /**
     * Create Inbound Movement in FnF when PO is completed WMS
     */
    public function createInboundMovement(PurchaseOrder $po)
    {
        // Only if PO is completed
        try {
            DB::beginTransaction();

            // Get received items matched with Quality
            $receivedItems = DB::table('pallet_items')
                ->join('pallets', 'pallet_items.pallet_id', '=', 'pallets.id')
                ->join('products', 'pallet_items.product_id', '=', 'products.id')
                ->join('qualities', 'pallet_items.quality_id', '=', 'qualities.id')
                ->where('pallets.purchase_order_id', $po->id)
                ->select(
                    'pallet_items.product_id',
                    'products.sku', 
                    'pallet_items.quality_id',
                    'qualities.name as quality_name',
                    DB::raw('SUM(pallet_items.quantity) as quantity')
                )
                ->groupBy('pallet_items.product_id', 'pallet_items.quality_id', 'products.sku', 'qualities.name')
                ->get();

            if ($receivedItems->isEmpty()) {
                 // Fallback to PO lines if no pallets processed (e.g. manual bypass)
                 // But in this new logic, we assume WMS flow via pallets.
                 // If empty, nothing to sync.
                 $this->logTransaction('Inbound Sync Warning', "PO {$po->po_number} completed but no pallet items found to sync.", ['po_id' => $po->id]);
                 DB::commit();
                 return;
            }

            foreach ($receivedItems as $item) {
                // Find matching FnF Product
                $ffProduct = ffProduct::where('sku', $item->sku)->first();
                
                if (!$ffProduct) {
                    $this->logError('Inbound Sync Error', "Product SKU {$item->sku} not found in FnF", ['po_id' => $po->id]);
                    continue; 
                }

                // Resolve FnF Quality
                // We sync qualities to FnF by name. Find the FnF ID.
                $ffQuality = FfQuality::where('name', $item->quality_name)
                                        ->where('area_id', $po->area_id) // Filter by area if needed, or global
                                        ->first();

                // If quality doesn't exist in FnF, try to sync it on the fly or log error?
                // WMS to FnF Quality Sync is usually proactive.
                if (!$ffQuality) {
                     // Try to find global quality or just by name
                     $ffQuality = FfQuality::where('name', $item->quality_name)->first();
                }

                // Resolve Warehouse
                $ffWarehouse = FfWarehouse::where('code', $po->warehouse->code)->first(); 
                
                ffInventoryMovement::create([
                    'ff_product_id' => $ffProduct->id,
                    'quantity' => $item->quantity,
                    'reason' => 'Compra / Entrada WMS',
                    'folio' => $po->po_number,
                    'ff_warehouse_id' => $ffWarehouse ? $ffWarehouse->id : null,
                    'ff_quality_id' => $ffQuality ? $ffQuality->id : null, // Save Quality!
                    'order_type' => 'Purchase', 
                    'status' => 'completed',
                    'user_id' => $po->user_id ?? auth()->id() ?? 1,
                    'area_id' => $po->area_id,
                ]);
            }

            DB::commit();
            $this->logTransaction('Inbound Movement Sync Success', "PO {$po->po_number} synced to FnF Inbound with Qualities", ['po_number' => $po->po_number]);
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError('PO Sync Error', $e->getMessage(), ['po_id' => $po->id]);
        }
    }

    /**
     * Create Adjustment Movement in FnF
     */
    public function createAdjustmentMovement(InventoryAdjustment $adjustment)
    {
        // Map WMS adjustment to FnF movement
        try {
            $ffProduct = ffProduct::where('sku', $adjustment->product->sku)->first();
            if (!$ffProduct) return;

             // Logic to determine if positive or negative
             $quantity = $adjustment->quantity_adjusted ?? $adjustment->quantity_difference; // Depending on model
             
             // Resolve Quality
             $ffQuality = null;
             if ($adjustment->palletItem && $adjustment->palletItem->quality) {
                 $ffQuality = FfQuality::where('name', $adjustment->palletItem->quality->name)->first();
             }

             // Resolve Warehouse
             // If adjustment has location, we can get warehouse, else default?
             $ffWarehouseId = 1; // Default
             if ($adjustment->location && $adjustment->location->warehouse) {
                 $ffWarehouse = FfWarehouse::where('code', $adjustment->location->warehouse->code)->first();
                 if ($ffWarehouse) $ffWarehouseId = $ffWarehouse->id;
             }

             ffInventoryMovement::create([
                'ff_product_id' => $ffProduct->id,
                'quantity' => $quantity, // Use signed value
                'reason' => 'Ajuste WMS: ' . $adjustment->reason,
                'order_type' => $quantity > 0 ? 'ajuste_entrada' : 'ajuste_salida', 
                'status' => 'completed',
                'user_id' => $adjustment->user_id ?? auth()->id() ?? 1,
                'area_id' => $ffProduct->area_id, 
                'ff_warehouse_id' => $ffWarehouseId,
                'ff_quality_id' => $ffQuality ? $ffQuality->id : null,
             ]);

             $this->logTransaction('Adjustment Sync Success', "Adjustment {$adjustment->id} synced to FnF", ['adjustment_id' => $adjustment->id]);

        } catch (\Exception $e) {
            $this->logError('Adjustment Sync Error', $e->getMessage(), ['adj_id' => $adjustment->id]);
        }
    }

    protected function logError($type, $message, $payload = [])
    {
        Log::error("[$type] $message", $payload);
        $this->logTransaction($type, $message, $payload);
    }

    protected function logTransaction($type, $message, $payload = [])
    {
        // Log to database only, no emails
        SyncNotification::create([
            'type' => $type,
            'message' => $message,
            'payload' => $payload,
        ]);
    }

    protected function checkProductCompleteness(ffProduct $product)
    {
        $missing = [];
        if (!$product->brand) $missing[] = 'Brand';
        if (!$product->type) $missing[] = 'Type';
        if ($product->length <= 0) $missing[] = 'Length';
        if ($product->width <= 0) $missing[] = 'Width';
        if ($product->height <= 0) $missing[] = 'Height';
        if (!$product->upc) $missing[] = 'UPC';
        
        return $missing;
    }

    protected function checkWarehouseCompleteness(FfWarehouse $warehouse)
    {
        $missing = [];
        if (empty($warehouse->address)) $missing[] = 'Address';
        return $missing;
    }
}
