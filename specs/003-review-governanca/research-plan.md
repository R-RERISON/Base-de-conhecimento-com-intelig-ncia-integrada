# SPEC-003 — Research Plan / Profiling Read-only

## Objetivo

Levantar o estado real de Review & Governança antes de definir schema, estado ou writer.

## 1. Fontes a inspecionar

### Código legado disponível

Buscar referências a conceitos como:

- review / revisão;
- approval / aprovado;
- reviewer / revisor;
- responsible / responsável;
- quality / qualidade;
- completeness / completude;
- AI Ready / elegibilidade;
- last review / reviewed at;
- workflow / status de conhecimento.

A busca deve registrar caminho, símbolo, tipo de acesso e store afetado; não inferir semântica só pelo nome.

### Banco WordPress

Profiler temporário e read-only deve limitar-se a stores candidatos descobertos no código ou documentação histórica.

Medir por candidato:

- número de posts com valor;
- número total de rows;
- valores distintos normalizados;
- cardinalidade;
- tipos/formato observados;
- tamanho máximo;
- timestamps/IDs de usuário quando existirem;
- distribuição por `post_status` editorial;
- exemplos sanitizados somente quando necessários para classificar a estrutura.

## 2. Writer / Consumer Map

Para cada candidato:

| Campo | Descrição |
|---|---|
| owner histórico | plugin/módulo que aparentemente escrevia |
| writer | função/handler/cron identificado |
| consumer | UI/query/regra que lê |
| primitive | meta/taxonomia/tabela/opção/comment/etc. |
| capability | autorização observada |
| side effects | relações com outros stores |
| confiança | alta/média/baixa + justificativa |

## 3. Perguntas obrigatórias

1. Existe um estado histórico de review realmente utilizado ou apenas visual?
2. O estado histórico tem semântica separada de `post_status`?
3. Há actor/reviewer persistido de modo confiável?
4. Há data de revisão distinta de `post_modified`?
5. Existe histórico real ou só estado atual sobrescrito?
6. Algum score possui fórmula reproduzível e ação operacional?
7. `AI Ready` existia como derivado, toggle ou estado manual?
8. Existem writers concorrentes para o mesmo conceito?
9. Existe volume que justifique tabela customizada para eventos?
10. Algum legado possui equivalência forte o bastante para migração automática? A resposta padrão é NÃO até prova.

## 4. Profiler temporário

Requisitos:

- admin-only / `manage_options`;
- POST + nonce;
- queries SELECT-only;
- nenhuma criação de termos/metas/options;
- nenhum conteúdo editorial bruto no JSON;
- nenhuma gravação persistente de relatório;
- download JSON explícito;
- versão/schema próprios;
- cleanup por remoção do próprio tooling no package seguinte.

## 5. Saída R-001

O Gate R-001 pode fechar PASS mesmo se não houver dado histórico útil. PASS significa que sabemos o suficiente para decidir com segurança, não que encontramos legado migrável.

Saída esperada:

- `evidencia-profiling-s001.md`;
- JSON de homologação;
- matriz writers/consumers;
- decisão inicial por conceito: `MIGRATION_CANDIDATE`, `ADVISORY_ONLY`, `DISCARD`, `NO_EVIDENCE`.

## 6. Proibições

- não criar estado canônico durante profiling;
- não migrar valores;
- não criar tabela;
- não criar `AI Ready`;
- não criar score;
- não assumir que `post_modified` é data de revisão;
- não assumir que autor/editor é reviewer;
- não tratar mockup KB2Ops como prova de persistência.
