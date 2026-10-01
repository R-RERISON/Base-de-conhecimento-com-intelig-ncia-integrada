# P-640 — Modular Runtime Contract v1

**Status:** IMPLEMENTATION  
**Data:** 2026-09-29

## Objetivo

Reduzir o composition root monolítico do plugin sem alterar comportamento funcional, contratos de persistência, busca, conteúdo editorial ou autorização.

P-640 é uma refatoração estrutural governada: modularidade interna de um único plugin WordPress, carregamento condicional explícito e separação clara entre runtime de produto e runners de engenharia.

## Baseline observada

O arquivo `base-conhecimento-inteligencia-integrada.php` acumula atualmente:

- metadata/header e constantes;
- dezenas de build flags históricas;
- carregamento de classes permanentes;
- carregamento de Search;
- carregamento de Public Experience;
- carregamento de Word Cloud;
- carregamento de runners/smokes/profilers;
- registro de hooks de produto;
- registro de runners de homologação.

O arquivo `includes/class-plugin.php` já é uma orquestração pequena dos hooks permanentes, portanto não deve ser substituído por framework próprio.

## Princípio de negação

Rejeitado neste gate:

- container de DI genérico;
- framework de módulos externo;
- autoloader customizado complexo;
- service locator global;
- tabela/opção para feature flags;
- REST para configuração de módulos;
- Composer como requisito de runtime.

A solução mínima é um registry/loader explícito em PHP, usando `require_once`, constantes existentes e métodos `register()`.

## Fronteiras de runtime

### Core obrigatório

Sempre carregado:

- contratos/stores canônicos;
- classificação;
- revisão;
- Content Extractor/KD necessário às jornadas permanentes;
- Knowledge Workspace;
- Admin shell;
- Visual Foundation;
- `Plugin::register()`.

### Módulos de produto condicionais

Carregados apenas quando habilitados por contrato atual:

- Search;
- Public Experience Preview;
- Word Cloud;
- Core Blocks activity/executor enquanto gates históricos ainda exigirem coexistência.

### Engenharia/homologação

Runners, profilers, smoke tests e acceptance harnesses:

- não pertencem ao runtime obrigatório;
- devem possuir loader separado;
- não podem ser carregados quando a flag correspondente estiver false;
- serão candidatos à exclusão física do ZIP production em P-650.

## Contrato do Module Registry

Criar `Runtime_Module_Registry` com responsabilidades limitadas:

1. declarar módulos conhecidos;
2. declarar predicado de habilitação;
3. declarar arquivos necessários por módulo;
4. carregar somente módulos habilitados;
5. registrar classes de entrada de cada módulo quando aplicável;
6. falhar de forma explícita em arquivo obrigatório ausente durante desenvolvimento, sem fallback silencioso.

O registry não deve:

- armazenar estado em banco;
- decidir capability de usuário;
- encapsular regras de domínio;
- registrar endpoints por conta própria;
- descobrir classes por filesystem scan;
- usar reflexão;
- carregar laboratório permanentemente.

## Gates internos

- P640-01 baseline e contrato;
- P640-02 registry mínimo;
- P640-03 extrair carregamento de módulos de produto;
- P640-04 extrair carregamento de engenharia;
- P640-05 reduzir bootstrap ao composition root;
- P640-06 static contract + PHP lint + WPCS afetado;
- P640-07 regressão funcional dos módulos habilitados;
- P640-08 package/runtime inventory para handoff P-650.

## Invariantes

- versão permanece `0.6.0-dev`;
- Search lexical permanece habilitada e funcional;
- Public Experience Preview permanece habilitada no mesmo estado;
- Word Cloud permanece habilitada no mesmo estado;
- nenhuma alteração de ranking;
- nenhuma alteração de post_content;
- nenhuma escrita em _elementor_data;
- nenhum dado/migration novo;
- nenhum cutover/retirement;
- nenhum endpoint novo;
- nenhum framework externo.

## Critérios de aceite

P-640 só fecha quando:

1. o bootstrap deixa de enumerar diretamente runners/smokes individuais;
2. módulos permanentes possuem ownership explícito;
3. módulos desabilitados não carregam seus arquivos;
4. comportamento das features hoje habilitadas permanece equivalente;
5. PHP lint passa no plugin;
6. WPCS passa nos arquivos novos/alterados dentro do baseline do gate;
7. testes unitários/estáticos do registry passam;
8. inventário de arquivos de laboratório fica pronto para P-650;
9. `PROD-005` só é promovido no Master Ledger após evidência suficiente.

## Rollback

Rollback é puramente estrutural: reverter o commit P-640 restaura o bootstrap anterior. Nenhum dado precisa ser revertido, pois P-640 não altera persistência.
