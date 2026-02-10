<?php

namespace App\Http\Controllers\FriendsAndFamily;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FfClient;
use App\Models\FfSalesChannel;
use App\Models\FfTransportLine;
use App\Models\FfPaymentCondition;
use App\Models\FfClientBranch;
use App\Models\FfClientDeliveryCondition;
use App\Models\FfWarehouse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use App\Models\Area;
use App\Models\FfQuality;

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
        'warehouses' => [
            'model' => FfWarehouse::class,
            'title' => 'Almacenes',
            'icon' => 'fa-warehouse'
        ],
        'qualities' => [
            'model' => FfQuality::class,
            'title' => 'Calidades',
            'icon' => 'fa-medal'
        ],        
    ];

    public function index()
    {
        if (!Auth::user()->isSuperAdmin() && !Auth::user()->hasFfPermission('admin.view')) {
            abort(403, 'No tienes permiso para acceder a la administración.');
        }

        return view('friends-and-family.admin.index');
    }

    public function show($type)
    {
        if (!Auth::user()->isSuperAdmin() && !Auth::user()->hasFfPermission('admin.view')) {
            abort(403, 'No tienes permiso para ver catálogos.');
        }

        if (!array_key_exists($type, $this->catalogs)) {
            abort(404);
        }

        $config = $this->catalogs[$type];
        $modelClass = $config['model'];
        $query = $modelClass::query();

        if (!Auth::user()->isSuperAdmin()) {
            if ($type === 'warehouses') {
                $query->where(function($q) {
                    $q->where('area_id', Auth::user()->area_id)
                      ->orWhereNull('area_id');
                });
            } else {
                $query->where('area_id', Auth::user()->area_id);
            }
        } else {
            $query->with('area');
        }

        if ($type === 'warehouses') {
            $items = $query->orderBy('code')->get();
        } else {
            $items = $query->orderBy('name')->get();
        }
        
        $areas = Auth::user()->isSuperAdmin() ? Area::all() : [];

        return view('friends-and-family.admin.catalog', compact('items', 'type', 'config', 'areas'));
    }

    public function store(Request $request, $type)
    {
        if (!array_key_exists($type, $this->catalogs)) {
            abort(404);
        }

        $permissionMap = [
            'clients' => 'admin.clients',
            'channels' => 'admin.channels',
            'warehouses' => 'admin.warehouses',
            'qualities' => 'admin.qualities',
        ];

        if (isset($permissionMap[$type])) {
            if (!Auth::user()->isSuperAdmin() && !Auth::user()->hasFfPermission($permissionMap[$type])) {
                abort(403, 'No tienes permiso para gestionar ' . $type);
            }
        } elseif (!Auth::user()->isSuperAdmin() && !Auth::user()->hasFfPermission('admin.view')) {
             // Fallback for others (transport/payment) if strict check not defined, assumes basic admin right + area check logic below
             abort(403, 'No tienes permiso para gestionar este catálogo.');
        }

        $modelClass = $this->catalogs[$type]['model'];
        $tableName = (new $modelClass)->getTable();
        $user = Auth::user();
        
        // Logic for area_id:
        // If Super Admin: use request input (can be null for global)
        // If Regular User: force their area_id (cannot create global unless we change this policy later)
        $targetAreaId = $user->isSuperAdmin() ? $request->input('area_id') : $user->area_id;

        if ($type === 'warehouses') {
            $request->validate([
                'code' => [
                    'required', 'string', 'max:50',
                    Rule::unique($tableName)
                ],
                'description' => 'required|string|max:255',
                'address' => 'required|string|max:500',
                'phone' => 'required|string|max:50',
                'area_id' => $user->isSuperAdmin() ? 'nullable|exists:areas,id' : 'nullable'
            ]);

            $modelClass::create([
                'code' => $request->code,
                'description' => $request->description,
                'address' => $request->address,
                'phone' => $request->phone,
                'is_active' => true,
                'area_id' => $targetAreaId
            ]);
        } else {
            $request->validate([
                'name' => [
                    'required', 'string', 'max:255',
                    Rule::unique($tableName)->where(function ($query) use ($targetAreaId) {
                        return $query->where('area_id', $targetAreaId);
                    })
                ],
                'area_id' => $user->isSuperAdmin() ? 'required|exists:areas,id' : 'nullable'
            ]);

            $modelClass::create([
                'name' => $request->name,
                'is_active' => true,
                'area_id' => $targetAreaId
            ]);
        }

        return redirect()->back()->with('success', 'Registro creado correctamente.');
    }

    public function update(Request $request, $type, $id)
    {
        if (!array_key_exists($type, $this->catalogs)) {
            abort(404);
        }

        $permissionMap = [
            'clients' => 'admin.clients',
            'channels' => 'admin.channels',
            'warehouses' => 'admin.warehouses',
            'qualities' => 'admin.qualities',
        ];

        if (isset($permissionMap[$type])) {
            if (!Auth::user()->isSuperAdmin() && !Auth::user()->hasFfPermission($permissionMap[$type])) {
                abort(403, 'No tienes permiso para gestionar ' . $type);
            }
        } elseif (!Auth::user()->isSuperAdmin() && !Auth::user()->hasFfPermission('admin.view')) {
             abort(403, 'No tienes permiso para gestionar este catálogo.');
        }

        $modelClass = $this->catalogs[$type]['model'];
        $tableName = (new $modelClass)->getTable();
        $user = Auth::user();
        $item = $modelClass::findOrFail($id);

        if (!$user->isSuperAdmin() && $item->area_id !== $user->area_id) {
            abort(403, 'No tienes permiso para editar este registro.');
        }

        // Logic for area_id update:
        // If Super Admin: check if 'area_id' is present in request. If so, use it (even if null).
        // If not present (or not super admin), keep existing.
        $targetAreaId = ($user->isSuperAdmin() && $request->has('area_id')) ? $request->input('area_id') : $item->area_id;

        if ($type === 'warehouses') {
            $request->validate([
                'code' => [
                    'required', 'string', 'max:50',
                    Rule::unique($tableName)->ignore($id)
                ],
                'description' => 'required|string|max:255',
                'address' => 'required|string|max:500',
                'phone' => 'required|string|max:50',
                'area_id' => $user->isSuperAdmin() ? 'nullable|exists:areas,id' : 'nullable'
            ]);

            $data = [
                'code' => $request->code,
                'description' => $request->description,
                'address' => $request->address,
                'phone' => $request->phone,
            ];
        } else {
            $request->validate([
                'name' => [
                    'required', 'string', 'max:255',
                    Rule::unique($tableName)->ignore($id)->where(function ($query) use ($targetAreaId) {
                        return $query->where('area_id', $targetAreaId);
                    })
                ],
                'area_id' => $user->isSuperAdmin() ? 'required|exists:areas,id' : 'nullable'
            ]);

            $data = ['name' => $request->name];
        }

        if ($user->isSuperAdmin()) {
            $data['area_id'] = $targetAreaId;
        }

        $item->update($data);

        return redirect()->back()->with('success', 'Registro actualizado correctamente.');
    }

    public function destroy($type, $id)
    {
        if (!array_key_exists($type, $this->catalogs)) {
            abort(404);
        }

        $permissionMap = [
            'clients' => 'admin.clients',
            'channels' => 'admin.channels',
            'warehouses' => 'admin.warehouses',
            'qualities' => 'admin.qualities',
        ];

        if (isset($permissionMap[$type])) {
            if (!Auth::user()->isSuperAdmin() && !Auth::user()->hasFfPermission($permissionMap[$type])) {
                abort(403, 'No tienes permiso para eliminar en ' . $type);
            }
        } elseif (!Auth::user()->isSuperAdmin() && !Auth::user()->hasFfPermission('admin.view')) {
             abort(403, 'No tienes permiso para eliminar este registro.');
        }

        $modelClass = $this->catalogs[$type]['model'];
        $item = $modelClass::findOrFail($id);

        if (!Auth::user()->isSuperAdmin() && $item->area_id !== Auth::user()->area_id) {
            abort(403, 'No tienes permiso para eliminar este registro.');
        }

        $item->delete();

        return redirect()->back()->with('success', 'Registro eliminado correctamente.');
    }

    public function branchesIndex(FfClient $client)
    {
        if (!Auth::user()->isSuperAdmin() && !Auth::user()->hasFfPermission('admin.branches')) {
            abort(403, 'No tienes permiso para ver sucursales.');
        }
        if (!Auth::user()->isSuperAdmin() && $client->area_id !== Auth::user()->area_id) {
            abort(403);
        }
        $branches = $client->branches()->orderBy('name')->get();
        return view('friends-and-family.admin.branches', compact('client', 'branches'));
    }

    public function branchesStore(Request $request, FfClient $client)
    {
        if (!Auth::user()->isSuperAdmin() && !Auth::user()->hasFfPermission('admin.branches')) {
            abort(403, 'No tienes permiso para crear sucursales.');
        }
        if (!Auth::user()->isSuperAdmin() && $client->area_id !== Auth::user()->area_id) {
            abort(403);
        }
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'schedule' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
        ]);

        $client->branches()->create($request->all());

        return redirect()->back()->with('success', 'Sucursal agregada correctamente.');
    }

    public function branchesUpdate(Request $request, FfClientBranch $branch)
    {
        if (!Auth::user()->isSuperAdmin() && !Auth::user()->hasFfPermission('admin.branches')) {
            abort(403, 'No tienes permiso para editar sucursales.');
        }
        if (!Auth::user()->isSuperAdmin() && $branch->client->area_id !== Auth::user()->area_id) {
            abort(403);
        }
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'schedule' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
        ]);

        $branch->update($request->all());

        return redirect()->back()->with('success', 'Sucursal actualizada correctamente.');
    }

    public function branchesDestroy(FfClientBranch $branch)
    {
        if (!Auth::user()->isSuperAdmin() && !Auth::user()->hasFfPermission('admin.branches')) {
            abort(403, 'No tienes permiso para eliminar sucursales.');
        }
        if (!Auth::user()->isSuperAdmin() && $branch->client->area_id !== Auth::user()->area_id) {
            abort(403);
        }
        $branch->delete();
        return redirect()->back()->with('success', 'Sucursal eliminada correctamente.');
    }

    public function conditionsEdit(FfClient $client)
    {
        if (!Auth::user()->isSuperAdmin() && !Auth::user()->hasFfPermission('admin.clients')) {
            abort(403, 'No tienes permiso para gestionar condiciones de clientes.');
        }
        if (!Auth::user()->isSuperAdmin() && $client->area_id !== Auth::user()->area_id) {
            abort(403);
        }
        $conditions = $client->deliveryConditions ?? new FfClientDeliveryCondition();

        return view('friends-and-family.admin.client-conditions', compact('client', 'conditions'));
    }

    public function conditionsUpdate(Request $request, FfClient $client)
    {
        if (!Auth::user()->isSuperAdmin() && !Auth::user()->hasFfPermission('admin.clients')) {
            abort(403, 'No tienes permiso para gestionar condiciones de clientes.');
        }
        if (!Auth::user()->isSuperAdmin() && $client->area_id !== Auth::user()->area_id) {
            abort(403);
        }
        $booleans = [
            'revision_upc', 'distribucion_tienda', 're_etiquetado', 'colocacion_sensor',
            'preparado_especial', 'tipo_unidad_aceptada', 'equipo_seguridad', 'registro_patronal',
            'entrega_otros_pedidos', 'insumos_herramientas', 'maniobra', 'identificaciones',
            'etiqueta_fragil', 'tarima_chep', 'granel', 'tarima_estandar',
            'doc_factura', 'doc_do', 'doc_carta_maniobra', 'doc_carta_poder', 'doc_orden_compra',
            'doc_carta_confianza', 'doc_confirmacion_cita', 'doc_carta_caja_cerrada',
            'doc_confirmacion_facturas', 'doc_caratula_entrega', 'doc_pase_vehicular',
            'evid_folio_recibo', 'evid_factura_sellada', 'evid_sello_tarima', 'evid_etiqueta_recibo',
            'evid_acuse_oc', 'evid_hoja_rechazo', 'evid_anotacion_rechazo', 'evid_contrarrecibo',
            'evid_formato_reparto'
        ];

        $sanitizedData = [];
        foreach ($booleans as $field) {
            $sanitizedData[$field] = $request->has($field);
        }

        $conditions = $client->deliveryConditions()->firstOrNew(['ff_client_id' => $client->id]);

        $imageFields = [
            'prep_img_1', 'prep_img_2', 'prep_img_3',
            'doc_img_1', 'doc_img_2', 'doc_img_3',
            'evid_img_1', 'evid_img_2', 'evid_img_3'
        ];

        foreach ($imageFields as $imgField) {
            if ($request->hasFile($imgField)) {
                if ($conditions->$imgField) {
                    Storage::disk('s3')->delete($conditions->$imgField);
                }

                $path = $request->file($imgField)->store("ff_conditions/{$client->id}", 's3');
                $sanitizedData[$imgField] = $path;
            }
        }

        $client->deliveryConditions()->updateOrCreate(
            ['ff_client_id' => $client->id],
            $sanitizedData
        );

        return redirect()->back()->with('success', 'Condiciones de entrega actualizadas.');
    }

    public function exportConditionsPdf(FfClient $client)
    {
        if (!Auth::user()->isSuperAdmin() && !Auth::user()->hasFfPermission('admin.clients')) {
            abort(403, 'No tienes permiso para ver/exportar condiciones.');
        }
        if (!Auth::user()->isSuperAdmin() && $client->area_id !== Auth::user()->area_id) {
            abort(403);
        }
        $conditions = $client->deliveryConditions;

        if (!$conditions) {
            return redirect()->back()->with('error', 'El cliente no tiene condiciones configuradas.');
        }

        $logoUrl = Storage::disk('s3')->url('LogoAzulm.PNG');

        $prepFields = self::getPrepFieldsStatic();
        $docFields = self::getDocFieldsStatic();
        $evidFields = self::getEvidFieldsStatic();

        $pdf = Pdf::loadView('friends-and-family.admin.conditions-pdf', compact('client', 'conditions', 'logoUrl', 'prepFields', 'docFields', 'evidFields'));

        $pdf->setPaper('A4', 'portrait');
        $pdf->setOption('isRemoteEnabled', true);
        $pdf->setOption('dpi', 150);

        return $pdf->stream('Condiciones_Entrega_' . Str::slug($client->name) . '.pdf');
    }

    public static function getPrepFieldsStatic()
    {
        return [
            'Revisión de UPC vs Factura' => 'revision_upc',
            'Distribución por Tienda' => 'distribucion_tienda',
            'Re-etiquetado' => 're_etiquetado',
            'Colocación de Sensor' => 'colocacion_sensor',
            'Preparado Especial' => 'preparado_especial',
            'Tipo de Unidad Aceptada' => 'tipo_unidad_aceptada',
            'Equipo de Seguridad' => 'equipo_seguridad',
            'Registro Patronal (SUA)' => 'registro_patronal',
            'Entrega con Otros Pedidos' => 'entrega_otros_pedidos',
            'Insumos y Herramientas' => 'insumos_herramientas',
            'Maniobra' => 'maniobra',
            'Identificaciones para Acceso' => 'identificaciones',
            'Etiqueta de Frágil' => 'etiqueta_fragil',
            'Tarima CHEP' => 'tarima_chep',
            'Granel' => 'granel',
            'Tarima Estándar' => 'tarima_estandar',
        ];
    }

    public static function getDocFieldsStatic()
    {
        return [
            'Factura' => 'doc_factura',
            'DO' => 'doc_do',
            'Carta Maniobra' => 'doc_carta_maniobra',
            'Carta Poder' => 'doc_carta_poder',
            'Orden de Compra' => 'doc_orden_compra',
            'Carta Confianza' => 'doc_carta_confianza',
            'Confirmación de Cita' => 'doc_confirmacion_cita',
            'Carta Caja Cerrada' => 'doc_carta_caja_cerrada',
            'Confirmación de Facturas' => 'doc_confirmacion_facturas',
            'Carátula de Entrega' => 'doc_caratula_entrega',
            'Pase Vehicular' => 'doc_pase_vehicular',
        ];
    }

    public static function getEvidFieldsStatic()
    {
        return [
            'Folio de Recibo' => 'evid_folio_recibo',
            'Factura Sellada o Firmada' => 'evid_factura_sellada',
            'Sello Tarima CHEP' => 'evid_sello_tarima',
            'Etiqueta de Recibo' => 'evid_etiqueta_recibo',
            'Acuse de Orden de Compra' => 'evid_acuse_oc',
            'Hoja de Rechazo' => 'evid_hoja_rechazo',
            'Anotación de Rechazo' => 'evid_anotacion_rechazo',
            'Contrarrecibo de Equipo' => 'evid_contrarrecibo',
            'Formato de Reparto' => 'evid_formato_reparto',
        ];
    }

    public function deleteConditionImage($conditionId, $field)
    {
        $allowedFields = [
            'prep_img_1', 'prep_img_2', 'prep_img_3',
            'doc_img_1', 'doc_img_2', 'doc_img_3',
            'evid_img_1', 'evid_img_2', 'evid_img_3'
        ];

        if (!in_array($field, $allowedFields)) {
            abort(403, 'Campo no permitido');
        }

        $condition = FfClientDeliveryCondition::findOrFail($conditionId);
        
        $client = $condition->client; 
        if (!Auth::user()->isSuperAdmin() && $client && $client->area_id !== Auth::user()->area_id) {
            abort(403);
        }

        if (!Auth::user()->isSuperAdmin() && !Auth::user()->hasFfPermission('admin.clients')) {
            abort(403, 'No tienes permiso para eliminar imágenes de condiciones.');
        }

        if ($condition->$field) {
            Storage::disk('s3')->delete($condition->$field);

            $condition->$field = null;
            $condition->save();

            return redirect()->back()->with('success', 'Imagen eliminada correctamente.');
        }

        return redirect()->back()->with('error', 'No había imagen para eliminar.');
    }
}