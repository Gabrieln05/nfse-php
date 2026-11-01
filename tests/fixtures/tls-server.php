<?php

/**
 * Servidor TLS de uma conexão só, que exige certificado do cliente e devolve o
 * CN dele. Uso: php tls-server.php <pem do servidor> <pem aceito do cliente>
 */
$contexto = stream_context_create(['ssl' => [
    'local_cert' => $argv[1],
    'cafile' => $argv[2],
    'verify_peer' => true,
    'verify_peer_name' => false,
    'capture_peer_cert' => true,
]]);

$servidor = stream_socket_server('ssl://127.0.0.1:0', $erro, $mensagem, STREAM_SERVER_BIND | STREAM_SERVER_LISTEN, $contexto);

echo parse_url('tcp://'.stream_socket_get_name($servidor, false), PHP_URL_PORT).PHP_EOL;

$conexao = @stream_socket_accept($servidor, 10);

if ($conexao) {
    $certificado = stream_context_get_params($conexao)['options']['ssl']['peer_certificate'];
    $corpo = openssl_x509_parse($certificado)['subject']['CN'];

    fread($conexao, 8192);
    fwrite($conexao, "HTTP/1.1 200 OK\r\nContent-Length: ".strlen($corpo)."\r\nConnection: close\r\n\r\n".$corpo);
    fclose($conexao);
}
