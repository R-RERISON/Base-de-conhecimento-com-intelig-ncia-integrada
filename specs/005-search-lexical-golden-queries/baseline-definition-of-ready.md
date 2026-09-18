# Baseline / Definition of Ready — SPEC-005

## Baseline técnica

- `main @ 07f877b2978429dc6b31fbe172e6ce8fca7ee634`;
- plugin `0.4.0-spec004-rc2`;
- SPEC-004 CLOSED/main;
- post type atual: `post`;
- busca administrativa atual: `WP_Query` com `s`;
- nenhum Search Retrieval module próprio;
- nenhum índice lexical próprio;
- nenhuma Golden Query no runtime novo;
- nenhuma IA/vetor.

## Baseline ambiental disponível

Última matriz E6 conhecida: 623 artigos.

A SPEC-005 deve recontar o corpus em R-500 e não assumir que 623 permanece atual.

## DoR obrigatório

Runtime Search só pode começar se:
- [ ] corpus atual medido;
- [ ] scope de status/visibilidade definido;
- [ ] superfície inicial decidida;
- [ ] baseline `WP_Query` medida;
- [ ] gaps contra Content Extractor medidos;
- [ ] pelo menos uma Golden Query ativa e validada;
- [ ] schema Golden v1 fechado;
- [ ] WordPress-first decision registrada;
- [ ] segurança/rollback definidos;
- [ ] nenhuma dependência de IA/vetor.

Estado inicial: **NOT_READY para implementação de engine; READY para discovery/diagnóstico read-only**.
