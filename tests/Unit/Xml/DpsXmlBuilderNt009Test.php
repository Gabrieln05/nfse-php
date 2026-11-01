<?php

namespace Nfse\Tests\Unit\Xml;

use Nfse\Dto\Nfse\DpsData;
use Nfse\Dto\Nfse\IbscbsData;
use Nfse\Support\IdGenerator;
use Nfse\Xml\DpsXmlBuilder;
use Nfse\Xml\NfseXmlParser;

function dpsLeiauteNt009(?string $finNfse, ?string $motivoEmisTI = null): DpsData
{
    return new DpsData([
        '@attributes' => ['versao' => '1.01'],
        'infDPS' => [
            '@attributes' => ['Id' => IdGenerator::generateDpsId('12345678000199', '3550308', '1', '1001')],
            'tpAmb' => 2,
            'dhEmi' => '2026-10-28T10:00:00-03:00',
            'verAplic' => '1.0',
            'serie' => '1',
            'nDPS' => '1001',
            'dCompet' => '2026-10-28',
            'tpEmit' => 1,
            'cMotivoEmisTI' => $motivoEmisTI,
            'finNFSe' => $finNfse,
            'cLocEmi' => '3550308',
            'prest' => [
                'CNPJ' => '12345678000199',
                'IM' => '12345',
                'regTrib' => [
                    'opSimpNac' => 3,
                    'regApTribSN' => 1,
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
                    'cTribNac' => '010701',
                    'xDescServ' => 'Hospedagem de sites',
                    'cNBS' => '115031000',
                ],
            ],
            'valores' => [
                'vServPrest' => ['vServ' => 100.00],
                'trib' => [
                    'tribMun.tribISSQN' => 1,
                    'tribMun.tpRetISSQN' => 1,
                    'totTrib.vTotTrib.vTotTribFed' => 0,
                    'totTrib.vTotTrib.vTotTribEst' => 0,
                    'totTrib.vTotTrib.vTotTribMun' => 0,
                ],
            ],
        ],
    ]);
}

it('always writes the NT 009 layout', function () {
    $xml = (new DpsXmlBuilder)->build(dpsLeiauteNt009(null));

    expect($xml)->toContain('<tpEmit>1</tpEmit><finNFSe>0</finNFSe><cLocEmi>3550308</cLocEmi>')
        ->and($xml)->toContain('<cNBS>115031000</cNBS><xDescServ>Hospedagem de sites</xDescServ></cServ>')
        ->and(substr_count($xml, '<finNFSe>'))->toBe(1)
        ->and(validarContraSchema($xml, 'DPS_v1.01.xsd'))->toBeTrue();
});

it('writes finNFSe after cMotivoEmisTI', function () {
    $xml = (new DpsXmlBuilder)->build(dpsLeiauteNt009('0', '4'));

    expect($xml)->toContain('<tpEmit>1</tpEmit><cMotivoEmisTI>4</cMotivoEmisTI><finNFSe>0</finNFSe><cLocEmi>');
});

it('reads the IBS and CBS classification back from the NT 009 layout', function () {
    $dps = dpsLeiauteNt009('0');
    $dps->infDps->ibscbs = new IbscbsData([
        'indDest' => 0,
        'valores' => ['trib' => ['CST' => '200', 'cClassTrib' => '200046']],
        'imovel' => ['cMun' => '3550308', 'gUnidImob' => ['end' => ['CEP' => '01001000', 'xLgr' => 'Rua A', 'nro' => '1']]],
    ]);
    $xml = (new DpsXmlBuilder)->build($dps);

    $nfse = (new NfseXmlParser)->parse('<NFSe xmlns="http://www.sped.fazenda.gov.br/nfse" versao="1.01"><infNFSe Id="NFS123">'.$xml.'</infNFSe></NFSe>');
    $lido = $nfse->infNfse->dps->infDps;

    expect($lido->finalidadeNfse)->toBe('0')
        ->and($lido->ibscbs->cst)->toBe('200')
        ->and($lido->ibscbs->codigoClassificacaoTributaria)->toBe('200046')
        ->and($lido->ibscbs->imovel->unidades[0]->endereco->cep)->toBe('01001000');
});
