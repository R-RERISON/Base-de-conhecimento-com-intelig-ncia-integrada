# Package G-240 — `0.4.0-acceptance.1`

## Objetivo

Artefato temporário de homologação para o Gate G-240 — Real Content Acceptance.

Este package não é RC nem build de produção. Ele existe somente para inspeção humana controlada da fidelidade `fonte editorial → Knowledge Document`.

## Identificação

- versão: `0.4.0-acceptance.1`;
- arquivo: `base-conhecimento-inteligencia-integrada-0.4.0-acceptance.1.zip`;
- SHA-256: `8391a0c2ace748711087c327a48d63aa2fa009963c2d4584488d2c1ed5679d82`;
- arquivos runtime no ZIP: `25`;
- PHP: `21` arquivos;
- JavaScript: `1` arquivo.

## Build flags

- `BDC_KB_SPEC004_PROFILE_BUILD=false`;
- `BDC_KB_SPEC004_G220_SMOKE_BUILD=false`;
- `BDC_KB_SPEC004_G230_SMOKE_BUILD=false`;
- `BDC_KB_SPEC004_G240_ACCEPTANCE_BUILD=true`.

## Ferramenta temporária habilitada

`Real_Content_Acceptance`

Menu:

**Base de Conhecimento → Aceitação G-240**

Controles:

- `manage_options`;
- POST + nonce para exportação da evidência;
- read-only;
- sem persistência de amostra/vereditos/documentos/hashes;
- sem chamadas de rede/IA;
- sem `do_shortcode()`;
- sem `render_block()`;
- sem renderer Elementor;
- sem primitives de write editorial/options/transients.

## Seleção / segurança

A ferramenta seleciona até oito slots determinísticos do corpus.

A versão empacotada mantém no primeiro passe somente metadados estruturais leves e materializa fonte/documento completos apenas para os slots escolhidos.

No submit:

- a amostra é recalculada;
- divergência entre slot esperado e enviado vira `selection_mismatches`;
- fingerprint editorial por post implementa stale guard;
- Knowledge Document é reconstruído duas vezes para repeatability;
- fingerprint da amostra é comparado before/after;
- nenhuma divergência é reconciliada automaticamente.

## Paridade Git/package

Arquivos críticos:

- `includes/class-real-content-acceptance.php`
  - Git blob branch: `5b88be5c9a3283e2055aea81950fdffed82f79f8`;
  - Git blob ZIP extraído: `5b88be5c9a3283e2055aea81950fdffed82f79f8`;
- `base-conhecimento-inteligencia-integrada.php`
  - Git blob branch: `49ea1b55b7535914c69c62f9434ae208b3970006`;
  - Git blob ZIP extraído: `49ea1b55b7535914c69c62f9434ae208b3970006`.

Paridade completa staging → ZIP extraído:

- `25/25 PASS`;
- mismatches: `0`.

## Validação do artefato

- PHP lint staging: `21/21 PASS`;
- PHP lint ZIP extraído: `21/21 PASS`;
- JS syntax: `1/1 PASS`;
- `unzip -t`: PASS;
- staging/ZIP source parity: `25/25 PASS`;
- acceptance safety scan: PASS.

## Limpeza de tooling anterior

Fisicamente ausentes do ZIP:

- `includes/class-content-profile.php`;
- `includes/class-content-extractor-smoke.php`;
- `includes/class-knowledge-document-smoke.php`.

Portanto Profiler R-200, Smoke G-220 e Smoke G-230 não podem ser carregados pelo package mesmo por alteração acidental de flag.

## Gate seguinte

Este package não fecha G-240 sozinho.

É necessário executar a revisão humana em homologação e retornar:

`bdc-kb-spec004-g240-acceptance-*.json`

PASS exige todos os slots disponíveis aprovados, `selection_mismatches=0`, `stale_slots=0`, `repeatability_failures=0`, fingerprint editorial equal e zero changed posts durante geração do relatório.
