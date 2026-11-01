<?php

use GuzzleHttp\Exception\GuzzleException;
use Nfse\Enums\TipoAmbiente;
use Nfse\Http\Client\AdnClient;
use Nfse\Http\Client\CncClient;
use Nfse\Http\Client\SefinClient;
use Nfse\Http\NfseContext;

/**
 * @return array{cert: string, pem: string, pfx: string}
 */
function certificadoDeTeste(string $nome): array
{
    $config = ['config' => __DIR__.'/../../../fixtures/openssl.cnf', 'private_key_bits' => 2048];
    $chave = openssl_pkey_new($config);
    $certificado = openssl_csr_sign(openssl_csr_new(['commonName' => $nome], $chave, $config), null, $chave, 1, $config);

    openssl_x509_export($certificado, $cert);
    openssl_pkey_export($chave, $pem);
    openssl_pkcs12_export($certificado, $pfx, $chave, 'senha');

    return ['cert' => $cert, 'pem' => $cert.$pem, 'pfx' => $pfx];
}

function opcoesDoCertificado(object $cliente): array
{
    return (new ReflectionMethod($cliente, 'clientCertificateCurlOptions'))->invoke($cliente);
}

it('sends the PFX content to cURL without touching the disk', function (string $classe) {
    $antes = glob(sys_get_temp_dir().DIRECTORY_SEPARATOR.'nfse_cert_*');

    $contexto = new NfseContext(TipoAmbiente::Homologacao, certificatePassword: 'senha', certificateContent: 'bytes do pfx');
    $curl = opcoesDoCertificado(new $classe($contexto));

    expect($curl[CURLOPT_SSLCERT_BLOB])->toBe('bytes do pfx')
        ->and($curl[CURLOPT_SSLCERTTYPE])->toBe('P12')
        ->and($curl[CURLOPT_SSLCERTPASSWD])->toBe('senha')
        ->and($curl)->not->toHaveKey(CURLOPT_SSLCERT)
        ->and(glob(sys_get_temp_dir().DIRECTORY_SEPARATOR.'nfse_cert_*'))->toBe($antes);
})->with([
    'SEFIN' => [SefinClient::class],
    'ADN' => [AdnClient::class],
    'CNC' => [CncClient::class],
]);

it('keeps using the file when a path is informed', function () {
    $contexto = new NfseContext(TipoAmbiente::Homologacao, 'caminho/do.pfx', 'senha');
    $curl = opcoesDoCertificado(new SefinClient($contexto));

    expect($curl[CURLOPT_SSLCERT])->toBe('caminho/do.pfx')
        ->and($curl)->not->toHaveKey(CURLOPT_SSLCERT_BLOB);
});

// Passa pelo cliente Guzzle de verdade: montar o cURL à mão aqui escondeu que o Guzzle 8
// recusava todas as opções, e nenhuma requisição saía.
it('authenticates in mutual TLS through the real client, with the PFX in memory', function (string $classe, string $senha, ?string $esperado) {
    $servidor = certificadoDeTeste('localhost');
    $cliente = certificadoDeTeste('Prestador de Teste');

    $pemServidor = tempnam(sys_get_temp_dir(), 'tls');
    $pemCliente = tempnam(sys_get_temp_dir(), 'tls');
    file_put_contents($pemServidor, $servidor['pem']);
    file_put_contents($pemCliente, $cliente['cert']);

    $processo = proc_open(
        [PHP_BINARY, __DIR__.'/../../../fixtures/tls-server.php', $pemServidor, $pemCliente],
        [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
        $tubos,
    );

    try {
        $porta = (int) fgets($tubos[1]);

        $contexto = new NfseContext(TipoAmbiente::Homologacao, certificatePassword: $senha, certificateContent: $cliente['pfx']);
        $http = (new ReflectionProperty($classe, 'httpClient'))->getValue(new $classe($contexto));

        try {
            $resposta = (string) $http->get("https://localhost:{$porta}/", ['verify' => $pemServidor])->getBody();
        } catch (GuzzleException) {
            $resposta = null;
        }

        expect($resposta)->toBe($esperado);
    } finally {
        proc_terminate($processo);
        proc_close($processo);
        unlink($pemServidor);
        unlink($pemCliente);
    }
})->with([
    'SEFIN' => [SefinClient::class],
    'ADN' => [AdnClient::class],
    'CNC' => [CncClient::class],
])->with([
    'senha certa' => ['senha', 'Prestador de Teste'],
    'senha errada' => ['errada', null],
])->skipOnWindows();
