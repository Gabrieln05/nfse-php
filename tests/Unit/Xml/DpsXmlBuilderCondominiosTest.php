<?php

namespace Nfse\Tests\Unit\Xml;

use Nfse\Xml\DpsXmlBuilder;
use Nfse\Xml\NfseXmlParser;

function grupoCondominios(array $sobrescritas = []): array
{
    return array_replace([
        'dVencOrig' => '2026-12-10',
        'gCobranca' => [
            ['tpCobranca' => '01', 'vCobranca' => 620.00],
            [
                'tpCobranca' => '99',
                'xTpCobranca' => 'Taxa extra: pintura',
                'vCobranca' => 150.50,
                'gDetCobranca' => [
                    ['tpDetCobranca' => '017', 'vDetCobranca' => 100.50],
                    ['tpDetCobranca' => '999', 'xTpDetCobranca' => 'Andaime', 'vDetCobranca' => 50.00],
                ],
            ],
        ],
        'gDesconto' => [
            ['tpDesconto' => '2', 'vDesconto' => 30.00, 'xTpDesconto' => 'Pontualidade'],
        ],
    ], $sobrescritas);
}

function ibscbsCondominio(array $condominios): array
{
    return [
        'imovel' => [
            'cMun' => '3550308',
            'gUnidImob' => ['inscImobFisc' => '12345678', 'cCIB' => '12345678'],
        ],
        'condominios' => $condominios,
        'valores' => ['trib' => ['CST' => '410', 'cClassTrib' => '410012']],
    ];
}

it('builds the condominios group in the order of Anexo VI', function () {
    $xml = (new DpsXmlBuilder)->build(dpsComIbscbs(ibscbsCondominio(grupoCondominios())));

    expect(validarContraSchema($xml, 'DPS_v1.01.xsd'))->toBeTrue()
        ->and($xml)->toContain('</imovel><condominios><dVencOrig>2026-12-10</dVencOrig><gCobranca>')
        ->and($xml)->toContain('<gCobranca><tpCobranca>01</tpCobranca><vCobranca>620.00</vCobranca></gCobranca>')
        ->and($xml)->toContain('<tpCobranca>99</tpCobranca><xTpCobranca>Taxa extra: pintura</xTpCobranca><vCobranca>150.50</vCobranca><gDetCobranca>')
        ->and($xml)->toContain('<gDetCobranca><tpDetCobranca>017</tpDetCobranca><vDetCobranca>100.50</vDetCobranca></gDetCobranca>')
        ->and($xml)->toContain('<tpDetCobranca>999</tpDetCobranca><xTpDetCobranca>Andaime</xTpDetCobranca>')
        ->and($xml)->toContain('<gDesconto><tpDesconto>2</tpDesconto><vDesconto>30.00</vDesconto><xTpDesconto>Pontualidade</xTpDesconto></gDesconto></condominios><valores>');
});

it('builds a charge without detail and without discount', function () {
    $xml = (new DpsXmlBuilder)->build(dpsComIbscbs(ibscbsCondominio([
        'dVencOrig' => '2026-12-10',
        'gCobranca' => [['tpCobranca' => '01', 'vCobranca' => 620.00]],
    ])));

    expect(validarContraSchema($xml, 'DPS_v1.01.xsd'))->toBeTrue()
        ->and($xml)->not()->toContain('gDetCobranca')
        ->and($xml)->not()->toContain('gDesconto');
});

it('keeps the condominios group apart from other services', function () {
    $xml = (new DpsXmlBuilder)->build(dpsComIbscbs([
        'cIndOp' => '010101',
        'valores' => ['trib' => ['CST' => '000', 'cClassTrib' => '000001']],
    ]));

    expect($xml)->not()->toContain('condominios');
});

it('reads the condominios group back', function () {
    $xml = (new DpsXmlBuilder)->build(dpsComIbscbs(ibscbsCondominio(grupoCondominios())));

    $nfse = (new NfseXmlParser)->parse('<NFSe xmlns="http://www.sped.fazenda.gov.br/nfse" versao="1.01"><infNFSe Id="NFS123">'.$xml.'</infNFSe></NFSe>');
    $condominios = $nfse->infNfse->dps->infDps->ibscbs->condominios;

    expect($condominios->dataVencimentoOriginal)->toBe('2026-12-10')
        ->and($condominios->cobrancas)->toHaveCount(2)
        ->and($condominios->cobrancas[1]->descricao)->toBe('Taxa extra: pintura')
        ->and($condominios->cobrancas[1]->detalhes[1]->tipo)->toBe('999')
        ->and($condominios->descontos[0]->descricao)->toBe('Pontualidade');
});

it('reads the destination indicator from the root of the dps', function () {
    $xml = (new DpsXmlBuilder)->build(dpsComIbscbs(ibscbsCondominio(grupoCondominios())));

    $nfse = (new NfseXmlParser)->parse('<NFSe xmlns="http://www.sped.fazenda.gov.br/nfse" versao="1.01"><infNFSe Id="NFS123">'.$xml.'</infNFSe></NFSe>');

    expect($nfse->infNfse->dps->infDps->indicadorDestinatario?->value)->toBe(0);
});
