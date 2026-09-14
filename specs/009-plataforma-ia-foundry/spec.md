# SPEC-009 — Plataforma de IA e Microsoft Foundry

**Status:** Planejada  
**Pré-requisito:** SPEC-008 concluída ou exceção arquitetural justificada.

## Problema
O produto precisa de IA governada e desacoplada para sugestões, embeddings e futuras sínteses sem contaminar o domínio com um fornecedor específico.

## Resultado esperado
Provider contract, Foundry adapter, configuração segura, Prompt Registry, uso/custo, timeout/retry, diagnóstico e fallback.

## WordPress-first
Usar WordPress HTTP API e Settings/Options seguras antes de cliente HTTP ou infraestrutura paralela.

## Princípio de negação
Cada uso de IA deve provar que regra determinística ou retrieval simples não resolve suficientemente.

## Gate
Teste pequeno e controlado → resposta validada → custo/uso registrado → falha do Foundry não quebra o core.
