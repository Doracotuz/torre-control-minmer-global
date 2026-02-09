<?php

namespace App\Http\Controllers\WMS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class WMSApiController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!Auth::user()->hasFfPermission('wms.products')) {
                abort(403, 'No tienes permiso para buscar productos WMS.');
            }
            return $next($request);
        });
    }

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