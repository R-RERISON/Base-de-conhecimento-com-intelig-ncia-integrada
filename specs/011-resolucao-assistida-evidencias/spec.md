# SPEC-011 — Resolução Assistida com Evidências

**Status:** Planejada  
**Pré-requisito:** SPEC-010 concluída.

## Problema
O resolvedor precisa de resposta operacional rápida sem perder rastreabilidade, fonte oficial e possibilidade de verificar o artigo completo.

## Resultado esperado
Retrieval confiável, síntese opcional, passos/validação/escalonamento e fontes explícitas, com fallback para resultados tradicionais.

## WordPress-first
A experiência pública permanece integrada ao WordPress; API adicional só nasce se houver consumidor real.

## Princípio de negação
Se o resultado operacional estruturado resolver, não chamar LLM apenas para reformular texto.

## Gate
Consulta → retrieval confiável → resposta estruturada → evidências/fontes → artigo oficial → fallback funcional sem IA.
