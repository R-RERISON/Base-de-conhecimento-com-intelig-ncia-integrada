# KB2Ops — Matriz de Herança Visual

**Status:** MATRIZ v1 FECHADA  
**Evidência principal:** `evidence-kb2ops-visual-benchmark.md`

## Objetivo

Tratar KB2Ops como patrimônio de experiência do produto, sem reproduzi-lo de forma acrítica. Cada padrão relevante é classificado com base em evidência visual e separado dos contratos de persistência/domínio atuais.

## Classificação

### PRESERVAR

Manter a intenção/padrão quando:

- reduz esforço cognitivo;
- melhora hierarquia e orientação;
- é compatível com a arquitetura nova;
- não depende de writer/schema descontinuado.

### EVOLUIR

Manter a ideia, mas adaptar quando:

- precisa do Design System v1;
- precisa de acessibilidade/responsividade;
- usa conceitos históricos já substituídos;
- depende de domínio futuro ainda não autorizado;
- deve respeitar WordPress/Elementor como fonte editorial.

### DESCARTAR

Não transportar quando:

- é dívida de lifecycle;
- cria writer paralelo;
- reintroduz owner antigo;
- apresenta feature/estado sem contrato;
- aumenta complexidade sem caso de uso.

## Matriz v1

| ID | Tela/padrão KB2Ops | Decisão | Motivo | Destino no novo produto |
|---|---|---|---|---|
| K01 | Visão Geral / Dashboard | EVOLUIR | KPIs/progresso são úteis, mas precisam de métricas canônicas | Futuro Operações/Inteligência |
| K02 | Lista de Posts | PRESERVAR | Busca/filtros/tabs/tabela resolvem bem a navegação de alto volume | Knowledge List |
| K03 | Revisão de Post com tabs | PRESERVAR PADRÃO | Contexto + tabs evita página infinita e mantém artigo como unidade de trabalho | Knowledge Workspace master |
| K04 | IA Assistente de Revisão | EVOLUIR FUTURO | Boa separação “evidência / sugestão / aplicar”, mas IA não está autorizada agora | Futuro IA/Review assistido |
| K05 | Search Home do Resolvedor | PRESERVAR COMO REFERÊNCIA | Boa orientação para problema/pergunta | Futuro Search |
| K06 | Resultados de Busca | PRESERVAR COMO REFERÊNCIA | Query, filtros e relevância em lista densa | Futuro Search Results |
| K07 | Artigo do Resolvedor | PRESERVAR/EVOLUIR | Resolução rápida + escalonamento têm forte valor operacional | Futuro Knowledge Result |
| K08 | Classificação/Metadados | EVOLUIR | Layout é bom; conceitos/writers históricos não são autoridade | Workspace / Classificação canônica |
| K09 | Relatórios e Métricas | EVOLUIR FUTURO | Estrutura visual útil; depende de telemetria real | Futuro Telemetria |
| K10 | Configurações | EVOLUIR | Tabs/toggles só entram quando settings existirem por contrato | Settings futuros |
| K11 | Migração/Limpeza | DESCARTAR DA NAVEGAÇÃO | É jornada de lifecycle, não domínio permanente | Setup temporário se necessário |
| K12 | Página Pública/Shortcode | EVOLUIR FUTURO | Boa separação da experiência do resolvedor | Futuro Search/Public surface |

## Padrões PRESERVADOS como linguagem de experiência

1. **Contexto do artigo sempre visível** durante Summary/Classificação/Review.
2. **Tabs internas** para domínios do mesmo artigo.
3. **Lista densa antes de cards ornamentais** para gestão de muitos artigos.
4. **Badges semânticos** para estado curto, sempre acompanhados de texto/significado.
5. **Formulários em grid** no desktop, reflow em largura estreita.
6. **Progressive disclosure** para detalhe técnico e histórico.
7. **Separação Curador x Resolvedor**: mesma base canônica, experiências distintas.
8. **Busca como tarefa primária do resolvedor**, não como extensão do formulário administrativo.

## Padrões EVOLUÍDOS

### Sidebar própria

KB2Ops usava sidebar escura própria. No novo produto:

- dentro do wp-admin, não duplicar navegação global;
- fora do wp-admin, uma shell própria pode reutilizar navy/blue do Design System futuro;
- navegação interna do artigo usa tabs/subnav, não segunda sidebar.

### “AI Ready” / incluir na base IA

Não transportar como badge/toggle canônico. Qualquer elegibilidade futura depende de política e owner aprovados em SPEC posterior.

### Cobertura / score

Pode existir como visualização futura, mas somente se cálculo, source-of-truth e ação operacional forem definidos.

### Classificação histórica

A tela K08 é referência de composição, não de schema. O novo produto reconhece apenas os quatro conceitos canônicos da SPEC-002 neste slice.

## Padrões DESCARTADOS

- migração/limpeza como item permanente;
- writers paralelos;
- cards/KPIs sem pergunta operacional;
- estados inventados por mockup;
- duplicação de wp-admin por estética;
- dependência de campo histórico apenas porque existia no KB2Ops.

## Herança visual candidata

O visual contract KB2Ops histórico fornece uma base coerente de navy/blue, superfícies claras, bordas frias, status green/amber/red, raios 10–22px e sombras discretas. Esses tokens são adotados como **baseline candidata para mockups UX-001**, com escopo de produto e sem CSS global no wp-admin.

## Regra de autoridade

A ordem de autoridade permanece:

1. contratos funcionais atuais;
2. UX-001 congelada;
3. evidência de usabilidade/benchmark KB2Ops;
4. preferência estética.

O legado pode inspirar a experiência; nunca recupera autoridade sobre persistência, segurança ou schema.