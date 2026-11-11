<?php

namespace Nfse\Dto\Nfse;

use Nfse\Dto\Dto;
use Nfse\Support\DTO\ArrayCaster;
use Spatie\DataTransferObject\Attributes\CastWith;
use Spatie\DataTransferObject\Attributes\MapFrom;

class ImovelData extends Dto
{
    /**
     * Código do município do endereço do imóvel (IBGE).
     */
    #[MapFrom('cMun')]
    public ?string $codigoMunicipio = null;

    /**
     * Locação, cessão onerosa ou arrendamento do imóvel. Obrigatório no subitem 99.03 (E0937).
     */
    #[MapFrom('gLocacao')]
    public ?LocacaoImovelData $locacao = null;

    /**
     * Unidades imobiliárias objeto da operação.
     *
     * @var UnidadeImobiliariaData[]|null
     */
    #[MapFrom('gUnidImob'), CastWith(ArrayCaster::class, itemType: UnidadeImobiliariaData::class)]
    public ?array $unidades = null;
}
