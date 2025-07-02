<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Folder;
use App\Models\FileLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SearchController extends Controller
{
    public function suggestions(Request $request)
    {
        $query = $request->input('query');
        $user = Auth::user();
        $suggestions = collect();

        if (strlen($query) < 3) { // Require at least 3 characters for suggestions
            return response()->json([]);
        }

        // --- Lógica de Permisos para la Búsqueda ---
        $folderQuery = Folder::query();
        $fileLinkQuery = FileLink::query();

        if ($user->area && $user->area->name === 'Administración') {
            // Super Admin: No se aplica ninguna restricción de área o permiso explícito
        } elseif ($user->is_area_admin) {
            // Administrador de Área: Sugerencias solo de su propia área
            $folderQuery->where('area_id', $user->area_id);
            $fileLinkQuery->whereHas('folder', function($q) use ($user) {
                $q->where('area_id', $user->area_id);
            });
        } else {
            // Usuario Normal: Sugerencias solo de su área y con acceso explícito
            $folderQuery->where('area_id', $user->area_id)
                        ->whereHas('usersWithAccess', function ($q) use ($user) {
                            $q->where('user_id', $user->id);
                        });
            $fileLinkQuery->whereHas('folder', function($q) use ($user) {
                $q->where('area_id', $user->area_id)
                  ->whereHas('usersWithAccess', function ($q2) use ($user) {
                      $q2->where('user_id', $user->id);
                  });
            });
        }

        // Buscar carpetas
        $folders = $folderQuery->where('name', 'like', '%' . $query . '%')
                               ->limit(5) // Limitar número de sugerencias
                               ->get()
                               ->map(function ($folder) {
                                   return [
                                       'id' => $folder->id,
                                       'name' => $folder->name,
                                       'type' => 'folder',
                                       'area' => $folder->area->name ?? 'N/A',
                                       'folder_id' => null, // No aplica para carpetas
                                   ];
                               });
        $suggestions = $suggestions->concat($folders);

        // Buscar archivos y enlaces
        $fileLinks = $fileLinkQuery->where('name', 'like', '%' . $query . '%')
                                   ->with('folder.area') // Cargar la relación de carpeta y área de la carpeta
                                   ->limit(5) // Limitar número de sugerencias
                                   ->get()
                                   ->map(function ($fileLink) {
                                       return [
                                           'id' => $fileLink->id,
                                           'name' => $fileLink->name,
                                           'type' => $fileLink->type,
                                           'area' => $fileLink->folder->area->name ?? 'N/A',
                                           'folder_id' => $fileLink->folder_id,
                                       ];
                                   });
        $suggestions = $suggestions->concat($fileLinks);

        return response()->json($suggestions->sortBy('name')->values()->all());
    }
}