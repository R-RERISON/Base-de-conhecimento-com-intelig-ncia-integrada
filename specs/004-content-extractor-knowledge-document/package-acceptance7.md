# Package de homologação — 0.4.0-acceptance.7

## Objetivo

Validar o refinamento semântico do Knowledge Document 2.0.1 sem relaxar o gate G-240.

O `acceptance.6` mostrou 50 documentos `structure_incomplete`, concentrados em headings. A telemetria demonstrou que grande parte do delta vinha de marcação HTML historicamente estrutural, mas não equivalente a unidades semânticas globais: headings vazios e headings locais dentro de listas/tabelas.

## Alteração central

Foi introduzido `Semantic_DOM_Expectation`, que calcula a expectativa estrutural diretamente do DOM bruto, independentemente de `fragments[]`/`blocks[]`.

A expectativa passa a contar unidades semanticamente materializáveis, não apenas tags HTML brutas.

Regras principais:

- heading vazio não conta como heading semântico esperado;
- heading local dentro de lista/tabela/parágrafo/blockquote/code não é promovido ao outline global;
- `HTML_LOCAL_HEADING_FLATTENED:<n>` exige `review_required`;
- listas dentro de tabelas achatadas geram `HTML_NESTED_LIST_IN_TABLE_FLATTENED:<n>` e `review_required`;
- tabela aninhada não representada gera `HTML_NESTED_TABLE_UNREPRESENTED:<n>` e `not_ready`;
- expected continua derivado do DOM, não de `blocks[]`.

## Gate endurecido

O smoke schema `1.3.0` exige:

- `structure_incomplete=0` nas duas passagens;
- `ai_readiness.not_ready=0` nas duas passagens;
- zero errors/throwables;
- zero hash/canonical JSON mismatch;
- fingerprint editorial igual;
- zero posts alterados;
- corpus invariável.

## Versões

- plugin: `0.4.0-acceptance.7`;
- Knowledge Document: `2.0.1`;
- smoke report: `1.3.0`.

## Artefato

`base-conhecimento-inteligencia-integrada-0.4.0-acceptance.7.zip`

SHA-256:

`635016ef4a5acb4d94d04f7b0e9e05ce8d86af637c5654f96b0d58e3904cc91e`

## Validação local/package

- 28 arquivos runtime;
- PHP lint 24/24 PASS;
- JS syntax PASS;
- ZIP integrity PASS;
- staging ↔ ZIP parity 28/28 PASS;
- KD regression 12/12 PASS;
- structural namespace regression 6/6 PASS;
- local heading warning → `review_required` PASS;
- nested table warning → `not_ready` PASS;
- focused safety scan PASS.

## Paridade Git ↔ package

Blobs críticos reconciliados com o package validado:

- `class-semantic-structure.php`: `c34815516378c35bfb2e47075dc9a110cdac70b6`;
- `class-knowledge-document-v2-smoke.php`: `058cd1873dcaf86c16372302e9baafd71b6159b6`;
- `class-semantic-dom-expectation.php`: `a4440c728d644a3963652aa71c73818d9968ecd5`;
- `class-knowledge-document.php`: `87ec7f861368dbb8c15691432e9f8d9a13bc0ea9`;
- bootstrap: `c0e9dac678b78c07194cef88dd59f964900e0ec3`.

## Execução ambiental

1. instalar/substituir por `0.4.0-acceptance.7` em homologação;
2. não executar ainda `Aceitação G-240 v2`;
3. executar somente `Base de Conhecimento → Validação KD v2`;
4. retornar `bdc-kb-spec004-kd-v2-smoke-*.json`;
5. só `gate.pass=true` libera a revisão A/B dos oito casos.

G-245 permanece bloqueado e nenhum writer/migration Elementor está autorizado.
