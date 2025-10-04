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
     * Display a listing of the folders.
     * Muestra una lista de las carpetas accesibles por el usuario, con opción de búsqueda.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Folder|null  $folder
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function index(Request $request, Folder $folder = null)
    {
        $user = Auth::user();
        $currentFolder = $folder;
        $searchQuery = $request->input('search');

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
            // Super Admin ve todo, no se aplican filtros de permisos.
        } elseif ($user->isClient()) {
            // Cliente solo ve carpetas y archivos a los que tiene acceso explícito.
            $accessibleFolderIds = $user->accessibleFolders()->pluck('id');
            
            // Se aplica el filtro de permisos a nivel general.
            $folderQuery->whereIn('id', $accessibleFolderIds);
            $fileLinkQuery->whereIn('folder_id', $accessibleFolderIds);

            // Si el cliente intenta NAVEGAR a una carpeta sin permiso, se le redirige.
            if ($currentFolder && !$accessibleFolderIds->contains($currentFolder->id)) {
                return redirect()->route('dashboard')->with('error', 'No tienes permiso para ver esta carpeta.');
            }

        } else { // Administradores de Área y Usuarios Normales
            // Todos los demás usuarios están restringidos a su propia área.
            $folderQuery->where('area_id', $user->area_id);
            $fileLinkQuery->whereHas('folder', function($q) use ($user) {
                $q->where('area_id', $user->area_id);
            });

            // Los usuarios normales (no admins de área) además requieren acceso explícito
            if (!$user->is_area_admin) {
                $accessibleFolderIds = $user->accessibleFolders()->pluck('id');
                $folderQuery->whereIn('id', $accessibleFolderIds);
                $fileLinkQuery->whereIn('folder_id', $accessibleFolderIds);
            }
        }

        // ---------------------------------------------------------------
        // PASO 2: APLICAR FILTRO DE BÚSQUEDA O DE NAVEGACIÓN
        // ---------------------------------------------------------------
        if ($searchQuery) {
            // MODO BÚSQUEDA: Busca por nombre en los resultados ya filtrados por permisos.
            $folders = $folderQuery->where('name', 'like', '%' . $searchQuery . '%')->orderBy('name')->get();
            $fileLinks = $fileLinkQuery->where('name', 'like', '%' . $searchQuery . '%')->orderBy('name')->get();
            
            $currentFolder = null; // En modo búsqueda, no hay carpeta actual.
            $breadcrumbs = collect(); // No hay breadcrumbs en la búsqueda.

        } else {
            // MODO NAVEGACIÓN: Muestra el contenido de la carpeta actual.
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

            // Generar breadcrumbs para la navegación
            $breadcrumbs = collect();
            if ($currentFolder) {
                $parent = $currentFolder->parent;
                while ($parent) {
                    $breadcrumbs->prepend($parent);
                    $parent = $parent->parent;
                }
            }
        }

        return view('folders.index', compact('folders', 'currentFolder', 'fileLinks', 'searchQuery', 'breadcrumbs'));
    }

    /**
     * Show the form for creating a new folder.
     * Muestra el formulario para crear una nueva carpeta.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Folder|null  $folder
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function create(Folder $folder = null)
    {
        $user = Auth::user();
        $currentFolder = $folder;

        // Super Admin puede crear en cualquier lugar
        if ($user->area && $user->area->name === 'Administración') {
            // No hay restricción
        }
        // Admin de Área solo puede crear en su área
        elseif ($user->is_area_admin) {
            if ($currentFolder && $currentFolder->area_id !== $user->area_id) {
                return redirect()->route('folders.index')->with('error', 'No puedes crear carpetas fuera de tu área.');
            }
        }
        // Clientes no pueden crear carpetas
        elseif ($user->isClient()) {
            return redirect()->route('folders.index')->with('error', 'Los usuarios tipo Cliente no tienen permiso para crear carpetas.');
        }
        // Usuario Normal ahora puede crear carpetas si está en su área
        elseif ($currentFolder && $currentFolder->area_id !== $user->area_id) {
            return redirect()->route('folders.index')->with('error', 'No puedes crear carpetas fuera de tu área.');
        }

        return view('folders.create', compact('currentFolder', 'user'));
    }

    /**
     * Store a newly created folder in storage.
     * Almacena una nueva carpeta creada en la base de datos.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // 1. Validar la solicitud
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:folders,id',
        ]);

        $user = Auth::user();
        $targetAreaId = null;

        // Determinar el área de la nueva carpeta
        if ($request->parent_id) {
            $parentFolder = Folder::find($request->parent_id);
            if (!$parentFolder) {
                return back()->withErrors(['parent_id' => 'La carpeta padre no existe.']);
            }
            $targetAreaId = $parentFolder->area_id;
        } else {
            // Si es carpeta raíz, usa el área del usuario
            $targetAreaId = $user->area_id;
        }

        // Verificar permisos para crear
        if ($user->area && $user->area->name === 'Administración') {
            // Super Admin puede crear
        } elseif ($user->is_area_admin && $targetAreaId === $user->area_id) {
            // Admin de Área puede crear en su propia área
        } elseif ($user->isClient()) { // Clientes no pueden crear carpetas
            return redirect()->route('folders.index')->with('error', 'Los usuarios tipo Cliente no tienen permiso para crear carpetas.');
        } elseif ($targetAreaId === $user->area_id) { // Usuario Normal puede crear en su área
            // No se necesita un 'else' adicional, la creación se permite si el área coincide.
        } else {
            return redirect()->route('folders.index')->with('error', 'No tienes permiso para crear carpetas aquí.');
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
            'parent_id' => 'nullable|exists:folders,id',
        ]);

        $newFolder = Folder::create([
            'name' => $request->name,
            'parent_id' => $request->parent_id,
            'area_id' => $targetAreaId,
            'user_id' => $user->id,
        ]);

        // 3. REGISTRAR la actividad en el log
        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'Creó una carpeta',
            'action_key' => 'created_folder',
            'item_type' => 'folder',
            'item_id' => $newFolder->id,
            'details' => ['name' => $newFolder->name, 'parent_id' => $newFolder->parent_id],
        ]);

        // 4. Redirigir al usuario
        $redirectPath = $request->parent_id ? route('folders.index', $request->parent_id) : route('folders.index');
        return redirect($redirectPath)->with('success', 'Carpeta creada exitosamente.');
    }

    /**
     * Display the specified folder and its contents.
     * Muestra la carpeta especificada y su contenido.
     *
     * @param  \App\Models\Folder  $folder
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function show(Folder $folder)
    {
        // La lógica de permisos ya está en el método index, que es llamado aquí.
        // Si el usuario no tiene acceso, index ya redirigirá.
        return $this->index(request(), $folder);
    }

    /**
     * Show the form for editing the specified folder.
     * Muestra el formulario para editar la carpeta especificada.
     *
     * @param  \App\Models\Folder  $folder
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function edit(Folder $folder)
    {
        $user = Auth::user();

        // Verificar permisos para editar
        if ($user->area && $user->area->name === 'Administración') {
            // Super Admin puede editar cualquier carpeta
        } elseif ($user->is_area_admin && $folder->area_id === $user->area_id) {
            // Admin de Área puede editar carpetas de su propia área
        } else {
            // Ni usuario normal ni cliente pueden editar carpetas
            return redirect()->route('folders.index')->with('error', 'No tienes permiso para editar esta carpeta.');
        }

        return view('folders.edit', compact('folder', 'user'));
    }

    /**
     * Update the specified folder in storage.
     * Actualiza la carpeta especificada en la base de datos.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Folder  $folder
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Folder $folder)
    {
        $user = Auth::user();

        // Verificar permisos para actualizar
        if ($user->area && $user->area->name === 'Administración') {
            // Super Admin puede actualizar cualquier carpeta
        } elseif ($user->is_area_admin && $folder->area_id === $user->area_id) {
            // Admin de Área puede actualizar carpetas de su propia área
        } else {
            // Ni usuario normal ni cliente pueden actualizar carpetas
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
     * Remove the specified folder from storage.
     * Elimina la carpeta especificada de la base de datos.
     *
     * @param  \App\Models\Folder  $folder
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Folder $folder)
    {
        $user = Auth::user();

        // Verificar permisos para eliminar
        if ($user->area && $user->area->name === 'Administración') {
            // Super Admin puede eliminar cualquier carpeta
        } elseif ($user->is_area_admin && $folder->area_id === $user->area_id) {
            // Admin de Área puede eliminar carpetas de su propia área
        } else {
            // Usuario Normal y Cliente NO tienen permiso para eliminar carpetas
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para eliminar esta carpeta.'
            ], 403); // Use a 403 Forbidden status code
        }

        // Log the activity
        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'Eliminó una carpeta',
            'action_key' => 'deleted_folder',
            'item_type' => 'folder',
            'item_id' => $folder->id,
            'details' => ['name' => $folder->name],
        ]);

        // Perform the deletion
        $folder->delete();

        // Return a JSON response for a successful deletion
        return response()->json([
            'success' => true,
            'message' => 'Carpeta eliminada exitosamente.'
        ]);
    }

    /**
     * Show the form for creating a new file or link.
     * Muestra el formulario para crear un nuevo archivo o enlace.
     *
     * @param  \App\Models\Folder  $folder
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function createFileLink(Folder $folder)
    {
        $user = Auth::user();

        // Verificar permisos para añadir a esta carpeta
        if ($user->area && $user->area->name === 'Administración') {
            // Super Admin puede añadir
        } elseif ($user->is_area_admin && $folder->area_id === $user->area_id) {
            // Admin de Área puede añadir en su propia área
        } elseif ($user->isClient()) { // Clientes no pueden añadir elementos
            return redirect()->route('folders.index', $folder)->with('error', 'Los usuarios tipo Cliente no tienen permiso para añadir elementos a esta carpeta.');
        } elseif ($folder->area_id === $user->area_id) { // Usuario Normal puede añadir en su área
            // No se necesita un 'else' adicional, se permite si el área coincide.
        } else {
            return redirect()->route('folders.index', $folder)->with('error', 'No tienes permiso para añadir elementos a esta carpeta.');
        }

        return view('file_links.create', compact('folder'));
    }

    /**
     * Store a newly created file or link in storage.
     * Almacena uno o varios archivos/enlaces creados en la base de datos.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Folder  $folder
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeFileLink(Request $request, Folder $folder)
    {
        $user = Auth::user();

        // Verificar permisos para almacenar
        if ($user->area && $user->area->name === 'Administración') {
            // Super Admin puede almacenar
        } elseif ($user->is_area_admin && $folder->area_id === $user->area_id) {
            // Admin de Área puede almacenar en su propia área
        } elseif ($user->isClient()) { // Clientes no pueden almacenar elementos
            return response()->json(['message' => 'Los usuarios tipo Cliente no tienen permiso para añadir elementos a esta carpeta.'], 403);
        } elseif ($folder->area_id === $user->area_id) { // Usuario Normal puede almacenar en su área
            // No se necesita un 'else' adicional, se permite si el área coincide.
        } else {
            return response()->json(['message' => 'No tienes permiso para añadir elementos a esta carpeta.'], 403);
        }

        $validationRules = [
            'type' => 'required|in:file,link',
            'files' => 'nullable|array',
            'files.*' => 'file|max:500000', // 500MB por archivo
            'url' => 'nullable|url|max:2048',
            'name' => 'nullable|string|max:255',
        ];

        if ($request->type === 'link') {
            $validationRules['name'] = 'required|string|max:255';
            $validationRules['url'] = 'required|url|max:2048';
        } else {
            $validationRules['files'] = 'required|array';
            $validationRules['files.*'] = 'file|max:500000'; // 500MB por archivo
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
                        $fileNameToStore .= '.' . strtolower($extension);
                    }

                    try {
                        $path = $file->store('files', 's3');

                        // 1. First, create the FileLink record and get the instance.
                        $fileLink = FileLink::create([
                            'name' => $fileNameToStore,
                            'type' => 'file',
                            'path' => $path,
                            'folder_id' => $folder->id,
                            'user_id' => Auth::id(),
                        ]);

                        // 2. Then, use the created instance to log the activity.
                        ActivityLog::create([
                            'user_id' => Auth::id(),
                            'action' => 'Subió un archivo',
                            'action_key' => 'uploaded_file',
                            'item_type' => 'file_link',
                            'item_id' => $fileLink->id, // Now $fileLink is defined and has an ID
                            'details' => ['name' => $fileLink->name],
                        ]);
                        
                        $uploadedCount++;
                    } catch (\Exception $e) {
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
     * Mueve una carpeta a una nueva carpeta padre.
     *
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

        // Validar permisos para mover
        // Super Admin puede mover cualquier carpeta a cualquier lugar
        if ($user->area && $user->area->name === 'Administración') {
            // No se requiere validación adicional de área
        }
        // Admin de Área solo puede mover carpetas dentro de su propia área
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
        // Usuario normal y Clientes no pueden mover carpetas
        else {
            return response()->json(['success' => false, 'message' => 'No tienes permiso para mover carpetas.'], 403);
        }

        // Evitar mover una carpeta a sí misma o a una de sus subcarpetas
        if ($targetFolder && $this->isDescendantOf($targetFolder, $folderToMove)) {
            return response()->json(['success' => false, 'message' => 'No puedes mover una carpeta a sí misma o a una de sus subcarpetas.'], 422);
        }

        // Asegurarse de que el nombre sea único en la nueva ubicación
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
     * Sube archivos arrastrados y soltados a una carpeta específica.
     *
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

        // Validar permisos para subir
        $targetAreaId = $targetFolder ? $targetFolder->area_id : ($user->area_id ?? null); // Si es raíz, usa el área del usuario

        if ($user->area && $user->area->name === 'Administración') {
            // Super Admin puede subir a cualquier lugar
        } elseif ($user->is_area_admin && $targetAreaId === $user->area_id) {
            // Admin de Área puede subir a su propia área
        } elseif ($user->isClient()) { // Clientes no pueden subir archivos
            return response()->json(['success' => false, 'message' => 'Los usuarios tipo Cliente no tienen permiso para subir archivos aquí.'], 403);
        } elseif ($targetAreaId === $user->area_id) { // Usuario Normal puede subir a su área
            // No se necesita un 'else' adicional, la subida se permite si el área coincide.
        } else {
            return response()->json(['success' => false, 'message' => 'No tienes permiso para subir archivos aquí.'], 403);
        }

        foreach ($request->file('files') as $file) {
            $originalFileName = $file->getClientOriginalName();
            $fileNameWithoutExt = pathinfo($originalFileName, PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();

            $fileNameToStore = $fileNameWithoutExt;
            if (!Str::endsWith(strtolower($fileNameToStore), '.' . strtolower($extension))) {
                $fileNameToStore .= '.' . strtolower($extension);
            }

            try {
                // Paso 1: Subir el archivo a S3 y obtener la ruta
                $path = $file->store('files', 's3');

                // Paso 2: Crear el registro en la base de datos y OBTENER la instancia
                $fileLink = FileLink::create([
                    'name' => $fileNameToStore,
                    'type' => 'file',
                    'path' => $path,
                    'folder_id' => $request->folder_id,
                    'user_id' => Auth::id(),
                ]);

                // Paso 3: Usar la instancia $fileLink para registrar la actividad
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
     * Elimina múltiples carpetas y/o file_links.
     *
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

        // Eliminar carpetas
        foreach ($folderIds as $folderId) {
            $folder = Folder::find($folderId);
            if ($folder) {
                // Verificar permisos para eliminar la carpeta
                if ($user->area && $user->area->name === 'Administración') {
                    // Super Admin puede eliminar cualquier carpeta
                } elseif ($user->is_area_admin && $folder->area_id === $user->area_id) {
                    // Admin de Área puede eliminar carpetas de su propia área
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

        // Eliminar FileLinks
        foreach ($fileLinkIds as $fileLinkId) {
            $fileLink = FileLink::find($fileLinkId);
            if ($fileLink) {
                // Verificar permisos para eliminar el FileLink
                if ($user->area && $user->area->name === 'Administración') {
                    // Super Admin puede eliminar cualquier file_link
                } elseif ($user->is_area_admin && $fileLink->folder->area_id === $user->area_id) {
                    // Admin de Área puede eliminar file_links de su propia área
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


    /**
     * Helper para prevenir ciclos en la jerarquía (ej. A es manager de B, B no puede ser manager de A)
     * Adaptado para carpetas: verifica si $descendant es una subcarpeta de $ancestor
     */
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

        // Aplicar filtros de permiso
        if ($user->area && $user->area->name === 'Administración') {
            // Super Admin: no hay restricción
        } elseif ($user->is_area_admin) {
            $query->where('area_id', $user->area_id);
        } elseif ($user->isClient()) { // Clientes solo ven sus carpetas accesibles
            $accessibleFolderIds = $user->accessibleFolders->pluck('id')->toArray();
            $query->whereIn('id', $accessibleFolderIds);
            // Además, si el parentId es una carpeta a la que el cliente tiene acceso, se muestran sus hijos (si están en la lista)
            if ($parentId && !in_array($parentId, $accessibleFolderIds)) {
                // Si el parentId no es una carpeta a la que el cliente tiene acceso, no se muestran hijos
                $query->whereRaw('1 = 0'); // Devuelve un resultado vacío
            }
        } else {
            // Usuario normal: solo sus carpetas accesibles
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
     * Mueve múltiples carpetas y/o file_links a una nueva ubicación.
     *
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

        // Determinar el área de destino.
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

        // Permisos para la carpeta de destino
        if ($user->area && $user->area->name === 'Administración') {
            // Super Admin
        } elseif ($user->is_area_admin) {
            if (!$targetArea || $targetArea->id !== $user->area_id) {
                return response()->json(['success' => false, 'message' => 'No tienes permiso para mover elementos a un área diferente a la tuya.'], 403);
            }
        } else {
            // Usuario normal y Clientes no pueden mover elementos
            return response()->json(['success' => false, 'message' => 'No tienes permiso para mover elementos.'], 403);
        }

        // Mover carpetas
        foreach ($folderIds as $folderId) {
            $folderToMove = Folder::find($folderId);
            if (!$folderToMove) {
                $errors[] = "Carpeta con ID {$folderId} no encontrada.";
                continue;
            }

            // Permisos para la carpeta a mover
            if ($user->area && $user->area->name === 'Administración') {
                // Super Admin
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

        // Mover FileLinks
        foreach ($fileLinkIds as $fileLinkId) {
            $fileLinkToMove = FileLink::find($fileLinkId);
            if (!$fileLinkToMove) {
                $errors[] = "Archivo/Enlace con ID {$fileLinkId} no encontrado.";
                continue;
            }

            // Permisos para el FileLink a mover:
            if ($user->area && $user->area->name === 'Administración') {
                // Super Admin
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

        // Esta API solo debería ser accesible por el Super Admin
        if (!($user->area && $user->area->name === 'Administración')) {
            return response()->json(['message' => 'Acceso no autorizado para esta API.'], 403);
        }

        $parentId = $request->input('parent_id');

        $folders = Folder::where('parent_id', $parentId)
                         ->orderBy('name')
                         ->get();

        // Para cada carpeta, verificar si tiene subcarpetas para el frontend
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
            'files' => 'required|array',
            'files.*' => 'file|max:500000', // 500MB
            'paths' => 'required|array',
        ]);

        $user = Auth::user();
        $files = $request->file('files');
        $paths = $request->input('paths');

        if (count($files) !== count($paths)) {
            return response()->json(['success' => false, 'message' => 'El número de archivos no coincide con el número de rutas.'], 400);
        }

        $baseFolder = $request->target_folder_id ? Folder::findOrFail($request->target_folder_id) : null;
        
        // --- Validación de Permisos (muy importante) ---
        $baseFolderAreaId = $baseFolder ? $baseFolder->area_id : $user->area_id;

        if (!$user->isSuperAdmin() && !$user->is_area_admin && $baseFolderAreaId !== $user->area_id) {
             return response()->json(['success' => false, 'message' => 'No tienes permiso para subir archivos aquí.'], 403);
        }
        
        $uploadedCount = 0;
        
        try {
            for ($i = 0; $i < count($files); $i++) {
                $file = $files[$i];
                $relativePath = $paths[$i];

                // 1. Encuentra o crea la carpeta de destino para este archivo
                $targetFolder = $this->findOrCreateNestedFolder($baseFolder, $relativePath, $user);
                
                // 2. Procesa y sube el archivo
                $originalFileName = $file->getClientOriginalName();
                $s3Path = $file->store('files', 's3');

                // 3. Crea el registro en la base de datos
                $fileLink = FileLink::create([
                    'name'      => $originalFileName,
                    'type'      => 'file',
                    'path'      => $s3Path,
                    'folder_id' => $targetFolder ? $targetFolder->id : null,
                    'user_id'   => $user->id,
                ]);

                // 4. Registrar actividad (opcional pero recomendado)
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
            // Log del error para depuración
            Log::error('Error al subir directorio: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Ocurrió un error en el servidor al procesar la subida.'], 500);
        }
    }

    /**
     * Encuentra o crea una estructura de carpetas anidada a partir de una ruta relativa.
     *
     * @param  \App\Models\Folder|null  $baseFolder La carpeta raíz donde inicia la creación.
     * @param  string  $filePath La ruta relativa del archivo (ej. "docs/reportes/2024.pdf").
     * @param  \App\Models\User  $user El usuario que realiza la acción.
     * @return \App\Models\Folder|null La carpeta final donde se debe guardar el archivo.
     */
    private function findOrCreateNestedFolder(?Folder $baseFolder, string $filePath, $user): ?Folder
    {
        // Extraemos solo la parte del directorio de la ruta del archivo
        $directoryPath = pathinfo($filePath, PATHINFO_DIRNAME);

        // Si el archivo está en la raíz de la carpeta subida, no hay nada que crear.
        if ($directoryPath === '.' || $directoryPath === '') {
            return $baseFolder;
        }

        $pathSegments = explode('/', $directoryPath);
        $currentFolder = $baseFolder;
        $currentFolderId = $baseFolder ? $baseFolder->id : null;
        $areaId = $baseFolder ? $baseFolder->area_id : $user->area_id;

        foreach ($pathSegments as $segment) {
            // firstOrCreate: busca una carpeta con ese nombre en el padre actual.
            // Si no la encuentra, la crea con los valores proporcionados.
            $currentFolder = Folder::firstOrCreate(
                [
                    'parent_id' => $currentFolderId,
                    'name'      => $segment,
                    'area_id'   => $areaId,
                ],
                [
                    'user_id'   => $user->id, // El creador de la nueva carpeta
                ]
            );
            $currentFolderId = $currentFolder->id;
        }

        return $currentFolder;
    }

}