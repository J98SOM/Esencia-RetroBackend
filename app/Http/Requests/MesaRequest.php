<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MesaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => [
                'required',
                'string',
                'max:255',
                Rule::unique('mesas', 'nombre')->ignore($this->route('mesa')),
            ],
            'capacidad' => 'required|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.unique' => 'El nombre de la mesa ya existe.',
        ];
    }
}
