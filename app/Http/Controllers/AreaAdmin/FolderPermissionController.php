<?php

namespace App\Http\Controllers\AreaAdmin;

use App\Http\Controllers\Controller;
use App\Models\Folder;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class FolderPermissionController extends Controller
{
    /**
     * Display a list of folders for the area admin to manage permissions, organized hierarchically.
     * Muestra una lista de carpetas para que el administrador de área gestione los permisos, organizada jerárquicamente.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();
        $areaId = $user->area_id;

        // Obtener solo las carpetas de nivel superior que pertenecen al área del administrador
        // y cargar recursivamente todos sus hijos
        $folders = Folder::where('area_id', $areaId)
                         ->whereNull('parent_id') // Obtener solo las carpetas raíz
                         ->with('childrenRecursive') // Usar una relación recursiva si está definida, o cargar manualmente
                         ->orderBy('name')
                         ->get();

        // Si 'childrenRecursive' no está definida en tu modelo Folder, podrías necesitar
        // cargar los hijos de forma más explícita o procesar la colección.
        // Asumiendo que has añadido 'childrenRecursive' o que 'children' es suficiente
        // para el primer nivel y el partial se encargará del resto de la recursión.
        // Si no tienes 'childrenRecursive', el 'with('children')' original y la recursión en la vista aún funcionarán.
        // Para asegurar una carga profunda, puedes definir una relación recursiva en Folder.php:
        // public function childrenRecursive() {
        //     return $this->children()->with('childrenRecursive');
        // }


        return view('area_admin.folder_permissions.index', compact('folders'));
    }

    /**
     * Show the form for managing permissions for a specific folder.
     * Muestra el formulario para gestionar los permisos de una carpeta específica.
     *
     * @param  \App\Models\Folder  $folder
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function edit(Folder $folder)
    {
        $user = Auth::user();

        // Asegurarse de que la carpeta pertenece al área del administrador
        if ($folder->area_id !== $user->area_id) {
            return redirect()->route('area_admin.folder_permissions.index')->with('error', 'No tienes permiso para gestionar los permisos de esta carpeta.');
        }

        // Obtener todos los usuarios de la misma área del administrador (excluyendo al propio admin)
        $areaUsers = User::where('area_id', $user->area_id)
                         ->where('id', '!=', $user->id) // Excluir al propio administrador
                         ->orderBy('name')
                         ->get();

        // Obtener los IDs de los usuarios que ya tienen acceso a esta carpeta
        $usersWithAccessIds = $folder->usersWithAccess->pluck('id')->toArray();

        return view('area_admin.folder_permissions.edit', compact('folder', 'areaUsers', 'usersWithAccessIds'));
    }

    /**
     * Update the permissions for the specified folder.
     * Actualiza los permisos para la carpeta especificada.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Folder  $folder
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Folder $folder)
    {
        $user = Auth::user();

        // Asegurarse de que la carpeta pertenece al área del administrador
        if ($folder->area_id !== $user->area_id) {
            return redirect()->route('area_admin.folder_permissions.index')->with('error', 'No tienes permiso para actualizar los permisos de esta carpeta.');
        }

        $request->validate([
            'users_with_access' => 'nullable|array',
            'users_with_access.*' => ['exists:users,id', Rule::in(User::where('area_id', $user->area_id)->pluck('id')->toArray())], // Asegura que los IDs pertenecen a usuarios de su misma área
        ]);

        // Sincronizar los usuarios seleccionados con la relación BelongsToMany
        // Esto desasocia los que no están en la lista y asocia los nuevos
        $folder->usersWithAccess()->sync($request->input('users_with_access', []));

        return redirect()->route('area_admin.folder_permissions.index')->with('success', 'Permisos de carpeta actualizados exitosamente.');
    }
}