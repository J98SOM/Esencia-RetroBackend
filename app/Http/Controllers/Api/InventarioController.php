<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inventario;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class InventarioController extends Controller
{
    public function index()
    {
        return Inventario::orderBy('id', 'desc')->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:255|unique:inventarios,nombre',
            'stock_inicial' => 'required|integer|min:0',
            'stock_minimo' => 'required|integer|min:0',
            'unidad_medida' => 'nullable|string|max:50',
        ]);

        $inventario = Inventario::create($data);

        return response()->json($inventario, Response::HTTP_CREATED);
    }

    public function show(Inventario $inventario)
    {
        return $inventario;
    }

    public function update(Request $request, Inventario $inventario)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:255|unique:inventarios,nombre,' . $inventario->id,
            'stock_inicial' => 'required|integer|min:0',
            'stock_minimo' => 'required|integer|min:0',
            'unidad_medida' => 'nullable|string|max:50',
        ]);

        $inventario->update($data);

        return response()->json($inventario);
    }

    public function destroy(Inventario $inventario)
    {
        $inventario->delete();
        return response()->noContent();
    }
}
