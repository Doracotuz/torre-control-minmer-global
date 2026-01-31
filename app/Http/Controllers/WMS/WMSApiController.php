<?php

namespace App\Http\Controllers\WMS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;

class WMSApiController extends Controller
{
    public function searchProducts(Request $request)
    {
        if (!$request->filled('query')) {
            return response()->json([]);
        }

        $term = $request->input('query');
        $areaId = $request->input('area_id');

        $query = Product::query()
            ->where(function($q) use ($term) {
                $q->where('sku', 'like', "%{$term}%")
                  ->orWhere('name', 'like', "%{$term}%")
                  ->orWhere('upc', 'like', "%{$term}%");
            });

        if ($areaId) {
            $query->where('area_id', $areaId);
        } else {
            return response()->json([]); 
        }

        $products = $query->limit(20)
            ->get(['id', 'sku', 'name', 'upc', 'area_id']);

        return response()->json($products);
    }
}