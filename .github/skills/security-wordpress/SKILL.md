# Skill — Segurança WordPress

**Nível:** Especialista / Principal  
**Experiência mínima representada:** 15 anos em AppSec, OWASP e plugins WordPress.

## Objetivo

Revisar mutações, superfícies públicas e integrações antes de release.

## Procedimento

Para cada ação:

1. identificar ator;
2. definir capability;
3. definir método HTTP;
4. definir nonce quando mutável;
5. sanitizar e validar entrada;
6. evitar confiança em IDs/estado do cliente;
7. persistir com APIs seguras;
8. escapar saída conforme contexto;
9. avaliar rate limit quando público;
10. avaliar logs/secrets/privacidade.

## Checklist especial

- CSRF;
- IDOR;
- XSS;
- SQL injection;
- privilege escalation;
- mass assignment;
- path/file operations;
- SSRF em HTTP externo;
- vazamento de secrets;
- replay/idempotência.

## Saída

Matriz de ações e achados com severidade e condição de bloqueio.