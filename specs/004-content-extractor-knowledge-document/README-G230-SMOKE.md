# Smoke G-230 — instruções de homologação

Package: `0.4.0-smoke.2`

SHA-256: `1466cd4fcd18120d0b2405bf04ec629230f23c2a2e759869a8123c45cedaf204`

## Procedimento

1. Instalar/substituir o plugin pelo package `0.4.0-smoke.2`.
2. Confirmar a versão na tela de plugins.
3. Confirmar que Profiler SPEC-004 e Smoke G-220 não aparecem.
4. Abrir **Base de Conhecimento → Smoke G-230**.
5. Evitar edição concorrente de posts durante o teste.
6. Executar **Smoke G-230 e baixar JSON**.
7. Retornar `bdc-kb-spec004-g230-smoke-*.json`.

## Gate

Exigir simultaneamente:

- `safety.editorial_fingerprint_equal=true`;
- `safety.changed_posts_during_run=0`;
- corpus before = after = 622;
- `first_pass_documents=622`;
- `second_pass_documents=622`;
- `first_pass_errors=0`;
- `second_pass_errors=0`;
- `first_pass_throwables=0`;
- `second_pass_throwables=0`;
- `hash_mismatches=0`;
- `canonical_json_mismatches=0`.

Nenhum conteúdo, ID, título ou URL é exportado pelo runner.
