<?php

namespace App\Http\Controllers\AreaAdmin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\User;
use App\Models\Folder;
use App\Models\FileLink;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate_Support_Str;

class DashboardController extends Controller
{
    private function getActiveArea()
    {
        $user = Auth::user();
        $activeAreaId = session('current_admin_area_id', $user->area_id);
        $activeArea = Area::find($activeAreaId);

        if (!$user->is_area_admin) {
            $activeArea = $user->area;
        } 
        else {
            $manageableAreaIds = $user->accessibleAreas->pluck('id')->push($user->area_id)->filter()->unique(); //
            if (!$activeArea || !$manageableAreaIds->contains($activeAreaId)) {
                $activeArea = $user->area;
                $activeAreaId = $user->area_id;
                session(['current_admin_area_id' => $activeAreaId, 'current_admin_area_name' => $activeArea->name]);
            }
        }
        
        return $activeArea;
    }

    public function data(Request $request)
    {
        $user = Auth::user();
        $activeArea = $this->getActiveArea();
        $activeAreaId = $activeArea->id;
        $isAreaAdmin = $user->is_area_admin;

        $kpiQuery = FileLink::query();
        $activityQuery = ActivityLog::query();

        if ($isAreaAdmin) {
            $kpiQuery->whereHas('folder', function($q) use ($activeAreaId) {
                $q->where('area_id', $activeAreaId);
            });
            
            $activityQuery->whereHas('user', function($q) use ($activeAreaId) {
                $q->where('area_id', $activeAreaId);
            });

            $folderCount = Folder::where('area_id', $activeAreaId)->count();
            $userCount = User::where('area_id', $activeAreaId)->count();
            
        } else {
            $kpiQuery->where('user_id', $user->id);
            $activityQuery->where('user_id', $user->id);

            $folderCount = Folder::where('user_id', $user->id)->count();
            $userCount = 1;
        }

        $fileCount = (clone $kpiQuery)->where('type', 'file')->count();
        $linkCount = (clone $kpiQuery)->where('type', 'link')->count();

        $teamMembers = \App\Models\OrganigramMember::where('area_id', $activeAreaId)
            ->with('position:id,name')
            ->select('id', 'name', 'position_id', 'profile_photo_path', 'email')
            ->orderBy('name')
            ->get();
            
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

        return response()->json([
            'isAreaAdmin' => $isAreaAdmin,
            'areaName' => $activeArea->name,
            'userCount' => $userCount,
            'folderCount' => $folderCount,
            'fileCount' => $fileCount,
            'linkCount' => $linkCount,
            'teamMembers' => $teamMembers,
            'recentActivities' => $recentActivities,
            'activityBreakdown' => $activityBreakdown,
            'fileTypes' => $fileTypes,
        ]);
    }
}