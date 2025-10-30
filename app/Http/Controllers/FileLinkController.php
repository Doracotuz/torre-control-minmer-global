<?php

namespace App\Http\Controllers;

use App\Models\FileLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Models\ActivityLog;

class FileLinkController extends Controller
{
    /**
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return redirect()->route('folders.index')->with('error', 'Acceso no permitido.');
    }

    /**
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return redirect()->route('folders.index')->with('error', 'Acceso no permitido.');
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Esta lógica ahora vive en FolderController@storeFileLink
        return redirect()->route('folders.index')->with('error', 'Acceso no permitido.');
    }

    /**
     * @param  \App\Models\FileLink  $fileLink
     * @return \Illuminate\Http\Response
     */
    public function show(FileLink $fileLink)
    {
        return redirect()->route('folders.index', $fileLink->folder_id)->with('error', 'Función de vista de elemento no implementada.');
    }

    /**
     * @param  \App\Models\FileLink  $fileLink
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function edit(FileLink $fileLink)
    {
        $user = Auth::user();

        $isSuperAdmin = $user->isSuperAdmin();
        $isAreaAdmin = $user->is_area_admin;
        $manageableAreaIds = $user->accessibleAreas->pluck('id')->push($user->area_id)->filter()->unique();

        if (!$isSuperAdmin && !($isAreaAdmin && $manageableAreaIds->contains($fileLink->folder->area_id))) {
            return redirect()->route('folders.index', $fileLink->folder_id)
                             ->with('error', 'No tienes permiso para editar este elemento.');
        }

        return view('file_links.edit', compact('fileLink'));
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\FileLink  $fileLink
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, FileLink $fileLink)
    {
        $user = Auth::user();

        $isSuperAdmin = $user->isSuperAdmin();
        $isAreaAdmin = $user->is_area_admin;
        $manageableAreaIds = $user->accessibleAreas->pluck('id')->push($user->area_id)->filter()->unique();

        if (!$isSuperAdmin && !($isAreaAdmin && $manageableAreaIds->contains($fileLink->folder->area_id))) {
             return redirect()->route('folders.index', $fileLink->folder_id)
                             ->with('error', 'No tienes permiso para actualizar este elemento.');
        }

        $validationRules = [
            'name' => 'required|string|max:255',
            'url' => 'nullable|url|max:2048',
        ];

        if ($fileLink->type === 'link') {
            $validationRules['url'] = 'required|url|max:2048';
        }

        $request->validate($validationRules);

        $fileLink->name = $request->name;
        if ($fileLink->type === 'link') {
            $fileLink->url = $request->url;
        }
        $fileLink->save();
        
        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'Editó un archivo/enlace',
            'action_key' => 'updated_file_link',
            'item_type' => 'file_link',
            'item_id' => $fileLink->id,
            'details' => ['old_name' => $fileLink->getOriginal('name'), 'new_name' => $fileLink->name],
        ]);        

        return redirect()->route('folders.index', $fileLink->folder_id)
                         ->with('success', 'Elemento actualizado exitosamente.');
    }

    /**
     * @param  \App\Models\FileLink  $fileLink
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(FileLink $fileLink)
    {
        $user = Auth::user();

        $isSuperAdmin = $user->isSuperAdmin();
        $isAreaAdmin = $user->is_area_admin;
        $manageableAreaIds = $user->accessibleAreas->pluck('id')->push($user->area_id)->filter()->unique();

        if (!$isSuperAdmin && !($isAreaAdmin && $manageableAreaIds->contains($fileLink->folder->area_id))) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para eliminar este elemento.'
            ], 403);
        }

        $folderId = $fileLink->folder_id;

        if ($fileLink->type === 'file' && Storage::disk('s3')->exists($fileLink->path)) {
            Storage::disk('s3')->delete($fileLink->path); //
        }

        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'Eliminó un archivo/enlace',
            'action_key' => 'deleted_file_link',
            'item_type' => 'file_link',
            'item_id' => $fileLink->id,
            'details' => ['name' => $fileLink->name],
        ]);

        $fileLink->delete();

        return response()->json([
            'success' => true,
            'message' => 'Elemento eliminado exitosamente.'
        ]);
    }

    /**
     * @param  \App\Models\FileLink  $fileLink
     * @return \Symfony\Component\HttpFoundation\StreamedResponse|\Illuminate\Http\RedirectResponse
     */
    public function download(FileLink $fileLink)
    {
        $user = Auth::user();

        if ($fileLink->type !== 'file' || !$fileLink->path) {
            return back()->with('error', 'No es un archivo descargable o la ruta no es válida.');
        }

        $hasPermission = false;
        $accessibleAreaIds = $user->accessibleAreas->pluck('id')->push($user->area_id)->filter()->unique();
        
        if ($user->area && $user->area->name === 'Administración') {
            $hasPermission = true;
        } elseif ($user->is_area_admin && $accessibleAreaIds->contains($fileLink->folder->area_id)) {
            $hasPermission = true;
        } elseif ($user->isClient()) {
            if ($user->accessibleFolders->contains($fileLink->folder_id)) {
                $hasPermission = true;
            }
        } elseif ($accessibleAreaIds->contains($fileLink->folder->area_id) && $user->accessibleFolders->contains($fileLink->folder_id)) {
            $hasPermission = true;
        }

        if (!$hasPermission) {
            return back()->with('error', 'No tienes permiso para descargar este archivo.');
        }

        if (!Storage::disk('s3')->exists($fileLink->path)) {
            return back()->with('error', 'El archivo no se encuentra en el almacenamiento.');
        }
        
        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'Descargó un archivo',
            'action_key' => 'downloaded_file',
            'item_type' => 'file_link',
            'item_id' => $fileLink->id,
            'details' => ['name' => $fileLink->name],
        ]);        
        return Storage::disk('s3')->download($fileLink->path, $fileLink->name);
    }
}