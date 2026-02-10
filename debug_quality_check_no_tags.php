$sku = 'SkuPruebaC1';
$p = \App\Models\Product::where('sku', $sku)->first();

if (!$p) {
    echo "Product not found: $sku\n";
} else {
    echo "Product ID: " . $p->id . "\n";
    $items = \App\Models\WMS\PalletItem::where('product_id', $p->id)->get();

    foreach($items as $item) {
        $quality = $item->quality;
        echo "Item ID: " . $item->id . " | Quality ID: " . $item->quality_id . " Name: " . ($quality ? $quality->name : 'Unknown') . "\n";
    }
}
exit();
