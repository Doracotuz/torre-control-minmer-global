<?php

namespace App\Http\Controllers;

use App\Models\Folder;
use App\Models\Area;
use App\Models\FileLink; // Importa el modelo FileLink
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage; // Importa el facade Storage

class FolderController extends Controller
{
    /**
     * Display a listing of the folders.
     * Muestra una lista de las carpetas.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Folder|null  $folder
     * @return \Illuminate\View\View
     */
    public function index(Request $request, Folder $folder = null)
    {
        $user = Auth::user();
        $currentFolder = $folder; // La carpeta actual que estamos viendo (null si estamos en la raíz)

        // Obtener las carpetas del nivel actual para el área del usuario autenticado
        // Si hay una carpeta actual, sus hijos; si no, las carpetas de nivel superior (parent_id is null)
        $folders = Folder::where('area_id', $user->area_id)
                         ->where('parent_id', $currentFolder ? $currentFolder->id : null)
                         ->orderBy('name')
                         ->get();

        // Obtener los archivos/enlaces de la carpeta actual
        $fileLinks = $currentFolder ? $currentFolder->fileLinks()->orderBy('name')->get() : collect();

        return view('folders.index', compact('folders', 'currentFolder', 'fileLinks'));
    }

    /**
     * Show the form for creating a new folder.
     * Muestra el formulario para crear una nueva carpeta.
     *
     * @param  \App\Models\Folder|null  $folder
     * @return \Illuminate\View\View
     */
    public function create(Folder $folder = null)
    {
        $currentFolder = $folder; // La carpeta padre donde se creará la nueva carpeta
        $userArea = Auth::user()->area; // El área del usuario autenticado

        return view('folders.create', compact('currentFolder', 'userArea'));
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

        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                // Validación de unicidad: el nombre debe ser único para el mismo parent_id y area_id
                Rule::unique('folders')->where(function ($query) use ($request, $user) {
                    return $query->where('parent_id', $request->parent_id)
                                 ->where('area_id', $user->area_id);
                }),
            ],
            'parent_id' => 'nullable|exists:folders,id',
        ]);

        Folder::create([
            'name' => $request->name,
            'parent_id' => $request->parent_id,
            'area_id' => $user->area_id, // Asigna el área del usuario autenticado
            'user_id' => $user->id,      // Asigna el usuario que la creó
        ]);

        // Redirigir a folders.index con el parent_id (si existe) o a la raíz
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
        // Asegurarse de que el usuario tiene permiso para ver esta carpeta (pertenece a su área)
        if (Auth::user()->area_id !== $folder->area_id) {
            return redirect()->route('dashboard')->with('error', 'No tienes permiso para ver esta carpeta.');
        }

        // Reutilizamos la lógica de index para mostrar el contenido de una carpeta específica
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
        // Asegurarse de que el usuario tiene permiso para editar esta carpeta (pertenece a su área)
        if (Auth::user()->area_id !== $folder->area_id) {
            return redirect()->route('dashboard')->with('error', 'No tienes permiso para editar esta carpeta.');
        }

        $userArea = Auth::user()->area; // El área del usuario autenticado
        return view('folders.edit', compact('folder', 'userArea'));
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
        // Asegurarse de que el usuario tiene permiso para actualizar esta carpeta
        if (Auth::user()->area_id !== $folder->area_id) {
            return redirect()->route('dashboard')->with('error', 'No tienes permiso para actualizar esta carpeta.');
        }

        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                // Validación de unicidad: el nombre debe ser único para el mismo parent_id y area_id, excluyendo la carpeta actual
                Rule::unique('folders')->where(function ($query) use ($request, $folder) {
                    return $query->where('parent_id', $folder->parent_id)
                                 ->where('area_id', $folder->area_id);
                })->ignore($folder->id),
            ],
        ]);

        $folder->update([
            'name' => $request->name,
        ]);

        // Redirigir a folders.index con el parent_id (si existe) o a la raíz
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
        // Asegurarse de que el usuario tiene permiso para eliminar esta carpeta
        if (Auth::user()->area_id !== $folder->area_id) {
            return redirect()->route('dashboard')->with('error', 'No tienes permiso para eliminar esta carpeta.');
        }

        $parentFolderId = $folder->parent_id;
        $folder->delete(); // Esto también eliminará subcarpetas y file_links debido a onDelete('cascade')

        // Redirigir a folders.index con el parent_id (si existe) o a la raíz
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
        // Asegurarse de que el usuario tiene permiso para añadir a esta carpeta
        if (Auth::user()->area_id !== $folder->area_id) {
            return redirect()->route('dashboard')->with('error', 'No tienes permiso para añadir elementos a esta carpeta.');
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
        // Asegurarse de que el usuario tiene permiso para añadir a esta carpeta
        if (Auth::user()->area_id !== $folder->area_id) {
            return redirect()->route('dashboard')->with('error', 'No tienes permiso para añadir elementos a esta carpeta.');
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
        // Asegurarse de que el usuario tiene permiso para editar este elemento
        if (Auth::user()->area_id !== $fileLink->folder->area_id) {
            return redirect()->route('dashboard')->with('error', 'No tienes permiso para editar este elemento.');
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
        // Asegurarse de que el usuario tiene permiso para actualizar este elemento
        if (Auth::user()->area_id !== $fileLink->folder->area_id) {
            return redirect()->route('dashboard')->with('error', 'No tienes permiso para actualizar este elemento.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'nullable|url|max:2048', // Solo se puede cambiar la URL si es un enlace
        ]);

        // Solo permitir cambiar la URL si es un enlace existente
        if ($fileLink->type === 'link') {
            $fileLink->update([
                'name' => $request->name,
                'url' => $request->url,
            ]);
        } else {
            // Para archivos, solo se permite cambiar el nombre
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
        // Asegurarse de que el usuario tiene permiso para eliminar este elemento
        if (Auth::user()->area_id !== $fileLink->folder->area_id) {
            return redirect()->route('dashboard')->with('error', 'No tienes permiso para eliminar este elemento.');
        }

        // Si es un archivo, eliminarlo del almacenamiento
        if ($fileLink->type === 'file' && $fileLink->path) {
            Storage::disk('public')->delete($fileLink->path);
        }

        $folder = $fileLink->folder;
        $fileLink->delete();

        return redirect()->route('folders.index', $folder)->with('success', 'Elemento eliminado exitosamente.');
    }
}
