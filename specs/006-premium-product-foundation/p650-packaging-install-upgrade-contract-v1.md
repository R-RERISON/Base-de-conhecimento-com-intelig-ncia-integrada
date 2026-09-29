# P-650 — Packaging / Install / Upgrade Contract v1

**Status:** IMPLEMENTATION  
**Data:** 2026-09-29  
**Pré-requisito funcional:** P-640 permanece aberto até gate local completo; P-650 pode preparar tooling, mas não fechar release.

## Objetivo

Produzir um artefato de distribuição profissional a partir do mesmo source do plugin, sem carregar laboratório para produção e sem alterar dados, comportamento editorial, ranking ou ownership.

## Princípio de negação

Não criar:
- pipeline remoto;
- framework de release;
- updater próprio;
- instalador customizado;
- nova tabela/opção de versão;
- cópia paralela do plugin.

A solução mínima é um builder local determinístico que transforma somente o bootstrap de distribuição e exclui arquivos explicitamente classificados como engenharia/homologação.

## Contrato do ZIP de produção

O ZIP deve:

1. possuir raiz única `base-conhecimento-inteligencia-integrada/`;
2. manter versão pública `0.6.0-dev` durante SPEC-006;
3. remover fisicamente runners/smokes/profilers/acceptance declarados pelo `Engineering_Module_Loader`;
4. remover `class-engineering-module-loader.php` do ZIP;
5. remover do bootstrap distribuído:
   - flags de engenharia;
   - require do Engineering_Module_Loader;
   - chamadas `load_enabled()` e `register_enabled()`;
6. preservar Core, Search, Public Experience e Word Cloud;
7. preservar documentação de distribuição;
8. não incluir specs, evidence, tests, tools, vendor ou artefatos temporários;
9. gerar manifest + SHA-256;
10. validar deterministicidade por build duplo.

## Compatibilidade / instalação

Ativação e atualização:

- não executam rebuild automático;
- não alteram `post_content`;
- não escrevem em `_elementor_data`;
- não removem plugins legados;
- preservam projeções Search existentes;
- não executam migração destrutiva;
- mantêm rollback por reinstalação do pacote anterior.

## Arquivos históricos fora do ownership P-640

Classes Elementor/migração não carregadas atualmente **não são removidas neste primeiro slice**.

Disposition exige evidência de consumidor zero / rollback / gate apropriado. P-650 primeiro prova package pruning apenas para engenharia explicitamente classificada.

## Gates internos

- P650-01 contrato de packaging/install/upgrade;
- P650-02 builder local determinístico;
- P650-03 static/package contract;
- P650-04 build local e package integrity;
- P650-05 install/upgrade/rollback environmental;
- P650-06 Plugin Check do ZIP final;
- P650-07 closeout + Ledger.

## Critério de fechamento

P-650 só fecha quando o **mesmo ZIP**:
- passa integridade;
- instala limpo;
- atualiza sobre baseline;
- preserva dados;
- passa Plugin Check ou waivers explícitos;
- possui rollback demonstrado;
- não contém laboratório indevido.

## Rollback

Reinstalação do pacote anterior. Nenhuma migração irreversível é permitida neste gate.
