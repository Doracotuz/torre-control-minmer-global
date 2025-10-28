<?php

namespace App\Http\Controllers\AreaAdmin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AreaSwitchController extends Controller
{
    public function switch(Request $request)
    {
        $user = Auth::user();
        $request->validate(['area_id' => 'required|exists:areas,id']);

        $targetAreaId = (int) $request->area_id;
        $targetArea = Area::find($targetAreaId);

        $manageableAreaIds = $user->accessibleAreas->pluck('id')->push($user->area_id)->filter()->unique();

        if ($user->is_area_admin && $manageableAreaIds->contains($targetAreaId)) {

            session([
                'current_admin_area_id' => $targetArea->id,
                'current_admin_area_name' => $targetArea->name,
            ]);

            return redirect()->back()->with('success', 'Mostrando gestión para el área: ' . $targetArea->name);
        }

        return redirect()->back()->with('error', 'No tienes permiso para gestionar esa área.');
    }
}