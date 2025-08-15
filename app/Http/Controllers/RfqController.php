<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Folder;

class RfqController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Buscar la carpeta principal 'RFQ' del área 'Comercial'
        $rfqFolder = Folder::whereHas('area', function ($query) {
            $query->where('name', 'Comercial');
        })
        ->where('name', 'RFQ')
        ->first();
        
        // Si no se encuentra la carpeta principal, se devuelve una colección vacía
        $rfqSubfolders = collect();

        if ($rfqFolder) {
            // Se obtienen las subcarpetas de 'RFQ' que el usuario tiene permisos para ver
            $rfqSubfolders = $user->accessibleFolders()->where('parent_id', $rfqFolder->id)->get();
        }

        return view('tablero.rfq', ['rfqSubfolders' => $rfqSubfolders]);
    }
}