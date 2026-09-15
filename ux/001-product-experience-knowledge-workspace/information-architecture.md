# Arquitetura de Informação v0 — UX-001

## Objetivo

Definir a organização conceitual do produto antes dos mockups detalhados. Este documento é uma hipótese controlada a ser validada visualmente e contra as jornadas reais.

## Estrutura global proposta

### 1. Base de Conhecimento

Entrada operacional principal.

- Lista de conhecimentos
- Abrir Knowledge Workspace
- Criar/editar conteúdo editorial continua pertencendo ao WordPress/Elementor conforme contrato

### 2. Vocabulários

Gestão controlada das taxonomias canônicas existentes.

- Audiências
- Equipes responsáveis
- Tipos de conhecimento
- Itens de catálogo

A gestão pode continuar usando a UI nativa WordPress enquanto entregar a experiência adequada. UX-001 não cria um segundo writer apenas para uniformidade visual.

### 3. Governança

Futuro domínio da SPEC-003. Na UX-001 aparece somente como arquitetura visual/placeholder controlado.

Possíveis superfícies de experiência a validar:

- fila de revisão;
- estado do conhecimento;
- responsável/revisor;
- qualidade;
- histórico de decisões.

Nenhuma dessas superfícies define contrato de dados nesta etapa.

### 4. Busca

Futuro domínio. Reservar coerência de navegação, filtro e resultados; não implementar nem definir algoritmo.

### 5. Operações / Inteligência

Futuro. Só deve existir se as SPECs posteriores justificarem casos de uso operacionais concretos.

## Jornada primária

`Lista de conhecimentos -> selecionar artigo -> Knowledge Workspace -> domínio desejado -> ação -> feedback -> permanência no contexto`

A navegação não deve obrigar retorno constante à lista após cada save.

## Knowledge Workspace

### Cabeçalho contextual

- breadcrumb/contexto;
- título do artigo;
- identificador/metadata essencial;
- estado visual quando o domínio Review existir;
- ações contextuais.

### Navegação interna

Hipótese preferencial: tabs ou subnav persistente, sujeita a validação de densidade e acessibilidade.

Domínios previstos:

- Visão geral
- Summary
- Classificação
- Review & Governança — futuro
- Histórico — futuro

### Painel contextual

Opcional, apenas se aumentar eficiência e não reduzir legibilidade. Pode receber estado, qualidade, responsável e sinais de saúde quando esses contratos existirem.

## Regras

1. Um domínio não deve abrir novo shell administrativo sem necessidade.
2. Ações destrutivas nunca competem visualmente com a ação primária.
3. O usuário deve saber sempre qual artigo está sendo tratado.
4. Save não deve causar perda de contexto.
5. Referência legada é informação secundária e não pode parecer valor canônico.
6. Estado vazio deve orientar a próxima ação sem sugerir criação automática indevida.
7. Funcionalidades futuras devem ser visualmente distinguíveis de recursos implementados durante prototipação.

## Decisões ainda abertas

- tabs horizontais vs navegação lateral interna;
- presença ou não de painel contextual fixo;
- densidade ideal da Knowledge List;
- até que ponto manter aparência wp-admin nativa vs camada visual própria;
- comportamento de Workspace em 782px e abaixo;
- mobile: edição completa vs experiência de consulta/ação essencial.
