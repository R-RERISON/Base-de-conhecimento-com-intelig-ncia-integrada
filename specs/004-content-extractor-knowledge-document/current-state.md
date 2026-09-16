# Current State — SPEC-004 Content Extractor e Knowledge Document

## Baseline e gates

- SPEC-001/002/003: concluídas.
- R-200: **PASS**.
- R-210: **PASS**.
- G-220: **PASS ambiental**.
- G-230/v1: **PASS de determinismo / superseded for AI**.
- G-240/v1: **FAIL CONTROLADO — perda estrutural**.
- G-240/v2/KD 2.0.1: **PASS técnico / FAIL humano — hierarchy fidelity**.
- KD 2.1.0 / `0.4.0-acceptance.12`: **PASS técnico full-corpus + PASS humano 8/8**.
- **G-240: PASS / CLOSED**.
- **G-245: IN PROGRESS — Production Preflight read-only**.
- G-250: NOT_RUN.

## Baseline ambiental validada

Ambiente de homologação, cópia de produção:

- WordPress `6.9.4`;
- PHP `8.5.10`;
- Elementor `4.1.0`;
- multisite: não;
- corpus: 622 posts;
- DOMDocument: ativo.

A validação KD 2.1 full-corpus executou duas passagens 622/622 com:

- zero errors;
- zero throwables;
- zero hash mismatch;
- zero canonical JSON mismatch;
- zero structure_incomplete;
- zero `not_ready`;
- zero mutação editorial;
- gate técnico PASS.

Readiness KD 2.1 observado:

- candidate_ready: 387;
- review_required: 233;
- not_applicable: 2;
- not_ready: 0.

## Fechamento humano G-240

Mesmo conjunto fixo de oito slots das rodadas anteriores:

- 8/8 revisados;
- coverage 8/8;
- order 8/8;
- no invented text 8/8;
- structure preserved 8/8;
- human_pass 8/8;
- gate_pass 8/8;
- stale 0;
- repeatability failures 0;
- sample mismatch 0;
- system not_ready 0;
- gate global true.

Os posts 1290, 370 e 1307, que haviam exposto perda de hierarchy fidelity em KD 2.0.1, fecharam com estrutura humana preservada em KD 2.1.0.

Evidências:

- `evidence/kd-v21-smoke-summary-20260916T172538Z.json`;
- `evidence/g240-kd21-acceptance-20260916T193359Z.json`.

## Runtime Content Extractor / Knowledge Document

Componentes principais ativos na baseline:

- `Content_Normalizer`;
- `Shortcode_Inspector`;
- `Legacy_HTML_Adapter`;
- `Content_Source`;
- `Elementor_Adapter`;
- `Gutenberg_Adapter`;
- `Content_Extractor`;
- `Canonical_JSON`;
- `Semantic_Structure`;
- `Hierarchy_Relationships`;
- `Numbered_Hierarchy_Resolver`;
- `Knowledge_Document` schema `2.1.0`.

O knowledge plane permanece estritamente read-only.

## Readiness Elementor conhecida

Full-corpus KD 2.1:

- native: 39;
- projectable: 505;
- review_required: 78;
- blocked: 0.

Isso é uma classificação de readiness; não autoriza migration.

## G-245 — Production Preflight v1

Branch dedicada:

`spec004-g245-production-readiness`

Build inicial:

`0.4.0-g245-preflight.1`

Novo componente:

`Production_Preflight`

Princípios:

1. read-only;
2. nenhuma execução de shortcode;
3. nenhuma chamada externa/loopback no v1;
4. nenhuma persistência de resultado;
5. nenhuma escrita em `post_content`;
6. nenhuma escrita em `_elementor_data`;
7. `writer_allowed=false` sempre;
8. `migration_execution_allowed=false` sempre.

O preflight coleta fatos do ambiente e classifica checks como:

- `compatible`;
- `review_required`;
- `blocking`.

Checks iniciais:

- WordPress >= 6.6;
- PHP >= 8.1;
- DOMDocument;
- Elementor carregado;
- versão Elementor na matriz homologada;
- backup confirmado quando target=production;
- dependências observáveis de shortcodes;
- WP-Cron;
- loopback explicitamente não testado no v1.

A matriz inicial reconhece apenas Elementor `4.1.0`, pois é a única versão já comprovada pela homologação atual. Versões diferentes exigem revisão; Elementor ausente bloqueia migration, mas não o knowledge plane read-only.

O preflight também inventaria plugins ativos e tenta mapear handlers de shortcodes usados para `plugin:<slug>`, `wordpress-core` ou `unknown`, sem exportar o corpo editorial.

## Testes G-245 locais

`tests/unit/spec004-production-preflight.php`

Validações já exercitadas:

- baseline homologada sem blocking;
- versão Elementor desconhecida => review_required;
- produção sem backup confirmado => blocking;
- Elementor ausente => blocking para migration;
- shortcode usado sem handler => review_required;
- PHP abaixo de 8.1 => blocking.

Resultado local do incremento: **PHP lint PASS + 7/7 assertions PASS**.

## Contratos que governam G-245

- `elementor-normalization-contract-v1.md`;
- `production-rollout-contract-v1.md`.

A arquitetura continua separando:

1. knowledge plane read-only;
2. editorial migration plane explícito.

Migration editorial nunca roda em installation/activation/update.

## Próximo passo

1. empacotar `0.4.0-g245-preflight.1`;
2. validar ZIP/lint/paridade;
3. instalar em homologação;
4. abrir **Base de Conhecimento → Preflight G-245**;
5. executar primeiro com target `Homologação`;
6. retornar `bdc-kb-spec004-g245-preflight-*.json`;
7. classificar os gaps reais antes de implementar Projection Plan.

## Guardrails

- nenhuma migration está autorizada;
- nenhum writer está habilitado;
- nenhuma tabela/journal foi criada;
- nenhum Knowledge Document é persistido;
- preflight não é gate final de G-245;
- produção não será usada como ambiente experimental.

> Quem não sabe onde está, não sabe para onde quer ir.

Baseline conhecida para o próximo avanço: **KD 2.1.0 / G-240 PASS / G-245 preflight read-only iniciado**.
