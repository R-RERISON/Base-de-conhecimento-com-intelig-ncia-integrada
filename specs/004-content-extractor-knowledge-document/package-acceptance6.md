# Package `0.4.0-acceptance.6` — SPEC-004 G-240 v2

## Objetivo

Remediar a classe dominante restante do smoke `acceptance.5`: estruturas semanticamente relevantes contabilizadas pelo DOM, principalmente headings, que ficam escondidas sob wrappers HTML legados não pertencentes à allowlist original de containers.

O package permanece temporário, exclusivo de homologação e read-only.

## Evidência de entrada

`evidence/kd-v2-smoke-20260916T112024Z.json`

Resultado `acceptance.5`:

- corpus `622 → 622`;
- zero writes, errors, throwables, hash mismatch e canonical JSON mismatch;
- `structure_incomplete`: `78 → 50`;
- legacy_html: `62 → 44`;
- elementor: `14 → 5`;
- mixed: `2 → 1`;
- `list_items` mismatch: `40 docs → 0`;
- lists mismatch: `43 docs → 5`;
- headings mismatch permanece em 47 docs (`504 expected / 302 actual`);
- tables mismatch permanece em 2 docs (`6 expected / 4 actual`).

## Causa corrigida

`Legacy_HTML_Adapter::collect_structure()` observa headings/listas/tabelas em qualquer profundidade do DOM. Entretanto `walk_children()` originalmente só atravessava uma allowlist de wrappers (`div`, `section`, `article`, etc.).

Um wrapper histórico ou desconhecido podia conter um heading/lista/tabela, ser contado na origem e depois ser achatado como texto, impedindo a materialização do descendente em `blocks[]`.

`acceptance.6` mantém o comportamento inline normal, porém, quando um wrapper desconhecido contém descendente estrutural, atravessa esse wrapper recursivamente.

Não há renderização de tema, Elementor, shortcode ou bloco dinâmico.

## Telemetria adicional

O report temporário sobe de `1.1.0` para `1.2.0` e passa a agregar `extraction_warnings`.

Warnings diagnósticos possíveis, sem texto/ID/título/URL:

- `HTML_STRUCTURAL_WRAPPER_TRAVERSED:<tag>`;
- `HTML_DIAG_EMPTY_HEADINGS:<count>`;
- `HTML_DIAG_HEADINGS_IN_TABLE:<count>`;
- `HTML_DIAG_HEADINGS_IN_LIST:<count>`;
- `HTML_DIAG_NESTED_TABLES:<count>`;
- `HTML_DIAG_LISTS_IN_TABLES:<count>`.

Esses warnings são observabilidade; não alteram a regra `structure_incomplete=0` nem promovem documentos a `candidate_ready`.

## O que não mudou

- Knowledge Document schema `2.0.0`;
- `Semantic_Structure` e regra de reconciliação;
- política de hashes;
- shortcodes continuam sem execução;
- `do_shortcode()`/`render_block()` continuam proibidos;
- nenhuma persistência de Knowledge Document ou hashes;
- nenhuma migration/writer Elementor;
- G-245 continua bloqueado.

## Validação local/package

Package: `base-conhecimento-inteligencia-integrada-0.4.0-acceptance.6.zip`

SHA-256:

`e9879812b670b0a322920e137e4f1027808a92f93faae24ce7ece8122c9f3e09`

Checks:

- 27 arquivos no package;
- PHP lint `23/23 PASS`;
- JS syntax `PASS`;
- ZIP integrity `PASS`;
- staging ↔ ZIP parity `27/27 PASS`;
- safety scan `PASS`;
- blobs críticos Git ↔ package:
  - bootstrap `144415925f7d86c240f56c8235a91d2724a907eb`;
  - Legacy adapter `6c7061111d9acd47b4ebe044f8b44eac4efe0a41`;
  - smoke v2 `ea5c415b1d1b99b84168e5cd999050ec511ae7c2`.

O PHP CLI local não possui DOMDocument; a execução real do caminho DOM é obrigatoriamente validada no WordPress de homologação, onde `DOMDocument=true` já foi comprovado.

## Próximo gate

1. instalar/substituir pelo `0.4.0-acceptance.6`;
2. executar somente **Base de Conhecimento → Validação KD v2**;
3. não executar ainda **Aceitação G-240 v2**;
4. exigir zero write/error/throwable/hash/canonical mismatch;
5. observar redução dos 47 mismatches de headings e os novos `extraction_warnings`;
6. se `structure_incomplete=0`, liberar o reteste humano A/B dos mesmos oito posts;
7. se houver resíduo, corrigir somente a classe comprovada pela telemetria, sem relaxar o gate.
