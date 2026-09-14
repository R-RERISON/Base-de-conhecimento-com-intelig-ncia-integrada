# SPEC-012 — Paridade, Cutover e Aposentadoria Controlada

**Status:** Planejada  
**Pré-requisito:** SPEC-011 concluída e matriz de paridade madura.

## Problema
O novo plugin só pode substituir os três anteriores quando dados, comportamentos e operação estiverem comprovadamente cobertos.

## Resultado esperado
Matriz final de paridade, coexistência segura, migração/adoção de dados, rollback, release gate e desativação controlada dos plugins anteriores.

## WordPress-first
Usar lifecycle e APIs nativas para coexistência/ativação; nenhuma exclusão automática de plugins/dados durante activation.

## Princípio de negação
Migrar somente dados necessários. Índices/projeções reconstruíveis devem preferir rebuild controlado a migração complexa quando isso for mais seguro.

## Gate
Todos os contratos críticos passam, Golden Queries passam, dados preservados, ambiente real homologado, rollback testado e plugins antigos podem ser desativados sem perda de função necessária.
