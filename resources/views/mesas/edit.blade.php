@extends('layouts.app')

@section('content')
<div class="p-6">
    <h1 class="text-2xl font-bold mb-4">Editar Mesa</h1>

    @if($errors->any())
        <div class="mb-4 text-red-600">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('mesas.update', $mesa) }}" method="POST" class="space-y-4 bg-white p-4 rounded shadow">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-sm font-medium text-gray-700">Nombre</label>
            <input type="text" name="nombre" value="{{ old('nombre', $mesa->nombre) }}" class="mt-1 block w-full border rounded px-3 py-2" required>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Capacidad</label>
            <input type="number" name="capacidad" value="{{ old('capacidad', $mesa->capacidad) }}" class="mt-1 block w-full border rounded px-3 py-2" min="1" required>
        </div>

        <div class="flex items-center gap-2">
            <button class="bg-primary text-white px-4 py-2 rounded">Actualizar</button>
            <a href="{{ route('mesas.index') }}" class="text-gray-600">Cancelar</a>
        </div>
    </form>
</div>
@endsection
