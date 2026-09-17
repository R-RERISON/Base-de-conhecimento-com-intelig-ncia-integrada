# Continuidade — SPEC-004 Content Extractor e Knowledge Document

## Repositório / branch
- Repositório: `R-RERISON/Base-de-conhecimento-com-intelig-ncia-integrada`.
- Branch ativa: `spec004-g245-production-readiness`.
- PR #4: DRAFT / NÃO MERGEAR.
- SPEC ativa: SPEC-004.

## Estado atual
- UX-002 `0.4.0-ux002.3`: PASS/CLOSED, contrato visual obrigatório.
- G-240: PASS/CLOSED/main.
- ADR-004-001: Core Blocks como destino editorial canônico.
- T091/T093/T094/T096/T097: PASS AMBIENTAL.
- T095: PASS LOCAL / READ-ONLY.
- T098.1: **FAIL CONTROLADO / SEM MUTAÇÃO**.
- T098.2: **CORRIGIDO / REHOMOLOGAÇÃO PENDENTE**.
- Nenhum writer/migration autorizado.

## T098.1
Evidência: `evidence/g245-t098-readiness-fail-20260917T190620Z.json`.
SHA-256 bruto: `b85e22c338d72f52dc11b3d113058618108b9dd20778b67a3062839c8eaba44d`.

Resultado: 623 throwables por passagem, safety violations 0, corpus unchanged, fingerprint editorial unchanged e gate false.

Causa raiz: mismatch de integração no `Block_Migration_Dry_Run`:
- `Core_Block_Editorial_Parity::validate()` inexistente; contrato real usa `assess()`;
- `Block_Migration_Stale_Source_Guard::inspect_post()` inexistente; contrato real usa `assess()` / `assert_fresh()`;
- `is_fresh` não existe; freshness é `status=fresh`.

## T098.2
Pipeline corrigido:
`Migration Fidelity Source -> Lossless Serializer -> parse_blocks -> Editorial Parity assess -> rebuild current source -> Stale Guard assess -> Dry Run simulate`.

Runner adiciona somente assinatura agregada de throwable (`classe + basename:linha + hash curto da mensagem`) se necessário, sem conteúdo editorial.

Validação local:
- 28/28 assertions PASS;
- 50 PHP lint PASS pré/pós ZIP;
- UX-002 byte parity PASS;
- writer OFF;
- journal store não invocado;
- lock não adquirido;
- `post_content`/`_elementor_data` não escritos.

Pacote:
- `0.4.0-g245-readiness-t098.2`;
- SHA-256 `a37ebc3ff018f71c96546e56e2b8434db53ea148b7d9d253db0332b1d389cef7`.

## Próximo passo
1. instalar T098.2;
2. abrir `Base de Conhecimento > Block Migration Readiness G-245`;
3. executar e devolver JSON;
4. se PASS, fechar T098;
5. T099A: journal store + lock com cleanup, sem write editorial;
6. T099B: Authorization Pack de um `legacy_html` de baixo risco;
7. T099C: canário real apenas com autorização específica.

## Guardrails
- UX-002 intacta;
- plugin Gutenberg não é dependência;
- Elementor não é removido agora;
- source mixed exige humano;
- nenhum write editorial autorizado nesta fase;
- PR #4 permanece DRAFT.

> Quem não sabe onde está, não sabe para onde quer ir.
