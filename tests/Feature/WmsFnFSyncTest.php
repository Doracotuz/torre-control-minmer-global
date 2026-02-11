<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Product;
use App\Models\ffProduct;
use App\Models\WMS\PurchaseOrder;
use App\Models\WMS\InventoryAdjustment;
use App\Models\ffInventoryMovement;
use App\Models\User;
use App\Models\WMS\Warehouse;
use App\Models\FfWarehouse;
use App\Models\Brand;
use App\Models\ProductType;
use Illuminate\Support\Facades\Notification;
use App\Notifications\SyncTransactionNotification;

class WmsFnFSyncTest extends TestCase
{

    public function test_wms_product_syncs_to_fnf()
    {
        Notification::fake();

        $sku = 'TEST-WMS-' . uniqid();
        $product = Product::create([
            'sku' => $sku,
            'name' => 'Test WMS Product',
            'length' => 10,
            'width' => 10,
            'height' => 10,
            'weight' => 1,
            'unit_of_measure' => 'PZA',
        ]);

        $this->assertDatabaseHas('ff_products', ['sku' => $sku]);
        
        $ffProduct = ffProduct::where('sku', $sku)->first();
        $this->assertEquals('Test WMS Product', $ffProduct->description);

        Notification::assertSentTo(
            User::where('email', 'ismael.garcia@minmer.com')->get(),
            SyncTransactionNotification::class
        );
        
        $product->delete();
        if ($ffProduct) $ffProduct->delete();
    }

    public function test_fnf_product_syncs_to_wms()
    {
        Notification::fake();

        $sku = 'TEST-FNF-' . uniqid();
        $ffProduct = ffProduct::create([
            'sku' => $sku,
            'description' => 'Test FnF Product',
            'price' => 100,
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('products', ['sku' => $sku]);
        
        $product = Product::where('sku', $sku)->first();
        $this->assertEquals('Test FnF Product', $product->name);

        $ffProduct->delete();
        if ($product) $product->delete();
    }
}
