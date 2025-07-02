<?php

namespace App\Http\Controllers;

use App\Models\Folder;
use App\Models\Area;
use App\Models\FileLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

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
                                   ->orderBy('name')
                                   ->get();

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
        $folder->delete();

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
     * Almacena un nuevo archivo o enlace creado en la base de datos.
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
            return redirect()->route('folders.index', $folder)->with('error', 'No tienes permiso para añadir elementos a esta carpeta.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:file,link',
            'file' => 'nullable|file|max:10240', // Max 10MB (10240 KB)
            'url' => 'nullable|url|max:2048',
        ]);

        $path = null;
        $url = null;

        if ($request->type === 'file') {
            if ($request->hasFile('file')) {
                $path = $request->file('file')->store('files', 'public'); // Guarda en storage/app/public/files
            } else {
                return back()->withErrors(['file' => 'Debe seleccionar un archivo para subir.']);
            }
        } elseif ($request->type === 'link') {
            if ($request->filled('url')) {
                $url = $request->url;
            } else {
                return back()->withErrors(['url' => 'Debe proporcionar una URL para el enlace.']);
            }
        }

        FileLink::create([
            'name' => $request->name,
            'type' => $request->type,
            'path' => $path,
            'url' => $url,
            'folder_id' => $folder->id,
            'user_id' => Auth::id(),
        ]);

        return redirect()->route('folders.index', $folder)->with('success', 'Elemento añadido exitosamente.');
    }

    /**
     * Show the form for editing the specified file or link.
     * Muestra el formulario para editar el archivo o enlace especificado.
     *
     * @param  \App\Models\FileLink  $fileLink
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function editFileLink(FileLink $fileLink)
    {
        $user = Auth::user();

        // Verificar permisos para editar
        if ($user->area && $user->area->name === 'Administración') {
            // Super Admin puede editar
        } elseif ($user->is_area_admin && $fileLink->folder->area_id === $user->area_id) {
            // Admin de Área puede editar
        } else {
            return redirect()->route('folders.index', $fileLink->folder)->with('error', 'No tienes permiso para editar este elemento.');
        }

        return view('file_links.edit', compact('fileLink'));
    }

    /**
     * Update the specified file or link in storage.
     * Actualiza el archivo o enlace especificado en la base de datos.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\FileLink  $fileLink
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateFileLink(Request $request, FileLink $fileLink)
    {
        $user = Auth::user();

        // Verificar permisos para actualizar
        if ($user->area && $user->area->name === 'Administración') {
            // Super Admin puede actualizar
        } elseif ($user->is_area_admin && $fileLink->folder->area_id === $user->area_id) {
            // Admin de Área puede actualizar
        } else {
            return redirect()->route('folders.index', $fileLink->folder)->with('error', 'No tienes permiso para actualizar este elemento.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'nullable|url|max:2048',
        ]);

        if ($fileLink->type === 'link') {
            $fileLink->update([
                'name' => $request->name,
                'url' => $request->url,
            ]);
        } else {
            $fileLink->update([
                'name' => $request->name,
            ]);
        }

        return redirect()->route('folders.index', $fileLink->folder)->with('success', 'Elemento actualizado exitosamente.');
    }

    /**
     * Remove the specified file or link from storage.
     * Elimina el archivo o enlace especificado de la base de datos y del almacenamiento si es un archivo.
     *
     * @param  \App\Models\FileLink  $fileLink
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyFileLink(FileLink $fileLink)
    {
        $user = Auth::user();

        // Verificar permisos para eliminar
        if ($user->area && $user->area->name === 'Administración') {
            // Super Admin puede eliminar
        } elseif ($user->is_area_admin && $fileLink->folder->area_id === $user->area_id) {
            // Admin de Área puede eliminar
        } else {
            return redirect()->route('folders.index', $fileLink->folder)->with('error', 'No tienes permiso para eliminar este elemento.');
        }

        if ($fileLink->type === 'file' && $fileLink->path) {
            Storage::disk('public')->delete($fileLink->path);
        }

        $folder = $fileLink->folder;
        $fileLink->delete();

        return redirect()->route('folders.index', $folder)->with('success', 'Elemento eliminado exitosamente.');
    }
}
