<?php

namespace App\Http\Controllers;

class InventarioController extends Controller
{
    public function index()
    {
        return view('inventarios.index');
    }
}
