<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use App\Traits\BelongsToArea;
use App\Models\ffInventoryMovement;
use App\Models\FfOrderEvidence;
use App\Models\FfOrderDocument;
// -----------------------------

class ffProduct extends Model
{
    use HasFactory, BelongsToArea;

    protected $fillable = [
        'sku',
        'description',
        'unit_price',
        'brand',
        'type',
        'pieces_per_box',
        'length',
        'width',
        'height',
        'upc',
        'photo_path',
        'is_active',
        'area_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'unit_price' => 'decimal:2',
        'length' => 'decimal:2',
        'width' => 'decimal:2',
        'height' => 'decimal:2',
        'pieces_per_box' => 'integer',
    ];

    protected $appends = ['photo_url'];
    
    protected $with = ['channels'];

    public function getPhotoUrlAttribute()
    {
        if ($this->photo_path) {
            return Storage::disk('s3')->url($this->photo_path);
        }
        return 'https://placehold.co/100x100/e0e0e0/909090?text=' . $this->sku;
    }

    protected static function booted(): void
    {
        static::deleting(function (ffProduct $product) {
            
            if ($product->photo_path) {
                if(Storage::disk('s3')->exists($product->photo_path)){
                    Storage::disk('s3')->delete($product->photo_path);
                }
            }

            $folios = ffInventoryMovement::where('ff_product_id', $product->id)
                        ->pluck('folio')
                        ->unique();

            foreach ($folios as $folio) {
                $otherItemsCount = ffInventoryMovement::where('folio', $folio)
                                    ->where('ff_product_id', '!=', $product->id)
                                    ->count();

                if ($otherItemsCount === 0) {
                    
                    $evidences = FfOrderEvidence::where('folio', $folio)->get();
                    foreach ($evidences as $evidence) {
                        if (Storage::disk('s3')->exists($evidence->path)) {
                            Storage::disk('s3')->delete($evidence->path);
                        }
                        $evidence->delete();
                    }

                    $documents = FfOrderDocument::where('folio', $folio)->get();
                    foreach ($documents as $doc) {
                        if ($doc->path && Storage::disk('s3')->exists($doc->path)) {
                            Storage::disk('s3')->delete($doc->path);
                        }
                        $doc->delete();
                    }
                }
            }
        });
    }

    public function movements()
    {
        return $this->hasMany(ffInventoryMovement::class);
    }

    public function cartItems()
    {
        return $this->hasMany(ffCartItem::class, 'ff_product_id');
    }
    
    public function channels()
    {
        return $this->belongsToMany(FfSalesChannel::class, 'ff_product_sales_channel', 'ff_product_id', 'ff_sales_channel_id');
    }
}