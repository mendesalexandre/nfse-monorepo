<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UploadCertificadoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pfx' => ['required', 'file', 'max:5120'], // 5 MB
            'senha' => ['required', 'string', 'min:1', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'pfx.required' => 'Arquivo PFX é obrigatório.',
            'pfx.file' => 'PFX inválido.',
            'pfx.max' => 'PFX excede 5 MB.',
            'senha.required' => 'Senha do certificado é obrigatória.',
        ];
    }
}
