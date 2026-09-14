# Manifesto do Projeto — Base de Conhecimento com Inteligência Integrada

## Identidade

**Produto:** Base de Conhecimento com Inteligência Integrada  
**Tipo:** Plugin WordPress único, modular internamente  
**Idioma:** Português do Brasil  
**Estado:** SPEC-000 concluída / SPEC-001 com gates funcionais de homologação PASS; `0.1.0-rc.1` limpo preparado e aguardando lifecycle G-130 real  
**Mantra:** “Quem não sabe onde está, não sabe para onde quer ir”.

## Missão

Construir uma plataforma única para governar a Base de Conhecimento sem substituir WordPress/Elementor como fonte editorial, evoluindo por vertical slices e mantendo dados derivados reconstruíveis.

## Estado da SPEC-001

T097 autorizou **SPEC-001 — Core mínimo + Summary narrativo** sob escopo estrito.

Comprovações atuais:

- S001/DoR: PASS;
- S002/runtime mínimo: implementado;
- unitário: PASS 15/15;
- instalação inicial/smoke real: PASS;
- G-001 Editorial/Elementor: PASS;
- G-020 Summary: PASS;
- G-070 Segurança/scope/HTTP: PASS;
- G-110 UI/UX: PASS;
- B-006 Write composto: PASS em WordPress real;
- G-130 Lifecycle/package: RC preparado, execução real do RC ainda pendente.

A SPEC está em **fechamento de homologação**, não em produção/cutover.

## Fonte da verdade e fronteiras

- Editorial: `WP_Post` + Elementor.
- Summary: Post Metadata API do WordPress.
- O plugin não escreve `_elementor_data`.
- O plugin não reescreve silenciosamente `post_content`.
- Projeções/cache/índices nunca são fonte editorial.
- IA é assistiva; não participa da SPEC-001.

## SPEC-001 canônica

Jornada:
`selecionar artigo -> ler objective/escalation/important -> editar -> salvar -> reler -> confirmar estado`.

Post type: `post` somente.

Metas:

- `_bdc_es_objective`;
- `_bdc_es_escalation`;
- `_bdc_es_important`.

Runtime:

- wp-admin server-rendered;
- GET read-only;
- POST `admin-post` + nonce;
- capability por objeto `edit_post`;
- allowlist exata;
- limite 32768 bytes antes da sanitização;
- `trim(sanitize_textarea_field())`;
- empty = delete;
- omitido = preservar;
- NO_CHANGE = zero write;
- read-after-write;
- B-006 com snapshot/diff/compensação/reread;
- PRG;
- nenhum schema/tabela/REST/AJAX/SPA/IA.

## Package `0.1.0-rc.1`

O RC remove integralmente instrumentos temporários de homologação. Não há rotina destrutiva de uninstall porque os metadados são institucionais/preexistentes e o plugin não cria estruturas persistentes próprias que exijam remoção.

Antes de qualquer release, G-130 deve comprovar no WordPress real: substituição/ativação, ausência de instrumentos de teste, smoke funcional, desativação/reativação e preservação de dados.

## Regra de liberação

Compilar ou passar unitário não basta. `FAIL`, `NOT_RUN`, `NOT_CONFIGURED` ou `STALE` em gate MUST bloqueia o avanço correspondente.

**GO de desenvolvimento/homologação != GO de produção.** B-003/preflight retorna antes de produção, coexistência de writers ou cutover.

## Fora da SPEC-001

Classificação; Review/AI READY; Content Extractor; Search/Golden Queries; Analytics; queue; schema/migration; REST/AJAX/SPA; Foundry/LLM/embeddings/vector/agentes; aliases; remoção de GRE/KB2Ops/ASI; cutover produtivo.
