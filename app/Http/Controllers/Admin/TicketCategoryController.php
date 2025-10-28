<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TicketCategory;
use App\Models\TicketSubCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TicketCategoryController extends Controller
{
    public function index()
    {
        $categories = TicketCategory::with(['subCategories' => function ($query) {
            $query->withCount('tickets')->orderBy('name');
        }])->orderBy('name')->get();

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
        return back()->with('success', 'Categoría y sus subcategorías eliminadas exitosamente.');
    }


    public function storeSubCategory(Request $request)
    {
        $request->validate([
            'ticket_category_id' => 'required|exists:ticket_categories,id',
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('ticket_sub_categories')->where(function ($query) use ($request) {
                    return $query->where('ticket_category_id', $request->ticket_category_id);
                }),
            ],
        ]);

        TicketSubCategory::create($request->all());
        return back()->with('success', 'Subcategoría añadida exitosamente.');
    }

    public function updateSubCategory(Request $request, TicketSubCategory $subCategory)
    {
        $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('ticket_sub_categories')->where(function ($query) use ($subCategory) {
                    return $query->where('ticket_category_id', $subCategory->ticket_category_id);
                })->ignore($subCategory->id),
            ],
        ]);

        $subCategory->update($request->all());
        return back()->with('success', 'Subcategoría actualizada exitosamente.');
    }

    public function destroySubCategory(TicketSubCategory $subCategory)
    {
        $subCategory->delete();
        return back()->with('success', 'Subcategoría eliminada exitosamente.');
    }
}
