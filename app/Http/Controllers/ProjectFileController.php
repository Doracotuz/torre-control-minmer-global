<?php
namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProjectFileController extends Controller
{
    /**
     * Guarda un nuevo archivo en el bucket de Amazon S3.
     */
    public function store(Request $request, Project $project)
    {
        $request->validate(['file' => 'required|file|max:10240']); // max 10MB

        $file = $request->file('file');

        // --- CAMBIO CLAVE ---
        // Usamos el disco 's3' y guardamos en una carpeta específica para el proyecto.
        $path = $file->store('project_files/' . $project->id, 's3');

        $project->files()->create([
            'user_id' => Auth::id(),
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path, // Guardamos la ruta relativa dentro del bucket
            'file_size' => $file->getSize(),
        ]);

        return back()->with('success_file', '¡Archivo subido a S3 exitosamente!');
    }

    /**
     * Genera una URL temporal y segura para descargar un archivo desde S3.
     */
    public function download(ProjectFile $file)
    {
        // --- CAMBIO CLAVE ---
        // En lugar de una descarga directa, generamos una URL temporalmente válida.
        // Esto es más seguro y eficiente para archivos privados en S3.
        // El archivo no pasa por nuestro servidor, el usuario lo descarga directamente de S3.

        $temporaryUrl = Storage::disk('s3')->temporaryUrl(
            $file->file_path,
            now()->addMinutes(10), // La URL será válida por 10 minutos
            [
                'ResponseContentDisposition' => 'attachment; filename="' . $file->file_name . '"'
            ]
        );

        return redirect($temporaryUrl);
    }
}