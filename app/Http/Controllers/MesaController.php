<?php

namespace App\Http\Controllers;

use App\Http\Requests\MesaRequest;
use App\Models\Mesa;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MesaController extends Controller
{
    public function index(): View
    {
        $mesas = Mesa::orderBy('id', 'desc')->paginate(10);

        return view('mesas.index', compact('mesas'));
    }

    public function create(): View
    {
        return view('mesas.create');
    }

    public function store(MesaRequest $request): RedirectResponse
    {
        Mesa::create($request->validated());

        return redirect()->route('mesas.index')->with('success', 'Mesa creada correctamente.');
    }

    public function show(Mesa $mesa): View
    {
        return view('mesas.show', compact('mesa'));
    }

    public function edit(Mesa $mesa): View
    {
        return view('mesas.edit', compact('mesa'));
    }

    public function update(MesaRequest $request, Mesa $mesa): RedirectResponse
    {
        $mesa->update($request->validated());

        return redirect()->route('mesas.index')->with('success', 'Mesa actualizada correctamente.');
    }

    public function destroy(Mesa $mesa): RedirectResponse
    {
        $mesa->delete();

        return redirect()->route('mesas.index')->with('success', 'Mesa eliminada.');
    }
}
