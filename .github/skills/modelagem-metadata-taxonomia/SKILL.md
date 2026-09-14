# Skill — Modelagem Metadata, Taxonomia e Options

**Nível:** Especialista  
**Experiência mínima representada:** 12 anos em modelagem de conteúdo WordPress e gestão de informação.

## Quando usar

Ao decidir onde armazenar classificação, estado, configuração ou atributos de conhecimento.

## Heurística

### Post Meta
Use para atributos específicos do post, geralmente escalares ou de baixa cardinalidade, sem necessidade primária de navegação/facetas globais.

### Taxonomia
Use para conceitos reutilizáveis, compartilhados entre posts, filtráveis, facetáveis ou hierárquicos.

### Options/Settings
Use para configuração global do plugin.

### Tabela própria
Somente quando volume, consulta, índice, retenção ou atomicidade justificar tecnicamente.

## Procedimento

1. Definir ownership.
2. Definir cardinalidade.
3. Definir padrão de consulta.
4. Definir se precisa de UI nativa do WordPress.
5. Definir versionamento/revisão.
6. Aplicar WordPress-first e negação.
7. Registrar decisão em SPEC/ADR.

## Saída

Modelo de dados simples, pesquisável e alinhado ao Core.