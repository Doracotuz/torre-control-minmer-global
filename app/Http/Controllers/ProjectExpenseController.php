<?php
namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectExpenseController extends Controller
{
    public function store(Request $request, Project $project)
    {
        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
        ]);

        $expense = $project->expenses()->create([
            'user_id' => Auth::id(),
            'description' => $validated['description'],
            'amount' => $validated['amount'],
            'expense_date' => $validated['expense_date'],
        ]);

        $project->history()->create([
            'user_id' => Auth::id(),
            'action_type' => 'expense_added',
            'comment_body' => "Registró un gasto: \"{$expense->description}\" por $".number_format($expense->amount, 2),
        ]);

        return back()->with('success_expense', '¡Gasto registrado exitosamente!');
    }
}