<?php

namespace Nfse\Dto\Nfse;

use Nfse\Dto\Dto;
use Nfse\Support\DTO\ArrayCaster;
use Spatie\DataTransferObject\Attributes\CastWith;
use Spatie\DataTransferObject\Attributes\MapFrom;

class CobrancaCondominioData extends Dto
{
    /**
     * Tipo da cobrança.
     * 01 - Condomínio Ordinário
     * 02 - Condomínio Privativo
     * 03 - Fundo de Promoção e Propaganda (FPP)
     * 04 - Impostos e Taxas
     * 05 - Fundo de Reserva
     * 99 - Outra Cobrança
     */
    #[MapFrom('tpCobranca')]
    public ?string $tipo = null;

    /**
     * Descrição do tipo da cobrança. Só quando tpCobranca = 99.
     */
    #[MapFrom('xTpCobranca')]
    public ?string $descricao = null;

    /**
     * Valor da cobrança em R$.
     */
    #[MapFrom('vCobranca')]
    public ?float $valor = null;

    /**
     * Detalhamento da cobrança, até 20.
     *
     * @var DetalheCobrancaData[]|null
     */
    #[MapFrom('gDetCobranca'), CastWith(ArrayCaster::class, itemType: DetalheCobrancaData::class)]
    public ?array $detalhes = null;
}
