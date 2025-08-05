<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TicketCategory;
use Illuminate\Http\Request;

class TicketCategoryController extends Controller
{
    public function index()
    {

        $categories = TicketCategory::withCount('tickets')->latest()->paginate(10);
        return view('admin.ticket-categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.ticket-categories.create');
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255|unique:ticket_categories,name']);
        TicketCategory::create($request->all());
        return redirect()->route('admin.ticket-categories.index')->with('success', 'Categoría creada exitosamente.');
    }

    public function edit(TicketCategory $ticketCategory)
    {
        return view('admin.ticket-categories.edit', compact('ticketCategory'));
    }

    public function update(Request $request, TicketCategory $ticketCategory)
    {
        $request->validate(['name' => 'required|string|max:255|unique:ticket_categories,name,' . $ticketCategory->id]);
        $ticketCategory->update($request->all());
        return redirect()->route('admin.ticket-categories.index')->with('success', 'Categoría actualizada exitosamente.');
    }

    public function destroy(TicketCategory $ticketCategory)
    {
        $ticketCategory->delete();
        return back()->with('success', 'Categoría eliminada exitosamente.');
    }
}