<?php

namespace App\Http\Controllers;

use App\Models\ElectronicLabel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class ElectronicLabelController extends Controller
{
    public function index()
    {
        return view('electronic_label.index');
    }

    public function create()
    {
        return view('electronic_label.create');
    }

    public function store(Request $request)
    {
        // 1. Validar entrada
        $request->validate([
            'series' => 'required|string|size:2|alpha_num|uppercase', // 2 caracteres exactos
            'quantity' => 'required|integer|min:1|max:5000', // Limite por seguridad
        ]);

        $series = strtoupper($request->series);
        $quantity = $request->quantity;
        $createdCount = 0;

        // 2. Usar transacción para asegurar que los consecutivos no se dupliquen si hay concurrencia
        DB::transaction(function () use ($series, $quantity, &$createdCount) {
            
            // Obtener el último consecutivo PARA ESTA SERIE específica
            // Bloqueamos la lectura para escritura (lockForUpdate) para evitar condiciones de carrera
            $lastLabel = ElectronicLabel::where('series', $series)
                                        ->lockForUpdate()
                                        ->orderBy('consecutive', 'desc')
                                        ->first();

            $currentConsecutive = $lastLabel ? $lastLabel->consecutive : 0;

            for ($i = 1; $i <= $quantity; $i++) {
                $currentConsecutive++;

                // Generar Folio: Serie + Guión + 10 dígitos (rellenado con ceros a la izquierda)
                $folio = $series . '-' . str_pad($currentConsecutive, 10, '0', STR_PAD_LEFT);

                // Generar Identificador único de 52 caracteres (letras mayus/minus y números)
                $uniqueId = Str::random(52);

                // Construir URL completa
                // El helper url('/') obtiene el dominio actual automáticamente
                $fullUrl = url('/app/qr/faces/pages/mobile/validadorqr/' . $uniqueId);

                ElectronicLabel::create([
                    'series' => $series,
                    'consecutive' => $currentConsecutive,
                    'folio' => $folio,
                    'unique_identifier' => $uniqueId,
                    'full_url' => $fullUrl,
                    'user_id' => Auth::id(),
                ]);

                $createdCount++;
            }
        });

        return redirect()->route('electronic-label.index')
                         ->with('success', "Se han generado exitosamente {$createdCount} marbetes de la serie {$series}.");
    }
}