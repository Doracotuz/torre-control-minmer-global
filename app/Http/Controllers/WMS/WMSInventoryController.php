<?php

namespace App\Http\Controllers\WMS;

use App\Http\Controllers\Controller;
use App\Models\WMS\InventoryStock;
use App\Models\WMS\InventoryTransfer;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class WMSInventoryController extends Controller
{
    public function index(Request $request)
    {
        $query = InventoryStock::with(['product', 'location'])
            ->where('quantity', '>', 0);

        // Lógica de búsqueda
        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->whereHas('product', function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('sku', 'like', "%{$searchTerm}%");
            });
        }

        $stock = $query->latest()->paginate(20)->withQueryString();

        return view('wms.inventory.index', compact('stock'));
    }

    public function createTransfer()
    {
        // Pasamos todo el stock y las ubicaciones a la vista para que Alpine.js los maneje
        $stockData = InventoryStock::with(['product', 'location'])
            ->where('quantity', '>', 0)
            ->get();
        $locations = Location::orderBy('code')->get();
        return view('wms.inventory.create-transfer', compact('stockData', 'locations'));
    }

    public function storeTransfer(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'from_location_id' => 'required|exists:locations,id',
            'to_location_id' => 'required|exists:locations,id|different:from_location_id',
            'quantity' => 'required|integer|min:1',
        ]);

        // Validación personalizada para asegurar que hay suficiente stock
        $request->validate([
            'quantity' => [ Rule::exists('inventory_stocks')->where(function ($query) use ($validated) {
                $query->where('product_id', $validated['product_id'])
                      ->where('location_id', $validated['from_location_id'])
                      ->where('quantity', '>=', $validated['quantity']);
            })],
        ], ['quantity.exists' => 'La cantidad a transferir excede el stock disponible en la ubicación de origen.']);

        DB::beginTransaction();
        try {
            // 1. Decrementar stock de la ubicación de origen
            $sourceStock = InventoryStock::where('product_id', $validated['product_id'])
                                         ->where('location_id', $validated['from_location_id'])
                                         ->first();
            $sourceStock->decrement('quantity', $validated['quantity']);

            // 2. Incrementar stock en la ubicación de destino
            $destinationStock = InventoryStock::firstOrCreate(
                ['product_id' => $validated['product_id'], 'location_id' => $validated['to_location_id']],
                ['quantity' => 0]
            );
            $destinationStock->increment('quantity', $validated['quantity']);

            // 3. Registrar la transacción de transferencia
            InventoryTransfer::create(array_merge($validated, ['user_id' => Auth::id()]));

            DB::commit();
            return redirect()->route('wms.inventory.index')
                             ->with('success', 'Transferencia realizada exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al procesar la transferencia: ' . $e->getMessage())->withInput();
        }
    }    
}