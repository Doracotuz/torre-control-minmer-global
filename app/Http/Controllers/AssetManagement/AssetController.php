<?php

namespace App\Http\Controllers\AssetManagement;

use App\Http\Controllers\Controller;
use App\Models\HardwareAsset;
use App\Models\HardwareModel;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AssetController extends Controller
{
    /**
     * Muestra el dashboard principal con KPIs y una lista paginada de activos.
     */
    public function index(Request $request)
    {
        $query = HardwareAsset::with(['model.category', 'model.manufacturer', 'site', 'currentAssignment.member']);

        if ($request->filled('search')) {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->where('asset_tag', 'like', $searchTerm)
                  ->orWhere('serial_number', 'like', 'searchTerm')
                  ->orWhereHas('model', fn($modelQuery) => $modelQuery->where('name', 'like', $searchTerm))
                  ->orWhereHas('currentAssignment.member', fn($memberQuery) => $memberQuery->where('name', 'like', $searchTerm));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('site_id')) {
            $query->where('site_id', $request->site_id);
        }

        if ($request->filled('category_id')) {
            $query->whereHas('model.category', function ($q) use ($request) {
                $q->where('id', $request->category_id);
            });
        }

        $assets = $query->latest()->paginate(15)->withQueryString();
        
        $stats = [
            'total' => HardwareAsset::count(),
            'assigned' => HardwareAsset::where('status', 'Asignado')->count(),
            'in_stock' => HardwareAsset::where('status', 'En Almacén')->count(),
            'in_repair' => HardwareAsset::where('status', 'En Reparación')->count(),
        ];
        
        
        $sites = \App\Models\Site::orderBy('name')->get();
        $categories = \App\Models\HardwareCategory::orderBy('name')->get();
        $statuses = \App\Models\HardwareAsset::query()
            ->select('status')
            ->whereNotNull('status')
            ->where('status', '!=', '')
            ->distinct()
            ->pluck('status');

        return view('asset-management.index', [
            'assets' => $assets,
            'stats' => $stats,
            'filters' => $request->all(),
            'sites' => $sites,
            'categories' => $categories,
            'statuses' => $statuses
        ]);
    }

    /**
     * Muestra el formulario para crear un nuevo activo.
     */
    public function create()
    {
        $sites = Site::orderBy('name')->get();
        $models = HardwareModel::with('category')->orderBy('name')->get();
        $groupedModels = $models->groupBy('category.name');

        return view('asset-management.assets.create', compact('sites', 'groupedModels'));
    }

    /**
     * Almacena un nuevo activo en la base de datos.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'asset_tag' => 'required|string|max:255|unique:hardware_assets,asset_tag',
            'serial_number' => 'required|string|max:255|unique:hardware_assets,serial_number',
            'hardware_model_id' => 'required|exists:hardware_models,id',
            'site_id' => 'required|exists:sites,id',
            'status' => 'required|in:En Almacén,Asignado,En Reparación,Prestado,De Baja',
            'purchase_date' => 'nullable|date',
            'warranty_end_date' => 'nullable|date',
            'cpu' => 'nullable|string|max:255',
            'ram' => 'nullable|string|max:255',
            'storage' => 'nullable|string|max:255',
            'mac_address' => ['nullable', 'string', 'max:17', Rule::unique('hardware_assets')->ignore($request->id)],
            'phone_plan_type' => 'nullable|in:Prepago,Plan',
            'phone_number' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
        ]);

        HardwareAsset::create($validated);

        return redirect()->route('asset-management.dashboard')->with('success', 'Activo registrado exitosamente.');
    }

    /**
     * Muestra la vista detallada de un activo específico.
     */
    public function show(HardwareAsset $asset)
    {
        $asset->load(['model.category', 'model.manufacturer', 'site', 'assignments.member']);
        return view('asset-management.assets.show', compact('asset'));
    }

    /**
     * Muestra el formulario para editar un activo existente.
     */
    public function edit(HardwareAsset $asset)
    {
        $sites = Site::orderBy('name')->get();
        $models = HardwareModel::with('category')->orderBy('name')->get();
        $groupedModels = $models->groupBy('category.name');
        
        // Obtener la categoría actual del modelo para la lógica de Alpine.js
        $asset->load('model.category');
        $currentCategoryName = $asset->model->category->name ?? null;

        return view('asset-management.assets.edit', compact('asset', 'sites', 'groupedModels', 'currentCategoryName'));
    }

    /**
     * Actualiza un activo en la base de datos.
     */
    public function update(Request $request, HardwareAsset $asset)
    {
        $validated = $request->validate([
            'asset_tag' => ['required', 'string', 'max:255', Rule::unique('hardware_assets')->ignore($asset->id)],
            'serial_number' => ['required', 'string', 'max:255', Rule::unique('hardware_assets')->ignore($asset->id)],
            'hardware_model_id' => 'required|exists:hardware_models,id',
            'site_id' => 'required|exists:sites,id',
            'status' => 'required|in:En Almacén,Asignado,En Reparación,Prestado,De Baja',
            'purchase_date' => 'nullable|date',
            'warranty_end_date' => 'nullable|date',
            'cpu' => 'nullable|string|max:255',
            'ram' => 'nullable|string|max:255',
            'storage' => 'nullable|string|max:255',
            'mac_address' => ['nullable', 'string', 'max:17', Rule::unique('hardware_assets')->ignore($asset->id)],
            'phone_plan_type' => 'nullable|in:Prepago,Plan',
            'phone_number' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
        ]);

        $asset->update($validated);

        return redirect()->route('asset-management.assets.show', $asset)->with('success', 'Activo actualizado correctamente.');
    }

    /**
     * Elimina un activo de la base de datos (Baja Lógica).
     * Nota: Una mejor práctica es cambiar el estado a "De Baja" en lugar de eliminarlo.
     */
    public function destroy(HardwareAsset $asset)
    {
        // Prevenir la eliminación si el activo está actualmente asignado
        if ($asset->status === 'Asignado' || $asset->status === 'Prestado') {
            return back()->with('error', 'No se puede eliminar un activo que está actualmente asignado.');
        }

        $asset->delete();

        return redirect()->route('asset-management.dashboard')->with('success', 'Activo eliminado exitosamente.');
    }
}