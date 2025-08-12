<?php

namespace App\Http\Controllers\CustomerService;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CustomerServiceController extends Controller
{
    public function index()
    {
        return view('customer-service.index');
    }
}