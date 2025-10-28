<?php

namespace App\Http\Controllers\AreaAdmin;

use App\Http\Controllers\Controller;
use App\Models\Folder;
use App\Models\User;
use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class FolderPermissionController extends Controller
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
        
        $manageableAreaIds = $user->accessibleAreas->pluck('id')->push($user->area_id)->filter()->unique();
        if (!$user->is_area_admin || !$manageableAreaIds->contains($activeAreaId)) {
            abort(403, 'No tienes permiso para gestionar esta área.');
        }
        
        return $activeArea;
    }

    public function index()
    {
        $currentArea = $this->getActiveArea();
        $areaId = $currentArea->id;

        $folders = Folder::where('area_id', $areaId)
                         ->whereNull('parent_id')
                         ->with('childrenRecursive')
                         ->orderBy('name')
                         ->get();

        return view('area_admin.folder_permissions.index', compact('folders'));
    }

    public function edit(Folder $folder)
    {
        $currentArea = $this->getActiveArea();

        if ($folder->area_id !== $currentArea->id) {
            return redirect()->route('area_admin.folder_permissions.index')->with('error', 'Esta carpeta no pertenece al área que estás gestionando.');
        }

        $areaUsers = User::where('area_id', $currentArea->id)
                         ->where('id', '!=', Auth::id())
                         ->orderBy('name')
                         ->get();
        
        $usersWithAccessIds = $folder->usersWithAccess->pluck('id')->toArray();

        return view('area_admin.folder_permissions.edit', compact('folder', 'areaUsers', 'usersWithAccessIds'));
    }

    public function update(Request $request, Folder $folder)
    {
        $currentArea = $this->getActiveArea();

        if ($folder->area_id !== $currentArea->id) {
            return redirect()->route('area_admin.folder_permissions.index')->with('error', 'No tienes permiso para actualizar esta carpeta.');
        }

        $request->validate([
            'users_with_access' => 'nullable|array',
            'users_with_access.*' => ['exists:users,id', Rule::in(User::where('area_id', $currentArea->id)->pluck('id')->toArray())],
        ]);

        $folder->usersWithAccess()->sync($request->input('users_with_access', []));

        return redirect()->route('area_admin.folder_permissions.index')->with('success', 'Permisos de carpeta actualizados exitosamente.');
    }
}