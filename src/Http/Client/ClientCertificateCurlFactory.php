<?php

namespace Nfse\Http\Client;

use GuzzleHttp\Handler\CurlFactory;
use GuzzleHttp\Handler\CurlFactoryInterface;
use GuzzleHttp\Handler\EasyHandle;
use Psr\Http\Message\RequestInterface;
use RuntimeException;

/**
 * Entrega o certificado do cliente direto no handle do cURL. O Guzzle 8 recusa essas opções
 * no array `curl`, e a opção `cert` dele só aceita caminho, que deixaria o PFX em disco.
 */
final class ClientCertificateCurlFactory implements CurlFactoryInterface
{
    private CurlFactory $factory;

    /**
     * @param  array<int, mixed>  $certificateOptions
     */
    public function __construct(
        #[\SensitiveParameter]
        private array $certificateOptions,
    ) {
        $this->factory = new CurlFactory(3);
    }

    public function create(RequestInterface $request, array $options): EasyHandle
    {
        $easy = $this->factory->create($request, $options);

        if (! curl_setopt_array($easy->handle, $this->certificateOptions)) {
            $this->factory->release($easy);

            throw new RuntimeException('O cURL recusou o certificado do cliente.');
        }

        return $easy;
    }

    public function release(EasyHandle $easy): void
    {
        $this->factory->release($easy);
    }

    /**
     * PFX e senha não aparecem em dump.
     *
     * @return array<string, mixed>
     */
    public function __debugInfo(): array
    {
        return [];
    }
}
