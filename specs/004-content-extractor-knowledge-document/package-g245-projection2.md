# Package — G-245 / T081 Projection Plan `.2`

## Objetivo

Preparar a execução ambiental do T081 no WordPress de homologação sem habilitar qualquer writer ou migration.

## Build

- plugin: `0.4.0-g245-projection.2`
- Projection Plan schema: `1.0.0`
- smoke report schema: `1.1.0`
- compatibility matrix: `g245-compatibility-matrix-v1`
- branch: `spec004-g245-production-readiness`
- PR: `#4` — DRAFT

## Alterações de segurança desta build

1. Warnings de migração que representam perda/indeterminação estrutural exigem `requires_review=true`:
   - `ELEMENTOR_JSON_INVALID`;
   - `GUTENBERG_DYNAMIC_NOT_RENDERED:*`;
   - `GUTENBERG_BLOCK_UNSUPPORTED:*`;
   - `ELEMENTOR_WIDGET_UNSUPPORTED:*`;
   - `SOURCE_OVERSIZE_HARD:*`.
2. `faq_wd`, `wpt` e qualquer shortcode sem handler continuam em review obrigatório.
3. `SHORTCODE_NOT_EXPANDED:*` com handler registrado permanece dependência opaca/warning e não bloqueia projeção sozinho.
4. Full-corpus smoke valida independentemente:
   - formato do `projection_hash`;
   - `writer_allowed=false`;
   - todos os safety flags estritamente false;
   - review obrigatório para warnings críticos;
   - review obrigatório para `faq_wd`/`wpt`;
   - repetibilidade entre duas passagens;
   - corpus e fingerprint editorial imutáveis.

## Validação local

- `tests/unit/spec004-projection-plan-v1.php`: **58 assertions PASS**;
- PHP lint do Projection Plan: PASS;
- PHP lint do Projection Plan Smoke: PASS;
- PHP lint do teste: PASS;
- `tools/homologation/spec004/validate-g245-projection-evidence.php`: lint PASS;
- validator sintético: relatório íntegro retorna exit `0`; violação de safety retorna exit `1`.

## Execução em homologação

1. Instalar/atualizar somente a build `0.4.0-g245-projection.2` no ambiente de homologação.
2. Não executar qualquer writer/migration; eles não estão autorizados.
3. No WP Admin, abrir **Projection Plan G-245**.
4. Executar **Projection Plan full-corpus e baixar JSON** uma única vez; o runner realiza internamente duas passagens sobre o corpus.
5. Preservar o JSON bruto sem edição.
6. Validar a evidência com:

```bash
php tools/homologation/spec004/validate-g245-projection-evidence.php /caminho/para/bdc-kb-spec004-g245-projection-smoke-*.json 622
```

7. O validator deve retornar `gate_pass=true`, `checks_failed=0` e exit code `0`.
8. Versionar um resumo verificável e o `evidence_sha256` calculado pelo validator no diretório `evidence/` antes de fechar T081.

## Critério de PASS ambiental

O gate só fecha com `gate.t081_pass=true` e, simultaneamente:

- corpus before/after idêntico;
- 622 planos em cada passagem para a baseline atual de homologação;
- zero errors e throwables;
- zero projection hash mismatches;
- zero canonical JSON mismatches;
- zero projection-hash violations;
- zero writer violations;
- zero safety violations;
- zero legacy-shortcode review violations;
- zero migration-warning review violations;
- fingerprint editorial before/after idêntico;
- zero posts alterados durante a execução.

## Proibições

Este package **não autoriza**:

- gravação em `post_content`;
- gravação em `_elementor_data`;
- persistência de Projection Plan;
- execução de shortcodes;
- chamada externa de rede pelo planner;
- migration Elementor;
- canário de escrita;
- merge do PR #4 antes do fechamento dos gates aplicáveis.
