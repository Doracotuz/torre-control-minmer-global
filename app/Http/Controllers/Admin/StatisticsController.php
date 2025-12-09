<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use App\Models\Folder;
use App\Models\FileLink;
use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;

class StatisticsController extends Controller
{
    /**
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $startDate = $request->input('start_date', now()->subMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));
        $filterArea = $request->input('area');
        $filterUserType = $request->input('user_type');
        $searchQuery = $request->input('search');

        $totalUsers = User::count();
        $totalFolders = Folder::count();
        $totalFiles = FileLink::where('type', 'file')->count();
        $totalLinks = FileLink::where('type', 'link')->count();

        $topUsersQuery = ActivityLog::select('user_id', DB::raw('count(*) as total_activity'))
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->groupBy('user_id')
            ->orderByDesc('total_activity')
            ->limit(3);

        if ($filterArea) {
            $topUsersQuery->whereHas('user', function ($q) use ($filterArea) {
                $q->where('area_id', $filterArea);
            });
        }
        if ($filterUserType) {
            $topUsersQuery->whereHas('user', function ($q) use ($filterUserType) {
                if ($filterUserType === 'super_admin') {
                    $q->where('is_area_admin', true)->whereHas('area', function ($a) { $a->where('name', 'Administración'); });
                } elseif ($filterUserType === 'area_admin') {
                    $q->where('is_area_admin', true)->whereDoesntHave('area', function ($a) { $a->where('name', 'Administración'); });
                } elseif ($filterUserType === 'client') {
                    $q->where('is_client', true);
                } elseif ($filterUserType === 'normal') {
                    $q->where('is_area_admin', false)->where('is_client', false);
                }
            });
        }

        $topUsers = $topUsersQuery->get();

        if ($topUsers->isNotEmpty()) {
            $topUsers = $topUsers->map(function($item) {
                $item->user = User::find($item->user_id);
                return $item;
            });
        }

        $activitiesQuery = ActivityLog::with('user', 'user.area')
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->latest();

        if ($filterArea) {
            $activitiesQuery->whereHas('user', function ($q) use ($filterArea) {
                $q->where('area_id', $filterArea);
            });
        }
        if ($filterUserType) {
            $activitiesQuery->whereHas('user', function ($q) use ($filterUserType) {
                if ($filterUserType === 'super_admin') {
                    $q->where('is_area_admin', true)->whereHas('area', function ($a) { $a->where('name', 'Administración'); });
                } elseif ($filterUserType === 'area_admin') {
                    $q->where('is_area_admin', true)->whereDoesntHave('area', function ($a) { $a->where('name', 'Administración'); });
                } elseif ($filterUserType === 'client') {
                    $q->where('is_client', true);
                } elseif ($filterUserType === 'normal') {
                    $q->where('is_area_admin', false)->where('is_client', false);
                }
            });
        }
        if ($searchQuery) {
            $activitiesQuery->where(function ($q) use ($searchQuery) {
                $q->where('action', 'like', '%' . $searchQuery . '%')
                  ->orWhereHas('user', function ($q) use ($searchQuery) {
                      $q->where('name', 'like', '%' . $searchQuery . '%')
                        ->orWhere('email', 'like', '%' . $searchQuery . '%');
                  });
            });
        }

        $activities = $activitiesQuery->paginate(30)->appends($request->except('page'));

        $activities->getCollection()->transform(function ($activity) {
            if (is_string($activity->details)) {
                $activity->details = json_decode($activity->details, true);
            }
            return $activity;
        });    
        
        $areas = Area::all();
        $userTypes = ['super_admin' => 'Super Admin', 'area_admin' => 'Admin de Área', 'normal' => 'Normal', 'client' => 'Cliente'];

        return view('admin.statistics.index', compact(
            'totalUsers', 'totalFolders', 'totalFiles', 'totalLinks', 'topUsers',
            'startDate', 'endDate', 'areas', 'userTypes', 'filterArea', 'filterUserType',
            'activities', 'searchQuery'
        ));
    }

    /**
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function charts(Request $request)
    {
        $startDate = $request->input('start_date', now()->subMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));
        $filterArea = $request->input('area');
        $filterUserType = $request->input('user_type');

        $actionData = ActivityLog::select('action', DB::raw('count(*) as total'))
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->groupBy('action')
            ->get();

        $userTypeData = ActivityLog::select(DB::raw('CASE
            WHEN users.is_area_admin = 1 AND areas.name = "Administración" THEN "Super Admin"
            WHEN users.is_area_admin = 1 THEN "Admin de Área"
            WHEN users.is_client = 1 THEN "Cliente"
            ELSE "Normal"
            END AS user_type_label'), DB::raw('count(*) as total_actions'))
            ->join('users', 'users.id', '=', 'activity_logs.user_id')
            ->leftJoin('areas', 'users.area_id', '=', 'areas.id')
            ->whereDate('activity_logs.created_at', '>=', $startDate)
            ->whereDate('activity_logs.created_at', '<=', $endDate)
            ->groupBy('user_type_label')
            ->pluck('total_actions', 'user_type_label');
        
        $foldersByArea = Folder::select('areas.name', DB::raw('count(folders.id) as total_folders'))
            ->join('areas', 'folders.area_id', '=', 'areas.id')
            ->groupBy('areas.name')
            ->pluck('total_folders', 'areas.name');

        $filesByArea = FileLink::select('areas.name', DB::raw('count(file_links.id) as total_files'))
            ->join('folders', 'file_links.folder_id', '=', 'folders.id')
            ->join('areas', 'folders.area_id', '=', 'areas.id')
            ->where('file_links.type', 'file')
            ->groupBy('areas.name')
            ->pluck('total_files', 'areas.name');

        $activeUsersByMonth = ActivityLog::select(DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'), DB::raw('count(distinct user_id) as total_users'))
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total_users', 'month');

        $totalActivityByMonth = ActivityLog::select(DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'), DB::raw('count(*) as total_actions'))
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total_actions', 'month');

        $fileLinkComparison = FileLink::select('type', DB::raw('count(*) as total'))
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->groupBy('type')
            ->pluck('total', 'type');

        $fileTypes = FileLink::select(DB::raw('LOWER(SUBSTR(name, LENGTH(name) - LOCATE(".", REVERSE(name)) + 2)) as file_extension'), DB::raw('count(*) as total'))
            ->where('type', 'file')
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->groupBy('file_extension')
            ->orderByDesc('total')
            ->limit(5)
            ->pluck('total', 'file_extension');

        $creationDeletion = ActivityLog::select('action', DB::raw('count(*) as total'))
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->whereIn('action', [
                'Creó una carpeta', 'Creó un enlace',
                'Subió un archivo', 'Subió múltiples archivos', 'Subió un directorio',
                'Eliminó una carpeta', 'Eliminó un archivo/enlace', 
                'Eliminación masiva de carpeta', 'Eliminación masiva de archivo/enlace'
            ])
            ->groupBy('action')
            ->get()
            ->groupBy(function ($item) {
                return Str::contains($item->action, ['Creó', 'Subió']) ? 'Creaciones' : 'Eliminaciones';
            })
            ->map(function ($group) {
                return $group->sum('total');
            });
            
        $activityByHour = ActivityLog::select(DB::raw('HOUR(created_at) as hour'), DB::raw('count(*) as total_actions'))
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->groupBy('hour')
            ->orderBy('hour')
            ->pluck('total_actions', 'hour');

        $areas = Area::all();
        $userTypes = ['super_admin' => 'Super Admin', 'area_admin' => 'Admin de Área', 'normal' => 'Normal', 'client' => 'Cliente'];
        
        return view('admin.statistics.charts', compact(
            'startDate', 'endDate', 'areas', 'userTypes', 'filterArea', 'filterUserType',
            'actionData', 'userTypeData', 'foldersByArea', 'filesByArea',
            'activeUsersByMonth', 'totalActivityByMonth', 'fileLinkComparison',
            'fileTypes', 'creationDeletion', 'activityByHour'
        ));
    }


    public function exportCsv(Request $request)
    {
        $startDate = $request->input('start_date', now()->subMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));
        $filterArea = $request->input('area');
        $filterUserType = $request->input('user_type');

        $activitiesQuery = ActivityLog::with('user', 'user.area')
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->latest();

        if ($filterArea) {
            $activitiesQuery->whereHas('user', function ($q) use ($filterArea) {
                $q->where('area_id', $filterArea);
            });
        }
        if ($filterUserType) {
            $activitiesQuery->whereHas('user', function ($q) use ($filterUserType) {
                if ($filterUserType === 'super_admin') {
                    $q->where('is_area_admin', true)->whereHas('area', function ($a) { $a->where('name', 'Administración'); });
                } elseif ($filterUserType === 'area_admin') {
                    $q->where('is_area_admin', true)->whereDoesntHave('area', function ($a) { $a->where('name', 'Administración'); });
                } elseif ($filterUserType === 'client') {
                    $q->where('is_client', true);
                } elseif ($filterUserType === 'normal') {
                    $q->where('is_area_admin', false)->where('is_client', false);
                }
            });
        }
        
        $activities = $activitiesQuery->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="bitacora_minmer.csv"',
        ];
        
        $callback = function() use ($activities) {
            $file = fopen('php://output', 'w');
            
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, ['Fecha', 'Usuario', 'Email', 'Area', 'Tipo de Usuario', 'Acción', 'Detalles']);
            
            foreach ($activities as $activity) {
                $userType = 'Normal';
                if ($activity->user) {
                    if ($activity->user->is_client) {
                        $userType = 'Cliente';
                    } elseif ($activity->user->is_area_admin) {
                        $userType = 'Admin de Área';
                        if ($activity->user->area && $activity->user->area->name === 'Administración') {
                             $userType = 'Super Admin';
                        }
                    }
                }

                $details = is_string($activity->details) ? json_decode($activity->details, true) : $activity->details;
                $detailsString = '';

                if (is_array($details)) {
                    foreach ($details as $key => $value) {
                        $detailsString .= Str::title(str_replace('_', ' ', $key)) . ': ' . $value . '; ';
                    }
                }
                
                fputcsv($file, [
                    $activity->created_at->format('d/m/Y H:i'),
                    $activity->user->name ?? 'N/A',
                    $activity->user->email ?? 'N/A',
                    $activity->user->area->name ?? 'N/A',
                    $userType,
                    $activity->action,
                    rtrim($detailsString, '; '),
                ]);
            }
            
            fclose($file);
        };
        
        return Response::stream($callback, 200, $headers);
    }

    public function getChartData(Request $request)
    {
        $startDate = now()->subDays(30)->format('Y-m-d');
        $endDate = now()->format('Y-m-d');

        $actionData = ActivityLog::select('action', DB::raw('count(*) as total'))
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->groupBy('action')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $userTypeData = ActivityLog::select(DB::raw('CASE
            WHEN users.is_area_admin = 1 AND areas.name = "Administración" THEN "Super Admin"
            WHEN users.is_area_admin = 1 THEN "Admin de Área"
            WHEN users.is_client = 1 THEN "Cliente"
            ELSE "Normal"
            END AS user_type_label'), DB::raw('count(*) as total_actions'))
            ->join('users', 'users.id', '=', 'activity_logs.user_id')
            ->leftJoin('areas', 'users.area_id', '=', 'areas.id')
            ->whereDate('activity_logs.created_at', '>=', $startDate)
            ->whereDate('activity_logs.created_at', '<=', $endDate)
            ->groupBy('user_type_label')
            ->pluck('total_actions', 'user_type_label');

        $foldersByArea = Folder::select('areas.name as x', DB::raw('count(folders.id) as y'))
            ->join('areas', 'folders.area_id', '=', 'areas.id')
            ->groupBy('areas.name')
            ->orderByDesc('y')
            ->get();

        $filesByArea = FileLink::select('areas.name', DB::raw('count(file_links.id) as total_files'))
            ->join('folders', 'file_links.folder_id', '=', 'folders.id')
            ->join('areas', 'folders.area_id', '=', 'areas.id')
            ->where('file_links.type', 'file')
            ->groupBy('areas.name')
            ->get();
            
        $creationDeletion = ActivityLog::select('action_key', DB::raw('count(*) as total'))
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->whereIn('action_key', [
                'created_folder', 'created_link',
                'uploaded_file', 'uploaded_multiple_files', 'uploaded_directory',
                'deleted_folder', 'deleted_file_link', 
                'deleted_folder_bulk', 'deleted_file_link_bulk'
            ])
            ->groupBy('action_key')
            ->get()
            ->groupBy(function ($item) {
                return Str::contains($item->action_key, ['created', 'uploaded']) ? 'Creaciones' : 'Eliminaciones';
            })
            ->map(function ($group) {
                return $group->sum('total');
            });

        $activityByHour = ActivityLog::select(DB::raw('HOUR(created_at) as hour'), DB::raw('count(*) as total_actions'))
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->groupBy('hour')
            ->orderBy('hour')
            ->pluck('total_actions', 'hour');

        $recentActivities = ActivityLog::with('user:id,name')
            ->latest()
            ->take(5)
            ->get();
            
        $totalUsers = User::count();
        $totalAreas = Area::count();
        $totalFolders = Folder::count();
        $totalFileLinks = FileLink::count();

        $usersByArea = User::select('areas.name', DB::raw('count(users.id) as count'))
            ->join('areas', 'users.area_id', '=', 'areas.id')
            ->groupBy('areas.name')
            ->orderByDesc('count')
            ->get();

        $fileTypes = FileLink::select(DB::raw('LOWER(SUBSTRING_INDEX(name, ".", -1)) as file_extension'), DB::raw('count(*) as total'))
            ->where('type', 'file')
            ->where('name', 'like', '%.%')
            ->groupBy('file_extension')
            ->orderByDesc('total')
            ->limit(5)
            ->pluck('total', 'file_extension');

        $topUsers = ActivityLog::select('users.name', DB::raw('count(*) as total_actions'))
            ->join('users', 'activity_logs.user_id', '=', 'users.id')
            ->whereDate('activity_logs.created_at', '>=', $startDate)
            ->whereDate('activity_logs.created_at', '<=', $endDate)
            ->groupBy('activity_logs.user_id', 'users.name')
            ->orderByDesc('total_actions')
            ->limit(5)
            ->get();

        return response()->json([
            'totalUsers' => $totalUsers,
            'totalAreas' => $totalAreas,
            'totalFolders' => $totalFolders,
            'totalFileLinks' => $totalFileLinks,
            'recentActivities' => $recentActivities,
            'actionData' => $actionData,
            'userTypeData' => $userTypeData,
            'foldersByArea' => $foldersByArea,
            'filesByArea' => $filesByArea,
            'creationDeletion' => $creationDeletion,
            'activityByHour' => $activityByHour,
            'usersByArea' => $usersByArea,
            'fileTypes' => $fileTypes,
            'topUsers' => $topUsers,
        ]);
    }

}