<?php

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

function opcoesCurlDoCliente(object $cliente): array
{
    $propriedade = new ReflectionProperty($cliente, 'httpClient');
    $propriedade->setAccessible(true);

    return $propriedade->getValue($cliente)->getConfig('curl');
}

it('sends the PFX content to cURL without touching the disk', function (string $classe) {
    $antes = glob(sys_get_temp_dir().DIRECTORY_SEPARATOR.'nfse_cert_*');

    $contexto = new NfseContext(TipoAmbiente::Homologacao, certificatePassword: 'senha', certificateContent: 'bytes do pfx');
    $curl = opcoesCurlDoCliente(new $classe($contexto));

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
    $curl = opcoesCurlDoCliente(new SefinClient($contexto));

    expect($curl[CURLOPT_SSLCERT])->toBe('caminho/do.pfx')
        ->and($curl)->not->toHaveKey(CURLOPT_SSLCERT_BLOB);
});

it('authenticates in mutual TLS with the PFX in memory', function (string $senha, ?string $esperado) {
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
        $opcoes = array_diff_key(opcoesCurlDoCliente(new SefinClient($contexto)), [CURLOPT_RESOLVE => true]);

        $curl = curl_init("https://localhost:{$porta}/");
        curl_setopt_array($curl, $opcoes + [CURLOPT_CAINFO => $pemServidor, CURLOPT_RETURNTRANSFER => true]);
        $resposta = curl_exec($curl);

        expect($resposta === false ? null : $resposta)->toBe($esperado);
    } finally {
        proc_terminate($processo);
        proc_close($processo);
        unlink($pemServidor);
        unlink($pemCliente);
    }
})->with([
    'senha certa' => ['senha', 'Prestador de Teste'],
    'senha errada' => ['errada', null],
])->skipOnWindows();
