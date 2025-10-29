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
        
        $rfqFolder = Folder::whereHas('area', function ($query) {
            $query->where('name', 'Comercial');
        })
        ->where('name', 'RFQ')
        ->first();
        
        $rfqSubfolders = collect();

        if ($rfqFolder) {
            $rfqSubfolders = $user->accessibleFolders()->where('parent_id', $rfqFolder->id)->get();
        }

        return view('tablero.rfq', ['rfqSubfolders' => $rfqSubfolders]);
    }
}