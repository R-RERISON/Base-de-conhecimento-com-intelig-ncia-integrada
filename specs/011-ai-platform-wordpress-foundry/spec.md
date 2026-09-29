# SPEC-011 — AI Platform, WordPress AI & Foundry

**Status:** PLANEJADA

## Problema

IA não pode virar subsistema provider-specific acoplado ao domínio.

## Princípio

Domain capability -> AI abstraction -> WordPress-native seam quando disponível -> provider adapter.

## Ordem técnica

1. avaliar WordPress AI Client;
2. avaliar Connectors API;
3. avaliar Abilities API;
4. feature detection para versões modernas;
5. compatibility seam para WordPress mínimo;
6. Foundry adapter quando necessário ao ambiente corporativo.

## Escopo

- provider registry mínimo;
- model capability detection;
- prompt/model registry;
- secrets;
- data egress;
- timeout/retry;
- circuit breaker;
- observability;
- token/cost budgets;
- NO_CHANGE;
- cache quando seguro;
- health diagnostics.

## Proibições

- secrets em source;
- chamada arbitrária client-side;
- IA como owner;
- provider vazando para domínio;
- bulk sem budget;
- fallback fabricando sucesso.

## Gates

AI-1100 Core API Evaluation  
AI-1110 Connector/Secret Contract  
AI-1120 Foundry Adapter  
AI-1130 Capability Detection  
AI-1140 Cost Governance  
AI-1150 Security/Data Egress  
AI-1160 Failure/Fallback  
AI-1170 Diagnostics  
AI-1180 Controlled Environmental Test  
AI-1190 Platform Acceptance
