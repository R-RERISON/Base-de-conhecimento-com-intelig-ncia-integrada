# Prompt de Continuidade — SPEC-001 em implementação

## Referência versionada

- Repositório: `R-RERISON/Base-de-conhecimento-com-intelig-ncia-integrada`
- Branch: `main`
- SPEC ativa: `SPEC-001 — Core mínimo + Summary narrativo`
- Estado: **Em implementação / Evidência S003**.
- Confirme o HEAD atual antes de qualquer nova alteração.

## Estado comprovado

- SPEC-000 está CONCLUÍDA.
- T097 autorizou SPEC-001, não release/produção/cutover.
- S001/Definition of Ready está PASS documental.
- Runtime mínimo S002 implementado.
- Suíte unitária: 15 PASS / 0 FAIL.
- PHP lint do runtime: PASS.
- Instalação inicial do plugin em WordPress real: PASS visual.
- Menu **Base de Conhecimento**: PASS visual.
- Listagem de posts reais: PASS visual.
- Leitura real das metas GRE existentes em `post_id=36431`: PASS visual sem migração.
- Harness PHPUnit WordPress real: PREPARADO; execução ainda NOT_RUN.
- Runner onclick técnico v2: IMPLEMENTADO; execução no ambiente alvo NOT_RUN.
- Browser acceptance guiado: IMPLEMENTADO; execução manual NOT_RUN.
- G-130/package final: NOT_RUN.

## Regra operacional do ambiente

O operador de homologação **não possui acesso ao `wp-config.php`**. Portanto nenhum gate/teste pode depender de configuração externa ao plugin.

### Build de homologação autossuficiente

O package `0.1.0-dev.2` declara internamente:

```php
define( 'BDC_KB_HOMOLOGATION_BUILD', true );
define( 'BDC_KB_ENABLE_DIAGNOSTICS', true );
```

As ferramentas temporárias aparecem automaticamente para `manage_options` na tela Base de Conhecimento.

Não solicitar edição de `wp-config.php`.

## Runtime de produto

- `base-conhecimento-inteligencia-integrada.php`
- `includes/class-plugin.php`
- `includes/class-meta-contract.php`
- `includes/class-summary-store.php`
- `includes/class-admin-page.php`
- `assets/css/admin.css`

## Ferramentas temporárias do build de homologação

- `includes/class-diagnostics-runner.php`
- `includes/class-browser-acceptance.php`

Elas não são funcionalidades permanentes do produto.

## Onclick técnico v2

- schema JSON 1.1.0;
- marker `_bdc_kb_diagnostic_fixture=spec001-onclick-v2`;
- exige `manage_options` + POST + nonce;
- cria somente fixtures efêmeras marcadas;
- cleanup em lotes;
- resultado global PASS exige zero FAIL e zero resíduos;
- cobre G-001, G-020, parte de G-070 e B-006;
- não persiste relatório.

## Browser acceptance temporário

- exige `manage_options` + POST + nonce;
- coleta oito checks G-110 observados manualmente;
- gera JSON baixável;
- não persiste respostas no WordPress;
- não é automação E2E.

## Matriz atual

- T040 unitário: PASS 15/15.
- instalação inicial/activation smoke: PASS visual.
- leitura real das metas existentes: PASS visual.
- T041 integração WordPress: NOT_RUN.
- G-001: NOT_RUN para write; leitura real preservada visualmente.
- G-020: NOT_RUN para write.
- G-070: NOT_RUN.
- T042/B-006 integração: NOT_RUN; unit PASS.
- G-110: NOT_RUN.
- G-130: NOT_RUN.

## Próximo passo exato

1. instalar/substituir pelo package de homologação `0.1.0-dev.2`;
2. confirmar versão `0.1.0-dev.2` e ausência de fatal error;
3. abrir Base de Conhecimento;
4. executar **Executar diagnóstico e gerar JSON**;
5. enviar o JSON técnico para revisão;
6. exigir `summary.overall=PASS` e `cleanup.residual_fixtures=0` antes de qualquer promoção;
7. somente depois executar a jornada real controlada e gerar o JSON de browser acceptance;
8. corrigir qualquer FAIL antes de avançar.

## Regra de limpeza antes do release

T044/G-130 deve remover integralmente:

- `BDC_KB_HOMOLOGATION_BUILD`;
- `BDC_KB_ENABLE_DIAGNOSTICS`;
- `class-diagnostics-runner.php`;
- `class-browser-acceptance.php`;
- requires e hooks temporários;
- qualquer fixture `_bdc_kb_diagnostic_fixture=spec001-onclick-v2`.

Depois repetir lint/regressão e comprovar que o package final não contém instrumentos de teste.

## Regra

Não antecipar Classificação, Review, Search, Analytics, IA, queue, schema, REST/AJAX/SPA ou cutover. Constituição, Manifesto, T097, SPEC-001, código e evidências versionadas prevalecem sobre memória de chat.
