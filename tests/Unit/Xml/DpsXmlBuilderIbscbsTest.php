<?php

namespace Nfse\Tests\Unit\Xml;

use Nfse\Dto\Nfse\DpsData;
use Nfse\Support\IdGenerator;
use Nfse\Xml\DpsXmlBuilder;

function dpsComIbscbs(array $ibscbs, array $sobrescritas = []): DpsData
{
    return new DpsData([
        '@attributes' => ['versao' => '1.01'],
        'infDPS' => array_replace([
            '@attributes' => ['Id' => IdGenerator::generateDpsId('12345678000199', '3550308', '1', '1001')],
            'tpAmb' => 2,
            'dhEmi' => '2026-03-10T10:00:00-03:00',
            'verAplic' => '1.0',
            'serie' => '1',
            'nDPS' => '1001',
            'dCompet' => '2026-03-10',
            'tpEmit' => 1,
            'cLocEmi' => '3550308',
            'prest' => [
                'CNPJ' => '12345678000199',
                'IM' => '12345',
                'xNome' => 'Prestador Exemplo Ltda',
                'regTrib' => [
                    'opSimpNac' => 3,
                    'regEspTrib' => 0,
                ],
            ],
            'toma' => [
                'CPF' => '11122233344',
                'xNome' => 'Tomador Exemplo',
            ],
            'serv' => [
                'locPrest' => ['cLocPrestacao' => '3550308'],
                'cServ' => [
                    'cTribNac' => '010201',
                    'xDescServ' => 'Analise de sistemas',
                    'cNBS' => '112011000',
                ],
            ],
            'valores' => [
                'vServPrest' => ['vServ' => 1000.00],
                'trib' => [
                    'tribMun.tribISSQN' => 1,
                    'tribMun.tpRetISSQN' => 1,
                    'tribMun.pAliq' => 5.00,
                    'totTrib.indTotTrib' => 0,
                ],
            ],
            'IBSCBS' => $ibscbs,
        ], $sobrescritas),
    ]);
}

it('builds a schema valid dps with the complete ibscbs group', function () {
    $dps = dpsComIbscbs([
        'indFinal' => 0,
        'cIndOp' => '010101',
        'tpOper' => 2,
        'gRefNFSe' => [
            'refNFSe' => [
                '12345678901234567890123456789012345678901234567890',
                '09876543210987654321098765432109876543210987654321',
            ],
        ],
        'tpEnteGov' => 1,
        'imovel' => [
            'cMun' => '3550308',
            'gUnidImob' => [
                'inscImobFisc' => '1234567890',
                'end' => [
                    'CEP' => '01001000',
                    'xLgr' => 'Rua do Imovel',
                    'nro' => '200',
                    'xBairro' => 'Centro',
                ],
            ],
        ],
        'valores' => [
            'trib' => [
                'CST' => '200',
                'cClassTrib' => '200011',
                'gIBSCBS' => [
                    'cCredPres' => '01',
                    'gTribRegular' => [
                        'CSTReg' => '000',
                        'cClassTribReg' => '000001',
                    ],
                    'gDif' => [
                        'pDifUF' => 10.00,
                        'pDifMun' => 10.00,
                        'pDifCBS' => 5.00,
                    ],
                ],
            ],
        ],
    ], [
        'indDest' => 1,
        'dest' => [
            'CNPJ' => '98765432000188',
            'xNome' => 'Destinatario Exemplo Ltda',
            'end' => [
                'endNac' => [
                    'cMun' => '3550308',
                    'CEP' => '01001000',
                ],
                'xLgr' => 'Praca da Se',
                'nro' => '100',
                'xBairro' => 'Se',
            ],
            'email' => 'destinatario@exemplo.com.br',
        ],
    ]);

    $xml = (new DpsXmlBuilder)->build($dps);

    expect(validarContraSchema($xml, 'DPS_v1.01.xsd'))->toBeTrue()
        ->and($xml)->toContain('</toma><indDest>1</indDest><dest><CNPJ>98765432000188</CNPJ>')
        ->and($xml)->toContain('</dest><serv>')
        ->and($xml)->toContain('<cIndOp>010101</cIndOp>')
        ->and($xml)->toContain('<CST>200</CST><cClassTrib>200011</cClassTrib><gIBSCBS><cCredPres>01</cCredPres>')
        ->and($xml)->toContain('<gUnidImob><inscImobFisc>1234567890</inscImobFisc><end><CEP>01001000</CEP>')
        ->and($xml)->toContain('<pDifCBS>5.00</pDifCBS>');
});

it('builds a schema valid dps with the minimum ibscbs group', function () {
    $dps = dpsComIbscbs([
        'cIndOp' => '010101',
        'valores' => [
            'trib' => [
                'CST' => '000',
                'cClassTrib' => '000001',
            ],
        ],
    ]);

    $xml = (new DpsXmlBuilder)->build($dps);

    expect(validarContraSchema($xml, 'DPS_v1.01.xsd'))->toBeTrue()
        ->and($xml)->toContain('</toma><indDest>0</indDest><serv>')
        ->and($xml)->toContain('<IBSCBS><cIndOp>010101</cIndOp><valores>')
        ->and($xml)->toContain('<trib><CST>000</CST><cClassTrib>000001</cClassTrib></trib>')
        ->and($xml)->not()->toContain('<gIBSCBS>')
        ->and($xml)->not()->toContain('<gRefNFSe>')
        ->and($xml)->not()->toContain('<dest>')
        ->and($xml)->not()->toContain('<imovel>')
        ->and($xml)->not()->toContain('<gDif>');
});

it('uses cCIB instead of the address when the property is registered', function () {
    $dps = dpsComIbscbs([
        'cIndOp' => '010101',
        'imovel' => [
            'cMun' => '3550308',
            'gUnidImob' => [
                'cCIB' => '12345678',
                'end' => [
                    'CEP' => '01001000',
                    'xLgr' => 'Rua do Imovel',
                    'nro' => '200',
                    'xBairro' => 'Centro',
                ],
            ],
        ],
        'valores' => [
            'trib' => [
                'CST' => '000',
                'cClassTrib' => '000001',
            ],
        ],
    ]);

    $xml = (new DpsXmlBuilder)->build($dps);

    expect(validarContraSchema($xml, 'DPS_v1.01.xsd'))->toBeTrue()
        ->and($xml)->toContain('<imovel><cMun>3550308</cMun><gUnidImob><cCIB>12345678</cCIB></gUnidImob></imovel>');
});
