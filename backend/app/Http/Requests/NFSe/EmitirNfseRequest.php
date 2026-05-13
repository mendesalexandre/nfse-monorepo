<?php

namespace App\Http\Requests\NFSe;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Valida o payload de emissão de NFS-e.
 *
 * Estrutura (todos os campos em snake_case na entrada):
 *   - numero_dps (int, opcional — autogerado pelo service se omitido)
 *   - serie_dps (string ≤ 5, opcional — default "1")
 *   - tomador.documento (CPF 11 ou CNPJ 14)
 *   - tomador.nome
 *   - tomador.email (opcional)
 *   - tomador.telefone (opcional)
 *   - tomador.inscricao_municipal (opcional)
 *   - tomador.endereco.{logradouro,numero,bairro,cep,codigo_municipio_ibge,uf,complemento?}
 *   - servico.discriminacao
 *   - servico.codigo_municipio_prestacao (opcional — default = cidade do prestador)
 *   - servico.c_trib_nac (opcional — default 210101)
 *   - servico.c_nbs (opcional — default 113040000)
 *   - valores.valor_servicos
 *   - valores.deducoes_reducoes
 *   - valores.aliquota_issqn_percentual
 *   - valores.issqn_retido (opcional)
 *   - valores.desconto_incondicionado (opcional)
 *   - data_emissao_retroativa (ISO 8601, opcional — usar com cuidado, SEFIN restringe retroatividade)
 */
class EmitirNfseRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Auth real fica no middleware AutenticarApiKey.
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'numero_dps' => ['nullable', 'integer', 'min:1', 'max:99999999'],
            'serie_dps' => ['nullable', 'string', 'max:5'],
            'data_emissao_retroativa' => ['nullable', 'date'],

            'tomador' => ['required', 'array'],
            'tomador.documento' => ['required', 'string', 'min:11', 'max:18'],
            'tomador.nome' => ['required', 'string', 'min:2', 'max:255'],
            'tomador.email' => ['nullable', 'email', 'max:255'],
            'tomador.telefone' => ['nullable', 'string', 'max:20'],
            'tomador.inscricao_municipal' => ['nullable', 'string', 'max:30'],

            'tomador.endereco' => ['required', 'array'],
            'tomador.endereco.logradouro' => ['required', 'string', 'max:255'],
            'tomador.endereco.numero' => ['required', 'string', 'max:20'],
            'tomador.endereco.bairro' => ['required', 'string', 'max:120'],
            'tomador.endereco.cep' => ['required', 'string', 'min:8', 'max:9'],
            'tomador.endereco.codigo_municipio_ibge' => ['required', 'string', 'size:7', 'regex:/^\d{7}$/'],
            'tomador.endereco.uf' => ['required', 'string', 'size:2', 'regex:/^[A-Za-z]{2}$/'],
            'tomador.endereco.complemento' => ['nullable', 'string', 'max:255'],

            'servico' => ['required', 'array'],
            'servico.discriminacao' => ['required', 'string', 'min:10', 'max:2000'],
            'servico.codigo_municipio_prestacao' => ['nullable', 'string', 'size:7', 'regex:/^\d{7}$/'],
            'servico.c_trib_nac' => ['nullable', 'string', 'size:6', 'regex:/^\d{6}$/'],
            'servico.c_nbs' => ['nullable', 'string', 'size:9', 'regex:/^\d{9}$/'],

            'valores' => ['required', 'array'],
            'valores.valor_servicos' => ['required', 'numeric', 'gt:0'],
            'valores.deducoes_reducoes' => ['nullable', 'numeric', 'gte:0'],
            'valores.aliquota_issqn_percentual' => ['required', 'numeric', 'between:0,10'],
            'valores.issqn_retido' => ['nullable', 'boolean'],
            'valores.desconto_incondicionado' => ['nullable', 'numeric', 'gte:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'tomador.documento.required' => 'Documento do tomador (CPF ou CNPJ) é obrigatório.',
            'tomador.endereco.codigo_municipio_ibge.regex' => 'Código IBGE do município deve ter 7 dígitos numéricos.',
            'servico.discriminacao.min' => 'Discriminação muito curta (mínimo 10 caracteres).',
        ];
    }
}
