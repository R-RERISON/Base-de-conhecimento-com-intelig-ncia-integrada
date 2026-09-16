# Package — G-245 Production Preflight 1

## Build

`0.4.0-g245-preflight.1`

## Objetivo

Executar somente o primeiro subgate de G-245: **Production Preflight read-only**.

Este package não contém autorização de migration e mantém:

- `writer_allowed=false`;
- `migration_execution_allowed=false`;
- zero escrita em `post_content`;
- zero escrita em `_elementor_data`;
- zero execução de shortcodes;
- zero chamada externa/loopback;
- zero persistência do relatório.

## Build flags

- `BDC_KB_SPEC004_G240_ACCEPTANCE_BUILD=false`
- `BDC_KB_SPEC004_KD_V2_SMOKE_BUILD=false`
- `BDC_KB_SPEC004_G245_PREFLIGHT_BUILD=true`

Os runners temporários do gate anterior deixam de ficar ativos neste package.

## Arquivos novos/alterados no plugin

1. `base-conhecimento-inteligencia-integrada.php`
2. `includes/class-production-preflight.php`

Git blob SHA esperado:

- bootstrap: `4d47f347293e7a4888585652dc8538ed7067daae`
- preflight: `19b78adc1a35cfc16da1a8ffdd44d942bea4fc33`

## Validação local do package

- PHP lint: **29/29 PASS**;
- ZIP integrity: **PASS**;
- WordPress root: `base-conhecimento-inteligencia-integrada/`;
- plugin header: `0.4.0-g245-preflight.1`;
- paridade bootstrap com Git: PASS;
- paridade preflight com Git: PASS.

SHA-256 do ZIP:

`e826cfbb0d15e8dbaf1800ec0c2ea0f5192e5da6461b00c0370ea99a898865b4`

## Execução em homologação

1. instalar/atualizar o plugin com o package `0.4.0-g245-preflight.1`;
2. confirmar a versão do plugin;
3. abrir **Base de Conhecimento → Preflight G-245**;
4. selecionar **Homologação**;
5. não é necessário marcar confirmação de backup para esse primeiro preflight de homologação;
6. clicar em **Executar preflight read-only e baixar JSON**;
7. retornar o arquivo `bdc-kb-spec004-g245-preflight-*.json`.

## O que será coletado

- versões WordPress/PHP/plugin/Elementor;
- DOMDocument;
- multisite;
- memory limit / max execution time;
- estado de WP-Cron;
- engine/versão de banco;
- plugins ativos e versões;
- tags de shortcode efetivamente observadas em `post_content` e `_elementor_data`;
- handlers registrados e provider aproximado (`plugin:<slug>`, `wordpress-core`, `unknown`);
- fingerprint agregado do corpus antes/depois para provar ausência de mutação.

Nenhum corpo editorial é exportado.

## Política inicial

- Elementor `4.1.0`: versão inicialmente homologada;
- versão Elementor diferente: `review_required`;
- Elementor ausente: `blocking` para migration;
- produção sem backup confirmado: `blocking`;
- shortcode usado sem handler: `review_required`;
- provider de shortcode não resolvido: `review_required`;
- WP-Cron desabilitado: `review_required`;
- loopback: `review_required` porque o v1 deliberadamente não executa rede.

## Resultado esperado desta rodada

O objetivo não é obter G-245 PASS. O objetivo é obter uma fotografia confiável do ambiente e dos gaps reais para congelar a matriz de compatibilidade e desenhar o Projection Plan read-only.
