# UX-001 — KB2Ops Heritage Pack v1

**Status:** FECHADO para baseline visual  
**Fonte:** 10 telas do designer KB2Ops fornecidas para a UX-001 em 15/09/2026.  
**Regra:** referência visual não recupera autoridade sobre schema, writer, IA, métricas ou estados de negócio.

## 1. Inventário das telas recebidas

| ID | Tela | Função observada | Decisão |
|---|---|---|---|
| H01 | Configurações | Geral, Integrações, Permissões, Limpeza, status do plugin | EVOLUIR |
| H02 | Revisão de Post / Resumo Executivo | Contexto do artigo, tabs, summary estruturado, checklist e análise lateral | PRESERVAR PADRÃO |
| H03 | Artigo operacional — VPN Always On | Resolução rápida, passos, ramificações e escalonamento | PRESERVAR/EVOLUIR |
| H04 | Posts da Base de Conhecimento | Busca, filtros, tabs de status, tabela densa, ações | PRESERVAR |
| H05 | Análise IA do Conteúdo | Elementos identificados e sugestões acionáveis | EVOLUIR FUTURO |
| H06 | Resultados Knowledge Search | Query, tabs, relevância, filtros laterais, resultados | PRESERVAR COMO REFERÊNCIA FUTURA |
| H07 | Relatórios e Métricas | KPIs, séries, distribuição e rankings | EVOLUIR FUTURO |
| H08 | Migração e Limpeza | lifecycle, limpeza, dados preservados, relatório e timeline | DESCARTAR DA NAVEGAÇÃO NORMAL |
| H09 | Search Home — Como posso resolver? | busca protagonista, temas populares e proposta de valor | PRESERVAR COMO REFERÊNCIA FUTURA |
| H10 | Classificação do Conhecimento | formulário em grid, tags, selects, status e save explícito | EVOLUIR PARA CONTRATO CANÔNICO |

## 2. Padrões que viram patrimônio oficial

### P01 — Cabeçalho contextual forte

Título, contexto, identificador e ações devem permanecer reconhecíveis durante a jornada. Esse padrão é base direta do novo Knowledge Workspace.

### P02 — Tabs internas por domínio

A tela H02 prova um padrão adequado para impedir crescimento vertical infinito. Summary, Classificação, Review e Histórico pertencem ao mesmo artigo, mas não precisam disputar o mesmo viewport.

### P03 — Lista densa para gestão

H04 é a principal referência para Knowledge List: busca e filtros acima; tabela/lista densa abaixo; metadados e ações por linha. Cards ornamentais não substituem essa estrutura.

### P04 — Grid editorial de duas colunas

H10 demonstra boa densidade para classificação. O novo produto preserva a composição, mas somente com os quatro conceitos autorizados pela SPEC-002.

### P05 — Painel contextual lateral

H02 mostra valor quando o painel contém informação acionável. No novo produto ele é permitido apenas quando existir contrato real; em largura estreita deve reflowar para o corpo.

### P06 — Separação Curador x Resolvedor

H02/H04/H10 representam curadoria. H03/H06/H09 representam resolução. As duas experiências compartilham dados e Design System, mas não precisam compartilhar o mesmo shell.

### P07 — Semântica por cor + texto + ícone

Aprovado, alerta, erro e saúde usam cor como reforço, nunca como único significado.

### P08 — Progressive disclosure

Acordeões, detalhes técnicos, histórico e ramificações entram sob demanda. Informação essencial fica acima da dobra.

## 3. O que não será transportado automaticamente

- `AI Ready` como estado canônico;
- toggle “incluir na base IA”;
- scores de qualidade/completude/relevância;
- tecnologias, versões, palavras-chave, serviço e categoria como campos canônicos apenas porque aparecem no mockup;
- métricas de dashboard sem owner e pergunta operacional;
- migração/limpeza como módulo permanente;
- sidebar KB2Ops duplicada dentro do wp-admin;
- writers históricos ou paralelos.

## 4. North Stars adotados

### NS-01 — Knowledge Workspace

Referência principal: H02, evoluída para a arquitetura nova.

Deve conter:

- cabeçalho contextual persistente;
- tabs internas;
- domínio ativo no corpo principal;
- ação primária única por contexto;
- painel contextual somente quando houver dado/ação real;
- Summary e Classificação sem alterar seus contratos atuais.

### NS-02 — Knowledge List

Referência principal: H04.

Deve conter:

- busca;
- filtros progressivos;
- lista/tabela de alta densidade;
- status somente quando autorizado por domínio;
- acesso direto ao Workspace;
- paginação e ações em massa apenas quando houver contrato real.

### NS-03 — Resolvedor futuro

Referências: H03 + H06 + H09.

Princípio: problema/pergunta primeiro, conteúdo operacional depois. Não entra no runtime durante UX-001.

## 5. Resultado

O Heritage Pack fecha a coleta genérica de referência para esta wave. A próxima etapa é transformar os padrões preservados em Design System v1 e Master Mockups editáveis, começando por Knowledge Workspace e Knowledge List.