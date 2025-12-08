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

    public function conditionsEdit(FfClient $client)
    {
        $conditions = $client->deliveryConditions ?? new \App\Models\FfClientDeliveryCondition();
        
        return view('friends-and-family.admin.client-conditions', compact('client', 'conditions'));
    }

    public function conditionsUpdate(Request $request, FfClient $client)
    {
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
                    \Illuminate\Support\Facades\Storage::disk('s3')->delete($conditions->$imgField);
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

    public function exportConditionsPdf(\App\Models\FfClient $client)
    {
        $conditions = $client->deliveryConditions;
        
        if (!$conditions) {
            return redirect()->back()->with('error', 'El cliente no tiene condiciones configuradas.');
        }

        $logoUrl = \Illuminate\Support\Facades\Storage::disk('s3')->url('LogoAzulm.PNG');

        $prepFields = [
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

        $docFields = [
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

        $evidFields = [
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

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('friends-and-family.admin.conditions-pdf', compact('client', 'conditions', 'logoUrl', 'prepFields', 'docFields', 'evidFields'));
        
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOption('isRemoteEnabled', true);
        $pdf->setOption('dpi', 150);

        return $pdf->stream('Condiciones_Entrega_' . \Illuminate\Support\Str::slug($client->name) . '.pdf');
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

        $condition = \App\Models\FfClientDeliveryCondition::findOrFail($conditionId);

        if ($condition->$field) {
            \Illuminate\Support\Facades\Storage::disk('s3')->delete($condition->$field);
            
            $condition->$field = null;
            $condition->save();
            
            return redirect()->back()->with('success', 'Imagen eliminada correctamente.');
        }

        return redirect()->back()->with('error', 'No había imagen para eliminar.');
    }    
    
}