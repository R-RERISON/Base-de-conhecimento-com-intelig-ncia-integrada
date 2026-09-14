# Hardening do diagnóstico onclick v2

## Objetivo

Antes da homologação, endurecer o runner temporário para evitar resíduos e ampliar a cobertura negativa de G-020 sem alterar o runtime funcional da SPEC-001.

## Mudanças obrigatórias do runner v2

- schema do JSON passa para `1.1.0`;
- marker de fixture passa para `spec001-onclick-v2`;
- limpeza de fixtures deixa de depender de uma única consulta limitada a 50 objetos;
- limpeza ocorre em lotes de 100 até esgotar o marker ou atingir guard rail explícito;
- contagem residual usa `WP_Query::found_posts`;
- falha de criação de fixture não pula a seção de cleanup do relatório;
- G-020 passa a cobrir adicionalmente:
  - leitura de meta ausente sem side effect;
  - valor acima de 32768 bytes;
  - valor não-string;
  - NO_CHANGE sem `update_post_meta`;
  - sanitização de script;
  - Unicode/multiline/backslash.

## Regra de segurança

O resultado global só pode ser `PASS` quando:

- nenhum check estiver em FAIL;
- `cleanup.residual_fixtures == 0`.

## Gate

O runner continua ferramenta transitória. Tanto `class-diagnostics-runner.php` quanto `class-browser-acceptance.php` devem ser removidos antes de T044/G-130/package.
