<?php

namespace Nfse\Dto\Nfse;

use Nfse\Dto\Dto;
use Spatie\DataTransferObject\Attributes\MapFrom;

class DescontoCondominioData extends Dto
{
    /**
     * Tipo do desconto.
     * 1 - Desconto Incondicionado
     * 2 - Desconto Condicionado
     */
    #[MapFrom('tpDesconto')]
    public ?string $tipo = null;

    /**
     * Valor do desconto em R$.
     */
    #[MapFrom('vDesconto')]
    public ?float $valor = null;

    /**
     * Descrição do tipo do desconto aplicado pelo condomínio.
     */
    #[MapFrom('xTpDesconto')]
    public ?string $descricao = null;
}
