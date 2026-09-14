# SPEC-002 — Resumo Executivo Integrado

**Status:** Planejada  
**Pré-requisito:** SPEC-001 concluída.

## Problema
O Resumo Executivo precisa deixar de ser um plugin separado e tornar-se domínio nativo da plataforma sem perder os dados já existentes.

## Resultado esperado
Reconstruir clean code do domínio dentro do Knowledge Studio, preservando inicialmente os oito `_bdc_es_*`, com edição, leitura, cobertura e renderer integrados.

## WordPress-first
`register_post_meta`, Metadata API, capabilities e formulários nativos antes de qualquer tabela/API própria.

## Princípio de negação
Não criar tabela, REST ou JavaScript obrigatório para resolver um domínio que o WordPress já atende com metadata.

## Gate
Abrir post no Studio → ler resumo existente → editar → salvar → reler → renderizar, sem tocar no conteúdo Elementor.
