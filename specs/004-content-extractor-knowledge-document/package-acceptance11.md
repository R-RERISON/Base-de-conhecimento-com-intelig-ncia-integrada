# Package `0.4.0-acceptance.11`

Objetivo: aplicar a correção final de materialização HTML comprovada pelo diagnóstico pipeline `.10`, sem alterar Knowledge Document `2.0.1`, Semantic Structure, hashes, readiness ou gate.

Mudanças runtime:

- `Legacy_HTML_Adapter`: travessia recursiva até a primeira fronteira estrutural `ul|ol|table` dentro de wrappers em `li`, `p` e `blockquote`;
- preservação de `alt` de imagens dentro de células de tabela como texto semântico da célula;
- bootstrap em `0.4.0-acceptance.11`;
- runners diagnósticos `.9/.10` desabilitados; smoke KD v2 e Aceitação G-240 v2 permanecem disponíveis.

Artefato: `base-conhecimento-inteligencia-integrada-0.4.0-acceptance.11.zip`

SHA-256: `c96e91bb5947d882ae636c0cc2a4bd1e9f08f05efe29dc1c6d6164908cbe81e1`

Validação local:

- 30 arquivos runtime;
- 26 PHP / lint 26/26 PASS;
- JS syntax PASS;
- ZIP integrity PASS;
- staging↔ZIP parity 30/30 PASS;
- focused safety scan PASS;
- blob esperado Legacy adapter: `d1b606ed24c9e1963981f4aeec3931a3d56c8894`;
- blob esperado bootstrap: `653b4c9915a4abafb17fa2e3ffa474f66b05dbb5`.

Gate ambiental esperado: `structure_incomplete=0`, `ai_readiness.not_ready=0`, zero errors/throwables/hash mismatches/canonical JSON mismatches e zero write editorial.
