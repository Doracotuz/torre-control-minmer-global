<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectCommentController extends Controller
{
    public function store(Request $request, Project $project)
    {
        $request->validate(['body' => 'required|string']);

        // 1. Crea el comentario (como ya lo hacías)
        $project->comments()->create([
            'user_id' => Auth::id(),
            'body' => $request->body,
        ]);

        // 2. REGISTRA EN EL HISTORIAL CENTRAL
        $project->history()->create([
            'user_id' => Auth::id(),
            'action_type' => 'comment',
            'comment_body' => $request->body,
        ]);

        return back()->with('success_comment', '¡Comentario añadido exitosamente!');
    }
}