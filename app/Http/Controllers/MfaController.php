<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PragmaRX\Google2FA\Google2FA;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Color\Color;

class MfaController extends Controller
{
    public function generate(Request $request)
    {
        $user = Auth::user();
        $google2fa = new Google2FA();
        $secret = $google2fa->generateSecretKey();
        $g2faUrl = $google2fa->getQRCodeUrl(
            'Control Tower - Minmer Global',
            $user->email,
            $secret
        );

        $builder = new Builder(
            writer: new PngWriter(),
            writerOptions: [],
            validateResult: false,
            data: $g2faUrl,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: 300,
            margin: 0,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
            foregroundColor: new Color(0, 0, 0),
            backgroundColor: new Color(255, 255, 255)
        );

        $result = $builder->build();
        
        $qrCodeBase64 = 'data:image/png;base64,' . base64_encode($result->getString());

        $request->session()->put('2fa_secret_temp', $secret);

        return response()->json([
            'qr_code' => $qrCodeBase64, 
            'secret' => $secret
        ]);
    }

    public function enable(Request $request)
    {
        $request->validate(['code' => 'required|digits:6']);
        
        $secret = $request->session()->get('2fa_secret_temp');
        $google2fa = new Google2FA();

        if (!$secret) {
            return response()->json(['success' => false, 'message' => 'El código QR ha expirado. Por favor recarga la página.'], 422);
        }

        if ($google2fa->verifyKey($secret, $request->code)) {
            $user = Auth::user();
            $user->google2fa_secret = $secret;
            $user->save();
            
            $request->session()->forget('2fa_secret_temp');
            
            return response()->json(['success' => true, 'message' => 'Autenticación de dos pasos activada exitosamente.']);
        }

        return response()->json(['success' => false, 'message' => 'El código ingresado es incorrecto.'], 422);
    }

    public function disable(Request $request)
    {
        $request->validate([
            'password' => 'required|current_password',
        ]);

        $user = Auth::user();
        $user->google2fa_secret = null;
        $user->save();

        return response()->json(['success' => true, 'message' => 'Autenticación de dos pasos desactivada.']);
    }
}