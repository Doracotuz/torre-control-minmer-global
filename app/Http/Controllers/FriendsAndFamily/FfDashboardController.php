<?php

namespace App\Http\Controllers\FriendsAndFamily;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FfDashboardController extends Controller
{
    public function index()
    {
        return view('friends-and-family.index');
    }
}