# Continuidade — SPEC-004 Content Extractor e Knowledge Document

## Estado atual

- SPEC-001/002/003: concluídas.
- R-200: PASS.
- R-210: PASS.
- G-220: PASS ambiental.
- G-230/v1: PASS de determinismo; v1 superseded for AI.
- G-240: **PASS / CLOSED / promovido para `main`**.
- merge G-240: `32a696386bf2ab5574d4d7725db78636fa51f36c`.
- baseline institucional `main`: `422de89f5e341204b7549116cc2022fbc978f3ab`.
- KD 2.1.0 / `0.4.0-acceptance.12`: PASS técnico full-corpus + PASS humano 8/8.
- **G-245: IN PROGRESS em `spec004-g245-production-readiness`; PR #4 DRAFT / NÃO MERGEAR.**
- T080 Production Preflight: **PASS WITH REVIEW ITEMS**.
- T081 Projection Plan: **LOCAL READY / ENVIRONMENTAL PENDING**.
- build T081 atual: `0.4.0-g245-projection.2`.
- writer/migration Elementor permanecem não autorizados.

## Baseline comprovada

A `main` contém somente a baseline aceita até G-240. A branch G-245 foi sincronizada com `main` sem perder o trabalho em andamento e sem reintroduzir versões antigas de Manifesto, README, SPEC ou Roadmap.

G-240 permanece comprovado por:

- `evidence/kd-v21-smoke-summary-20260916T172538Z.json`;
- `evidence/g240-kd21-acceptance-20260916T193359Z.json`;
- duas passagens 622/622;
- zero errors/throwables/hash/canonical mismatches;
- zero mutação editorial;
- aceite humano 8/8.

## G-245 / T080

Production Preflight read-only executado em homologação:

- WordPress 6.9.4;
- PHP 8.5.10;
- Elementor 4.1.0;
- MariaDB 12.2.2;
- corpus 622 posts;
- blockers: 0;
- review items: `faq_wd`, `wpt` e loopback não testado;
- corpus/fingerprint editorial preservados;
- zero execução de shortcode;
- zero rede externa;
- zero persistência;
- `writer_allowed=false`;
- `migration_execution_allowed=false`.

Evidência: `evidence/g245-preflight-summary-20260916T215612Z.json`.

## G-245 / T081 — trabalho concluído localmente

Contrato: `elementor-projection-plan-contract-v1.md`.

Implementação atual:

- Projection Plan derivado de KD 2.1.0 e `source_hash_before`;
- determinístico e canonicalizado;
- sem LLM, embeddings, render dinâmico ou `do_shortcode()`;
- dependências de shortcode tratadas como opacas;
- `faq_wd`, `wpt` e handlers ausentes exigem review;
- Gutenberg dinâmico/unsupported, widget Elementor unsupported, JSON Elementor inválido e source oversize hard exigem review;
- `SHORTCODE_NOT_EXPANDED` com handler registrado não força review sozinho;
- `projection_hash` deve ser SHA-256 canônico;
- `writer_allowed=false` e todos os safety flags estritamente false.

Validação local:

- 58 assertions PASS em `tests/unit/spec004-projection-plan-v1.php`;
- lint PHP PASS nos artefatos alterados;
- repetibilidade por ordenação de dependências PASS;
- mudança de `source_hash` altera `projection_hash` como esperado.

O runner `Elementor_Projection_Plan_Smoke` foi endurecido e agora bloqueia T081 se detectar:

- hash de projeção inválido;
- writer permitido;
- qualquer safety flag não read-only;
- `faq_wd`/`wpt` sem review;
- warning estrutural/dinâmico que deveria exigir review mas não exige;
- diferença entre hashes ou JSON canônico das duas passagens;
- mutação do corpus/fingerprint editorial.

## Próximo passo exato — T081I/T081J

Executar a build `0.4.0-g245-projection.2` no WordPress de homologação e baixar o JSON produzido por **Projection Plan G-245**.

T081 só pode fechar se a evidência demonstrar simultaneamente:

1. corpus 622 → 622;
2. primeira passagem 622/622;
3. segunda passagem 622/622;
4. errors/throwables = 0;
5. projection hash mismatches = 0;
6. canonical JSON mismatches = 0;
7. projection-hash violations = 0 nas duas passagens;
8. writer/safety violations = 0 nas duas passagens;
9. legacy-shortcode review violations = 0;
10. migration-warning review violations = 0;
11. fingerprint editorial before/after idêntico;
12. changed posts = 0;
13. `gate.t081_pass=true`.

Após receber a evidência ambiental, versionar o resumo no repositório e somente então decidir o fechamento formal de T081.

## Próximos subgates — ainda proibidos de avançar como writer

T082 Elementor Gateway version-gated → T083 journal/rollback → T084 stale-source guard → T085 dry-run → T086 batches retomáveis → T087 canário controlado → T088 runbook → T089 autorização explícita posterior para writer real.

Nenhum desses passos autoriza gravação em `post_content` ou `_elementor_data` por antecipação.

## Guardrails preservados

- WordPress/Elementor continuam fonte editorial;
- Knowledge Document e Projection Plan são derivados reconstruíveis;
- nenhum writer Elementor está autorizado;
- nenhuma migration Elementor está autorizada;
- nenhuma persistência de KD/Projection Plan foi autorizada;
- produção não é ambiente experimental;
- GO de homologação != GO de produção;
- PR #4 permanece DRAFT enquanto T081 ambiental estiver aberto.

> Quem não sabe onde está, não sabe para onde quer ir.
