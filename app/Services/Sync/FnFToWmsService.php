<?php

namespace App\Services\Sync;

use App\Models\Product;
use App\Models\ffProduct;
use App\Models\WMS\Quality;
use App\Models\FfQuality;
use App\Models\Warehouse;
use App\Models\FfWarehouse;
use App\Models\ffInventoryMovement;
use App\Models\WMS\PickList;
use App\Models\SyncNotification;
use App\Models\Brand;
use App\Models\ProductType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FnFToWmsService
{
    public function syncProduct(ffProduct $ffProduct)
    {
        try {
            DB::beginTransaction();

            $brandId = $this->resolveBrandId($ffProduct->brand, $ffProduct->area_id);
            $typeId = $this->resolveTypeId($ffProduct->type, $ffProduct->area_id);

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
                        'weight' => $ffProduct->master_box_weight,
                        'upc' => $ffProduct->upc,
                        'unit_of_measure' => 'PZA',
                        'pieces_per_case' => $ffProduct->pieces_per_box ?? 1,
                        'area_id' => $ffProduct->area_id,
                    ]
                );
            });

            DB::commit();
            
            $warnings = $this->checkProductCompleteness($product);
            if (!empty($warnings)) {
                $this->logTransaction('Advertencia de Sincronización de Producto', "Producto {$ffProduct->sku} sincronizado pero tiene campos faltantes: " . implode(', ', $warnings), ['sku' => $ffProduct->sku, 'missing' => $warnings]);
            } else {
                $this->logTransaction('Sincronización de Producto Exitosa', "Producto FnF {$ffProduct->sku} sincronizado a WMS", ['sku' => $ffProduct->sku]);
            }
            
            return $product;
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            if ($e->errorInfo[1] == 1062) {
                $this->logError('Error de Sincronización de Producto', "Valor Duplicado: UPC ya existe en otro producto WMS. " . $e->getMessage(), ['sku' => $ffProduct->sku, 'upc' => $ffProduct->upc]);
            } else {
                $this->logError('Error de Sincronización de Producto FnF', $e->getMessage(), ['sku' => $ffProduct->sku]);
            }
            return null;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError('Error de Sincronización de Producto FnF', $e->getMessage(), ['sku' => $ffProduct->sku]);
            return null;
        }
    }

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
            $this->logTransaction('Sincronización de Calidad Exitosa', "Calidad FnF {$ffQuality->name} sincronizada a WMS", ['quality' => $ffQuality->name]);
        } catch (\Exception $e) {
            $this->logError('Error de Sincronización de Calidad FnF', $e->getMessage(), ['quality' => $ffQuality->name]);
        }
    }

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
                $this->logTransaction('Advertencia de Sincronización de Almacén', "Almacén {$ffWarehouse->code} sincronizado pero tiene campos faltantes: " . implode(', ', $warnings), ['warehouse' => $ffWarehouse->code, 'missing' => $warnings]);
            } else {
                $this->logTransaction('Sincronización de Almacén Exitosa', "Almacén FnF {$ffWarehouse->code} sincronizado a WMS", ['warehouse' => $ffWarehouse->code]);
            }
        } catch (\Exception $e) {
            $this->logError('Error de Sincronización de Almacén FnF', $e->getMessage(), ['warehouse' => $ffWarehouse->code]);
        }
    }

    public function createOutboundOrder(ffInventoryMovement $movement)
    {
        if ($movement->quantity >= 0) return;

        try {
            DB::beginTransaction();

            $product = Product::where('sku', $movement->product->sku)->first();
            if (!$product) {
                $this->logError('Error de Sincronización de Salida', "Producto SKU {$movement->product->sku} no encontrado en WMS", ['movement_id' => $movement->id]);
                DB::commit(); 
                return;
            }
            
            $wmsWarehouseId = $this->resolveWarehouseId($movement->ff_warehouse_id);
            if (!$wmsWarehouseId) {
                 $wh = Warehouse::where('area_id', $movement->area_id)->first();
                 $wmsWarehouseId = $wh ? $wh->id : 1; 
            }

            $so = \App\Models\WMS\SalesOrder::create([
                'so_number' => $movement->folio,
                'customer_name' => $movement->client_name ?? 'Friends & Family',
                'warehouse_id' => $wmsWarehouseId,
                'area_id' => $movement->area_id,
                'user_id' => $movement->user_id,
                'status' => 'Pending', 
                'order_date' => now(),
                'notes' => 'Generado desde FnF. Motivo: ' . $movement->reason
            ]);
            
            $wmsQualityId = null;
            if ($movement->ff_quality_id && $movement->quality) {
                 $wmsQuality = \App\Models\WMS\Quality::where('name', $movement->quality->name)
                                    ->where('area_id', $movement->area_id)
                                    ->first();
                 $wmsQualityId = $wmsQuality ? $wmsQuality->id : null;
            }

            $so->lines()->create([
                'product_id' => $product->id,
                'quantity_ordered' => abs($movement->quantity),
                'quality_id' => $wmsQualityId
            ]);
            
            DB::commit();
            $this->logTransaction('Sincronización de Pedido de Salida Exitosa', "Movimiento FnF {$movement->folio} sincronizado a Orden de Venta WMS #{$so->id}", ['movement_id' => $movement->id, 'so_id' => $so->id]);

        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError('Error de Sincronización de Salida FnF', $e->getMessage(), ['movement_id' => $movement->id]);
        }
    }

    public function syncOutboundOrderFromFolio($folio)
    {
        $movements = ffInventoryMovement::where('folio', $folio)
            ->where('quantity', '<', 0)
            ->get();

        if ($movements->isEmpty()) return;

        try {
            DB::beginTransaction();
            
            $first = $movements->first();
            
            $wmsWarehouseId = $this->resolveWarehouseId($first->ff_warehouse_id);
            if (!$wmsWarehouseId) {
                 $wh = Warehouse::where('area_id', $first->area_id)->first();
                 $wmsWarehouseId = $wh ? $wh->id : 1; 
            }

            $so = \App\Models\WMS\SalesOrder::firstOrCreate(
                ['so_number' => $folio],
                [
                    'customer_name' => $first->client_name ?? 'Friends & Family',
                    'warehouse_id' => $wmsWarehouseId,
                    'area_id' => $first->area_id,
                    'user_id' => $first->user_id,
                    'status' => 'Pending', 
                    'order_date' => now(),
                    'notes' => 'Generado desde FnF. Motivo: ' . $first->reason
                ]
            );

            foreach ($movements as $movement) {
                $product = Product::where('sku', $movement->product->sku)->first();
                if (!$product) {
                    $this->logError('Advertencia de Sincronización de Salida', "Producto {$movement->product->sku} no encontrado para Orden de Venta #$folio", ['folio' => $folio]);
                    continue;
                }

                $wmsQualityId = null;
                if ($movement->quality) {
                     $wmsQuality = \App\Models\WMS\Quality::where('name', $movement->quality->name)
                                        ->where('area_id', $movement->area_id)
                                        ->first();
                     $wmsQualityId = $wmsQuality ? $wmsQuality->id : null;
                }

                $line = $so->lines()->where('product_id', $product->id)
                           ->where('quality_id', $wmsQualityId)
                           ->first();
                
                if ($line) {
                    $line->quantity_ordered = abs($movement->quantity);
                    $line->save();
                } else {
                    $so->lines()->create([
                        'product_id' => $product->id,
                        'quantity_ordered' => abs($movement->quantity),
                        'quality_id' => $wmsQualityId
                    ]);
                }
            }

            DB::commit();
            $this->logTransaction('Sincronización de Pedido de Salida Exitosa', "Folio FnF $folio sincronizado a Orden de Venta WMS #{$so->id}", ['folio' => $folio]);

        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError('Error de Sincronización de Salida FnF', $e->getMessage(), ['folio' => $folio]);
        }
    }

    protected function resolveBrandId($brandName, $areaId = null)
    {
        if (!$brandName) return null;
        $brand = Brand::updateOrCreate(
            ['name' => $brandName],
            ['area_id' => $areaId]
        );
        return $brand->id;
    }

    protected function resolveTypeId($typeName, $areaId = null)
    {
        if (!$typeName) return null;
        $type = ProductType::updateOrCreate(
            ['name' => $typeName],
            ['area_id' => $areaId]
        );
        return $type->id;
    }
    
    protected function resolveWarehouseId($ffWarehouseId)
    {
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
