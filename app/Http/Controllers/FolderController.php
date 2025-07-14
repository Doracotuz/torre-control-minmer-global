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
        $currentFolder = $folder; // La carpeta actual que estamos viendo (null si estamos en la raíz)
        $searchQuery = $request->input('search'); // Obtener el término de búsqueda

        // --- Lógica de Permisos para ver Carpetas y Contenido ---
        $folderQuery = Folder::query();
        $fileLinkQuery = FileLink::query();

        // Aplicar filtros de permiso según el rol del usuario
        if ($user->area && $user->area->name === 'Administración') {
            // Super Administrador: No se aplica ninguna restricción de área o permiso explícito
        } elseif ($user->is_area_admin) {
            // Administrador de Área: Ve todas las carpetas y archivos de su propia área
            $folderQuery->where('area_id', $user->area_id);
            $fileLinkQuery->whereHas('folder', function($q) use ($user) {
                $q->where('area_id', $user->area_id);
            });
        } else {
            // Usuario Normal: Solo ve carpetas y archivos de su área con acceso explícito
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

        // --- Lógica de Búsqueda ---
        if ($searchQuery) {
            // Si hay un término de búsqueda, buscamos en todas las carpetas y archivos accesibles
            // Ignoramos la jerarquía de parent_id para la búsqueda global
            $folders = $folderQuery->where('name', 'like', '%' . $searchQuery . '%')
                                   ->orderBy('name')
                                   ->get();
            $fileLinks = $fileLinkQuery->where('name', 'like', '%' . $searchQuery . '%')
                                       ->orderBy('name')
                                       ->get();
            $currentFolder = null; // En modo búsqueda, no estamos en una carpeta específica
        } else {
            // Si no hay término de búsqueda, aplicamos la lógica de jerarquía normal
            $folders = $folderQuery->where('parent_id', $currentFolder ? $currentFolder->id : null)
                                   // MODIFICADO: Esta es la parte clave para contar todos los elementos
                                   ->withCount(['children', 'fileLinks'])
                                   ->get()
                                   ->map(function ($folder) {
                                       // Sumamos los conteos de children y fileLinks
                                       $folder->items_count = $folder->children_count + $folder->file_links_count;
                                       return $folder;
                                   });

            $fileLinks = collect(); // Inicializar como colección vacía
            if ($currentFolder) {
                // Verificar permisos para la carpeta actual antes de mostrar su contenido
                $hasAccessToCurrentFolder = false;
                if ($user->area && $user->area->name === 'Administración') {
                    $hasAccessToCurrentFolder = true; // Super Admin siempre tiene acceso
                } elseif ($user->is_area_admin && $currentFolder->area_id === $user->area_id) {
                    $hasAccessToCurrentFolder = true; // Admin de Área tiene acceso a sus carpetas
                } elseif ($currentFolder->area_id === $user->area_id && $user->accessibleFolders->contains($currentFolder->id)) {
                    $hasAccessToCurrentFolder = true; // Usuario normal con acceso explícito y en su área
                }

                if (!$hasAccessToCurrentFolder) {
                    return redirect()->route('dashboard')->with('error', 'No tienes permiso para ver esta carpeta o su contenido.');
                }

                $fileLinks = $fileLinkQuery->where('folder_id', $currentFolder->id)
                                           ->orderBy('name')
                                           ->get();
            }
        }

        return view('folders.index', compact('folders', 'currentFolder', 'fileLinks', 'searchQuery'));
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
        // Usuario Normal no puede crear carpetas
        else {
            return redirect()->route('folders.index')->with('error', 'No tienes permiso para crear carpetas.');
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
        $user = Auth::user();

        // Determinar el area_id de la nueva carpeta
        $targetAreaId = null;
        if ($request->parent_id) {
            $parentFolder = Folder::find($request->parent_id);
            if (!$parentFolder) {
                return back()->withErrors(['parent_id' => 'La carpeta padre no existe.']);
            }
            $targetAreaId = $parentFolder->area_id;
        } else {
            // Si es carpeta raíz, usa el área del usuario (o permite al Super Admin elegir)
            if ($user->area && $user->area->name === 'Administración') {
                $targetAreaId = $user->area_id; // Super Admin crea en su propia área por defecto
            } else {
                $targetAreaId = $user->area_id;
            }
        }

        // Verificar permisos para crear
        if ($user->area && $user->area->name === 'Administración') {
            // Super Admin puede crear
        } elseif ($user->is_area_admin && $targetAreaId === $user->area_id) {
            // Admin de Área puede crear en su propia área
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

        Folder::create([
            'name' => $request->name,
            'parent_id' => $request->parent_id,
            'area_id' => $targetAreaId, // Asigna el área determinada
            'user_id' => $user->id,
        ]);

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
            return redirect()->route('folders.index')->with('error', 'No tienes permiso para eliminar esta carpeta.');
        }

        $parentFolderId = $folder->parent_id;
        $folder->delete(); // Esto debería manejar la eliminación de sus contenidos por cascada si está configurado en el modelo o migraciones

        $redirectPath = $parentFolderId ? route('folders.index', $parentFolderId) : route('folders.index');

        return redirect($redirectPath)->with('success', 'Carpeta eliminada exitosamente.');
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
            // Para múltiples archivos, el campo 'name' del formulario se ignora y se usan los nombres originales.
            // Para un solo archivo, si se proporciona un nombre en el formulario, se usa.
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

                    $fileNameToStore = $fileNameWithoutExt; // Por defecto el nombre original del archivo

                    // Si es un solo archivo Y se proporcionó un nombre en el campo 'name', úsalo.
                    if (count($files) === 1 && $request->filled('name')) {
                        $fileNameToStore = $request->name;
                    }

                    // Asegúrate de que la extensión del archivo se mantenga o se añada
                    if (!Str::endsWith(strtolower($fileNameToStore), '.' . strtolower($extension))) {
                        $fileNameToStore .= '.' . strtolower($extension);
                    }

                    try {
                        $path = $file->store('files', 'public'); // Guarda el archivo
                        FileLink::create([
                            'name' => $fileNameToStore, // Usa el nombre determinado
                            'type' => 'file',
                            'path' => $path,
                            'folder_id' => $folder->id,
                            'user_id' => Auth::id(),
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
            'target_folder_id' => 'nullable|exists:folders,id', // null para mover a la raíz
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
            // Si mueve a la raíz, debe ser su propia área
            if (is_null($targetFolder) && $folderToMove->area_id !== $user->area_id) {
                 return response()->json(['success' => false, 'message' => 'No puedes mover carpetas de otras áreas a la raíz de tu área.'], 403);
            }
        }
        // Usuario normal no puede mover carpetas
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
                                        ->where('area_id', $folderToMove->area_id) // El área de la carpeta no cambia al moverla
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
            'folder_id' => 'nullable|exists:folders,id', // null para subir a la raíz
            'files' => 'required|array',
            'files.*' => 'file|max:500000', // 10MB por archivo
            // 'names' ya no es necesario si usamos getClientOriginalName()
        ]);

        $targetFolder = $request->folder_id ? Folder::findOrFail($request->folder_id) : null;
        $user = Auth::user();
        $uploadedCount = 0;
        $errors = [];

        // Validar permisos para subir
        $targetAreaId = $targetFolder ? $targetFolder->area_id : $user->area_id; // Si es raíz, usa el área del usuario

        if ($user->area && $user->area->name === 'Administración') {
            // Super Admin puede subir a cualquier lugar
        } elseif ($user->is_area_admin && $targetAreaId === $user->area_id) {
            // Admin de Área puede subir a su propia área
        } else {
            return response()->json(['success' => false, 'message' => 'No tienes permiso para subir archivos aquí.'], 403);
        }

        foreach ($request->file('files') as $file) {
            $originalFileName = $file->getClientOriginalName();
            $fileNameWithoutExt = pathinfo($originalFileName, PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();

            // Construir el nombre final del archivo para el registro
            $fileNameToStore = $fileNameWithoutExt;
            if (!Str::endsWith(strtolower($fileNameToStore), '.' . strtolower($extension))) {
                $fileNameToStore .= '.' . strtolower($extension);
            }

            try {
                $path = $file->store('files', 'public'); // Guarda el archivo
                FileLink::create([
                    'name' => $fileNameToStore, // Usar el nombre construido
                    'type' => 'file',
                    'path' => $path,
                    'folder_id' => $request->folder_id,
                    'user_id' => Auth::id(),
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
                    continue; // Saltar a la siguiente carpeta
                }

                try {
                    $folder->delete(); // Esto debería manejar la eliminación en cascada
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
                    continue; // Saltar al siguiente file_link
                }

                try {
                    // Si es un archivo, elimina el archivo físico del almacenamiento
                    if ($fileLink->type === 'file' && Storage::disk('public')->exists($fileLink->path)) {
                        Storage::disk('public')->delete($fileLink->path);
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
            if (!$current) break; // Evitar bucles infinitos si la relación es inconsistente
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
        } else {
            // Usuario normal: solo sus carpetas accesibles
            $query->where('area_id', $user->area_id)
                  ->whereHas('usersWithAccess', function ($q) use ($user) {
                      $q->where('user_id', $user->id);
                  });
        }

        $folders = $query->where('parent_id', $parentId)
                         ->withCount(['children', 'fileLinks']) // Para el conteo de elementos
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
            'target_folder_id' => 'nullable|exists:folders,id', // null para mover a la raíz
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

            // ***** VALIDACIÓN ADICIONAL Y MÁS EXPLÍCITA PARA EL PROBLEMA *****
            // Si la carpeta de destino es la misma que la carpeta a mover, O si la carpeta de destino es una subcarpeta de la que se va a mover
            if ($targetFolderId == $folderToMove->id || ($targetFolder && $this->isDescendantOf($targetFolder, $folderToMove))) {
                $errors[] = "No puedes mover la carpeta '{$folderToMove->name}' a sí misma o a una de sus subcarpetas.";
                continue; // Saltar a la siguiente carpeta si esta validación falla
            }
            // ***************************************************************

            // Evitar mover a una carpeta si ya existe un elemento con el mismo nombre y área
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
                $folderToMove->parent_id = $targetFolderId;
                $folderToMove->save();
                $movedCount++;
            } catch (\Exception $e) {
                $errors[] = "Error al mover la carpeta '{$folderToMove->name}': " . $e->getMessage();
            }
        }

        // Mover FileLinks (la lógica permanece igual, ya que no son carpetas que puedan contenerse a sí mismas)
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

            // Evitar mover a una carpeta si ya existe un elemento con el mismo nombre y área
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

    /**
     * Helper para prevenir ciclos en la jerarquía
     */


}