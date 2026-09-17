# Package `0.4.0-acceptance.11`

Objetivo: aplicar a correção final de materialização HTML comprovada pelo diagnóstico pipeline `.10`, sem alterar Knowledge Document `2.0.1`, Semantic Structure, hashes, readiness ou gate.

## Evidência que autorizou a correção

`evidence/pipeline-diag-20260916T143948Z.json` comprovou:

- `raw_expected == unwrapped_expected` para listas e tabelas nos cinco resíduos;
- `fragment containers == blocks` em todos os casos;
- a perda ocorre exclusivamente em HTML/DOM → fragments;
- shortcode unwrap e Semantic Structure não são a causa.

## Mudanças runtime

- `Legacy_HTML_Adapter`: travessia recursiva até a primeira fronteira estrutural `ul|ol|table` dentro de wrappers em `li`, `p` e `blockquote`;
- ao encontrar lista/tabela, o próprio adapter assume a subárvore e a busca não desce novamente nela, evitando duplicidade;
- preservação de `alt` de imagens dentro de células de tabela como texto semântico da célula;
- bootstrap em `0.4.0-acceptance.11`;
- runners diagnósticos `.9/.10` desabilitados; smoke KD v2 e Aceitação G-240 v2 permanecem disponíveis.

Artefato: `base-conhecimento-inteligencia-integrada-0.4.0-acceptance.11.zip`

SHA-256: `c96e91bb5947d882ae636c0cc2a4bd1e9f08f05efe29dc1c6d6164908cbe81e1`

## Validação local

- 30 arquivos runtime;
- 26 PHP / lint 26/26 PASS;
- JS syntax PASS;
- ZIP integrity PASS;
- staging↔ZIP parity 30/30 PASS;
- focused safety scan PASS;
- Git↔package bootstrap: `653b4c9915a4abafb17fa2e3ffa474f66b05dbb5`;
- Git↔package Legacy adapter: `d1b606ed24c9e1963981f4aeec3931a3d56c8894`.

## Status

**PACKAGE READY / ENV SMOKE PENDING.**

## Próximo gate ambiental

Executar somente **Base de Conhecimento → Validação KD v2** e exigir:

- `structure_incomplete=0` em ambas as passagens;
- `ai_readiness.not_ready=0` em ambas as passagens;
- zero errors/throwables;
- zero hash/canonical JSON mismatch;
- fingerprint editorial invariável;
- zero posts alterados.

Somente após full-corpus PASS reexecutar os mesmos oito casos A/B humanos do G-240 v2.
