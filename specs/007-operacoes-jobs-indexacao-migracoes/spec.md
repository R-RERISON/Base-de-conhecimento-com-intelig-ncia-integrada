# SPEC-007 — Operações, Jobs, Indexação e Migrações

**Status:** Planejada  
**Pré-requisito:** SPEC-006 concluída.

## Problema
Indexação e processamento derivado precisam ser operáveis sem transformar WP-Cron em fila improvisada ou activation em processo pesado.

## Resultado esperado
Processamento incremental, jobs/fila apenas se comprovadamente necessários, migrações retomáveis, diagnóstico e rollback.

## WordPress-first
WP-Cron é disparador. Site Health deve ser avaliado para saúde. Infraestrutura própria entra apenas quando Core não oferece durabilidade/lease/retry necessários.

## Princípio de negação
Não recriar Post-Install Orchestrator/Fila ASI inteiros sem demanda comprovada.

## Gate
Mudança de post → trabalho derivado → projeção atualizada → erro retomável → saúde visível, sem impacto editorial.
