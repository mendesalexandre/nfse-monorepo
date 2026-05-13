<?php

namespace App\Http\Requests\NFSe;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Valida o payload de cancelamento de NFS-e (evento e101101).
 *
 * Motivos aceitos pelo SEFIN:
 *   1 → Erro na Emissão
 *   2 → Serviço não Prestado
 *   9 → Outros
 */
class CancelarNfseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'motivo' => ['required', 'integer', Rule::in([1, 2, 9])],
            'justificativa' => ['required', 'string', 'min:15', 'max:200'],
        ];
    }

    public function messages(): array
    {
        return [
            'motivo.in' => 'Motivo inválido — deve ser 1 (Erro na Emissão), 2 (Serviço não Prestado) ou 9 (Outros).',
            'justificativa.min' => 'Justificativa precisa ter pelo menos 15 caracteres (exigência SEFIN).',
            'justificativa.max' => 'Justificativa pode ter no máximo 200 caracteres (exigência SEFIN).',
        ];
    }
}
