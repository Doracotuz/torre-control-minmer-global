$sku = 'SkuPruebaC1';
$p = \App\Models\Product::where('sku', $sku)->first();

if (!$p) {
    echo "Product not found: $sku\n";
} else {
    echo "Product ID: " . $p->id . "\n";
    $items = \App\Models\WMS\PalletItem::where('product_id', $p->id)->get();

    foreach($items as $item) {
        $pallet = $item->pallet;
        $loc = $pallet->location;
        $po = $pallet->purchaseOrder;
        
        echo "Item ID: " . $item->id . " | Qty: " . $item->quantity . " | Committed: " . $item->committed_quantity . "\n";
        echo "  Pallet LPN: " . $pallet->lpn . "\n";
        
        if ($loc) {
            $wh = \App\Models\Warehouse::find($loc->warehouse_id);
            echo "  Location: " . $loc->code . " (ID: " . $loc->id . ") | Warehouse ID: " . $loc->warehouse_id . " (" . ($wh ? $wh->name : 'Unknown') . ")\n";
        } else {
            echo "  Location: NULL\n";
        }

        if ($po) {
            $area = \App\Models\Area::find($po->area_id);
            echo "  PO Area ID: " . ($po->area_id ?? 'NULL') . " (" . ($area ? $area->name : 'Global/Unknown') . ")\n";
        } else {
            echo "  PO: NULL\n";
        }
        echo "--------------------------\n";
    }
}
exit();
