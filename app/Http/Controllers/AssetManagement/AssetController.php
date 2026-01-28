<?php

namespace App\Http\Controllers\AssetManagement;

use App\Http\Controllers\Controller;
use App\Models\HardwareAsset;
use App\Models\HardwareModel;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\HardwareCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use League\Csv\Writer;
use Carbon\Carbon;

class AssetController extends Controller
{
    public function index(Request $request)
    {
        $query = HardwareAsset::with(['model.category', 'model.manufacturer', 'site', 'currentAssignments']);

        if ($request->filled('search')) {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->where('asset_tag', 'like', $searchTerm)
                ->orWhere('serial_number', 'like', $searchTerm)
                ->orWhereHas('model', fn($modelQuery) => $modelQuery->where('name', 'like', $searchTerm))
                ->orWhereHas('currentAssignments.member', fn($memberQuery) => $memberQuery->where('name', 'like', $searchTerm));
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

        $balanceData = DB::table('hardware_assets')
            ->join('hardware_models', 'hardware_assets.hardware_model_id', '=', 'hardware_models.id')
            ->join('hardware_categories', 'hardware_models.hardware_category_id', '=', 'hardware_categories.id')
            ->select('hardware_categories.name as category_name', 'hardware_assets.status', DB::raw('count(*) as count'))
            ->groupBy('hardware_categories.name', 'hardware_assets.status')
            ->orderBy('category_name')
            ->get();

        $assetBalance = collect($balanceData)->groupBy('category_name')->map(function ($group) {
            $total = $group->sum('count');
            $inStock = $group->where('status', 'En Almacén')->sum('count');
            
            return [
                'total' => $total,
                'utilizados' => $total - $inStock,
                'restantes' => $inStock,
                'breakdown' => $group->pluck('count', 'status'),
            ];
        });        

        return view('asset-management.index', [
            'assets' => $assets,
            'stats' => $stats,
            'filters' => $request->all(),
            'sites' => $sites,
            'categories' => $categories,
            'statuses' => $statuses,
            'assetBalance' => $assetBalance, 
        ]);
    }

    public function create()
    {
        $sites = Site::orderBy('name')->get();
        $models = HardwareModel::with('category')->orderBy('name')->get();
        $groupedModels = $models->groupBy('category.name');

        return view('asset-management.assets.create', compact('sites', 'groupedModels'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'asset_tag' => 'required|string|max:255|unique:hardware_assets,asset_tag',
            'serial_number' => 'required|string|max:255|unique:hardware_assets,serial_number',
            'hardware_model_id' => 'required|exists:hardware_models,id',
            'site_id' => 'required|exists:sites,id',
            'status' => 'required|in:En Almacén,Asignado,En Reparación,Prestado,De Baja,En Mantenimiento,En espera de reparación',
            'purchase_date' => 'nullable|date',
            'warranty_end_date' => 'nullable|date',
            'cpu' => 'nullable|string|max:255',
            'ram' => 'nullable|string|max:255',
            'storage' => 'nullable|string|max:255',
            'mac_address' => ['nullable', 'string', 'max:17', Rule::unique('hardware_assets')->ignore($request->id)],
            'phone_plan_type' => 'nullable|in:Prepago,Plan',
            'phone_number' => 'nullable|string|max:20',
                'notes' => 'nullable|string',
            'photo_1' => 'nullable|image|max:10048',
            'photo_2' => 'nullable|image|max:10048',
            'photo_3' => 'nullable|image|max:10048',            
        ]);

        for ($i = 1; $i <= 3; $i++) {
            if ($request->hasFile("photo_{$i}")) {
                $validated["photo_{$i}_path"] = $request->file("photo_{$i}")->store('assets/photos', 's3');
            }
        }

        $asset = HardwareAsset::create($validated);

        $creationTime = $asset->created_at;
        $eventDate = $asset->purchase_date 
                        ? \Carbon\Carbon::parse($asset->purchase_date)->setTime($creationTime->hour, $creationTime->minute, $creationTime->second)
                        : $creationTime;

        $asset->logs()->create([
            'user_id' => Auth::id(),
            'action_type' => 'Creación',
            'notes' => 'El activo fue registrado en el sistema.',
            'event_date' => $eventDate,
        ]);        

        return redirect()->route('asset-management.dashboard')->with('success', 'Activo registrado exitosamente.');
    }

    public function show(HardwareAsset $asset)
    {
        $asset->load([
            'model.category', 
            'model.manufacturer', 
            'site', 
            'currentAssignments.member.userResponsivas',
            'assignments.member',
            'logs.user',
            'softwareAssignments.license'
        ]);
        
        $userResponsivas = collect();

        return view('asset-management.assets.show', compact('asset', 'userResponsivas'));
    }


    public function edit(HardwareAsset $asset)
    {
        $sites = Site::orderBy('name')->get();
        $models = HardwareModel::with('category')->orderBy('name')->get();
        $groupedModels = $models->groupBy('category.name');
        
        $asset->load('model.category');
        $currentCategoryName = $asset->model->category->name ?? null;

        return view('asset-management.assets.edit', compact('asset', 'sites', 'groupedModels', 'currentCategoryName'));
    }

    public function update(Request $request, HardwareAsset $asset)
    {
        $validated = $request->validate([
            'asset_tag' => ['required', 'string', 'max:255', Rule::unique('hardware_assets')->ignore($asset->id)],
            'serial_number' => ['required', 'string', 'max:255', Rule::unique('hardware_assets')->ignore($asset->id)],
            'hardware_model_id' => 'required|exists:hardware_models,id',
            'site_id' => 'required|exists:sites,id',
            'status' => 'required|in:En Almacén,Asignado,En Reparación,Prestado,De Baja,En Mantenimiento,En espera de reparación',
            'purchase_date' => 'nullable|date',
            'warranty_end_date' => 'nullable|date',
            'cpu' => 'nullable|string|max:255',
            'ram' => 'nullable|string|max:255',
            'storage' => 'nullable|string|max:255',
            'mac_address' => ['nullable', 'string', 'max:17', Rule::unique('hardware_assets')->ignore($asset->id)],
            'phone_plan_type' => 'nullable|in:Prepago,Plan',
            'phone_number' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
            'photo_1' => 'nullable|image|max:10048',
            'photo_2' => 'nullable|image|max:10048',
            'photo_3' => 'nullable|image|max:10048',
        ]);

        $originalStatus = $asset->status;
        $originalPurchaseDate = $asset->purchase_date ? \Carbon\Carbon::parse($asset->purchase_date)->format('Y-m-d') : null;

        for ($i = 1; $i <= 3; $i++) {
            $fileInputName = "photo_{$i}";
            $dbColumnName = "photo_{$i}_path";
            $removeInputName = "remove_photo_{$i}";

            if ($request->input($removeInputName) === 'true') {
                if ($asset->{$dbColumnName}) {
                    Storage::disk('s3')->delete($asset->{$dbColumnName});
                }
                $validated[$dbColumnName] = null;
            }

            if ($request->hasFile($fileInputName)) {
                if ($asset->{$dbColumnName}) {
                    Storage::disk('s3')->delete($asset->{$dbColumnName});
                }
                
                $validated[$dbColumnName] = $request->file($fileInputName)->store('assets/photos', 's3');
            }
        }

        $asset->update($validated);
        $asset->refresh();

        if ($originalStatus !== $asset->status) {
            $asset->logs()->create([
                'user_id' => Auth::id(),
                'action_type' => 'Cambio de Estatus',
                'notes' => "El estatus cambió de '{$originalStatus}' a '{$asset->status}'.",
                'event_date' => now(),
            ]);
        }

        $newPurchaseDate = $asset->purchase_date ? \Carbon\Carbon::parse($asset->purchase_date)->format('Y-m-d') : null;

        if ($originalPurchaseDate !== $newPurchaseDate) {
            $creationLog = $asset->logs()
                                  ->where('action_type', 'Creación')
                                  ->orderBy('event_date', 'asc') 
                                  ->first();
            
            if ($creationLog) {
                $creationTime = $creationLog->created_at;
                $eventDate = $asset->purchase_date
                                ? \Carbon\Carbon::parse($asset->purchase_date)->setTime($creationTime->hour, $creationTime->minute, $creationTime->second)
                                : $creationTime;
                
                $creationLog->event_date = $eventDate;
                $creationLog->save();
            }
        }

        return redirect()->route('asset-management.assets.show', $asset)->with('success', 'Activo actualizado correctamente.');
    }

    public function destroy(HardwareAsset $asset)
    {
        if (in_array($asset->status, ['Asignado', 'Prestado'])) {
            return back()->with('error', 'No se puede eliminar un activo que está actualmente asignado.');
        }

        $asset->delete();

        return redirect()->route('asset-management.dashboard')->with('success', 'Activo eliminado exitosamente.');
    }

    public function exportCsv()
    {
        $headers = [
            'asset_tag',
            'serial_number',
            'status',
            'model_name',
            'category_name',
            'manufacturer_name',
            'site_name',
            'assigned_to_member_name',
            'assigned_to_member_email',
        ];

        $assets = HardwareAsset::with([
            'model.category', 
            'model.manufacturer', 
            'site', 
            'currentAssignments.member'
        ])->get();

        $csv = Writer::createFromString('');
        $csv->setOutputBOM(Writer::BOM_UTF8);
        $csv->insertOne($headers);

        foreach ($assets as $asset) {
            
            $assignedNames = $asset->currentAssignments->pluck('member.name')->implode(', ');
            $assignedEmails = $asset->currentAssignments->pluck('member.email')->implode(', ');

            $csv->insertOne([
                $asset->asset_tag,
                $asset->serial_number,
                $asset->status,
                $asset->model->name ?? 'N/A',
                $asset->model->category->name ?? 'N/A',
                $asset->model->manufacturer->name ?? 'N/A',
                $asset->site->name ?? 'N/A',
                $assignedNames ?: 'N/A',
                $assignedEmails ?: 'N/A',
            ]);
        }

        $fileName = 'export_inventario_activos_' . Carbon::now()->format('Y-m-d_His') . '.csv';

        return response((string) $csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    public function filter(Request $request)
    {
        $query = HardwareAsset::with(['model.category', 'model.manufacturer', 'site', 'currentAssignments.member']);

        if ($request->filled('search')) {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->where('asset_tag', 'like', $searchTerm)
                ->orWhere('serial_number', 'like', $searchTerm)
                ->orWhereHas('model', fn($modelQuery) => $modelQuery->where('name', 'like', $searchTerm))
                ->orWhereHas('currentAssignments.member', fn($memberQuery) => $memberQuery->where('name', 'like', $searchTerm));
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
        
        return view('asset-management.assets._list', compact('assets'));
    }    

}