<?php

namespace Nfse\Dto\Nfse;

use Nfse\Dto\Dto;
use Spatie\DataTransferObject\Attributes\MapFrom;

class DetalheCobrancaData extends Dto
{
    /**
     * Tipo do detalhamento: de 001 a 036, ou 999 - Outro.
     */
    #[MapFrom('tpDetCobranca')]
    public ?string $tipo = null;

    /**
     * Descrição do tipo de detalhamento. Só quando tpDetCobranca = 999.
     */
    #[MapFrom('xTpDetCobranca')]
    public ?string $descricao = null;

    /**
     * Valor detalhado em R$, que compõe vCobranca.
     */
    #[MapFrom('vDetCobranca')]
    public ?float $valor = null;
}
