<?php

namespace Nfse\Http\Client;

use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\RequestOptions;
use Nfse\Http\Retry;

trait UsesClientCertificate
{
    /**
     * O que os clientes HTTP têm em comum. Vai pelas opções do próprio Guzzle: o Guzzle 8
     * recusa no array `curl` o que tem opção própria, e só na hora da requisição.
     *
     * @return array<string, mixed>
     */
    private function httpClientOptions(): array
    {
        return [
            'handler' => Retry::stack(new CurlHandler([
                'handle_factory' => new ClientCertificateCurlFactory($this->clientCertificateCurlOptions()),
            ])),
            // Com o Retry (3 tentativas), um host inacessível segura a requisição
            // por até 3x o connect timeout; 10s basta para o handshake.
            RequestOptions::CONNECT_TIMEOUT => 10,
            RequestOptions::TIMEOUT => 60,
            RequestOptions::FORCE_IP_RESOLVE => 'v4',
            RequestOptions::VERSION => '1.1',
            RequestOptions::HEADERS => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ];
    }

    /**
     * O conteúdo do PFX vai para o cURL em memória. Arquivo temporário apagado no
     * __destruct fica para trás se o worker morrer, e é a chave privada do cliente.
     *
     * @return array<int, mixed>
     */
    private function clientCertificateCurlOptions(): array
    {
        if ($this->context->nssCertificateSelector !== null) {
            return [
                CURLOPT_SSLCERTTYPE => 'ENG',
                CURLOPT_SSLCERT => $this->context->nssCertificateSelector,
            ];
        }

        $options = [
            CURLOPT_SSLCERTTYPE => 'P12',
            CURLOPT_SSLCERTPASSWD => $this->context->certificatePassword,
        ];

        if ($this->context->certificatePath !== null) {
            return $options + [CURLOPT_SSLCERT => $this->context->certificatePath];
        }

        return $options + [CURLOPT_SSLCERT_BLOB => $this->context->certificateContent];
    }
}
