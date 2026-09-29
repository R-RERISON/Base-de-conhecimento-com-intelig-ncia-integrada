# Package — SPEC-005 R-510/T513-T514 Auto Validator

**Build:** `0.5.0-r510-t513.1`  
**SHA-256:** `e5c10eba2f831584536e0f3e0c2ab50102c87c504424117530c1eafd64cbe0bc`

## Objetivo

Substituir a revisão manual generalizada por validação automática objetiva.

O build executa:
- T513 Automated Golden Candidate Validator;
- T514 Diversity Validator;
- synthetic robustness de normalização.

Humano só é acionado para `REVIEW_REQUIRED`.

## Política

`AUTO_PASS` pode confirmar somente uma expectativa já originada de curadoria humana/histórica e preservada na fixture própria.

O validator **não pode**:
- inventar consulta Golden real;
- criar `expected_post_id`;
- trocar `expected_post_id`;
- aceitar ambiguidade;
- tratar variante sintética como uso real.

## Validação local

- unit tests: 9/9 PASS;
- PHP lint: 43/43 PASS pré-ZIP;
- PHP lint: 43/43 PASS pós-ZIP;
- active requires: 42/42;
- missing requires: 0;
- ZIP integrity: PASS;
- deterministic rebuild: PASS;
- Git blob parity: 5/5;
- ASI runtime technical identifiers: 0;
- auto-runner forbidden write/network calls: 0;
- Elementor writer: OFF.

## Artefato

O ZIP contém somente os componentes Golden de engenharia necessários:
- `class-golden-candidate-seed.php`;
- `class-golden-candidate-validator.php`;
- `class-golden-diversity-validator.php`;
- `class-golden-auto-validation-runner.php`.

T502, T510 e T511 runners não estão ativos no artefato.

## Homologação

Instalar sobre T511.2.

Menu:

`Base de Conhecimento -> Golden Auto Validator`

Executar:

`Executar validadores automáticos e baixar JSON`.

O JSON decidirá:
- quantos candidates são `AUTO_PASS`;
- quantos são `REVIEW_REQUIRED`;
- se há `AUTO_FAIL`;
- quais classes T514 já têm cobertura;
- synthetic robustness PASS/FAIL;
- se R-510 pode ou não avançar.

Nenhum resultado ambiental é presumido antes da execução.
