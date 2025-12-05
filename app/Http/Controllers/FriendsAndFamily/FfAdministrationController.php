<?php

namespace App\Http\Controllers\FriendsAndFamily;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FfClient;
use App\Models\FfSalesChannel;
use App\Models\FfTransportLine;
use App\Models\FfPaymentCondition;

class FfAdministrationController extends Controller
{
    protected $catalogs = [
        'clients' => [
            'model' => FfClient::class,
            'title' => 'Clientes',
            'icon' => 'fa-users'
        ],
        'channels' => [
            'model' => FfSalesChannel::class,
            'title' => 'Canales de Venta',
            'icon' => 'fa-store'
        ],
        'transport' => [
            'model' => FfTransportLine::class,
            'title' => 'Líneas de Transporte',
            'icon' => 'fa-truck'
        ],
        'payment' => [
            'model' => FfPaymentCondition::class,
            'title' => 'Condiciones de Pago',
            'icon' => 'fa-credit-card'
        ],
    ];

    public function index()
    {
        return view('friends-and-family.admin.index');
    }

    public function show($type)
    {
        if (!array_key_exists($type, $this->catalogs)) {
            abort(404);
        }

        $config = $this->catalogs[$type];
        $items = $config['model']::orderBy('name')->get();

        return view('friends-and-family.admin.catalog', compact('items', 'type', 'config'));
    }

    public function store(Request $request, $type)
    {
        if (!array_key_exists($type, $this->catalogs)) { abort(404); }

        $request->validate(['name' => 'required|string|max:255']);
        
        $modelClass = $this->catalogs[$type]['model'];
        $modelClass::create(['name' => $request->name, 'is_active' => true]);

        return redirect()->back()->with('success', 'Registro creado correctamente.');
    }

    public function update(Request $request, $type, $id)
    {
        if (!array_key_exists($type, $this->catalogs)) { abort(404); }

        $request->validate(['name' => 'required|string|max:255']);

        $modelClass = $this->catalogs[$type]['model'];
        $item = $modelClass::findOrFail($id);
        $item->update(['name' => $request->name]);

        return redirect()->back()->with('success', 'Registro actualizado correctamente.');
    }

    public function destroy($type, $id)
    {
        if (!array_key_exists($type, $this->catalogs)) { abort(404); }

        $modelClass = $this->catalogs[$type]['model'];
        $item = $modelClass::findOrFail($id);
        $item->delete();

        return redirect()->back()->with('success', 'Registro eliminado correctamente.');
    }

    public function branchesIndex(FfClient $client)
    {
        $branches = $client->branches()->orderBy('name')->get();
        return view('friends-and-family.admin.branches', compact('client', 'branches'));
    }

    public function branchesStore(Request $request, FfClient $client)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'schedule' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
        ]);

        $client->branches()->create($request->all());

        return redirect()->back()->with('success', 'Sucursal agregada correctamente.');
    }

    public function branchesUpdate(Request $request, \App\Models\FfClientBranch $branch)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'schedule' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
        ]);

        $branch->update($request->all());

        return redirect()->back()->with('success', 'Sucursal actualizada correctamente.');
    }

    public function branchesDestroy(\App\Models\FfClientBranch $branch)
    {
        $branch->delete();
        return redirect()->back()->with('success', 'Sucursal eliminada correctamente.');
    }
    
}