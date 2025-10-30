<?php

namespace App\Http\Controllers;

use App\Models\Folder;
use App\Models\Area;
use App\Models\FileLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Log;



class FolderController extends Controller
{
    /**
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Folder|null  $folder
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function index(Request $request, Folder $folder = null)
    {
        $user = Auth::user();
        $currentFolder = $folder;
        $searchQuery = $request->input('search');

        $manageableAreaIds = collect();
        $manageableAreas = collect();

        if ($user->isSuperAdmin()) {
            $manageableAreas = Area::orderBy('name')->get();
            $manageableAreaIds = $manageableAreas->pluck('id');
        } elseif ($user->is_area_admin) {
            $manageableAreaIds = $user->accessibleAreas->pluck('id')->push($user->area_id)->filter()->unique(); //
            $manageableAreas = Area::whereIn('id', $manageableAreaIds)->orderBy('name')->get();
        } elseif (!$user->isClient()) {
            $manageableAreaIds->push($user->area_id);
            $manageableAreas = Area::where('id', $user->area_id)->get();
        }

        $breadcrumbs = collect();
        if ($currentFolder) {
            $parent = $currentFolder->parent;
            while ($parent) {
                $breadcrumbs->prepend($parent);
                $parent = $parent->parent;
            }
        }

        $folderQuery = Folder::query();
        $fileLinkQuery = FileLink::query();

        if ($user->isSuperAdmin()) {
        } elseif ($user->isClient()) {
            $accessibleFolderIds = $user->accessibleFolders()->pluck('id');
            
            $folderQuery->whereIn('id', $accessibleFolderIds);
            $fileLinkQuery->whereIn('folder_id', $accessibleFolderIds);

            if ($currentFolder && !$accessibleFolderIds->contains($currentFolder->id)) {
                return redirect()->route('dashboard')->with('error', 'No tienes permiso para ver esta carpeta.');
            }

        } else {
            $folderQuery->whereIn('area_id', $manageableAreaIds);
            $fileLinkQuery->whereHas('folder', function($q) use ($manageableAreaIds) {
                $q->whereIn('area_id', $manageableAreaIds);
            });

            if (!$user->is_area_admin) {
                $accessibleFolderIds = $user->accessibleFolders()->pluck('id');
                $folderQuery->whereIn('id', $accessibleFolderIds);
                $fileLinkQuery->whereIn('folder_id', $accessibleFolderIds);
            }
        }

        if ($searchQuery) {
            $folders = $folderQuery->where('name', 'like', '%' . $searchQuery . '%')->orderBy('name')->get();
            $fileLinks = $fileLinkQuery->where('name', 'like', '%' . $searchQuery . '%')->orderBy('name')->get();
            
            $currentFolder = null;
            $breadcrumbs = collect();

        } else {
            $folders = $folderQuery->where('parent_id', $currentFolder ? $currentFolder->id : null)
                                ->withCount(['children', 'fileLinks'])
                                ->get()
                                ->map(function ($folder) {
                                    $folder->items_count = $folder->children_count + $folder->file_links_count;
                                    return $folder;
                                });
            
            $fileLinks = $fileLinkQuery->where('folder_id', $currentFolder ? $currentFolder->id : null)
                                    ->orderBy('name')
                                    ->get();

            $breadcrumbs = collect();
            if ($currentFolder) {
                $parent = $currentFolder->parent;
                while ($parent) {
                    $breadcrumbs->prepend($parent);
                    $parent = $parent->parent;
                }
            }
        }

        return view('folders.index', compact(
            'folders', 
            'currentFolder', 
            'fileLinks', 
            'searchQuery', 
            'breadcrumbs',
            'manageableAreas'
        ));
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Folder|null  $folder
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function create(Folder $folder = null)
    {
        $user = Auth::user();
        $currentFolder = $folder;
        
        $manageableAreaIds = $user->accessibleAreas->pluck('id')->push($user->area_id)->filter()->unique(); 
        $manageableAreas = Area::whereIn('id', $manageableAreaIds)->orderBy('name')->get();

        $isSuperAdmin = $user->isSuperAdmin();
        $isAreaAdmin = $user->is_area_admin;
        
        if ($user->isClient()) {
             return redirect()->route('folders.index')->with('error', 'Los usuarios tipo Cliente no tienen permiso para crear carpetas.');
        }

        if ($currentFolder) {
            if (!$isSuperAdmin && !($isAreaAdmin && $manageableAreaIds->contains($currentFolder->area_id))) {
                return redirect()->route('folders.index')->with('error', 'No tienes permiso para crear carpetas en esta área.');
            }
        }
        elseif (!$isSuperAdmin && $manageableAreas->isEmpty()) {
             return redirect()->route('folders.index')->with('error', 'No tienes asignada un área para crear carpetas.');
        }

        return view('folders.create', compact('currentFolder', 'user', 'manageableAreas')); 
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:folders,id',
            'area_id' => 'nullable|required_without:parent_id|exists:areas,id',
        ]);

        $user = Auth::user();
        $targetAreaId = null;
        
        $manageableAreaIds = $user->accessibleAreas->pluck('id')->push($user->area_id)->filter()->unique();

        if ($request->parent_id) {
            $parentFolder = Folder::find($request->parent_id);
            if (!$parentFolder) {
                return back()->withErrors(['parent_id' => 'La carpeta padre no existe.']);
            }
            $targetAreaId = $parentFolder->area_id;
        } else {
            $targetAreaId = $request->area_id; 
        }

        $isSuperAdmin = $user->isSuperAdmin();
        $isAreaAdmin = $user->is_area_admin;

        if ($user->isClient()) {
             return redirect()->route('folders.index')->with('error', 'Los usuarios tipo Cliente no tienen permiso para crear carpetas.');
        }

        if (!$isSuperAdmin && !($isAreaAdmin && $manageableAreaIds->contains($targetAreaId))) {
             return redirect()->route('folders.index')->with('error', 'No tienes permiso para crear carpetas en esta área.');
        }

        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('folders')->where(function ($query) use ($request, $targetAreaId) {
                    return $query->where('parent_id', $request->parent_id)
                                 ->where('area_id', $targetAreaId);
                }),
            ],
        ]);

        $newFolder = Folder::create([
            'name' => $request->name,
            'parent_id' => $request->parent_id,
            'area_id' => $targetAreaId,
            'user_id' => $user->id,
        ]);

        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'Creó una carpeta',
            'action_key' => 'created_folder',
            'item_type' => 'folder',
            'item_id' => $newFolder->id,
            'details' => ['name' => $newFolder->name, 'parent_id' => $newFolder->parent_id, 'area_id' => $newFolder->area_id],
        ]);

        $redirectPath = $request->parent_id ? route('folders.index', $request->parent_id) : route('folders.index');
        return redirect($redirectPath)->with('success', 'Carpeta creada exitosamente.');
    }

    /**
     * @param  \App\Models\Folder  $folder
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function show(Folder $folder)
    {
        return $this->index(request(), $folder);
    }

    /**
     * @param  \App\Models\Folder  $folder
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function edit(Folder $folder)
    {
        $user = Auth::user();

        if ($user->area && $user->area->name === 'Administración') {
        } elseif ($user->is_area_admin && $folder->area_id === $user->area_id) {
        } else {
            return redirect()->route('folders.index')->with('error', 'No tienes permiso para editar esta carpeta.');
        }

        return view('folders.edit', compact('folder', 'user'));
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Folder  $folder
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Folder $folder)
    {
        $user = Auth::user();

        if ($user->area && $user->area->name === 'Administración') {
        } elseif ($user->is_area_admin && $folder->area_id === $user->area_id) {
        } else {
            return redirect()->route('folders.index')->with('error', 'No tienes permiso para actualizar esta carpeta.');
        }

        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('folders')->where(function ($query) use ($request, $folder) {
                    return $query->where('parent_id', $folder->parent_id)
                                 ->where('area_id', $folder->area_id);
                })->ignore($folder->id),
            ],
        ]);

        $folder->update([
            'name' => $request->name,
        ]);

        $redirectPath = $folder->parent_id ? route('folders.index', $folder->parent_id) : route('folders.index');

        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'Editó una carpeta',
            'action_key' => 'updated_folder',
            'item_type' => 'folder',
            'item_id' => $folder->id,
            'details' => ['old_name' => $folder->getOriginal('name'), 'new_name' => $folder->name],
        ]);        

        return redirect($redirectPath)->with('success', 'Carpeta actualizada exitosamente.');
    }

    /**
     * @param  \App\Models\Folder  $folder
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Folder $folder)
    {
        $user = Auth::user();

        if ($user->area && $user->area->name === 'Administración') {
        } elseif ($user->is_area_admin && $folder->area_id === $user->area_id) {
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para eliminar esta carpeta.'
            ], 403);
        }

        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'Eliminó una carpeta',
            'action_key' => 'deleted_folder',
            'item_type' => 'folder',
            'item_id' => $folder->id,
            'details' => ['name' => $folder->name],
        ]);

        $folder->delete();

        return response()->json([
            'success' => true,
            'message' => 'Carpeta eliminada exitosamente.'
        ]);
    }

    /**
     * @param  \App\Models\Folder  $folder
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function createFileLink(Folder $folder)
    {
        $user = Auth::user();

        $isSuperAdmin = $user->isSuperAdmin();
        $isAreaAdmin = $user->is_area_admin;
        $manageableAreaIds = $user->accessibleAreas->pluck('id')->push($user->area_id)->filter()->unique();
        
        if ($user->isClient()) {
            return redirect()->route('folders.index', $folder)->with('error', 'Los usuarios tipo Cliente no tienen permiso para añadir elementos a esta carpeta.');
        }

        if (!$isSuperAdmin && !($isAreaAdmin && $manageableAreaIds->contains($folder->area_id))) {
             return redirect()->route('folders.index', $folder)->with('error', 'No tienes permiso para añadir elementos a esta carpeta.');
        }

        return view('file_links.create', compact('folder'));
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Folder  $folder
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeFileLink(Request $request, Folder $folder)
    {
        $user = Auth::user();

        $isSuperAdmin = $user->isSuperAdmin();
        $isAreaAdmin = $user->is_area_admin;
        $manageableAreaIds = $user->accessibleAreas->pluck('id')->push($user->area_id)->filter()->unique();

        if ($user->isClient()) {
            return response()->json(['message' => 'Los usuarios tipo Cliente no tienen permiso para añadir elementos a esta carpeta.'], 403);
        }
        
        if (!$isSuperAdmin && !($isAreaAdmin && $manageableAreaIds->contains($folder->area_id))) {
            return response()->json(['message' => 'No tienes permiso para añadir elementos a esta carpeta.'], 403);
        }
        $validationRules = [
            'type' => 'required|in:file,link',
            'files' => 'nullable|array',
            'files.*' => 'file|max:500000',
            'url' => 'nullable|url|max:2048',
            'name' => 'nullable|string|max:255',
        ];

        if ($request->type === 'link') {
            $validationRules['name'] = 'required|string|max:255';
            $validationRules['url'] = 'required|url|max:2048';
        } else {
            $validationRules['files'] = 'required|array';
            $validationRules['files.*'] = 'file|max:500000';
            if (count($request->file('files') ?: []) > 1) {
                $validationRules['name'] = 'nullable|string|max:255';
            }
        }

        $request->validate($validationRules);

        if ($request->type === 'link') {
            FileLink::create([
                'name' => $request->name,
                'type' => 'link',
                'url' => $request->url,
                'folder_id' => $folder->id,
                'user_id' => Auth::id(),
            ]);
            return response()->json(['message' => 'Enlace añadido exitosamente.'], 200);
        }

        $uploadedCount = 0;
        $errors = [];

        $files = $request->file('files');
        if (is_array($files)) {
            foreach ($files as $file) {
                if ($file instanceof \Illuminate\Http\UploadedFile) {
                    $originalFileName = $file->getClientOriginalName();
                    $fileNameWithoutExt = pathinfo($originalFileName, PATHINFO_FILENAME);
                    $extension = $file->getClientOriginalExtension();

                    $fileNameToStore = $fileNameWithoutExt;

                    if (count($files) === 1 && $request->filled('name')) {
                        $fileNameToStore = $request->name;
                    }

                    if (!Str::endsWith(strtolower($fileNameToStore), '.' . strtolower($extension))) {
                        $fileNameToStore .= '.' . $extension;
                    }

                    try {
                        $path = $file->store('files', 's3');

                        $fileLink = FileLink::create([
                            'name' => $fileNameToStore,
                            'type' => 'file',
                            'path' => $path,
                            'folder_id' => $folder->id,
                            'user_id' => Auth::id(),
                        ]); //

                        ActivityLog::create([
                            'user_id' => Auth::id(),
                            'action' => 'Subió un archivo',
                            'action_key' => 'uploaded_file',
                            'item_type' => 'file_link',
                            'item_id' => $fileLink->id,
                            'details' => ['name' => $fileLink->name, 'folder_id' => $folder->id],
                        ]);
                        
                        $uploadedCount++;
                    } catch (\Exception $e) {
                        Log::error("Error al subir archivo: " . $e->getMessage());
                        $errors[] = "Error al subir {$originalFileName}: " . $e->getMessage();
                    }
                }
            }
        } else {
            $errors[] = "No se recibieron archivos válidos.";
        }

        if ($uploadedCount > 0) {
            $message = "Se subieron {$uploadedCount} archivo(s) exitosamente.";
            if (!empty($errors)) {
                $message .= ' Advertencias: ' . implode(', ', $errors);
            }
            return response()->json(['message' => $message], 200);
        } else {
            $errorMessage = 'No se pudo subir ningún archivo.' . (!empty($errors) ? ' Errores: ' . implode(', ', $errors) : '');
            return response()->json(['message' => $errorMessage], 500);
        }
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function moveFolder(Request $request)
    {
        $request->validate([
            'folder_id' => 'required|exists:folders,id',
            'target_folder_id' => 'nullable|exists:folders,id',
        ]);

        $folderToMove = Folder::findOrFail($request->folder_id);
        $targetFolder = $request->target_folder_id ? Folder::findOrFail($request->target_folder_id) : null;
        $user = Auth::user();

        if ($user->area && $user->area->name === 'Administración') {
        }
        elseif ($user->is_area_admin) {
            if ($folderToMove->area_id !== $user->area_id) {
                return response()->json(['success' => false, 'message' => 'No tienes permiso para mover esta carpeta fuera de tu área.'], 403);
            }
            if ($targetFolder && $targetFolder->area_id !== $user->area_id) {
                return response()->json(['success' => false, 'message' => 'No puedes mover carpetas a un área diferente a la tuya.'], 403);
            }
            if (is_null($targetFolder) && $folderToMove->area_id !== $user->area_id) {
                 return response()->json(['success' => false, 'message' => 'No puedes mover carpetas de otras áreas a la raíz de tu área.'], 403);
            }
        }
        else {
            return response()->json(['success' => false, 'message' => 'No tienes permiso para mover carpetas.'], 403);
        }

        if ($targetFolder && $this->isDescendantOf($targetFolder, $folderToMove)) {
            return response()->json(['success' => false, 'message' => 'No puedes mover una carpeta a sí misma o a una de sus subcarpetas.'], 422);
        }

        $existingFolderInTarget = Folder::where('name', $folderToMove->name)
                                        ->where('parent_id', $request->target_folder_id)
                                        ->where('area_id', $folderToMove->area_id)
                                        ->where('id', '!=', $folderToMove->id)
                                        ->first();
        if ($existingFolderInTarget) {
            return response()->json(['success' => false, 'message' => 'Ya existe una carpeta con el mismo nombre en la carpeta de destino.'], 409);
        }

        $folderToMove->parent_id = $request->target_folder_id;
        $folderToMove->save();

        return response()->json(['success' => true, 'message' => 'Carpeta movida exitosamente.']);
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadDroppedFiles(Request $request)
    {
        $request->validate([
            'folder_id' => 'nullable|exists:folders,id',
            'files' => 'required|array',
            'files.*' => 'file|max:500000',
        ]);

        $targetFolder = $request->folder_id ? Folder::findOrFail($request->folder_id) : null;
        $user = Auth::user();
        $uploadedCount = 0;
        $errors = [];

        $targetAreaId = $targetFolder ? $targetFolder->area_id : ($user->area_id ?? null);
        
        $isSuperAdmin = $user->isSuperAdmin();
        $isAreaAdmin = $user->is_area_admin;
        $manageableAreaIds = $user->accessibleAreas->pluck('id')->push($user->area_id)->filter()->unique();

        if ($user->isClient()) {
            return response()->json(['success' => false, 'message' => 'Los usuarios tipo Cliente no tienen permiso para subir archivos aquí.'], 403);
        }

        if (!$isSuperAdmin && !($isAreaAdmin && $manageableAreaIds->contains($targetAreaId))) {
             return response()->json(['success' => false, 'message' => 'No tienes permiso para subir archivos aquí.'], 403);
        }

        foreach ($request->file('files') as $file) {
            $originalFileName = $file->getClientOriginalName();
            $fileNameWithoutExt = pathinfo($originalFileName, PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();

            $fileNameToStore = $fileNameWithoutExt;
            if (!Str::endsWith(strtolower($fileNameToStore), '.' . strtolower($extension))) {
                $fileNameToStore .= '.' . $extension;
            }

            try {
                $path = $file->store('files', 's3');

                $fileLink = FileLink::create([
                    'name' => $fileNameToStore,
                    'type' => 'file',
                    'path' => $path,
                    'folder_id' => $request->folder_id,
                    'user_id' => Auth::id(),
                ]);

                ActivityLog::create([
                    'user_id' => Auth::id(),
                    'action' => 'Subió un archivo',
                    'action_key' => 'uploaded_file',
                    'item_type' => 'file_link',
                    'item_id' => $fileLink->id,
                    'details' => ['name' => $fileLink->name],
                ]);

                $uploadedCount++;
            } catch (\Exception $e) {
                $errors[] = "Error al subir {$originalFileName}: " . $e->getMessage();
            }
        }

        if ($uploadedCount > 0) {
            return response()->json(['success' => true, 'message' => "Se subieron {$uploadedCount} archivo(s) exitosamente."]);
        } else {
            return response()->json(['success' => false, 'message' => 'No se pudo subir ningún archivo.' . (!empty($errors) ? ' Errores: ' . implode(', ', $errors) : '')], 500);
        }
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulkDelete(Request $request)
    {
        $user = Auth::user();
        $folderIds = json_decode($request->input('folder_ids', '[]'));
        $fileLinkIds = json_decode($request->input('file_link_ids', '[]'));

        $deletedCount = 0;
        $errors = [];

        foreach ($folderIds as $folderId) {
            $folder = Folder::find($folderId);
            if ($folder) {
                if ($user->area && $user->area->name === 'Administración') {
                } elseif ($user->is_area_admin && $folder->area_id === $user->area_id) {
                } else {
                    $errors[] = "No tienes permiso para eliminar la carpeta '{$folder->name}'.";
                    continue;
                }

                try {
                    ActivityLog::create([
                        'user_id' => $user->id,
                        'action' => 'Eliminación masiva de carpeta',
                        'action_key' => 'deleted_folder_bulk',
                        'item_type' => 'folder',
                        'item_id' => $folder->id,
                        'details' => ['name' => $folder->name],
                    ]);                    
                    $folder->delete();
                    $deletedCount++;
                } catch (\Exception $e) {
                    $errors[] = "Error al eliminar la carpeta '{$folder->name}': " . $e->getMessage();
                }
            }
        }

        foreach ($fileLinkIds as $fileLinkId) {
            $fileLink = FileLink::find($fileLinkId);
            if ($fileLink) {
                if ($user->area && $user->area->name === 'Administración') {
                } elseif ($user->is_area_admin && $fileLink->folder->area_id === $user->area_id) {
                } else {
                    $errors[] = "No tienes permiso para eliminar el elemento '{$fileLink->name}'.";
                    continue;
                }

                try {
                    ActivityLog::create([
                        'user_id' => $user->id,
                        'action' => 'Eliminación masiva de archivo/enlace',
                        'action_key' => 'deleted_file_link_bulk',
                        'item_type' => 'file_link',
                        'item_id' => $fileLink->id,
                        'details' => ['name' => $fileLink->name],
                    ]);                    
                    if ($fileLink->type === 'file' && Storage::disk('s3')->exists($fileLink->path)) {
                        Storage::disk('s3')->delete($fileLink->path);
                    }
                    $fileLink->delete();
                    $deletedCount++;
                } catch (\Exception $e) {
                    $errors[] = "Error al eliminar el elemento '{$fileLink->name}': " . $e->getMessage();
                }
            }
        }

        if ($deletedCount > 0) {
            $message = "Se eliminaron {$deletedCount} elemento(s) exitosamente.";
            if (!empty($errors)) {
                $message .= ' Algunas eliminaciones fallaron: ' . implode(', ', $errors);
            }
            return response()->json(['success' => true, 'message' => $message]);
        } else {
            $errorMessage = 'No se pudo eliminar ningún elemento.' . (!empty($errors) ? ' Errores: ' . implode(', ', $errors) : '');
            return response()->json(['success' => false, 'message' => $errorMessage], 500);
        }
    }


    protected function isDescendantOf($descendant, $ancestor)
    {
        if (!$descendant || !$ancestor) {
            return false;
        }

        $current = $descendant;
        while ($current->parent_id) {
            if ($current->parent_id === $ancestor->id) {
                return true;
            }
            $current = Folder::find($current->parent_id);
            if (!$current) break;
        }
        return false;
    }

    public function apiChildren(Request $request)
    {
        $user = Auth::user();
        $parentId = $request->input('parent_id');

        $query = Folder::query();

        if ($user->area && $user->area->name === 'Administración') {
        } elseif ($user->is_area_admin) {
            $query->where('area_id', $user->area_id);
        } elseif ($user->isClient()) {
            $accessibleFolderIds = $user->accessibleFolders->pluck('id')->toArray();
            $query->whereIn('id', $accessibleFolderIds);
            if ($parentId && !in_array($parentId, $accessibleFolderIds)) {
                $query->whereRaw('1 = 0');
            }
        } else {
            $query->where('area_id', $user->area_id)
                  ->whereHas('usersWithAccess', function ($q) use ($user) {
                      $q->where('user_id', $user->id);
                  });
        }

        $folders = $query->where('parent_id', $parentId)
                         ->withCount(['children', 'fileLinks'])
                         ->orderBy('name')
                         ->get()
                         ->map(function ($folder) {
                            $folder->items_count = $folder->children_count + $folder->file_links_count;
                            return $folder;
                        });

        return response()->json($folders);
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulkMove(Request $request)
    {
        $request->validate([
            'folder_ids' => 'array',
            'folder_ids.*' => 'exists:folders,id',
            'file_link_ids' => 'array',
            'file_link_ids.*' => 'exists:file_links,id',
            'target_folder_id' => 'nullable|exists:folders,id',
        ]);

        $user = Auth::user();
        $folderIds = $request->input('folder_ids', []);
        $fileLinkIds = $request->input('file_link_ids', []);
        $targetFolderId = $request->input('target_folder_id');

        $movedCount = 0;
        $errors = [];

        $targetArea = null;
        $targetFolder = null;
        if ($targetFolderId) {
            $targetFolder = Folder::find($targetFolderId);
            if (!$targetFolder) {
                return response()->json(['success' => false, 'message' => 'La carpeta de destino no existe.'], 404);
            }
            $targetArea = $targetFolder->area;
        } else {
            $targetArea = $user->area;
        }

        if ($user->area && $user->area->name === 'Administración') {
        } elseif ($user->is_area_admin) {
            if (!$targetArea || $targetArea->id !== $user->area_id) {
                return response()->json(['success' => false, 'message' => 'No tienes permiso para mover elementos a un área diferente a la tuya.'], 403);
            }
        } else {
            return response()->json(['success' => false, 'message' => 'No tienes permiso para mover elementos.'], 403);
        }

        foreach ($folderIds as $folderId) {
            $folderToMove = Folder::find($folderId);
            if (!$folderToMove) {
                $errors[] = "Carpeta con ID {$folderId} no encontrada.";
                continue;
            }

            if ($user->area && $user->area->name === 'Administración') {
            } elseif ($user->is_area_admin) {
                if ($folderToMove->area_id !== $user->area_id) {
                    $errors[] = "No tienes permiso para mover la carpeta '{$folderToMove->name}' fuera de tu área.";
                    continue;
                }
            } else {
                $errors[] = "No tienes permiso para mover la carpeta '{$folderToMove->name}'.";
                continue;
            }

            if ($targetFolderId == $folderToMove->id || ($targetFolder && $this->isDescendantOf($targetFolder, $folderToMove))) {
                $errors[] = "No puedes mover la carpeta '{$folderToMove->name}' a sí misma o a una de sus subcarpetas.";
                continue;
            }

            $existingFolderInTarget = Folder::where('name', $folderToMove->name)
                                    ->where('parent_id', $targetFolderId)
                                    ->where('area_id', $folderToMove->area_id)
                                    ->where('id', '!=', $folderToMove->id)
                                    ->first();
            if ($existingFolderInTarget) {
                $errors[] = "Ya existe una carpeta con el mismo nombre ('{$folderToMove->name}') en la carpeta de destino.";
                continue;
            }

            try {
                ActivityLog::create([
                    'user_id' => $user->id,
                    'action' => 'Movió una carpeta',
                    'action_key' => 'moved_folder_bulk',
                    'item_type' => 'folder',
                    'item_id' => $folderToMove->id,
                    'details' => ['name' => $folderToMove->name, 'target_folder_id' => $targetFolderId],
                ]);                
                $folderToMove->parent_id = $targetFolderId;
                $folderToMove->save();
                $movedCount++;
            } catch (\Exception $e) {
                $errors[] = "Error al mover la carpeta '{$folderToMove->name}': " . $e->getMessage();
            }
        }

        foreach ($fileLinkIds as $fileLinkId) {
            $fileLinkToMove = FileLink::find($fileLinkId);
            if (!$fileLinkToMove) {
                $errors[] = "Archivo/Enlace con ID {$fileLinkId} no encontrado.";
                continue;
            }

            if ($user->area && $user->area->name === 'Administración') {
            } elseif ($user->is_area_admin) {
                if ($fileLinkToMove->folder->area_id !== $user->area_id) {
                    $errors[] = "No tienes permiso para mover el elemento '{$fileLinkToMove->name}' fuera de tu área.";
                    continue;
                }
            } else {
                $errors[] = "No tienes permiso para mover el elemento '{$fileLinkToMove->name}'.";
                continue;
            }

            $existingFileLinkInTarget = FileLink::where('name', $fileLinkToMove->name)
                                    ->where('folder_id', $targetFolderId)
                                    ->whereHas('folder', function($q) use ($fileLinkToMove) {
                                        $q->where('area_id', $fileLinkToMove->folder->area_id);
                                    })
                                    ->where('id', '!=', $fileLinkToMove->id)
                                    ->first();
            if ($existingFileLinkInTarget) {
                $errors[] = "Ya existe un archivo o enlace con el mismo nombre ('{$fileLinkToMove->name}') en la carpeta de destino.";
                continue;
            }

            try {
                ActivityLog::create([
                    'user_id' => $user->id,
                    'action' => 'Movió un archivo/enlace',
                    'action_key' => 'moved_file_link_bulk',
                    'item_type' => 'file_link',
                    'item_id' => $fileLinkToMove->id,
                    'details' => ['name' => $fileLinkToMove->name, 'target_folder_id' => $targetFolderId],
                ]);                
                $fileLinkToMove->folder_id = $targetFolderId;
                $fileLinkToMove->save();
                $movedCount++;
            } catch (\Exception $e) {
                $errors[] = "Error al mover el elemento '{$fileLinkToMove->name}': " . $e->getMessage();
            }
        }

        if ($movedCount > 0 && empty($errors)) {
            $message = "Se movieron {$movedCount} elemento(s) exitosamente.";
            return response()->json(['success' => true, 'message' => $message]);
        } elseif ($movedCount > 0 && !empty($errors)) {
             $message = "Se movieron {$movedCount} elemento(s) con advertencias: " . implode(', ', $errors);
             return response()->json(['success' => true, 'message' => $message]);
        } else {
            $errorMessage = 'No se pudo mover ningún elemento.' . (!empty($errors) ? ' Errores: ' . implode(', ', $errors) : '');
            return response()->json(['success' => false, 'message' => $errorMessage], 500);
        }
    }

    public function getFoldersForClientAccess(Request $request)
    {
        $user = Auth::user();

        if (!($user->area && $user->area->name === 'Administración')) {
            return response()->json(['message' => 'Acceso no autorizado para esta API.'], 403);
        }

        $parentId = $request->input('parent_id');

        $folders = Folder::where('parent_id', $parentId)
                         ->orderBy('name')
                         ->get();

        $folders->map(function ($folder) {
            $folder->has_children = $folder->children()->exists();
            return $folder;
        });

        return response()->json($folders);
    }

    public function uploadDirectory(Request $request)
    {
        $request->validate([
            'target_folder_id' => 'nullable|exists:folders,id',
            'area_id' => 'nullable|required_without:target_folder_id|exists:areas,id', 
            'files' => 'required|array',
            'files.*' => 'file|max:500000',
            'paths' => 'required|array',
        ]);

        $user = Auth::user();
        $files = $request->file('files');
        $paths = $request->input('paths');

        if (count($files) !== count($paths)) {
            return response()->json(['success' => false, 'message' => 'El número de archivos no coincide con el número de rutas.'], 400);
        }

        $baseFolder = $request->target_folder_id ? Folder::findOrFail($request->target_folder_id) : null;
        
        $baseFolderAreaId = null;
        if ($baseFolder) {
            $baseFolderAreaId = $baseFolder->area_id;
        } else {
            $baseFolderAreaId = $request->input('area_id'); 
        }

        $isSuperAdmin = $user->isSuperAdmin();
        $isAreaAdmin = $user->is_area_admin;
        $manageableAreaIds = $user->accessibleAreas->pluck('id')->push($user->area_id)->filter()->unique();

        if ($user->isClient()) {
             return response()->json(['success' => false, 'message' => 'No tienes permiso para subir carpetas aquí.'], 403);
        }

        if (!$isSuperAdmin && !($isAreaAdmin && $manageableAreaIds->contains($baseFolderAreaId))) {
             return response()->json(['success' => false, 'message' => 'No tienes permiso para subir carpetas en esta área.'], 403);
        }
        
        $uploadedCount = 0;
        
        try {
            for ($i = 0; $i < count($files); $i++) {
                $file = $files[$i];
                $relativePath = $paths[$i];

                $targetFolder = $this->findOrCreateNestedFolder($baseFolder, $relativePath, $user, $baseFolderAreaId);
                
                $originalFileName = $file->getClientOriginalName();
                $s3Path = $file->store('files', 's3');

                $fileLink = FileLink::create([
                    'name'      => $originalFileName,
                    'type'      => 'file',
                    'path'      => $s3Path,
                    'folder_id' => $targetFolder ? $targetFolder->id : null,
                    'user_id'   => $user->id,
                ]);

                ActivityLog::create([
                    'user_id' => $user->id,
                    'action' => 'Subió un archivo (en carpeta)',
                    'action_key' => 'uploaded_file_in_directory',
                    'item_type' => 'file_link',
                    'item_id' => $fileLink->id,
                    'details' => ['name' => $fileLink->name, 'path' => $relativePath],
                ]);
                $uploadedCount++;
            }

            return response()->json(['success' => true, 'message' => "Se subieron {$uploadedCount} archivos exitosamente."]);

        } catch (\Exception $e) {
            Log::error('Error al subir directorio: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Ocurrió un error en el servidor al procesar la subida.'], 500);
        }
    }

    /**
     * @param  \App\Models\Folder|null  $baseFolder La carpeta raíz donde inicia la creación.
     * @param  string  $filePath La ruta relativa del archivo.
     * @param  \App\Models\User  $user El usuario que realiza la acción.
     * @return \App\Models\Folder|null La carpeta final donde se debe guardar el archivo.
     */
    private function findOrCreateNestedFolder(?Folder $baseFolder, string $filePath, $user, $baseAreaId): ?Folder
    {
        $directoryPath = pathinfo($filePath, PATHINFO_DIRNAME);

        if ($directoryPath === '.' || $directoryPath === '') {
            return $baseFolder;
        }

        $pathSegments = explode('/', $directoryPath);
        $currentFolder = $baseFolder;
        $currentFolderId = $baseFolder ? $baseFolder->id : null;
        $areaId = $baseAreaId; 

        foreach ($pathSegments as $segment) {
            $currentFolder = Folder::firstOrCreate(
                [
                    'parent_id' => $currentFolderId,
                    'name'      => $segment,
                    'area_id'   => $areaId,
                ],
                [
                    'user_id'   => $user->id,
                ]
            );
            $currentFolderId = $currentFolder->id;
        }

        return $currentFolder;
    }

}