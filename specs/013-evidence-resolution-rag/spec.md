# SPEC-013 — Evidence Resolution & RAG

**Status:** PLANEJADA

## Problema

O consumidor precisa chegar à resposta confiável com menos leitura sem perder rastreabilidade.

## Jornada

query -> retrieval -> evidence -> structured resolution -> official source.

Síntese por LLM é opcional.

## Escopo

- retrieval context;
- evidence references;
- resolution steps;
- validation;
- escalation;
- limitations;
- article links;
- optional RAG synthesis;
- prompt injection defenses;
- grounding;
- insufficient-evidence state;
- feedback/outcome integration.

## Fallback

Sem provider:
- resultados tradicionais;
- trechos/evidências;
- artigos oficiais;
continuam funcionando.

## Gates

RAG-1300 Resolution Contract  
RAG-1310 Evidence Model  
RAG-1320 Retrieval Grounding  
RAG-1330 Structured Resolution  
RAG-1340 Optional Synthesis  
RAG-1350 Injection/Security  
RAG-1360 Fallback  
RAG-1370 Accuracy/Human Evaluation  
RAG-1380 Cost/Latency  
RAG-1390 Acceptance
