# Evidência S004 — Segurança HTTP `0.2.0-dev.7`

Resultado real: **PASS 18/18**.

Cobertura: GET 405, nonce ausente/inválido/vinculado ao post, payload ausente/escalar, mass assignment, XSS em term ID, page/ID inexistente, capability/forbidden no handler real e POST válido com PRG + releitura.

O ambiente corporativo interceptava roles reduzidas em `/acesso-restrito/` antes do `admin-post.php`; H11 foi então exercitado por negação `edit_post` assinada, restrita à fixture e exclusiva do build de homologação. Essa instrumentação não existe no RC.

Cleanup: 3 posts/page removidos; 5 termos removidos; 0 usuários temporários no dev7; resíduos 0/0/0.

JSON: `evidencias/bdc-kb-classification-http-security-20260915-105820.json`.
SHA-256: `6f739f4b13dd3c941587fece4d170b2781987b992bd7ee96fab64465afa00fa2`.
