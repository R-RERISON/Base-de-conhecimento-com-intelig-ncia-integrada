# JSON de Browser Acceptance — contrato de evidência

O formulário temporário de T043 gera um JSON sem persistência com:

- `schema_version`;
- `mode=temporary_browser_acceptance`;
- timestamp UTC;
- WordPress/PHP/plugin;
- navegador/versão informado;
- viewport validado;
- `user_agent` sanitizado;
- oito checks G-110 em `PASS|FAIL`;
- `source=operator_assertion` por check;
- `safety.persistent_report=false`;
- `safety.database_writes=false`;
- observações sanitizadas.

## Regra

Este JSON é evidência manual estruturada, não automação E2E. G-110 só muda de `NOT_RUN` após revisão do arquivo gerado no ambiente alvo. Qualquer `FAIL` mantém o gate bloqueado.
