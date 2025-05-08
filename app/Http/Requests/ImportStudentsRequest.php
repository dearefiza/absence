<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportStudentsRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'file' => 'required|file|mimes:csv,txt',
        ];
    }

    public function rules()
{
    return [
        'nisn' => 'required|string|max:255',
        'name' => 'required|string|max:255',
        'image' => 'nullable|image',
        'wa_ortu' => ['nullable', 'regex:/^+62[1-9][0-9]{7,10}$/'],
        'class_id' => 'nullable|exists:classes,id',
    ];
}
    
}
