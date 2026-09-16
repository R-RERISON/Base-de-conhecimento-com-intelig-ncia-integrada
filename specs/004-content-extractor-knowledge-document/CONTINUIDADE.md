# Continuidade — SPEC-004 Content Extractor e Knowledge Document

## Estado atual

- SPEC-003: concluída.
- R-200: PASS.
- R-210: PASS.
- G-220: PASS ambiental.
- G-230/v1: PASS de determinismo; v1 superseded for AI.
- G-240/v1: FAIL CONTROLADO — perda estrutural.
- G-240/v2/KD 2.0.1: full-corpus técnico PASS, porém aceite humano A/B **FAIL CONTROLADO — HIERARCHY FIDELITY**.
- KD 2.1.0 / build `0.4.0-acceptance.12`: **PASS técnico full-corpus + PASS humano 8/8**.
- **G-240: CLOSED / PASS.**
- evidência técnica resumida: `evidence/kd-v21-smoke-summary-20260916T172538Z.json`.
- evidência humana final: `evidence/g240-kd21-acceptance-20260916T193359Z.json`.
- contrato KD 2.1.0 congelado: `knowledge-document-contract-v2.1.0.md`.
- PR #3 permanece DRAFT/NÃO MERGEAR até revisão do fechamento e preparação segura do próximo gate.
- **G-245: READY — ainda não iniciado.**
- writer/migration Elementor permanecem disabled-by-default.

## Evidência técnica que fecha T079N

Ambiente de homologação:

- WordPress 6.9.4;
- PHP 8.5.10;
- Elementor 4.1.0;
- plugin `0.4.0-acceptance.12`;
- Knowledge Document schema `2.1.0`;
- DOMDocument ativo.

Full-corpus:

- corpus 622 → 622;
- primeira passagem: 622/622;
- segunda passagem: 622/622;
- errors: 0;
- throwables: 0;
- hash mismatches: 0;
- canonical JSON mismatches: 0;
- structure_incomplete: 0 nas duas passagens;
- `not_ready`: 0;
- fingerprint editorial antes/depois idêntico;
- changed posts: 0;
- gate técnico: PASS.

Readiness observado:

- candidate_ready: 387;
- review_required: 233;
- not_applicable: 2;
- not_ready: 0.

O resolver numérico permaneceu deliberadamente conservador. Foram observados 6 conflitos de numeração e 964 ocorrências agregadas de ambiguidade. Esses sinais permanecem `review_required`; nenhum mismatch explícito de relationship fidelity bloqueante foi detectado no corpus.

O artefato bruto da execução é `bdc-kb-spec004-kd-v2-smoke-20260916-172538.json`, SHA-256 `4ae43163f2aeb96f8c8d2677b83bf236d5b3f7a505281f1058c57394efdcffa5`. O repositório mantém um resumo verificável com esse hash para rastreabilidade.

## Evidência humana que fecha T079O/T079P

Mesmo conjunto de oito slots usado nas rodadas anteriores:

- expected/reviewed: 8/8;
- human_passed: 8/8;
- gate_passed: 8/8;
- coverage: 8/8;
- order: 8/8;
- no invented text: 8/8;
- structure preserved: 8/8;
- stale: 0;
- repeatability failures: 0;
- sample ID mismatches: 0;
- system not_ready: 0;
- gate global: true.

Os três casos que motivaram KD 2.1 foram resolvidos no aceite humano:

- post 1290: structure preserved = true;
- post 370: structure preserved = true;
- post 1307: structure preserved = true.

O slot 36431 também fecha com order preserved = true e gate individual = true. A execução anterior 7/8 foi descartada como marcação humana incompleta, não como regressão técnica.

Artefato final: `evidence/g240-kd21-acceptance-20260916T193359Z.json`.

## Decisão formal — G-240

**G-240 PASS / CLOSED.**

Critérios satisfeitos:

1. full-corpus determinístico em duas passagens;
2. zero mutação editorial;
3. zero structure_incomplete;
4. zero `not_ready`;
5. relationship fidelity sem mismatch bloqueante;
6. mesmos oito A/B revisados;
7. 8/8 estrutura humana preservada;
8. 8/8 gate_pass;
9. zero stale/repeatability/sample mismatch;
10. limitações restantes refletidas explicitamente como `review_required`.

## Próximo estágio — G-245

G-245 deixa de estar bloqueado por G-240 e passa para **READY**, porém nenhuma escrita editorial está autorizada automaticamente.

Próxima sequência obrigatória:

1. estabelecer baseline de G-245 e inventário atual de compatibilidade Elementor/produção;
2. executar **Production Preflight read-only**;
3. construir matriz de compatibilidade por tipo de conteúdo/versão/widget;
4. produzir **Projection Plan read-only** sem persistência;
5. definir `Elementor_Gateway` version-gated com writer disabled-by-default;
6. definir journal, rollback, stale-source guard e idempotência;
7. executar dry-run sem escrita;
8. só após subgates aprovados planejar canário controlado;
9. migration/writer real exige autorização explícita posterior e rollback comprovado.

## Guardrails preservados

- PR #3 continua DRAFT / NÃO MERGEAR por enquanto.
- Content Extractor/KD continuam read-only.
- nenhum writer Elementor está habilitado.
- nenhuma migration Elementor está autorizada.
- nenhuma persistência de KD/resultado foi introduzida.
- produção não será usada como ambiente experimental.
- toda futura mutação deve ser version-gated, auditável, retomável, idempotente e reversível.

## Princípio de continuidade

> Quem não sabe onde está, não sabe para onde quer ir.

Baseline agora conhecida: **KD 2.1.0 / acceptance.12 / G-240 PASS**. O próximo objetivo é abrir G-245 apenas por meio de preflight e projeção read-only, preservando essa baseline sem regressão.
