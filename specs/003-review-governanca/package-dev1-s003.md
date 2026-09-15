# SPEC-003 — Package `0.3.0-dev.1`

## Identificação

- versão: `0.3.0-dev.1`;
- baseline anterior: `0.2.0-rc.1`;
- SHA-256: `632d2e5e56a7d89abd513f3f4b0b7f75f1c20a8dbd05e6ee383869489cf00acd`;
- finalidade: smoke do bootstrap permanente de Review & Governança antes do runner técnico/HTTP.

## Delta funcional

Somente:

1. bootstrap sobe versão e carrega Review;
2. `class-review-contract.php`;
3. `class-review-store.php`.

Não há ainda:

- handler HTTP de Review;
- formulário/tela de Review;
- runner de diagnóstico;
- migração;
- meta de current state;
- tabela customizada;
- AI Ready/score.

## Validação local

- PHP lint: **PASS 10/10**;
- unitários Review: **PASS 19/19**;
- comparação com RC anterior confirma que Summary/Classificação não foram alterados.

## Smoke esperado

1. instalar/substituir o plugin;
2. ativar sem fatal;
3. abrir Base de Conhecimento;
4. abrir listagem e artigo;
5. Summary e Classificação permanecem funcionando exatamente como na baseline;
6. nenhuma nova UI de Review aparece ainda — comportamento esperado nesta build.

Após smoke PASS, o próximo build acrescenta tooling temporário para provar o Review Store contra WordPress Comments API real antes de abrir G-070/UI.
