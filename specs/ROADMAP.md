# Roadmap SpecKit — Base de Conhecimento com Inteligência Integrada

> **Mantra:** “Quem não sabe onde está, não sabe para onde quer ir”.

## Rebaseline após T097

A versão inicial deste roadmap previa uma SPEC-001 de shell separada e uma SPEC-002 com oito campos de Resumo Executivo. O fechamento T095/T096 e a decisão formal T097 simplificaram essa sequência.

### Estado canônico

1. **SPEC-000 — Inventário profundo e contratos** — CONCLUÍDA.
2. **SPEC-001 — Core mínimo + Summary narrativo** — PRONTA após Definition of Ready documental; runtime ainda não iniciado.

A nova SPEC-001 entrega o Core mínimo somente na medida necessária para a jornada real do Analista de Conhecimento e limita Summary aos três campos narrativos `objective`, `escalation` e `important`.

### Próxima ordem conceitual recomendada por T096

A numeração/título de futuras SPECs será fixada apenas quando formalmente abertas. A ordem conceitual atual é:

1. Summary narrativo (SPEC-001 atual);
2. Classificação mínima, com eixo inicial candidato `knowledge_type`;
3. Review mínimo;
4. Content Extractor + Search post-level + Golden mínimo;
5. classificação adicional/item/deep-link/Search Knowledge;
6. Analytics/IA e demais capacidades apenas por evidência.

## Placeholders anteriores

Os diretórios `001-core-shell-design-system` e `002-resumo-executivo-integrado` são preservados como histórico e marcados como supersedidos. Diretórios `003`–`012` também são planejamento anterior/provisório e não constituem autorização de implementação.

## Invariantes do roadmap

- sem big-bang;
- vertical slice homologável;
- WordPress-first;
- baseline antes de mudança;
- testes/gates antes de substituição;
- rollback e preservação de dados;
- nenhum runtime fora de SPEC ativa;
- nenhuma capacidade postergada implementada silenciosamente.

## Regra de alteração

O roadmap pode evoluir por decisão formal, mas nunca por conveniência de implementação. Constituição, Manifesto, decisões da SPEC-000 e a SPEC ativa prevalecem sobre planejamento histórico.
