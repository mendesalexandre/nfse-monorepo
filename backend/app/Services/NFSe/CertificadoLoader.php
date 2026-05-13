<?php

namespace App\Services\NFSe;

use App\Models\Cliente;
use PhpNfseNacional\Certificate\Certificate;
use PhpNfseNacional\Exceptions\CertificateException;

/**
 * Carrega o `Certificate` do SDK a partir de um Cliente, descriptografando
 * `pfx_encrypted` e `pfx_senha_encrypted` em memória só durante o uso.
 *
 * O conteúdo do PFX é guardado em base64 dentro do campo cifrado pra evitar
 * problemas de encoding ao serializar binário em texto.
 */
class CertificadoLoader
{
    /**
     * Carrega o cert A1 do cliente. Lança CertificateException em qualquer
     * falha (PFX corrompido, senha errada, sem chave privada, etc.).
     */
    public function carregar(Cliente $cliente): Certificate
    {
        $pfxBase64 = $cliente->pfx_encrypted;
        $senha = $cliente->pfx_senha_encrypted;

        if (! is_string($pfxBase64) || $pfxBase64 === '') {
            throw new CertificateException("Cliente {$cliente->id} não tem PFX cadastrado");
        }

        $pfxBinario = base64_decode($pfxBase64, true);
        if ($pfxBinario === false || $pfxBinario === '') {
            throw new CertificateException("Cliente {$cliente->id} tem PFX em formato inválido (base64 corrompido)");
        }

        return Certificate::fromPfxContent($pfxBinario, (string) $senha);
    }
}
