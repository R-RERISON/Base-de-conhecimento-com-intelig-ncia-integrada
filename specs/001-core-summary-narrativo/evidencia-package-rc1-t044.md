# T044/G-130 — Preparação do package limpo `0.1.0-rc.1`

> Estado: **PREPARADO — lifecycle real no WordPress ainda PENDENTE**.

## Objetivo

Remover integralmente instrumentos temporários usados na homologação e produzir um release candidate mínimo, sem alterar o core funcional já homologado.

## Delta em relação ao runtime homologado

O core de produto homologado permanece o mesmo:

- `assets/css/admin.css`;
- `includes/class-admin-page.php`;
- `includes/class-meta-contract.php`;
- `includes/class-summary-store.php`.

Foram alterados somente bootstrap/orquestração para retirar homologação e promover a versão a `0.1.0-rc.1`:

- `base-conhecimento-inteligencia-integrada.php`;
- `includes/class-plugin.php`.

Foram removidas do package:

- `class-diagnostics-runner.php`;
- `class-browser-acceptance.php`;
- `class-http-security-diagnostics.php`;
- `BDC_KB_HOMOLOGATION_BUILD`;
- `BDC_KB_ENABLE_DIAGNOSTICS`;
- hooks, notices, painéis e markers temporários.

## Verificações locais

- PHP lint: **PASS 5/5 arquivos PHP**;
- scan de strings/markers temporários: **PASS — zero ocorrência**;
- raiz única de plugin: **PASS**;
- quantidade total no ZIP: **6 arquivos**;
- SHA-256 do package: `c395e65f872f56f3930f0a3f14ec192c03bb6a52a5623360fa15bf7e0c15e7fb`.

Package: `base-conhecimento-inteligencia-integrada-0.1.0-rc.1.zip`.

## Política de desinstalação

A SPEC-001 não cria tabela, option, transient persistente, cron, usuário ou taxonomia própria. Os três metadados canônicos são deliberadamente reutilizados do domínio existente (`_bdc_es_objective`, `_bdc_es_escalation`, `_bdc_es_important`).

Por segurança, o RC **não possui rotina destrutiva de uninstall**: remover o plugin não deve apagar conteúdo institucional compartilhado/preexistente. Deactivation também não apaga dados.

## Gate ainda pendente

G-130 só poderá ser marcado PASS após o `0.1.0-rc.1` ser instalado/substituído no WordPress real e comprovar:

1. upgrade/substituição sem fatal error;
2. plugin ativo na versão RC;
3. ausência total dos painéis/botões de diagnóstico;
4. listagem e leitura de Summary continuam funcionando;
5. desativar -> ativar sem erro;
6. dados existentes continuam legíveis após reativação;
7. nenhuma fixture de homologação reaparece.
