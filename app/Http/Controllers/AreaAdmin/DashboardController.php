<?php

namespace App\Http\Controllers\AreaAdmin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\User;
use App\Models\Folder;
use App\Models\FileLink;
use App\Models\ActivityLog;
use App\Models\OrganigramMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Models\ffInventoryMovement;

class DashboardController extends Controller
{
    private function getActiveArea()
    {
        $user = Auth::user();
        $activeAreaId = session('current_admin_area_id', $user->area_id);
        $activeArea = Area::find($activeAreaId);

        if (!$activeArea) {
            $activeArea = $user->area;
            $activeAreaId = $user->area_id;
            session(['current_admin_area_id' => $activeAreaId, 'current_admin_area_name' => $activeArea->name]);
        }
        
        $manageableAreaIds = $user->accessibleAreas->pluck('id')->push($user->area_id)->filter()->unique(); //
        if (!$user->is_area_admin || !$manageableAreaIds->contains($activeAreaId)) { //
            $activeArea = $user->area;
            session(['current_admin_area_id' => $activeArea->id, 'current_admin_area_name' => $activeArea->name]);
        }
        
        return $activeArea;
    }

    public function data(Request $request)
    {
        $user = Auth::user();
        $isAreaAdmin = $user->is_area_admin;

        $corporateAreas = ['Consorcio Monter', 'Empresa1', 'Empresa2', 'Empresa3'];

        $areaScopeIds = collect();
        $areaName = "";
        $teamMembers = collect();
        
        $kpiQuery = FileLink::query();
        $activityQuery = ActivityLog::query();

        $myProfile = null;
        $recentFiles = collect();
        
        if ($isAreaAdmin) {
            $areaScopeIds = $user->accessibleAreas->pluck('id')->push($user->area_id)->filter()->unique();
            $areaNamesCollection = Area::whereIn('id', $areaScopeIds)->get();
            $areaName = $areaNamesCollection->pluck('name')->implode(', ');
            
            $isCorporateContext = $areaNamesCollection->whereIn('name', $corporateAreas)->isNotEmpty();

            $kpiQuery->whereHas('folder', function($q) use ($areaScopeIds) {
                $q->whereIn('area_id', $areaScopeIds);
            });
            $activityQuery->whereHas('user', function($q) use ($areaScopeIds) {
                $q->whereIn('area_id', $areaScopeIds);
            });
            
            $folderCount = Folder::whereIn('area_id', $areaScopeIds)->count();

        } else {
            $areaName = $user->area->name ?? 'Mi Área';
            $areaScopeIds = collect([$user->area_id]);

            $isCorporateContext = in_array($areaName, $corporateAreas);

            $kpiQuery->where('user_id', $user->id);
            $activityQuery->where('user_id', $user->id); 
            
            $folderCount = Folder::where('user_id', $user->id)->count();

            $myProfile = OrganigramMember::where('user_id', $user->id)
                ->with('position:id,name')
                ->select('id', 'name', 'position_id', 'profile_photo_path', 'user_id')
                ->first();
                
            if ($myProfile) {
                $myProfile->profile_photo_path_url = $myProfile->profile_photo_path 
                    ? Storage::disk('s3')->url($myProfile->profile_photo_path) 
                    : null;
            }

            $recentFiles = FileLink::where('user_id', $user->id)
                ->with('folder:id,name')
                ->latest('updated_at')
                ->take(5)
                ->get();
        }

        if ($isCorporateContext) {
            $rawMembers = User::whereIn('area_id', $areaScopeIds)
                ->select('id', 'name', 'position', 'profile_photo_path', 'email')
                ->orderBy('name')
                ->get();

            $teamMembers = $rawMembers->map(function ($member) {
                return [
                    'id' => $member->id,
                    'name' => $member->name,
                    'position' => (object)['name' => $member->position ?? 'Sin puesto'],
                    'email' => $member->email,
                    'profile_photo_path_url' => $member->profile_photo_path 
                        ? Storage::disk('s3')->url($member->profile_photo_path)
                        : null
                ];
            });

        } else {
            $rawMembers = OrganigramMember::whereIn('area_id', $areaScopeIds)
                ->with('position:id,name')
                ->select('id', 'name', 'position_id', 'profile_photo_path', 'email')
                ->orderBy('name')
                ->get();

            $teamMembers = $rawMembers->map(function ($member) {
                return [
                    'id' => $member->id,
                    'name' => $member->name,
                    'position' => $member->position,
                    'email' => $member->email,
                    'profile_photo_path_url' => $member->profile_photo_path 
                        ? Storage::disk('s3')->url($member->profile_photo_path)
                        : null
                ];
            });
        }

        $userCount = $isAreaAdmin ? $teamMembers->count() : null;
        
        $fileCount = (clone $kpiQuery)->where('type', 'file')->count();
        $linkCount = (clone $kpiQuery)->where('type', 'link')->count();
        
        $recentActivities = (clone $activityQuery)
            ->with('user:id,name')
            ->latest()
            ->take(5)
            ->get();

        $activityBreakdown = (clone $activityQuery)
            ->select(DB::raw('CASE 
                WHEN action LIKE "%Subió%" OR action LIKE "%Creó%" THEN "Creaciones"
                WHEN action LIKE "%Eliminó%" THEN "Eliminaciones"
                WHEN action LIKE "%Editó%" OR action LIKE "%Actualizó%" THEN "Ediciones"
                WHEN action LIKE "%Descargó%" THEN "Descargas"
                ELSE "Otros"
            END as action_type'), DB::raw('count(*) as total'))
            ->whereDate('created_at', '>=', now()->subDays(30))
            ->groupBy('action_type')
            ->orderByDesc('total')
            ->get();

        $fileTypes = (clone $kpiQuery)
            ->where('type', 'file')
            ->where('name', 'like', '%.%')
            ->select(DB::raw('LOWER(SUBSTRING_INDEX(name, ".", -1)) as extension'), DB::raw('count(*) as total'))
            ->groupBy('extension')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $backorderQuery = \App\Models\ffInventoryMovement::where('is_backorder', true)
            ->where('backorder_fulfilled', false)
            ->with(['product:id,description,sku,unit_price', 'user:id,name']);

        if ($isAreaAdmin) {
            $backorderQuery->whereHas('user', function($q) use ($areaScopeIds) {
                $q->whereIn('area_id', $areaScopeIds);
            });
        } else {
            $backorderQuery->where('user_id', $user->id);
        }

        $backorderCount = $backorderQuery->count();

        $backorderList = $backorderQuery->orderBy('created_at', 'asc')
            ->take(5)
            ->get()
            ->map(function($mov) {
                return [
                    'id' => $mov->id,
                    'folio' => $mov->folio,
                    'product' => $mov->product->description ?? 'Producto Eliminado',
                    'sku' => $mov->product->sku ?? 'N/A',
                    'quantity' => abs($mov->quantity),
                    'user' => $mov->user->name ?? 'Usuario',
                    'date' => $mov->created_at->diffForHumans(),
                    'client' => $mov->client_name
                ];
            });

        return response()->json([
            'isAreaAdmin' => $isAreaAdmin,
            'areaName' => $areaName,
            'userCount' => $userCount,
            'folderCount' => $folderCount,
            'fileCount' => $fileCount,
            'linkCount' => $linkCount,
            'teamMembers' => $teamMembers,
            'recentActivities' => $recentActivities,
            'activityBreakdown' => $activityBreakdown,
            'fileTypes' => $fileTypes,
            'myProfile' => $myProfile,
            'recentFiles' => $recentFiles,
            'backorderCount' => $backorderCount,
            'backorderList' => $backorderList,
        ]);
    }
}