<?php

namespace App\Http\Controllers;

use App\Models\FileLink; // Importar el modelo FileLink
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Para acceder al usuario autenticado
use Illuminate\Support\Facades\Storage; // Para manejar la eliminación de archivos físicos
use Illuminate\Support\Str; // Para usar funciones de cadena como Str::endsWith
use Symfony\Component\HttpFoundation\StreamedResponse; // Necesario para la descarga de archivos
use App\Models\ActivityLog;

class FileLinkController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Este método no se usará directamente ya que la lista se muestra en FolderController@index
        // Puedes redirigir o mostrar un error si se accede directamente
        return redirect()->route('folders.index')->with('error', 'Acceso no permitido.');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Este método no se usará directamente ya que la creación se maneja en FolderController@createFileLink
        // Puedes redirigir o mostrar un error si se accede directamente
        return redirect()->route('folders.index')->with('error', 'Acceso no permitido.');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Este método no se usará directamente ya que el almacenamiento se maneja en FolderController@storeFileLink
        // Puedes redirigir o mostrar un error si se accede directamente
        return redirect()->route('folders.index')->with('error', 'Acceso no permitido.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\FileLink  $fileLink
     * @return \Illuminate\Http\Response
     */
    public function show(FileLink $fileLink)
    {
        // Este método se podría usar para ver detalles de un FileLink, pero por ahora no está implementado
        // y no es estrictamente necesario si ya tienes el modal de propiedades.
        return redirect()->route('folders.index', $fileLink->folder_id)->with('error', 'Función de vista de elemento no implementada.');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\FileLink  $fileLink
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function edit(FileLink $fileLink)
    {
        $user = Auth::user();

        // Lógica de permisos para editar un FileLink
        if ($user->area && $user->area->name === 'Administración') {
            // Super Admin puede editar cualquier elemento
        } elseif ($user->is_area_admin && $fileLink->folder->area_id === $user->area_id) {
            // Admin de Área puede editar elementos en su área
        } else {
            return redirect()->route('folders.index', $fileLink->folder_id)
                             ->with('error', 'No tienes permiso para editar este elemento.');
        }

        return view('file_links.edit', compact('fileLink'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\FileLink  $fileLink
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, FileLink $fileLink)
    {
        $user = Auth::user();

        // Lógica de permisos para actualizar un FileLink
        if ($user->area && $user->area->name === 'Administración') {
            // Super Admin puede actualizar cualquier elemento
        } elseif ($user->is_area_admin && $fileLink->folder->area_id === $user->area_id) {
            // Admin de Área puede actualizar elementos en su área
        } else {
            return redirect()->route('folders.index', $fileLink->folder_id)
                             ->with('error', 'No tienes permiso para actualizar este elemento.');
        }

        $validationRules = [
            'name' => 'required|string|max:255',
            'url' => 'nullable|url|max:2048', // La URL es nullable para archivos
        ];

        // Si el tipo es 'link', la 'url' es requerida
        if ($fileLink->type === 'link') {
            $validationRules['url'] = 'required|url|max:2048';
        }

        $request->validate($validationRules);

        // Actualizar solo el nombre y la URL (si aplica)
        $fileLink->name = $request->name;
        if ($fileLink->type === 'link') {
            $fileLink->url = $request->url;
        }
        $fileLink->save();
        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'Editó un archivo/enlace',
            'item_type' => 'file_link',
            'item_id' => $fileLink->id,
            'details' => ['old_name' => $fileLink->getOriginal('name'), 'new_name' => $fileLink->name],
        ]);        

        return redirect()->route('folders.index', $fileLink->folder_id)
                         ->with('success', 'Elemento actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\FileLink  $fileLink
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(FileLink $fileLink)
    {
        $user = Auth::user();

        // Logic for deleting a FileLink
        if ($user->area && $user->area->name === 'Administración') {
            // Super Admin can delete any element
        } elseif ($user->is_area_admin && $fileLink->folder->area_id === $user->area_id) {
            // Area Admin can delete elements in their area
        } else {
            // Normal User and Client do not have permission
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para eliminar este elemento.'
            ], 403); // Use a 403 Forbidden status code
        }

        $folderId = $fileLink->folder_id; // Save the folder ID before deleting

        // If it's a file, delete the physical file from storage
        if ($fileLink->type === 'file' && Storage::disk('s3')->exists($fileLink->path)) {
            Storage::disk('s3')->delete($fileLink->path);
        }

        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'Eliminó un archivo/enlace',
            'item_type' => 'file_link',
            'item_id' => $fileLink->id,
            'details' => ['name' => $fileLink->name],
        ]);

        $fileLink->delete(); // Delete the record from the database

        // Return a JSON response for a successful deletion
        return response()->json([
            'success' => true,
            'message' => 'Elemento eliminado exitosamente.'
        ]);
    }

    /**
     * Descarga un archivo desde S3.
     *
     * @param  \App\Models\FileLink  $fileLink
     * @return \Symfony\Component\HttpFoundation\StreamedResponse|\Illuminate\Http\RedirectResponse
     */
    public function download(FileLink $fileLink)
    {
        $user = Auth::user();

        // 1. Verificar si es realmente un archivo y si existe el path
        if ($fileLink->type !== 'file' || !$fileLink->path) {
            return back()->with('error', 'No es un archivo descargable o la ruta no es válida.');
        }

        // 2. Lógica de Permisos para descargar el archivo
        $hasPermission = false;
        if ($user->area && $user->area->name === 'Administración') {
            // Super Admin puede descargar cualquier archivo
            $hasPermission = true;
        } elseif ($user->is_area_admin && $fileLink->folder->area_id === $user->area_id) {
            // Administrador de Área puede descargar archivos de su área
            $hasPermission = true;
        } elseif ($user->isClient()) {
            // Cliente solo puede descargar si la carpeta es accesible para él
            if ($user->accessibleFolders->contains($fileLink->folder_id)) {
                $hasPermission = true;
            }
        } elseif ($fileLink->folder->area_id === $user->area_id && $user->accessibleFolders->contains($fileLink->folder_id)) {
            // Usuario Normal: archivo de su área y con acceso explícito a la carpeta
            $hasPermission = true;
        }

        if (!$hasPermission) {
            return back()->with('error', 'No tienes permiso para descargar este archivo.');
        }

        // 3. Verificar si el archivo existe en S3
        if (!Storage::disk('s3')->exists($fileLink->path)) {
            return back()->with('error', 'El archivo no se encuentra en el almacenamiento.');
        }
        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'Descargó un archivo',
            'item_type' => 'file_link',
            'item_id' => $fileLink->id,
            'details' => ['name' => $fileLink->name],
        ]);        
        // 4. Descargar el archivo desde S3
        // El segundo argumento es el nombre deseado del archivo al descargar
        return Storage::disk('s3')->download($fileLink->path, $fileLink->name);
    }
}