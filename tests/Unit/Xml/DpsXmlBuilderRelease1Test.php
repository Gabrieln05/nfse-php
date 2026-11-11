<?php

namespace Nfse\Tests\Unit\Xml;

use Nfse\Enums\LeiauteDps;
use Nfse\Xml\DpsXmlBuilder;

function ibscbsLocacao(array $sobrescritas = []): array
{
    return array_replace([
        'indFinal' => 0,
        'cIndOp' => '020101',
        'imovel' => [
            'cMun' => '3550308',
            'gLocacao' => ['pCopropriedade' => 100.0, 'vTotOper' => 1000.0],
            'gUnidImob' => [
                'end' => ['CEP' => '01001000', 'xLgr' => 'Rua do Imovel', 'nro' => '200', 'xBairro' => 'Centro'],
            ],
        ],
        'valores' => ['trib' => ['CST' => '200', 'cClassTrib' => '200027']],
    ], $sobrescritas);
}

function destinatarioDiverso(): array
{
    return [
        'indDest' => 1,
        'dest' => [
            'CNPJ' => '98765432000188',
            'xNome' => 'Destinatario Exemplo Ltda',
            'end' => [
                'endNac' => ['cMun' => '3550308', 'CEP' => '01001000'],
                'xLgr' => 'Praca da Se',
                'nro' => '100',
                'xBairro' => 'Se',
            ],
        ],
    ];
}

it('writes indDest and dest inside IBSCBS in the release 1 layout', function () {
    $xml = (new DpsXmlBuilder(LeiauteDps::Release1))->build(dpsComIbscbs(ibscbsLocacao(['tpEnteGov' => 1]), destinatarioDiverso()));

    expect(validarContraSchema($xml, 'DPS_v1.01.xsd', 'schemas-release1'))->toBeTrue()
        ->and($xml)->toContain('</toma><serv>')
        ->and($xml)->toContain('<tpEnteGov>1</tpEnteGov><indDest>1</indDest><dest><CNPJ>98765432000188</CNPJ>')
        ->and($xml)->toContain('</dest><imovel>');
});

it('writes indDest 0 inside IBSCBS when the recipient is the buyer', function () {
    $xml = (new DpsXmlBuilder(LeiauteDps::Release1))->build(dpsComIbscbs(ibscbsLocacao()));

    expect(validarContraSchema($xml, 'DPS_v1.01.xsd', 'schemas-release1'))->toBeTrue()
        ->and($xml)->toContain('<cIndOp>020101</cIndOp><indDest>0</indDest><imovel>')
        ->and($xml)->not()->toContain('</toma><indDest>');
});

it('keeps the v1.01 layout by default, which the release 1 schema refuses', function () {
    $xml = (new DpsXmlBuilder)->build(dpsComIbscbs(ibscbsLocacao(), destinatarioDiverso()));

    expect($xml)->toContain('</toma><indDest>1</indDest><dest>')
        ->and(validarContraSchema($xml, 'DPS_v1.01.xsd'))->toBeTrue()
        ->and(validarContraSchema($xml, 'DPS_v1.01.xsd', 'schemas-release1'))->toBeFalse();
});

it('builds the gLocacao group after cMun', function () {
    $ibscbs = ibscbsLocacao();
    $ibscbs['imovel']['gLocacao'] += ['vDescIncondTot' => 50.0, 'vDescCondTot' => 10.5, 'dVencOrig' => '2026-12-10'];

    $xml = (new DpsXmlBuilder)->build(dpsComIbscbs($ibscbs));

    expect(validarContraSchema($xml, 'DPS_v1.01.xsd'))->toBeTrue()
        ->and($xml)->toContain('<imovel><cMun>3550308</cMun><gLocacao><pCopropriedade>100.00</pCopropriedade><vTotOper>1000.00</vTotOper>'
            .'<vDescIncondTot>50.00</vDescIncondTot><vDescCondTot>10.50</vDescCondTot><dVencOrig>2026-12-10</dVencOrig></gLocacao><gUnidImob>');
});

it('leaves gLocacao out when it is not given', function () {
    $ibscbs = ibscbsLocacao();
    unset($ibscbs['imovel']['gLocacao']);

    expect((new DpsXmlBuilder)->build(dpsComIbscbs($ibscbs)))->not()->toContain('gLocacao');
});
