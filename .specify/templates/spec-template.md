# SPEC-XXX — <Título>

**Status:** Rascunho | Pronta | Em implementação | Homologação | Concluída | Bloqueada  
**Dono:** Orquestrador + agentes convocados  
**Data:** AAAA-MM-DD  
**Mantra:** “Quem não sabe onde está, não sabe para onde quer ir”.

## 1. Problema

Descreva o problema real, sem antecipar solução.

## 2. Baseline — onde estamos

- comportamento atual;
- dados atuais;
- telas atuais;
- dependências;
- métricas;
- limitações;
- evidências e referências.

## 3. Usuários e jornada

Quem é afetado e qual fluxo ponta a ponta precisa funcionar.

## 4. Resultado esperado — para onde queremos ir

Descreva o comportamento observável depois da SPEC.

## 5. Invariantes constitucionais afetados

Liste artigos relevantes da Constituição.

## 6. Avaliação WordPress-first

| Necessidade | Recurso nativo avaliado | Atende? | Justificativa |
|---|---|---:|---|
| | | | |

## 7. Princípio de negação

### Solução inicialmente proposta

### Alternativa mais simples

### O que pode ser removido?

### Decisão

## 8. Escopo

### Dentro

### Fora

## 9. Contrato funcional

Comportamentos obrigatórios, estados, entradas, saídas e erros.

## 10. Modelo de dados

Dados canônicos, derivados, reconstruíveis, chaves, ownership e retenção.

## 11. Integrações e eventos

Hooks, filtros, WordPress APIs, serviços externos e contratos internos.

## 12. Segurança

Capabilities, nonces, sanitização, validação, escaping, privacidade e abuso.

## 13. UI/UX

Telas, estados, Design System, responsividade, acessibilidade, feedback e navegação.

## 14. Observabilidade e custo

Logs, métricas, saúde, telemetria, limites, custo de IA/processamento.

## 15. Performance

Orçamento de queries, latência, memória, volume e degradação graciosa.

## 16. Migração e compatibilidade

Dados legados, coexistência, cutover, rollback e preservação.

## 17. Testes

### Unitários
### Integração
### Regressão
### Segurança
### Performance
### Browser/manual
### Golden Queries, quando aplicável

## 18. Critérios de aceite

- [ ] ...

## 19. Critérios de NÃO aceite

Situações que obrigatoriamente bloqueiam a conclusão.

## 20. Rollback

Como voltar ao estado anterior sem perda indevida.

## 21. Evidências de conclusão

Commits, relatórios, screenshots, checksums, resultados de testes e homologação.

## 22. Continuidade entre chats

Toda implementação material desta SPEC deve criar ou atualizar `CONTINUIDADE.md` na própria pasta da SPEC usando `.specify/templates/continuity-prompt-template.md`.

O handoff deve registrar estado comprovado, branch/commit, decisões, invariantes, arquivos/dados/contratos afetados, testes, gaps, riscos, próximo passo exato e critério de conclusão.

- [ ] `CONTINUIDADE.md` atualizado.
- [ ] Prompt pronto para colar em novo chat.
- [ ] Novo chat instruído a reler repositório/Constituição/Manifesto/SPEC/DoD antes de agir.
- [ ] Estado comprovado distinguido de intenção ou planejamento.
- [ ] Nenhum blocker conhecido omitido.

**Regra:** a SPEC não pode ser marcada como `Homologação` ou `Concluída` sem o Prompt de Continuidade atualizado.
