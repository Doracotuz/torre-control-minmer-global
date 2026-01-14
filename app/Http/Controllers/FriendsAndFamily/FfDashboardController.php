<?php

namespace App\Http\Controllers\FriendsAndFamily;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ffProduct;
use App\Models\ffInventoryMovement;
use Carbon\Carbon;

class FfDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $productsQuery = ffProduct::where('is_active', true);
        $movementsQuery = ffInventoryMovement::query();

        if (!$user->isSuperAdmin()) {
            $productsQuery->where('area_id', $user->area_id);
            $movementsQuery->where('area_id', $user->area_id);
        }

        $productsCount = $productsQuery->count();

        $todayMovements = (clone $movementsQuery)
            ->whereDate('created_at', Carbon::today())
            ->count();

        $lowStockCount = $productsQuery->withSum('movements', 'quantity')
            ->get()
            ->filter(function ($product) { 
                return ($product->movements_sum_quantity ?? 0) < 10; 
            })
            ->count();

        $lastSale = (clone $movementsQuery)
            ->where('quantity', '<', 0)
            ->latest('created_at')
            ->first();

        return view('friends-and-family.index', compact(
            'productsCount', 
            'todayMovements', 
            'lowStockCount', 
            'lastSale'
        ));
    }
}