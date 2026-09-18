# UX-003 — Consolidação Visual da Post Management Workspace

**Status:** PASS LOCAL / HOMOLOGAÇÃO PENDENTE  
**Build:** `0.4.0-g245-ux003.1`  
**Base funcional:** T100D PASS + T100E-E5 local

## Objetivo

Consolidar a experiência visual da Workspace já aprovada funcionalmente, sem alterar contratos editoriais, journal, lock, stale guard, autorização ou persistência.

## Decisões

### Navegação

- A barra superior da Workspace é a única navegação entre áreas do artigo.
- A Visão geral não replica as mesmas ações como um segundo menu.
- Cards da Visão geral passam a ser indicadores de estado, sem botões redundantes.
- O Preflight G-245 deixa de aparecer no menu lateral do produto.
- O preflight técnico continua disponível programaticamente enquanto T100E não fornecer substituto ambiental equivalente.

### Idioma e redação

A superfície de produto deve usar português do Brasil de forma consistente.

Padronização principal:
- `Summary` → **Sumário**;
- `Core Blocks` → **Blocos do WordPress**;
- `Review & Governança` → **Revisão e governança**;
- `Status` → **Situação**, quando aplicável;
- termos de gates, hashes, dry-run, journal, lock e authorization id não aparecem na superfície principal.

A redação deve:
- respeitar a ortografia vigente do português do Brasil;
- usar linguagem institucional, objetiva e compreensível;
- evitar jargão de desenvolvimento;
- usar convenções documentais da ABNT quando aplicáveis a documentos/relatórios, sem forçar terminologia técnica de ABNT em microcopy de interface.

### Layout

- Workspace usa toda a largura útil do wp-admin.
- Não há max-width fixo de 1240/1320 px para a superfície principal.
- Visão geral usa grade responsiva de indicadores.
- Abas continuam responsivas e horizontalmente navegáveis em telas menores.
- Sidebar contextual permanece apenas nas áreas específicas; a Visão geral ocupa largura integral.

### Blocos do WordPress

A interface deve traduzir estados técnicos para linguagem de produto.

Exemplos:
- `no_action_required` → **Atualizado**;
- `applied` → **Aplicada**;
- `free` → **Nenhum**;
- `core/freeform` → **Conteúdo preservado**.

No post 358, após T100D PASS, a expectativa visual é:
- origem atual: Blocos do WordPress;
- situação: Atualizado;
- migração necessária: Não;
- histórico de migração: registros existentes;
- última operação: Aplicada;
- bloqueio operacional: Nenhum;
- nenhuma nova ação de write disponível.

## Anti-regressão

UX-003 não autoriza:
- novo writer;
- novo batch;
- alteração editorial;
- alteração de `_elementor_data`;
- reativação do executor T100D;
- remoção do Elementor Adapter;
- remoção do Production Preflight runtime.

## Aceite

PASS de homologação requer:
1. Preflight ausente do menu lateral;
2. navegação superior única;
3. Visão geral sem botões redundantes;
4. pt-BR consistente;
5. ausência de textos de desenvolvimento na superfície principal;
6. largura útil ampliada;
7. Summary/Classificação/Revisão/Histórico continuam operacionais;
8. post 358 continua em estado Core Blocks aplicado;
9. nenhuma mutação editorial provocada pela atualização visual.
