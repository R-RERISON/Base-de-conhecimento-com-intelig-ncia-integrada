# Visão Arquitetural — Base de Conhecimento com Inteligência Integrada

## Objetivo

Definir uma direção arquitetural inicial sem antecipar implementação antes da SPEC-000.

## Produto

Um único plugin WordPress, modular internamente, com uma experiência integrada baseada no Design System do KB2Ops.

```text
WordPress / Elementor
        │
        ▼
   Fonte editorial
        │
        ├───────────────┐
        │               │
        ▼               ▼
 Knowledge Core     Content Extractor
        │               │
        ├──────┬────────┘
        │      │
        ▼      ▼
  Curadoria   Projeção de conhecimento
        │      │
        │      ├── índice lexical
        │      ├── chunks
        │      └── embeddings/vetores
        │
        ├──────── Search / Retrieval
        │
        └──────── IA assistida
```

## Módulos conceituais

### Core
Responsável por bootstrap, lifecycle, capabilities, settings, health e coordenação mínima.

### Knowledge
Responsável pela camada de conhecimento ao redor do post: resumo, classificação, revisão, estado, qualidade e projeções.

### UI
Responsável pelo Design System e superfícies administrativas/públicas do plugin.

### Search
Responsável por query normalization, lexical retrieval, semantic retrieval, hybrid ranking, Golden Queries e contratos de resultado.

### Analytics
Responsável por eventos, interações, outcomes, lacunas e relatórios.

### Operations
Responsável por indexação, jobs realmente necessários, migrações, diagnóstico, reprocessamento e saúde.

### AI
Responsável por providers, Foundry, prompts versionados, embeddings, custo, uso e fluxos assistidos.

## Fonte da verdade e projeções

### Fonte editorial

`WP_Post` + Elementor.

### Fonte de classificação/governança

WordPress metadata e taxonomias, quando adequadas.

### Projeções

Índices, chunks e embeddings podem ser reconstruídos. Devem possuir hashes e versão de projeção.

## Busca prevista

```text
Query
  │
  ├── lexical
  │     └── FULLTEXT / índice derivado
  │
  └── semântica
        └── embedding / vetor
              │
              ▼
         Hybrid Ranker
              │
              ▼
       resultados confiáveis
```

Busca lexical deve funcionar sem IA e sem vetor.

## Inteligência Artificial

### Curadoria

`IA sugere → humano avalia → humano decide → WordPress persiste`.

### Resposta ao resolvedor

`query → retrieval → evidências → síntese opcional → links/fontes`.

## Decisão de armazenamento

Nenhuma tabela própria é criada sem justificativa na SPEC correspondente.

Tabelas candidatas futuras — não aprovadas ainda:

- índice de busca;
- chunks;
- embeddings;
- eventos de busca;
- jobs/fila durável.

## Elementor

O plugin pode ler:

- `post_content`;
- `_elementor_data`;
- HTML renderizado em fallback controlado.

O plugin nunca deve escrever em `_elementor_data`.

## REST/AJAX

Não são padrão obrigatório. Usar somente quando a superfície realmente exigir interação assíncrona ou consumidor externo.

## Degradação graciosa

- sem vetor → lexical continua;
- sem Foundry → curadoria manual e busca continuam;
- falha de telemetria → busca continua quando seguro;
- falha de indexação → fonte editorial não é afetada.

## Regra de arquitetura

A arquitetura deste documento é uma direção, não autorização para criar todas as camadas desde o início. Cada módulo só nasce quando uma SPEC vertical o justificar.