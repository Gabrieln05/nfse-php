<?php

namespace Nfse\Dto\Nfse;

use Nfse\Dto\Dto;
use Spatie\DataTransferObject\Attributes\MapFrom;

class EnderecoImovelData extends Dto
{
    /**
     * CEP. Aqui ele vem direto em end, sem o grupo endNac.
     */
    #[MapFrom('CEP')]
    public ?string $cep = null;

    /**
     * Logradouro.
     */
    #[MapFrom('xLgr')]
    public ?string $logradouro = null;

    /**
     * Número.
     */
    #[MapFrom('nro')]
    public ?string $numero = null;

    /**
     * Complemento.
     */
    #[MapFrom('xCpl')]
    public ?string $complemento = null;

    /**
     * Bairro.
     */
    #[MapFrom('xBairro')]
    public ?string $bairro = null;
}
