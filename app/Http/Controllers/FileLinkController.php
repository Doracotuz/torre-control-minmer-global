<?php

namespace App\Http\Controllers;

use App\Models\FileLink; // Importar el modelo FileLink
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Para acceder al usuario autenticado
use Illuminate\Support\Facades\Storage; // Para manejar la eliminación de archivos físicos
use Illuminate\Support\Str; // Para usar funciones de cadena como Str::endsWith

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

        // Lógica de permisos para eliminar un FileLink
        if ($user->area && $user->area->name === 'Administración') {
            // Super Admin puede eliminar cualquier elemento
        } elseif ($user->is_area_admin && $fileLink->folder->area_id === $user->area_id) {
            // Admin de Área puede eliminar elementos en su área
        } else {
            return redirect()->route('folders.index', $fileLink->folder_id)
                             ->with('error', 'No tienes permiso para eliminar este elemento.');
        }

        $folderId = $fileLink->folder_id; // Guarda el ID de la carpeta antes de eliminar el fileLink

        // Si es un archivo, elimina el archivo físico del almacenamiento
        // CAMBIO PARA S3: Usar 's3' disk para verificar existencia y eliminar
        if ($fileLink->type === 'file' && Storage::disk('s3')->exists($fileLink->path)) {
            Storage::disk('s3')->delete($fileLink->path);
        }

        $fileLink->delete(); // Elimina el registro de la base de datos

        return redirect()->route('folders.index', $folderId)
                         ->with('success', 'Elemento eliminado exitosamente.');
    }
}