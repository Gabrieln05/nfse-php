<?php

namespace Nfse\Http\Client;

trait UsesClientCertificate
{
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
