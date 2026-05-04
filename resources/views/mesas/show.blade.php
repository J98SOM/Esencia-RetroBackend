@extends('layouts.app')

@section('content')
<div class="p-6">
    <h1 class="text-2xl font-bold mb-4">Mesa #{{ $mesa->id }}</h1>

    <div class="bg-white p-4 rounded shadow space-y-2">
        <div><strong>Nombre:</strong> {{ $mesa->nombre }}</div>
        <div><strong>Capacidad:</strong> {{ $mesa->capacidad }}</div>
        <div><strong>Creada:</strong> {{ $mesa->created_at->format('Y-m-d H:i') }}</div>
    </div>

    <div class="mt-4">
        <a href="{{ route('mesas.edit', $mesa) }}" class="bg-primary text-white px-4 py-2 rounded">Editar</a>
        <a href="{{ route('mesas.index') }}" class="ml-2 text-gray-600">Volver</a>
    </div>
</div>
@endsection
