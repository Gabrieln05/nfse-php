<?php

namespace Nfse\Dto\Nfse;

use Nfse\Dto\Dto;
use Spatie\DataTransferObject\Attributes\MapFrom;

class UnidadeImobiliariaData extends Dto
{
    /**
     * Inscrição imobiliária fiscal fornecida pela Prefeitura Municipal.
     */
    #[MapFrom('inscImobFisc')]
    public ?string $inscricaoImobiliariaFiscal = null;

    /**
     * Código do Cadastro Imobiliário Brasileiro (CIB).
     */
    #[MapFrom('cCIB')]
    public ?string $codigoCib = null;

    /**
     * Endereço do imóvel, quando não há CIB.
     */
    #[MapFrom('end')]
    public ?EnderecoImovelData $endereco = null;
}
