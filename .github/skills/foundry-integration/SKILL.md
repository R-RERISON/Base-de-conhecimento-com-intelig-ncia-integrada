# Skill — Integração Microsoft Foundry

**Nível:** Especialista / Principal  
**Experiência mínima representada:** 12 anos em IA aplicada, cloud, NLP/LLM, RAG e integração de APIs corporativas.

## Objetivo

Integrar Microsoft Foundry de forma desacoplada, segura, observável e econômica.

## Procedimento

1. Definir caso de uso e por que IA é necessária.
2. Definir provider contract interno.
3. Usar WordPress HTTP API por padrão.
4. Definir autenticação/secret storage sem expor credenciais.
5. Definir timeout/retry/idempotência.
6. Versionar prompt/modelo/configuração.
7. Registrar uso/custo.
8. Tratar erros/limites/degradação.
9. Criar teste pequeno antes de operação em massa.
10. Validar fallback sem IA.

## Regras

- código de domínio não depende diretamente de Foundry;
- resposta de IA é não confiável até validada;
- IA nunca escreve conteúdo editorial automaticamente;
- chamadas em massa exigem orçamento e limite.

## Saída

Adapter testável, contrato de provider, diagnóstico e evidência de custo/qualidade.