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
    // use RefreshDatabase; // Be careful with this on persistent environments. Maybe just create/delete.
    // Given the user environment, I better not wipe their DB. I'll use specific cleanup.

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
            // 'brand_id' => ... need brand
        ]);

        $this->assertDatabaseHas('ff_products', ['sku' => $sku]);
        
        $ffProduct = ffProduct::where('sku', $sku)->first();
        $this->assertEquals('Test WMS Product', $ffProduct->description);

        Notification::assertSentTo(
            User::where('email', 'ismael.garcia@minmer.com')->get(), // Verify admin check logic might fail if user doesn't exist
            SyncTransactionNotification::class
        );
        
        // Cleanup
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

        // Cleanup
        $ffProduct->delete();
        if ($product) $product->delete();
    }
}
