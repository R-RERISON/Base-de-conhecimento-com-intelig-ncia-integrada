# Continuidade — SPEC-004 Content Extractor e Knowledge Document

## Estado atual

- SPEC-003: concluída.
- baseline consolidado em `main`: commit `a676f8daaaf9a794d503ccd6a2c28178be1fb8bf`.
- R-200: **PASS**.
- R-210: **PASS**.
- G-220 — Content Extractor: **PASS ambiental**.
- G-230/v1 — Knowledge Document determinístico: **PASS ambiental**; v1 permanece `SUPERSEDED_FOR_AI` após G-240 v1.
- G-240/v1 — Real Content Acceptance: **FAIL CONTROLADO — perda estrutural**.
- G-240/v2 full-corpus — **PASS ambiental** no `0.4.0-acceptance.11`.
- G-240/v2 humano A/B — **PENDING**.
- build atual: **`0.4.0-acceptance.11`**, KD `2.0.1`, smoke `1.3.0`.
- branch: `spec004-g240-real-content-acceptance`.
- PR #3: **DRAFT / NÃO MERGEAR** antes do aceite humano G-240 v2 PASS.
- G-245 — Elementor/produção: **BLOCKED pelo aceite humano G-240**; writer proibido.
- G-250: **NOT_RUN**.

## Full-corpus v2 — PASS em 2026-09-16

Evidência: `evidence/kd-v2-smoke-20260916T150651Z.json`.

Ambiente:

- WordPress `6.9.4`;
- PHP `8.5.10`;
- Elementor `4.1.0`;
- Knowledge Document `2.0.1`;
- `DOMDocument=true`.

Segurança/determinismo:

- corpus `622 → 622`;
- fingerprint editorial before/after idêntico;
- zero posts alterados;
- duas passagens completas `622/622`;
- zero errors/throwables;
- zero hash mismatch;
- zero canonical JSON mismatch;
- `structure_incomplete=0` nas duas passagens;
- `ai_readiness.not_ready=0`;
- `gate.pass=true`.

AI readiness final do corpus:

- `candidate_ready=548`;
- `review_required=72`;
- `not_ready=0`;
- `not_applicable=2`.

Os `review_required` são limitações explícitas/auditáveis, principalmente shortcodes não expandidos, headings locais achatados e listas locais dentro de tabelas; não representam perda estrutural silenciosa.

## Causa raiz final comprovada

O diagnóstico pipeline `0.4.0-acceptance.10` mostrou:

- listas: raw expected `199` = unwrapped expected `199`; fragments `184` = blocks `184`;
- tabelas: raw expected `13` = unwrapped expected `13`; fragments `11` = blocks `11`.

Conclusão:

- `Shortcode_Inspector::unwrap_without_execution()` não era a causa;
- `Semantic_Structure` não era a causa;
- o resíduo ocorria exclusivamente na materialização HTML/DOM → fragments pelo `Legacy_HTML_Adapter`.

O `acceptance.11` corrigiu:

1. travessia recursiva em `li`, `p` e `blockquote` até a primeira fronteira estrutural `ul|ol|table`;
2. preservação de `alt` de imagem dentro de células de tabela como texto semântico.

Após isso, o full-corpus passou integralmente.

## Contratos ativos

- `knowledge-document-contract-v2.md` — base `2.0.0`.
- `knowledge-document-contract-v2.0.1-amendment.md` — patch semântico ativo para `2.0.1`.

Invariantes preservados:

- construção read-only;
- nenhuma persistência de documento/hash/cache/progresso durante validação;
- nenhum write em `post_content`, `_elementor_data`, status, revisão ou publicação;
- nenhum `do_shortcode()` genérico;
- nenhum `render_block()` ou renderização dinâmica arbitrária;
- nenhuma dependência de IA, Foundry, embeddings ou vetores para parsing;
- `structure_incomplete=0` obrigatório;
- `ai_readiness.not_ready=0` obrigatório.

## Próximo passo obrigatório

Executar agora **Base de Conhecimento → Aceitação G-240 v2** no mesmo build `0.4.0-acceptance.11`.

A ferramenta usa exatamente os mesmos oito posts congelados do G-240 v1 e quatro critérios humanos observáveis:

1. cobertura completa;
2. ordem semântica preservada;
3. nenhum texto inventado;
4. estrutura semântica preservada.

`AI readiness` é calculado pelo sistema e não é checkbox humano.

Critérios para G-240 v2 PASS:

- todos os oito slots disponíveis e não stale;
- zero selection mismatch;
- zero repeatability failure;
- quatro critérios humanos aprovados em cada slot aplicável;
- limitações conhecidas refletidas em `ai_readiness`/warnings;
- zero write editorial durante a geração da evidência.

Arquivo esperado: `bdc-kb-spec004-g240-v2-acceptance-*.json`.

## Produção / Elementor

Permanece inalterado:

- Elementor é direção editorial futura;
- Knowledge plane é multi-source e read-only;
- install/activation/update nunca migra posts automaticamente;
- migration editorial exige Production Preflight, version gate, dry-run, journal/rollback, stale-source guard, canário e batches retomáveis;
- writer continua desabilitado.

## Gates

- R-200: **PASS**.
- R-210: **PASS**.
- G-220: **PASS**.
- G-230/v1: **PASS determinístico**.
- G-240/v1: **FAIL CONTROLADO — STRUCTURE LOSS**.
- G-240/v2 full-corpus: **PASS**.
- G-240/v2 humano A/B: **PENDING**.
- G-245: **BLOCKED**.
- G-250: **NOT_RUN**.
