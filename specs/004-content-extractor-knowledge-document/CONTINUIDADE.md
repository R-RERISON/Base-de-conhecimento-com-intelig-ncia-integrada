# Continuidade — SPEC-004 Content Extractor e Knowledge Document

## Estado atual

- SPEC-003: concluída.
- baseline consolidado em `main`: commit `a676f8daaaf9a794d503ccd6a2c28178be1fb8bf`.
- R-200: **PASS**.
- R-210: **PASS**.
- G-220 — Content Extractor: **PASS ambiental**.
- G-230/v1 — Knowledge Document determinístico: **PASS ambiental**; v1 permanece `SUPERSEDED_FOR_AI` após G-240 v1.
- G-240/v1 — Real Content Acceptance: **FAIL CONTROLADO — perda estrutural**.
- G-240/v2 — remediação ativa; `acceptance.10` isolou definitivamente a perda em HTML/DOM → fragments.
- candidato atual: **`0.4.0-acceptance.11` / Knowledge Document `2.0.1` / smoke `1.3.0`**.
- branch: `spec004-g240-real-content-acceptance`.
- PR #3: **DRAFT / NÃO MERGEAR** antes de G-240 v2 PASS.
- G-245 — Elementor/produção: **BLOCKED por G-240**; writer proibido.
- G-250: **NOT_RUN**.

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
- `structure_incomplete=0` continua obrigatório;
- `ai_readiness.not_ready=0` também é obrigatório.

## Histórico da remediação G-240 v2

- `acceptance.3`: 82 `structure_incomplete`.
- `acceptance.4`: 78; eliminada classe de colisão de IDs.
- `acceptance.5`: 50; `list_items` reconciliados por structural anchor.
- `acceptance.6`: 50; hipótese de wrappers históricos como causa dominante rejeitada.
- `acceptance.7`: 5; headings zerados após expectativa DOM semântica `2.0.1`.
- `acceptance.8`: 5; telemetria separou listas/tabelas residuais.
- `acceptance.9`: 5; contexto mostrou 4 legacy + 1 Elementor, sem resíduos em pre/code/heading.
- `acceptance.10`: diagnóstico pipeline comprovou que `raw_expected == unwrapped_expected` e que `fragment containers == blocks` nos cinco casos.

### Conclusão do `acceptance.10`

A perda não ocorre em shortcode unwrap nem em `Semantic_Structure`. Ela ocorre exclusivamente na materialização HTML/DOM → fragments pelo `Legacy_HTML_Adapter`:

- listas: 199 expected / 184 fragments / 184 blocks;
- tabelas: 13 expected / 11 fragments / 11 blocks.

Evidência: `evidence/pipeline-diag-20260916T143948Z.json`.

## Candidato `acceptance.11`

Correção runtime limitada ao `Legacy_HTML_Adapter`:

1. percorrer wrappers internos recursivamente até a primeira fronteira `ul|ol|table` dentro de `li`, `p` e `blockquote`;
2. ao alcançar lista/tabela, delegar a subárvore ao adapter específico e não descer novamente nela, evitando duplicidade;
3. preservar `alt` de imagens dentro de células de tabela como texto semântico, evitando desaparecimento de tabela image-only;
4. nenhum ajuste em schema, hashes, readiness ou gate.

Package: `package-acceptance11.md`.

SHA-256: `c96e91bb5947d882ae636c0cc2a4bd1e9f08f05efe29dc1c6d6164908cbe81e1`.

Git↔package parity:

- bootstrap `653b4c9915a4abafb17fa2e3ffa474f66b05dbb5`;
- Legacy adapter `d1b606ed24c9e1963981f4aeec3931a3d56c8894`.

## Sequência obrigatória atual

1. instalar/substituir pelo `0.4.0-acceptance.11` em homologação;
2. executar somente **Base de Conhecimento → Validação KD v2**;
3. não executar ainda Aceitação G-240 v2;
4. exigir `structure_incomplete=0` e `not_ready=0`, além dos gates de segurança/determinismo;
5. somente depois repetir os mesmos oito casos A/B humanos;
6. somente G-240 v2 PASS libera G-245.

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
- G-240/v2: **FAIL CONTROLADO / `acceptance.11` ENV SMOKE PENDING**.
- G-245: **BLOCKED**.
- G-250: **NOT_RUN**.
