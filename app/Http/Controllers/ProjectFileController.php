<?php
namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProjectFileController extends Controller
{
    public function store(Request $request, Project $project)
    {
        $request->validate(['file' => 'required|file|max:10240']);

        $file = $request->file('file');

        $path = $file->store('project_files/' . $project->id, 's3');

        $project->files()->create([
            'user_id' => Auth::id(),
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_size' => $file->getSize(),
        ]);

        $project->history()->create([
            'user_id' => Auth::id(),
            'action_type' => 'file_upload',
            'comment_body' => 'Subió el archivo: ' . $file->getClientOriginalName(),
        ]);

        return back()->with('success_file', '¡Archivo subido a S3 exitosamente!');
    }

    public function download(ProjectFile $file)
    {
        $temporaryUrl = Storage::disk('s3')->temporaryUrl(
            $file->file_path,
            now()->addMinutes(10),
            [
                'ResponseContentDisposition' => 'attachment; filename="' . $file->file_name . '"'
            ]
        );

        return redirect($temporaryUrl);
    }
}