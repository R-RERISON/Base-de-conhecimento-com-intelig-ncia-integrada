# Prompt de Continuidade — SPEC-002 Classificação de Conhecimento

## Estado

- SPEC-001: CONCLUÍDA para desenvolvimento/homologação.
- Baseline funcional: `0.1.0-rc.1`.
- SPEC ativa: **SPEC-002 — Classificação de Conhecimento**.
- Fase: **S001 profiling read-only**.
- Runtime permanente de classificação: **NOT_READY / não autorizado**.

## Baseline de abertura

`main @ 8ec60e67c42afc6459ea6266c018c59730d86588`

## Objetivo imediato

Executar o profiler classificatório no WordPress real e obter evidência suficiente para decidir:

- quais conceitos entram no primeiro slice;
- Taxonomy vs Post Meta;
- cardinalidade single/multi;
- compatibilidade legada;
- política de normalização;
- rollback.

## Primeiro slice candidato, não autorizado ainda

- audiência;
- equipe responsável;
- tipo de conhecimento;
- item de catálogo.

## Regras

- não escrever nenhuma classificação antes do DoR;
- não mesclar `service/affected_service`;
- não mesclar `technologies/systems_involved`;
- não transformar categorias/tags editoriais em domínio do plugin;
- não criar tabela própria;
- não introduzir Search, Review, IA ou Analytics;
- não quebrar `0.1.0-rc.1`.

## Próximo passo exato

1. instalar package temporário de profiling SPEC-002;
2. abrir Base de Conhecimento;
3. executar **Profiling classificatório — gerar JSON**;
4. enviar o JSON;
5. revisar C-001;
6. somente depois criar a decisão física de S002.

## Package de profiling preparado

- build: `0.2.0-profile.1`;
- SHA-256: `0b09d21603f08a9c8d6ee887f0fd169032dd68a374ced80d18560f009f16d000`;
- PHP lint: PASS 6/6 arquivos PHP;
- natureza: temporário/read-only, autossuficiente, sem `wp-config.php`.
