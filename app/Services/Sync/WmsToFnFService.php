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
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Models\Area;
use App\Mail\OrderActionMail;

class WmsToFnFService
{
    public function syncProduct(Product $product)
    {
        try {
            DB::beginTransaction();

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
                        'master_box_weight' => $product->weight,
                        'upc' => $product->upc,
                        'area_id' => $product->area_id,
                    ]
                );
            });

            DB::commit();
            DB::commit();

            $warnings = $this->checkProductCompleteness($ffProduct);
            if (!empty($warnings)) {
                $this->logTransaction('Advertencia de Sincronización de Producto', "Producto {$product->sku} sincronizado a FnF pero tiene campos faltantes: " . implode(', ', $warnings), ['sku' => $product->sku, 'missing' => $warnings]);
            } else {
                $this->logTransaction('Sincronización de Producto Exitosa', "Producto {$product->sku} sincronizado a FnF", ['sku' => $product->sku]);
            }
            
            return $ffProduct;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError('Error de Sincronización de Producto', $e->getMessage(), ['sku' => $product->sku]);
            return null;
        }
    }

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
            $this->logTransaction('Sincronización de Calidad Exitosa', "Calidad {$quality->name} sincronizada a FnF", ['quality' => $quality->name]);
            return $ffQuality;
        } catch (\Exception $e) {
            $this->logError('Error de Sincronización de Calidad', $e->getMessage(), ['quality_name' => $quality->name]);
            return null;
        }
    }

    public function syncWarehouse(Warehouse $warehouse)
    {
        try {
                return ffWarehouse::updateOrCreate(
                    ['code' => $warehouse->code], 
                    [
                        'description' => $warehouse->name,
                        'address' => $warehouse->address ?? 'Dirección Pendiente',
                        'is_active' => true,
                        'phone' => 'S/T',
                        'area_id' => null,
                    ]
                );
            
            $warnings = $this->checkWarehouseCompleteness($ffWarehouse);
            if (!empty($warnings)) {
                $this->logTransaction('Advertencia de Sincronización de Almacén', "Almacén {$warehouse->code} sincronizado a FnF pero tiene campos faltantes: " . implode(', ', $warnings), ['warehouse' => $warehouse->code, 'missing' => $warnings]);
            } else {
                $this->logTransaction('Sincronización de Almacén Exitosa', "Almacén {$warehouse->code} sincronizado a FnF", ['warehouse' => $warehouse->code]);
            }
            return $ffWarehouse;
        } catch (\Exception $e) {
            $this->logError('Error de Sincronización de Almacén', $e->getMessage(), ['warehouse_code' => $warehouse->code]);
            return null;
        }
    }

    public function createInboundMovement(PurchaseOrder $po)
    {
        try {
            DB::beginTransaction();

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
                 $this->logTransaction('Advertencia de Sincronización de Entrada', "OC {$po->po_number} completada pero no se encontraron pallets para sincronizar.", ['po_id' => $po->id]);
                 DB::commit();
                 return;
            }

            foreach ($receivedItems as $item) {
                $ffProduct = ffProduct::where('sku', $item->sku)->first();
                
                if (!$ffProduct) {
                    $this->logError('Error de Sincronización de Entrada', "Producto SKU {$item->sku} no encontrado en FnF", ['po_id' => $po->id]);
                    continue; 
                }

                $ffQuality = FfQuality::where('name', $item->quality_name)
                                        ->where('area_id', $po->area_id)
                                        ->first();

                if (!$ffQuality) {
                     $ffQuality = FfQuality::where('name', $item->quality_name)->first();
                }

                $ffWarehouse = FfWarehouse::where('code', $po->warehouse->code)->first(); 
                
                ffInventoryMovement::create([
                    'ff_product_id' => $ffProduct->id,
                    'quantity' => $item->quantity,
                    'reason' => 'Compra / Entrada WMS - Folio: ' . $po->po_number,
                    'folio' => $po->id,
                    'ff_warehouse_id' => $ffWarehouse ? $ffWarehouse->id : null,
                    'ff_quality_id' => $ffQuality ? $ffQuality->id : null,
                    'order_type' => 'Purchase', 
                    'status' => 'completed',
                    'user_id' => $po->user_id ?? auth()->id() ?? 1,
                    'area_id' => $po->area_id,
                ]);
            }

            $this->logTransaction('Sincronización de Movimiento de Entrada Exitosa', "OC {$po->po_number} sincronizada a Entrada FnF con Calidades", ['po_number' => $po->po_number]);

            foreach ($receivedItems as $item) {
                $ffProduct = ffProduct::where('sku', $item->sku)->first();
                $ffQuality = FfQuality::where('name', $item->quality_name)->where('area_id', $po->area_id)->first();
                if (!$ffQuality) $ffQuality = FfQuality::where('name', $item->quality_name)->first();
                $ffWarehouse = FfWarehouse::where('code', $po->warehouse->code)->first();

                if ($ffProduct) {
                    $this->allocateBackorders($ffProduct, $ffWarehouse, $ffQuality);
                }
            }
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError('Error de Sincronización de OC', $e->getMessage(), ['po_id' => $po->id]);
        }
    }

    protected function allocateBackorders($product, $warehouse, $quality)
    {
        try {
            $query = ffInventoryMovement::where('ff_product_id', $product->id)
                ->where('is_backorder', 1)
                ->where('backorder_fulfilled', 0)
                ->orderBy('created_at', 'asc');

            if ($warehouse) {
                $query->where('ff_warehouse_id', $warehouse->id);
            }
            if ($quality) {
                $query->where('ff_quality_id', $quality->id);
            } else {
                $query->whereNull('ff_quality_id');
            }

            $backorders = $query->get();

            if ($backorders->isEmpty()) return;

            $stockQuery = $product->movements();
            if ($warehouse) $stockQuery->where('ff_warehouse_id', $warehouse->id);
            if ($quality) $stockQuery->where('ff_quality_id', $quality->id);
            else $stockQuery->whereNull('ff_quality_id');
            
            $currentStock = $stockQuery->sum('quantity');

            foreach ($backorders as $bo) {
                $required = abs($bo->quantity);

                if ($currentStock >= $required) {
                    $bo->update([
                        'backorder_fulfilled' => true,
                        'observations' => $bo->observations . " [AUTO-SURTIDO FIFO: " . date('d/m/Y H:i') . "]",
                    ]);

                    $currentStock -= $required;

                    $pendingBackorders = ffInventoryMovement::where('folio', $bo->folio)
                        ->where('is_backorder', 1)
                        ->where('backorder_fulfilled', 0)
                        ->exists();

                    if (!$pendingBackorders) {
                        ffInventoryMovement::where('folio', $bo->folio)->update([
                            'status' => 'approved',
                            'approved_at' => now()
                        ]);

                        try {
                            $syncService = app(\App\Services\Sync\FnFToWmsService::class);
                            $syncService->syncOutboundOrderFromFolio($bo->folio);
                            
                            $this->logTransaction('Auto-Surtido Completo', "Pedido #{$bo->folio} liberado por llegada de stock.", ['folio' => $bo->folio]);
                            
                            if ($bo->user && $bo->user->email) {
                                try {
                                    $area = Area::find($bo->area_id);
                                    $logoPath = ($area && $area->icon_path) ? $area->icon_path : 'LogoAzulm.PNG';
                                    $logoUrl = Storage::disk('s3')->url($logoPath);
                                    
                                    $mailData = [
                                        'folio' => $bo->folio,
                                        'client_name' => $bo->client_name,
                                        'company_name' => $bo->company_name,
                                        'delivery_date' => $bo->delivery_date ? $bo->delivery_date->format('d/m/Y') : 'N/A',
                                        'vendedor_name' => $bo->user->name,
                                        'logo_url' => $logoUrl,
                                        'items' => [
                                            [
                                                'sku' => $product->sku,
                                                'description' => $product->description,
                                                'quantity' => abs($bo->quantity)
                                            ]
                                        ]
                                    ];
                    
                                    Mail::to($bo->user->email)->send(new OrderActionMail($mailData, 'backorder_filled'));
                                } catch (\Exception $e) {
                                    Log::error("Error enviando email auto-surtido: " . $e->getMessage());
                                }
                            }

                        } catch (\Exception $e) {
                            $this->logError('Error Sync Auto-Surtido', $e->getMessage(), ['folio' => $bo->folio]);
                        }
                    } else {
                        $this->logTransaction('Auto-Surtido Parcial', "Linea de backorder surtida en Pedido #{$bo->folio}. Aún faltan items.", ['folio' => $bo->folio]);
                    }

                } else {
                    break;
                }
            }

        } catch (\Exception $e) {
             $this->logError('Error en Auto-Allocation', $e->getMessage());
        }
    }

    public function createAdjustmentMovement(InventoryAdjustment $adjustment)
    {
        try {
            $ffProduct = ffProduct::where('sku', $adjustment->product->sku)->first();
            if (!$ffProduct) return;

             $quantity = $adjustment->quantity_adjusted ?? $adjustment->quantity_difference;
             
             $ffQuality = null;
             if ($adjustment->palletItem && $adjustment->palletItem->quality) {
                 $ffQuality = FfQuality::where('name', $adjustment->palletItem->quality->name)->first();
             }

             $ffWarehouseId = 1;
             if ($adjustment->location && $adjustment->location->warehouse) {
                 $ffWarehouse = FfWarehouse::where('code', $adjustment->location->warehouse->code)->first();
                 if ($ffWarehouse) $ffWarehouseId = $ffWarehouse->id;
             }

             ffInventoryMovement::create([
                'ff_product_id' => $ffProduct->id,
                'quantity' => $quantity,
                'reason' => 'Ajuste WMS: ' . $adjustment->reason,
                'order_type' => $quantity > 0 ? 'ajuste_entrada' : 'ajuste_salida', 
                'status' => 'completed',
                'user_id' => $adjustment->user_id ?? auth()->id() ?? 1,
                'area_id' => $ffProduct->area_id, 
                'ff_warehouse_id' => $ffWarehouseId,
                'ff_quality_id' => $ffQuality ? $ffQuality->id : null,
             ]);

             if ($quantity > 0) {
                 $wh = FfWarehouse::find($ffWarehouseId);
                 $this->allocateBackorders($ffProduct, $wh, $ffQuality);
             }

             $this->logTransaction('Sincronización de Ajuste Exitosa', "Ajuste {$adjustment->id} sincronizado a FnF", ['adjustment_id' => $adjustment->id]);

        } catch (\Exception $e) {
            $this->logError('Error de Sincronización de Ajuste', $e->getMessage(), ['adj_id' => $adjustment->id]);
        }
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
