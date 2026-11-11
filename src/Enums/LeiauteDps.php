<?php

namespace Nfse\Enums;

/**
 * Onde a DPS leva indDest e dest.
 *
 * - V101: na raiz de infDPS, depois de toma (NT 009 v1.01, item 2.11). É o leiaute de produção a
 *   partir de 01/12/2026, e o de `references/schemas`.
 * - Release1: dentro de IBSCBS, depois de tpEnteGov, como no XSD de Produção Restrita de
 *   20/10/2026 (`references/schemas-release1`). Enquanto o release 2 não chega lá, a Sefin de
 *   Produção Restrita recusa o V101 com E1235.
 *
 * O resto da DPS é igual nos dois. O grupo `condominios` só existe no V101: no Release1 ele sai
 * como veio, e a Sefin recusa pelo esquema.
 */
enum LeiauteDps: string
{
    case V101 = 'v1.01';
    case Release1 = 'release1';
}
