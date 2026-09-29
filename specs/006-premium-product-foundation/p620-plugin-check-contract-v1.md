# P-620 — WordPress Plugin Check Contract v1

**Status:** ACTIVE
**Data:** 2026-09-29
**SPEC:** 006 Premium Product Foundation

## Objetivo

Executar o WordPress Plugin Check oficial contra a superfície atual do plugin e classificar findings antes de modularização/package pruning.

## Ferramenta canônica

- ação oficial: `wordpress/plugin-check-action@v1`;
- WordPress: latest;
- build-dir: `plugin/base-conhecimento-inteligencia-integrada`;
- checks estáveis;
- experimental checks desabilitados neste gate inicial;
- sem ignore-codes;
- sem ignore-errors;
- sem ignore-warnings.

## Categorias de disposição

Cada finding deve ser classificado como:

1. **BLOCKER_P620**
   - metadata/readme/license inválidos;
   - erro de segurança objetivo;
   - API/proibição incompatível com distribuição;
   - fatal/runtime defect;
   - arquivo proibido na superfície atual.

2. **P640_RUNTIME_REFACTOR**
   - finding ligado ao bootstrap monolítico;
   - runner/laboratório carregado ou empacotado indevidamente;
   - composição modular inexistente.

3. **P650_PACKAGE_PRUNING**
   - arquivo de engenharia/homologação que não deve existir no ZIP production;
   - documentação interna/evidência/test harness em package.

4. **P660_SECURITY_PRIVACY**
   - escaping/capability/privacy finding que exige análise semântica;
   - não pode ser silenciado apenas como estilo.

5. **LEGACY_STYLE_DEBT**
   - PHPCS/WPCS puramente histórico já inventariado em P-610;
   - não autoriza waiver permanente da superfície production.

6. **FALSE_POSITIVE_WITH_EVIDENCE**
   - somente com evidência específica;
   - proibido criar lista genérica de ignores.

## Regra

P-620 não exige corrigir toda a dívida histórica antes de P-640/P-650.

P-620 fecha quando:
- Plugin Check oficial executa;
- todos os findings relevantes são inventariados;
- blockers imediatos seguros são corrigidos;
- findings de runtime/package são encaminhados para seus gates proprietários;
- nenhuma severidade é ocultada por ignore global.

## Invariantes

- não alterar Search Ranker;
- não reabrir SPEC-005;
- não executar PHPCBF global;
- não autorizar ASI retirement;
- não declarar Plugin Check clean se o pacote atual ainda contiver findings.
