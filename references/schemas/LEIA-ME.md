# Esquemas

Base: `esquemas_prod_restrita_20261020.zip`, o XSD mais novo publicado pelo portal da NFS-e Nacional.

**Estes arquivos não são mais iguais aos oficiais.** A NT 009 v1.01 e o Anexo VI v1.04.01 trazem mudanças que nenhum XSD
publicado tem. Para gerar e validar a DPS desse leiaute, `tiposComplexos_v1.01.xsd` e `tiposSimples_v1.01.xsd` receberam,
à mão, só o que está abaixo. Cada trecho está marcado com `NÃO OFICIAL (Anexo VI v1.04.01)`.

- `indDest` e `dest` saem de `IBSCBS` e vão para a raiz de `infDPS`, depois de `toma` (NT 009 v1.01, item 2.11).
- Grupo `condominios` em `IBSCBS`, depois de `bensMoveis` (item 2.15), com os tipos e domínios dele.

O nome dos tipos novos é nosso, e a ordem e as ocorrências vêm das linhas 178, 179 e 407 a 420 do leiaute no Anexo VI.

Quando o XSD oficial com esse leiaute sair, ele substitui estes arquivos e este aviso some.
