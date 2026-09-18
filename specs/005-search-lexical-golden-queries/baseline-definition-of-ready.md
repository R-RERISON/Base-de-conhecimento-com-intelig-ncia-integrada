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

R-500 recontou 623 artigos (606 publicados). T511.2 registrou 623 antes/depois em 2026-09-18 21:45:45 UTC. Essas contagens são snapshots ambientais, não promessa de corpus imutável.

## DoR obrigatório

Runtime Search só pode começar se:
- [x] corpus atual medido;
- [x] scope inicial admin/status baseline definido;
- [x] superfície inicial decidida: admin-first;
- [x] baseline `WP_Query` medida;
- [x] gaps contra Content Extractor medidos;
- [ ] pelo menos uma Golden Query ativa e validada;
- [ ] schema Golden v1 fechado;
- [ ] WordPress-first decision registrada;
- [ ] segurança/rollback definidos;
- [ ] nenhuma dependência de IA/vetor.

Estado atual: **R-500 PASS, porém NOT_READY para implementação de engine** até R-510 Golden Dataset + G-520 contracts/storage/security.


T511.2: PASS AMBIENTAL para medição dos seis candidates; isso não fecha R-510. Próximo passo: [revisão humana T513 e diversidade T514](r510-golden-candidate-review-v1.md), antes de versão/hash T515 e fechamento T516.
