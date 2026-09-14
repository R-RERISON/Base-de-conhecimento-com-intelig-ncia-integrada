# SPEC-006 — Telemetria, Inteligência de Busca e Lacunas

**Status:** Planejada  
**Pré-requisito:** SPEC-005 concluída.

## Problema
Sem outcomes confiáveis não sabemos se a busca ajuda, falha ou apenas não gera clique.

## Resultado esperado
Eventos, interações, zero-result, engajamento, privacidade e knowledge gaps integrados ao produto.

## WordPress-first
Usar recursos nativos para configuração/cron/cache; tabela própria só se volume e consultas justificarem.

## Princípio de negação
Coletar apenas métricas que suportam uma decisão real.

## Gate
Busca real → evento → clique válido → interação correlacionada → outcome correto → relatório/lacuna sem inferência indevida.
