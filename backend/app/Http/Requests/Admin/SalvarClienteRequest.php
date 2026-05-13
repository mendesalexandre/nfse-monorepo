<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validação para criar/editar cliente. Não cobre cert (rota separada
 * `uploadCert`) nem credenciais (rotas `regenerar-*` / `revogar`).
 */
class SalvarClienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $clienteId = $this->route('cliente')?->id ?? $this->route('cliente');

        return [
            'nome_empresa' => ['required', 'string', 'max:255'],
            'cnpj' => [
                'required',
                'string',
                'size:14',
                Rule::unique('cliente', 'cnpj')->ignore($clienteId)->whereNull('data_exclusao'),
            ],
            'email' => ['required', 'email', 'max:255'],
            'telefone' => ['nullable', 'string', 'max:20'],

            // Endereço prestador
            'cep' => ['required', 'string', 'size:8'],
            'uf' => ['required', 'string', 'size:2'],
            'codigo_municipio_ibge' => ['required', 'string', 'size:7', 'regex:/^\d{7}$/'],
            'logradouro' => ['required', 'string', 'max:255'],
            'numero' => ['required', 'string', 'max:20'],
            'bairro' => ['required', 'string', 'max:120'],
            'complemento' => ['nullable', 'string', 'max:255'],

            // Tributário
            'inscricao_municipal' => ['required', 'string', 'max:30'],
            'razao_social_prestador' => ['required', 'string', 'max:255'],
            'regime_especial_tributacao' => ['required', 'integer', Rule::in([0, 1, 2, 3, 4, 5, 6, 7])],
            'simples_nacional' => ['required', 'integer', Rule::in([1, 2, 3])],

            // Toggles
            'ambiente' => ['required', 'string', Rule::in(['homologacao', 'producao'])],
            'incluir_ibscbs' => ['required', 'boolean'],
            'is_ativo' => ['required', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'cnpj' => preg_replace('/\D/', '', (string) $this->cnpj),
            'cep' => preg_replace('/\D/', '', (string) $this->cep),
            'telefone' => $this->telefone ? preg_replace('/\D/', '', (string) $this->telefone) : null,
        ]);
    }
}
