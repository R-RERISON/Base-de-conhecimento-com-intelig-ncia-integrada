# Prompt de Continuidade — SPEC-002 Classificação de Conhecimento

## Estado

- SPEC-001: CONCLUÍDA para desenvolvimento/homologação.
- Baseline de regressão: `0.1.0-rc.1`.
- SPEC ativa: **SPEC-002 — Classificação de Conhecimento**.
- C-001: **PASS**.
- C-010: **PASS**.
- G-001: **PASS**.
- G-030: **PASS**.
- B-006: **PASS**.
- G-070: **PASS**.
- G-110: **PASS**.
- Regressão SPEC-001: **PASS**.
- G-130: **PENDENTE lifecycle real do RC limpo**.

## Slice canônico

Taxonomias WordPress namespaced:

- `audience` -> `bdc_kb_audience` — multi;
- `responsible_team` -> `bdc_kb_responsible_team` — multi;
- `knowledge_type` -> `bdc_kb_knowledge_type` — single;
- `catalog_item` -> `bdc_kb_catalog_item` — multi.

Legado permanece somente referência read-only. Não há dual-write, seed automático ou migração destrutiva.

## Evidência funcional final

- técnico `0.2.0-dev.3`: 20/20 PASS;
- HTTP `0.2.0-dev.7`: 18/18 PASS;
- browser `0.2.0-dev.8`: overall PASS, 2 manual PASS, 0 auto FAIL, min viewport 492x660;
- todos os runners encerraram com zero resíduos e sem modificar conteúdo real.

## RC preparado

- build: `0.2.0-rc.1`;
- SHA-256: `a5120299ea907d271bc39b318857ba033cf0cf340fe1836289f42a4c717b8fb5`;
- PHP lint: PASS 8/8;
- scan por instrumentação temporária: PASS;
- runners/flags de homologação removidos;
- nenhuma rotina destrutiva de uninstall introduzida;
- pequena melhoria CSS: multi-select de vocabulário vazio fica compacto, sem alterar contrato ou write path.

## Próximo passo exato — G-130

1. substituir `0.2.0-dev.8` por `0.2.0-rc.1`;
2. confirmar versão `0.2.0-rc.1` e ausência de qualquer painel/botão de homologação;
3. abrir a listagem e um artigo real apenas para leitura;
4. confirmar Summary e painel Classificação normais;
5. confirmar que os quatro links de vocabulário continuam disponíveis para administrador;
6. desativar o plugin;
7. ativar novamente;
8. repetir listagem + leitura de Summary/Classificação;
9. confirmar que dados existentes permanecem e nenhum fixture reaparece.

Não é necessário alterar conteúdo real no lifecycle.

## Depois do G-130

Se PASS: fechar T046B/T047, congelar `0.2.0-rc.1` como baseline e abrir **SPEC-003 — Review & Governança**. Não iniciar Search, vetores ou IA antes dessa decisão.
