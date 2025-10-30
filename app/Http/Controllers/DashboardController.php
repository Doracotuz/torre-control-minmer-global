<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Area;
use App\Models\Folder;
use App\Models\FileLink;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    public function data(Request $request)
    {
        $user = Auth::user();

        $totalUsers = User::count();
        $totalAreas = Area::count();
        $totalFolders = Folder::count();
        $totalFileLinks = FileLink::count();

        $recentActivities = ActivityLog::with('user:id,name')
            ->latest()
            ->take(5)
            ->get();

        $startDate = now()->subDays(30)->format('Y-m-d');
        $endDate = now()->format('Y-m-d');

        $userTypeData = User::select(DB::raw('CASE
            WHEN is_area_admin = 1 AND area_id IN (SELECT id FROM areas WHERE name = "Administración") THEN "Super Admin"
            WHEN is_area_admin = 1 THEN "Admin de Área"
            WHEN is_client = 1 THEN "Cliente"
            ELSE "Normal"
            END AS user_type_label'), DB::raw('count(*) as total'))
            ->groupBy('user_type_label')
            ->pluck('total', 'user_type_label');

        $foldersByArea = Folder::select('areas.name as x', DB::raw('count(folders.id) as y'))
            ->join('areas', 'folders.area_id', '=', 'areas.id')
            ->groupBy('areas.name')
            ->get();

        $fileTypes = FileLink::select(DB::raw('LOWER(SUBSTRING_INDEX(name, ".", -1)) as file_extension'), DB::raw('count(*) as total'))
            ->where('type', 'file')
            ->where('name', 'like', '%.%')
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->groupBy('file_extension')
            ->orderByDesc('total')
            ->limit(5)
            ->pluck('total', 'file_extension');

        $usersByArea = null;
        if ($user->isSuperAdmin()) {
            $usersByArea = User::select('areas.name', DB::raw('count(users.id) as count'))
                ->join('areas', 'users.area_id', '=', 'areas.id')
                ->groupBy('areas.name')
                ->get();
        }

        return response()->json([
            'totalUsers' => $totalUsers,
            'totalAreas' => $totalAreas,
            'totalFolders' => $totalFolders,
            'totalFileLinks' => $totalFileLinks,
            
            'recentActivities' => $recentActivities,

            'userTypeData' => $userTypeData,
            'foldersByArea' => $foldersByArea,
            'fileTypes' => $fileTypes,
            'usersByArea' => $usersByArea,
        ]);
    }
}