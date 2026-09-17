<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class BerandaController extends Controller
{
    public function index(): View
    {
        return view('beranda');
    }
}