<?php

namespace Nfse\Dto\Nfse;

use Nfse\Dto\Dto;
use Spatie\DataTransferObject\Attributes\MapFrom;

/**
 * Grupo gLocacao do imóvel: locação, cessão onerosa ou arrendamento de bens imóveis (NT 009).
 * A Sefin o exige no subitem 99.03 (E0937), e vServ = pCopropriedade x vTotOper.
 */
class LocacaoImovelData extends Dto
{
    /**
     * Percentual de copropriedade. Com um só locador, cedente ou arrendador, 100.
     */
    #[MapFrom('pCopropriedade')]
    public ?float $percentualCopropriedade = null;

    /**
     * Valor total da operação, sem a parte proporcional do coproprietário.
     */
    #[MapFrom('vTotOper')]
    public ?float $valorTotalOperacao = null;

    /**
     * Valor total do desconto incondicionado da operação.
     */
    #[MapFrom('vDescIncondTot')]
    public ?float $valorTotalDescontoIncondicionado = null;

    /**
     * Valor total do desconto condicionado da operação.
     */
    #[MapFrom('vDescCondTot')]
    public ?float $valorTotalDescontoCondicionado = null;

    /**
     * Data de vencimento original do aluguel, renda ou equivalente (AAAA-MM-DD).
     */
    #[MapFrom('dVencOrig')]
    public ?string $dataVencimentoOriginal = null;
}
