# SPEC-000 — Inventário Profundo e Contratos dos Projetos de Referência

**Status:** Pronta  
**Dono:** Orquestrador  
**Data:** 2026-09-14  
**Mantra:** “Quem não sabe onde está, não sabe para onde quer ir”.

## 1. Problema

Temos três plugins funcionais que resolvem partes do mesmo domínio, mas com sobreposição, contratos divergentes, decisões históricas e níveis de complexidade diferentes. Começar a escrever o novo plugin sem ler detalhadamente esses sistemas cria risco de regressão invisível, duplicação desnecessária e perda de comportamentos que hoje protegem produção.

## 2. Baseline

### KB2Ops

- produto/UX mais recente;
- Design System aprovado;
- Knowledge Studio e Search;
- revisão e AI READY;
- Content Extractor endurecido para Elementor;
- busca provisória.

### ASI

- engine de busca madura;
- índice lexical/FULLTEXT;
- itens/trechos;
- ranking;
- vocabulário/regras;
- Golden Queries;
- telemetria/outcomes;
- privacidade;
- fila/migração/operações;
- alta complexidade acumulada.

### Resumo Executivo

- domínio pequeno e funcional;
- WordPress Metadata API;
- oito campos canônicos;
- clean code;
- baixo acoplamento;
- boa referência arquitetural.

## 3. Usuários afetados

Nenhum usuário final será alterado por esta SPEC. A entrega é de engenharia/governança e existe para reduzir risco das Specs seguintes.

## 4. Resultado esperado

Ao concluir, qualquer desenvolvedor/agente deve conseguir responder com evidência:

- o que cada plugin faz;
- por que faz;
- quais dados possui;
- quais rotas/hooks/events usa;
- quais comportamentos precisam sobreviver;
- quais complexidades devem morrer;
- o que WordPress pode substituir;
- o que precisa evoluir com IA/vetores;
- quais testes garantem não regressão;
- quais dados precisam coexistir no cutover.

## 5. Invariantes constitucionais afetados

Principalmente Artigos I, II, III, IV, V, VI, XIV e XVI.

## 6. Avaliação WordPress-first

Esta SPEC não cria runtime. O inventário deve identificar, para cada construção própria dos legados, se existe primitive WordPress suficiente.

Exemplos:

| Construção atual | Avaliar contra |
|---|---|
| configuração própria | Settings/Options API |
| classificação em string | Taxonomy API |
| histórico de revisão | Revisions / meta revisionável / audit próprio |
| health dashboard técnico | Site Health |
| HTTP externo | WordPress HTTP API |
| cron scheduler | WP-Cron |
| endpoint administrativo | admin-post/AJAX/REST conforme consumidor |

## 7. Princípio de negação

Para cada componente legado:

1. Se removermos, qual comportamento quebra?
2. O WordPress já garante esse comportamento?
3. Podemos reduzir para uma construção menor?
4. Precisamos transportar esse conceito para o novo produto?

Nenhuma classe/tabela/tela ganha direito automático de existir apenas porque está em produção hoje.

## 8. Escopo

### Dentro

Leitura detalhada dos três repositórios e construção de inventário estruturado.

### Fora

- criar bootstrap do novo plugin;
- copiar código;
- criar tabelas;
- criar Design System runtime;
- integrar Foundry;
- criar vetores;
- migrar dados.

## 9. Contrato funcional do inventário

Cada artefato relevante deve possuir registro com:

- projeto de origem;
- versão/SHA;
- arquivo/classe/função;
- responsabilidade;
- entrada/saída;
- hooks/eventos;
- persistência;
- dependências;
- segurança;
- impacto visual;
- erros/fallbacks;
- testes associados;
- uso atual conhecido;
- decisão preliminar;
- risco de remoção;
- proposta de contrato novo.

## 10. Categorias obrigatórias

- bootstrap/lifecycle;
- administração;
- público/frontend;
- post metadata;
- taxonomias;
- options;
- transients/cache;
- cron;
- capabilities;
- shortcodes;
- AJAX;
- REST;
- hooks/actions/filters;
- eventos de domínio;
- tabelas/índices;
- migrações;
- queue/jobs;
- Content Extraction;
- search/index/ranking;
- telemetria;
- analytics;
- privacidade;
- IA existente ou preparada;
- CSS/JS;
- Elementor;
- testes;
- build/release;
- uninstall/cleanup;
- integrações externas.

## 11. Classificação de decisão

Cada item recebe uma das classificações:

### MANTER
Comportamento continua necessário praticamente como está.

### REDESENHAR
Comportamento é necessário, implementação deve nascer novamente.

### SUBSTITUIR POR WORDPRESS
Construção própria deve ser removida em favor de recurso nativo.

### EVOLUIR COM IA/VETOR
Comportamento continua, mas o novo projeto deve ampliá-lo deliberadamente.

### DESCARTAR
Não existe justificativa de produto/técnica para continuar.

### AINDA NÃO SABEMOS
Evidência insuficiente; exige investigação/homologação.

## 12. Segurança

Inventário deve registrar:

- capabilities;
- nonces;
- sanitização;
- validação;
- escaping;
- rate limits;
- privacidade;
- secrets;
- risco de CSRF/IDOR/escalada;
- ações destrutivas.

## 13. UI/UX

Catalogar:

- menu/submenu;
- rotas;
- telas;
- filtros;
- paginação;
- estados;
- botões;
- shortcodes;
- frontend;
- Design Systems/CSS scopes;
- conflitos conhecidos Astra/Elementor/wp-admin.

## 14. Dados

Construir mapa de ownership:

`dado → proprietário atual → leitores → escritores → novo proprietário proposto`.

## 15. Testes

O inventário deve mapear testes existentes para comportamentos, identificando:

- testes úteis como regressão futura;
- testes acoplados a implementação antiga;
- lacunas sem cobertura;
- testes manuais obrigatórios;
- Golden Queries existentes.

## 16. Critérios de aceite

- [ ] 100% dos arquivos de runtime relevantes dos três projetos foram classificados.
- [ ] 100% dos mecanismos persistentes foram inventariados.
- [ ] Hooks/rotas/capabilities/cron/shortcodes/AJAX/REST foram catalogados.
- [ ] Tabelas, meta keys, options e transients foram catalogados.
- [ ] Testes e gates existentes foram mapeados.
- [ ] Sobreposições entre plugins foram identificadas.
- [ ] Contratos quebrados/drift foram registrados.
- [ ] Cada domínio possui decisão preliminar.
- [ ] Matriz de paridade futura existe.
- [ ] Lista de dados que não podem ser perdidos existe.
- [ ] SPEC-001 pode ser escrita sem adivinhação estrutural.

## 17. Critérios de NÃO aceite

Bloqueiam conclusão:

- inventário baseado apenas em READMEs;
- arquivos de runtime não lidos;
- tabelas/options/meta desconhecidos;
- não saber quais fluxos reais precisam sobreviver;
- começar runtime novo para “ganhar tempo”;
- assumir que documentação e código estão sincronizados sem verificar.

## 18. Rollback

Não há alteração de runtime nesta SPEC. Rollback é apenas documental via Git.

## 19. Evidências

- inventários por projeto;
- matrizes de contratos;
- catálogo de persistência;
- catálogo de integração;
- catálogo de testes;
- ADRs produzidas;
- checklist final da SPEC.
