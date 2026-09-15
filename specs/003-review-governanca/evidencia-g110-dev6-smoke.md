# Evidência G-110 — Smoke ambiental `0.3.0-dev.6`

Data: 2026-09-15

O operador instalou o build `0.3.0-dev.6` no ambiente real e confirmou que o fluxo funcionou conforme orientado.

A captura recebida confirma visualmente o Knowledge Workspace com Context Header, navegação horizontal, Visão geral ativa e cards independentes de Summary, Classificação e Review & Governança. O estado de Review aparece na overview sem introduzir um terceiro bloco vertical.

Decisão:

- W-001 Knowledge Workspace shell: PASS ambiental inicial.
- W-002 encaixe visual de Review: PASS ambiental inicial.

Este smoke não fecha G-110. Permanecem Histórico read-only, teclado/foco, viewports finais, transições pela UI e Browser Acceptance com cleanup.

O smoke autoriza a próxima slice W-003 sem alterar stores canônicos.
