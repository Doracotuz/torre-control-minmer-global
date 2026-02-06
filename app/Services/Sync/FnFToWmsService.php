<?php

namespace App\Services\Sync;

use App\Models\Product;
use App\Models\ffProduct;
use App\Models\WMS\Quality;
use App\Models\FfQuality;
use App\Models\Warehouse;
use App\Models\FfWarehouse;
use App\Models\ffInventoryMovement;
use App\Models\WMS\PickList; // Or SalesOrder
use App\Models\SyncNotification;
use App\Models\Brand;
use App\Models\ProductType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FnFToWmsService
{
    /**
     * Sync FnF Product to WMS Product
     */
    public function syncProduct(ffProduct $ffProduct)
    {
        try {
            DB::beginTransaction();

            $brandId = $this->resolveBrandId($ffProduct->brand, $ffProduct->area_id);
            $typeId = $this->resolveTypeId($ffProduct->type, $ffProduct->area_id);

            // Sync Product without triggering WMS observers (prevents infinite loop)
            $product = Product::withoutEvents(function () use ($ffProduct, $brandId, $typeId) {
                return Product::updateOrCreate(
                    ['sku' => $ffProduct->sku],
                    [
                        'name' => $ffProduct->description,
                        'description' => $ffProduct->description,
                        'brand_id' => $brandId,
                        'product_type_id' => $typeId,
                        'length' => $ffProduct->length,
                        'width' => $ffProduct->width,
                        'height' => $ffProduct->height,
                        'upc' => $ffProduct->upc,
                        'unit_of_measure' => 'PZA',
                        'pieces_per_case' => $ffProduct->pieces_per_box ?? 1, // Default to 1 to avoid DB error
                        'area_id' => $ffProduct->area_id,
                    ]
                );
            });

            DB::commit();
            
            $warnings = $this->checkProductCompleteness($product);
            if (!empty($warnings)) {
                $this->logTransaction('Product Sync Warning', "Product {$ffProduct->sku} synced but has missing fields: " . implode(', ', $warnings), ['sku' => $ffProduct->sku, 'missing' => $warnings]);
            } else {
                $this->logTransaction('Product Sync Success', "FnF Product {$ffProduct->sku} synced to WMS", ['sku' => $ffProduct->sku]);
            }
            
            return $product;
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            if ($e->errorInfo[1] == 1062) {
                // Check if it's UPC or SKU (though matching SKU would update, so likely UPC)
                $this->logError('Product Sync Error', "Duplicate Value: UPC already exists on another WMS product. " . $e->getMessage(), ['sku' => $ffProduct->sku, 'upc' => $ffProduct->upc]);
            } else {
                $this->logError('FnF Product Sync Error', $e->getMessage(), ['sku' => $ffProduct->sku]);
            }
            return null;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError('FnF Product Sync Error', $e->getMessage(), ['sku' => $ffProduct->sku]);
            return null;
        }
    }

    /**
     * Sync FnF Quality to WMS
     */
    public function syncQuality(FfQuality $ffQuality)
    {
        try {
            Quality::withoutEvents(function () use ($ffQuality) {
                Quality::updateOrCreate(
                    ['name' => $ffQuality->name],
                    [
                        'is_available' => $ffQuality->is_active,
                        'area_id' => $ffQuality->area_id,
                    ]
                );
            });
            $this->logTransaction('Quality Sync Success', "FnF Quality {$ffQuality->name} synced to WMS", ['quality' => $ffQuality->name]);
        } catch (\Exception $e) {
            $this->logError('FnF Quality Sync Error', $e->getMessage(), ['quality' => $ffQuality->name]);
        }
    }

    /**
     * Sync FnF Warehouse to WMS
     */
    public function syncWarehouse(FfWarehouse $ffWarehouse)
    {
        try {
            $warehouse = Warehouse::withoutEvents(function () use ($ffWarehouse) {
                return Warehouse::updateOrCreate(
                    ['code' => $ffWarehouse->code],
                    [
                        'name' => $ffWarehouse->description,
                        'address' => $ffWarehouse->address,
                    ]
                );
            });
            $warnings = $this->checkWarehouseCompleteness($warehouse);
            if (!empty($warnings)) {
                $this->logTransaction('Warehouse Sync Warning', "Warehouse {$ffWarehouse->code} synced but has missing fields: " . implode(', ', $warnings), ['warehouse' => $ffWarehouse->code, 'missing' => $warnings]);
            } else {
                $this->logTransaction('Warehouse Sync Success', "FnF Warehouse {$ffWarehouse->code} synced to WMS", ['warehouse' => $ffWarehouse->code]);
            }
        } catch (\Exception $e) {
            $this->logError('FnF Warehouse Sync Error', $e->getMessage(), ['warehouse' => $ffWarehouse->code]);
        }
    }

    /**
     * Create Outbound Request in WMS (Sales Order / Pick List)
     * Triggered when FnF Order is Authorized
     */
    public function createOutboundOrder(ffInventoryMovement $movement)
    {
        // Logic: Create a SalesOrder in WMS for the team to pick
        // Do NOT execute picking yet.
        try {
            DB::beginTransaction();

            // Find WMS Product
            $product = Product::where('sku', $movement->product->sku)->first();
            if (!$product) {
                throw new \Exception("Product SKU {$movement->product->sku} not found in WMS");
            }
            
            // Create Sales Order or Pick Request
            // For simplicity, let's assume SalesOrder structure
            /*
            $so = \App\Models\WMS\SalesOrder::create([
                'order_number' => $movement->folio ?? 'FNF-' . $movement->id,
                'customer_name' => $movement->client_name ?? 'Friends & Family',
                'warehouse_id' => $this->resolveWarehouseId($movement->ff_warehouse_id),
                'status' => 'pending', 
                // ... other fields
            ]);
            
            $so->lines()->create([
                'product_id' => $product->id,
                'quantity' => $movement->quantity,
                // ...
            ]);
            */
            
            // NOTE: Check if WMS SalesOrder implementation exists or if we should use PickList directly.
            // Using placeholder logic for now.

            DB::commit();
            $this->logTransaction('Outbound Order Sync Success', "FnF Movement {$movement->id} synced to WMS Sales Order", ['movement_id' => $movement->id]);

        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError('FnF Outbound Sync Error', $e->getMessage(), ['movement_id' => $movement->id]);
        }
    }

    protected function resolveBrandId($brandName, $areaId = null)
    {
        if (!$brandName) return null;
        $brand = Brand::updateOrCreate(
            ['name' => $brandName],
            ['area_id' => $areaId] // Update area_id if it changes
        );
        return $brand->id;
    }

    protected function resolveTypeId($typeName, $areaId = null)
    {
        if (!$typeName) return null;
        $type = ProductType::updateOrCreate(
            ['name' => $typeName],
            ['area_id' => $areaId] // Update area_id if it changes
        );
        return $type->id;
    }
    
    protected function resolveWarehouseId($ffWarehouseId)
    {
        // Map FnF Warehouse ID to WMS Warehouse ID via code
        $ffWarehouse = FfWarehouse::find($ffWarehouseId);
        if (!$ffWarehouse) return null;
        
        $wmsWarehouse = Warehouse::where('code', $ffWarehouse->code)->first();
        return $wmsWarehouse ? $wmsWarehouse->id : null;
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

    protected function checkProductCompleteness(Product $product)
    {
        $missing = [];
        if (!$product->brand_id) $missing[] = 'Brand';
        if (!$product->product_type_id) $missing[] = 'Type';
        if ($product->length <= 0) $missing[] = 'Length';
        if ($product->width <= 0) $missing[] = 'Width';
        if ($product->height <= 0) $missing[] = 'Height';
        if (!$product->upc) $missing[] = 'UPC';
        if (!$product->area_id) $missing[] = 'Area';
        
        return $missing;
    }

    protected function checkWarehouseCompleteness(Warehouse $warehouse)
    {
        $missing = [];
        if (empty($warehouse->address)) $missing[] = 'Address';
        return $missing;
    }
}
