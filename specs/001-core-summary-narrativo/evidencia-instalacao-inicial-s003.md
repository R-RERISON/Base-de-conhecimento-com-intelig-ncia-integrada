# Evidência S003 — Instalação inicial e leitura real

## Estado

**PASS parcial de smoke/instalação.**

Esta evidência não promove, sozinha, G-001/G-020/G-070/G-110/B-006 para PASS. Ela comprova somente instalação/ativação, bootstrap administrativo, listagem real e leitura dos três metadados existentes.

## Ambiente observado

- Plugin: **Base de Conhecimento com Inteligência Integrada**
- Versão exibida: `0.1.0-dev`
- Estado: ativo no WordPress (`Desativar` disponível na listagem de plugins)
- Menu `Base de Conhecimento`: presente no `wp-admin`
- Tela principal: carregada sem fatal error
- Listagem: posts reais exibidos com status, data, ID e ação `Editar Summary`
- Coexistência visual: plugins legados permanecem ativos/presentes no mesmo ambiente sem colisão visível nesta carga inicial

## Leitura real comprovada

Foi aberto o artigo real:

- Título: `Mensageria`
- Post ID: `36431`

A tela de Summary carregou valores preexistentes nos três campos autorizados:

- `objective` / `_bdc_es_objective`
- `escalation` / `_bdc_es_escalation`
- `important` / `_bdc_es_important`

Isso comprova, no ambiente alvo, que o novo plugin consegue **ler diretamente os metadados legados reutilizados**, sem migration e sem dual-write para esta jornada de leitura.

## Limites da evidência

Ainda NÃO foi executado save pelo novo plugin sobre artigo real nesta etapa.

Portanto permanecem abertos:

- preservação editorial após write;
- POST/nonce/PRG real;
- tratamento B-006 em WordPress real;
- IDOR/capability negativa via handler real;
- browser acceptance G-110 completo;
- lifecycle/package G-130.

## Decisão operacional

Próxima sequência correta:

1. manter o artigo real sem alteração manual enquanto o diagnóstico técnico é preparado;
2. habilitar temporariamente `BDC_KB_ENABLE_DIAGNOSTICS=true`;
3. executar primeiro o onclick v2 em fixtures próprias;
4. exigir `summary.overall=PASS` e `cleanup.residual_fixtures=0`;
5. somente depois executar save controlado/browser acceptance;
6. desabilitar a flag de diagnóstico imediatamente após coleta.

## Regra

Esta evidência é de homologação inicial. Não é autorização de produção, cutover ou remoção dos plugins legados.
