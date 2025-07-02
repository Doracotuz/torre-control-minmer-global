<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Area;
use App\Models\Folder;
use App\Models\FileLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; // Para consultas de agregación

class DashboardController extends Controller
{
    public function data(Request $request)
    {
        $user = Auth::user();

        // --- Métricas Generales (accesibles para todos, pero filtradas por área si no es Super Admin) ---
        $totalUsers = User::count();
        $totalAreas = Area::count();
        $totalFolders = Folder::count();
        $totalFileLinks = FileLink::count();

        // Datos para gráficos (filtrados por área si no es Super Admin)
        $usersByArea = [];
        $foldersByArea = [];
        $fileTypesDistribution = [];

        // Lógica de permisos para los datos
        if ($user->area && $user->area->name === 'Administración') {
            // Super Admin: Obtiene datos de todas las áreas
            $usersByArea = User::select('areas.name as area_name', DB::raw('count(users.id) as count'))
                                ->join('areas', 'users.area_id', '=', 'areas.id')
                                ->groupBy('areas.name')
                                ->orderBy('areas.name')
                                ->get();

            $foldersByArea = Folder::select('areas.name as area_name', DB::raw('count(folders.id) as count'))
                                    ->join('areas', 'folders.area_id', '=', 'areas.id')
                                    ->groupBy('areas.name')
                                    ->orderBy('areas.name')
                                    ->get();

            // Modificación CRÍTICA aquí para tipos de archivo
            $fileTypesDistribution = FileLink::select(DB::raw('CASE
                                        WHEN type = "file" AND (path LIKE "%.pdf") THEN "PDF"
                                        WHEN type = "file" AND (path LIKE "%.jpg" OR path LIKE "%.jpeg" OR path LIKE "%.png" OR path LIKE "%.gif" OR path LIKE "%.bmp" OR path LIKE "%.webp") THEN "Imagen"
                                        WHEN type = "file" THEN UPPER(SUBSTRING_INDEX(path, ".", -1)) /* Extraer extensión para otros archivos */
                                        ELSE "Enlaces"
                                    END as type_category'), DB::raw('count(*) as count'))
                                    ->groupBy('type_category')
                                    ->orderBy('type_category')
                                    ->get();

        } elseif ($user->is_area_admin || ($user->area_id && $user->area->name !== 'Administración')) {
            // Administrador de Área o Usuario Normal: Obtiene datos solo de su área
            $areaId = $user->area_id;

            $usersByArea = User::select('areas.name as area_name', DB::raw('count(users.id) as count'))
                                ->join('areas', 'users.area_id', '=', 'areas.id')
                                ->where('users.area_id', $areaId)
                                ->groupBy('areas.name')
                                ->get(); // Solo habrá una fila para su área

            $foldersByArea = Folder::select('areas.name as area_name', DB::raw('count(folders.id) as count'))
                                    ->join('areas', 'folders.area_id', '=', 'areas.id')
                                    ->where('folders.area_id', $areaId)
                                    ->groupBy('areas.name')
                                    ->get(); // Solo habrá una fila para su área

            // Modificación CRÍTICA aquí para tipos de archivo
            $fileTypesDistribution = FileLink::select(DB::raw('CASE
                                        WHEN type = "file" AND (path LIKE "%.pdf") THEN "PDF"
                                        WHEN type = "file" AND (path LIKE "%.jpg" OR path LIKE "%.jpeg" OR path LIKE "%.png" OR path LIKE "%.gif" OR path LIKE "%.bmp" OR path LIKE "%.webp") THEN "Imagen"
                                        WHEN type = "file" THEN UPPER(SUBSTRING_INDEX(path, ".", -1)) /* Extraer extensión para otros archivos */
                                        ELSE "Enlaces"
                                    END as type_category'), DB::raw('count(*) as count'))
                                    ->whereHas('folder', function($q) use ($areaId) {
                                        $q->where('area_id', $areaId);
                                    })
                                    ->groupBy('type_category')
                                    ->orderBy('type_category')
                                    ->get();
        }


        return response()->json([
            'totalUsers' => $totalUsers,
            'totalAreas' => $totalAreas,
            'totalFolders' => $totalFolders,
            'totalFileLinks' => $totalFileLinks,
            'usersByArea' => $usersByArea,
            'foldersByArea' => $foldersByArea,
            'fileTypesDistribution' => $fileTypesDistribution,
        ]);
    }
}
