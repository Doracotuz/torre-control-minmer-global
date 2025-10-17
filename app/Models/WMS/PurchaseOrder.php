<?php

namespace App\Models\WMS;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\WMS\PurchaseOrderLine;
use App\Models\WMS\Pallet;
use Illuminate\Support\Facades\DB;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'po_number', 'expected_date', 'status', 'user_id', 'container_number',
        'document_invoice', 'pedimento_a4', 'pedimento_g1', 'total_pallets',
        'expected_bottles', 'received_bottles', 'operator_name',
        'download_start_time', 'download_end_time'
    ];

    // --- RELACIONES ---
    public function user() { return $this->belongsTo(User::class); }
    public function lines() { return $this->hasMany(PurchaseOrderLine::class); }
    public function latestArrival() { return $this->hasOne(DockArrival::class)->latestOfMany(); }

    // CORRECCIÓN: Asegúrate de que esta relación apunte a tu modelo Pallet correcto
    public function pallets() { return $this->hasMany(Pallet::class); }

    // --- ACCESORES ---
    public function getStatusInSpanishAttribute(): string
    {
        return match ($this->status) {
            'Pending' => 'Pendiente', 'Receiving' => 'En Recepción',
            'Completed' => 'Completado', default => $this->status,
        };
    }
    
    // --- FUNCIÓN DE CÁLCULO CORREGIDA ---
    public function getReceiptSummary()
    {
        $expectedLines = $this->lines()->with('product')->get();
        
        $receivedItemsByProduct = DB::table('pallet_items')
            ->join('pallets', 'pallet_items.pallet_id', '=', 'pallets.id')
            ->where('pallets.purchase_order_id', $this->id)
            ->select('pallet_items.product_id', DB::raw('SUM(pallet_items.quantity) as total_received'), DB::raw('COUNT(DISTINCT pallets.id) as pallet_count'))
            ->groupBy('pallet_items.product_id')
            ->get()
            ->keyBy('product_id');

        return $expectedLines->map(function ($line) use ($receivedItemsByProduct) {
            $receivedData = $receivedItemsByProduct->get($line->product_id);
            
            $quantity_received = $receivedData ? $receivedData->total_received : 0;
            $pieces_per_case = $line->product->pieces_per_case > 0 ? $line->product->pieces_per_case : 1;
            
            return (object) [
                'sku' => $line->product->sku,
                'product_name' => $line->product->name,
                'quantity_ordered' => $line->quantity_ordered,
                'quantity_received' => $quantity_received,
                'pallet_count' => $receivedData ? $receivedData->pallet_count : 0,
                
                // --- CAMBIO DE round() a ceil() ---
                'cases_received' => ceil($quantity_received / $pieces_per_case),
            ];
        });
    }

    public function getTotalCasesReceivedAttribute()
    {
        $summary = $this->getReceiptSummary();
        
        return $summary->sum('cases_received');
    }

    public function evidences()
    {
        return $this->hasMany(ReceiptEvidence::class);
    }    

}