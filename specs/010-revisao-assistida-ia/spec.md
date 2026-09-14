# SPEC-010 — Revisão Assistida por IA

**Status:** Planejada  
**Pré-requisito:** SPEC-009 concluída.

## Problema
Analistas precisam acelerar revisão e padronização sem delegar autoridade editorial ao modelo.

## Resultado esperado
Sugestões rastreáveis de resumo, classificação, qualidade, inconsistências e obsolescência, com aceite/rejeição humano e auditoria.

## WordPress-first
Persistência final usa APIs WordPress dos domínios; IA apenas propõe valores.

## Princípio de negação
Executar regras estruturais/determinísticas antes de chamar LLM.

## Gate
Post → análise local → IA somente onde agrega → sugestão → humano aceita/rejeita → apenas decisão humana persiste → custo registrado.
