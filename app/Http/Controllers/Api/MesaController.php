<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\MesaRequest;
use App\Models\Mesa;
use Illuminate\Http\JsonResponse;

class MesaController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Mesa::orderBy('id', 'desc')->get());
    }

    public function store(MesaRequest $request): JsonResponse
    {
        $mesa = Mesa::create($request->validated());

        return response()->json($mesa, 201);
    }

    public function show(Mesa $mesa): JsonResponse
    {
        return response()->json($mesa);
    }

    public function update(MesaRequest $request, Mesa $mesa): JsonResponse
    {
        $mesa->update($request->validated());

        return response()->json($mesa);
    }

    public function destroy(Mesa $mesa): JsonResponse
    {
        $mesa->delete();

        return response()->json(null, 204);
    }
}
