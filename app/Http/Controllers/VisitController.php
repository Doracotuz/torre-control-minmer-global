<?php

namespace App\Http\Controllers;

use App\Models\Tms\Visit;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\VisitInvitation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

// ==========================================================
// USO DE endroid/qr-code (VERSIÓN ACTUALIZADA Y MÁS MANTENIDA)
// ==========================================================
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Logo\Logo;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;

use Endroid\QrCode\RoundBlockSizeMode;

// Se eliminó: use Endroid\QrCode\Builder\Builder;
// ==========================================================

class VisitController extends Controller
{
    /**
     * Muestra el formulario para crear un nuevo registro de visita.
     */
    public function create()
    {
        return view('tms.visits.create');
    }


public function index(Request $request)
{
    // Inicia la consulta base
    $query = Visit::query();

    // 1. Filtro de búsqueda por texto (nombre, email, empresa)
    if ($request->filled('search')) {
        $searchTerm = '%' . $request->search . '%';
        $query->where(function($q) use ($searchTerm) {
            $q->where('visitor_name', 'like', $searchTerm)
              ->orWhere('visitor_last_name', 'like', $searchTerm)
              ->orWhere('email', 'like', $searchTerm)
              ->orWhere('company', 'like', $searchTerm)
              ->orWhere('license_plate', 'like', $searchTerm);
        });
    }

    // 2. Filtro por rango de fechas
    if ($request->filled('start_date')) {
        $query->whereDate('visit_datetime', '>=', $request->start_date);
    }
    if ($request->filled('end_date')) {
        $query->whereDate('visit_datetime', '<=', $request->end_date);
    }

    // 3. Filtro por estatus
    if ($request->filled('status') && $request->status != '') {
        $query->where('status', $request->status);
    }

    // Ordenar los resultados y paginar
    $visits = $query->orderBy('visit_datetime', 'desc')->paginate(15);

    // Renderizar la vista pasando las visitas y los valores de los filtros
    return view('tms.visits.index', [
        'visits' => $visits,
        'filters' => $request->all() // Envía los filtros actuales para rellenar el formulario
    ]);
}    

    /**
     * Almacena una nueva visita, genera el QR y opcionalmente envía un correo.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'visitor_name' => 'required|string|max:255',
            'visitor_last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'company' => 'nullable|string|max:255',
            'vehicle_make' => 'nullable|string|max:255',
            'vehicle_model' => 'nullable|string|max:255',
            'license_plate' => 'nullable|string|max:255',
            'visit_datetime' => 'required|date',
            'reason' => 'required|string|max:1000',
            'companions' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $visit = Visit::create([
            'visitor_name' => $request->visitor_name,
            'visitor_last_name' => $request->visitor_last_name,
            'email' => $request->email,
            'company' => $request->company,
            'vehicle_make' => $request->vehicle_make,
            'vehicle_model' => $request->vehicle_model,
            'license_plate' => $request->license_plate,
            'visit_datetime' => Carbon::parse($request->visit_datetime),
            'reason' => $request->reason,
            'companions' => json_encode(array_filter(preg_split('/\\r\\n|\\r|\\n/', $request->companions))),
            'qr_code_token' => Str::uuid(),
            'created_by_user_id' => Auth::id(),
            'status' => 'Programada',
        ]);

            if ($request->has('send_email')) {
                $validationUrl = route('visits.validate.show', ['token' => $visit->qr_code_token]);

                $logoPath = public_path('images/LogoBlanco.png'); // Ruta al logo

                // 1. Crear y configurar el objeto QrCode
            // Generar QR con Builder (compatible con endroid/qr-code 6.x)
            $builder = new \Endroid\QrCode\Builder\Builder(
                writer: new PngWriter(),
                writerOptions: [],
                validateResult: false,
                data: $validationUrl,
                encoding: new Encoding('UTF-8'),
                errorCorrectionLevel: ErrorCorrectionLevel::High,
                size: 250,
                margin: 10,
                roundBlockSizeMode: RoundBlockSizeMode::Margin,
                foregroundColor: new Color(0, 0, 0),
                backgroundColor: new Color(255, 255, 255),
                logoPath: $logoPath,
                logoResizeToWidth: 50,
                logoPunchoutBackground: true
            );
            $result = $builder->build();
            $qrCodeImage = $result->getString();

                Mail::to($visit->email)
                    ->cc(Auth::user()->email)
                    ->send(new VisitInvitation($visit, $qrCodeImage));
            }

            return redirect()->route('area_admin.visits.create')->with('success', 'Visita registrada y QR generado exitosamente.');
        }

    /**
     * Muestra la página pública con la cámara para escanear el QR.
     */
    public function showScanPage()
    {
        return view('tms.visits.scan');
    }

    /**
     * Muestra el resultado de la validación de un QR (página pública).
     */
    public function showValidationResult($token)
    {
        $visit = Visit::where('qr_code_token', $token)->firstOrFail();

        $validationResult = $this->performValidation($visit);

        return view('tms.visits.validation-result', [
            'message' => $validationResult['message'],
            'status' => $validationResult['status'],
            'visit' => $visit,
        ]);
    }

    /**
     * Lógica central de validación.
     */
    private function performValidation(Visit $visit)
    {
        if ($visit->status !== 'Programada') {
            return [
                'status' => 'warning',
                'message' => 'Este código QR ya fue procesado. Estatus actual: ' . $visit->status,
            ];
        }

        if (Carbon::now()->gt($visit->visit_datetime)) {
            $visit->status = 'No ingresado';
            $visit->save();
            return [
                'status' => 'error',
                'message' => 'ACCESO DENEGADO: El tiempo para esta visita ha expirado.',
            ];
        }

        $visit->status = 'Ingresado';
        $visit->save();
        return [
            'status' => 'success',
            'message' => 'ACCESO AUTORIZADO: ¡Bienvenido(a)!',
        ];
    }

    public function destroy(Visit $visit)
    {
        // La inyección de modelos de ruta (Route Model Binding) ya encuentra la visita por nosotros.
        // Si no la encuentra, Laravel automáticamente arrojará un error 404.
        
        $visit->delete();

        // Redirigir de vuelta al índice con un mensaje de éxito.
        return redirect()->route('area_admin.visits.index')
                        ->with('success', 'Visita eliminada exitosamente.');
    }

}