# Real Content Acceptance Contract v1 — SPEC-004 / G-240

**Versão:** `1.0.0`  
**Natureza:** aceitação humana/estrutural read-only sobre amostra real  
**Pré-requisitos:** G-220 PASS + G-230 PASS

## 1. Objetivo

Comprovar que um Knowledge Document determinístico também é semanticamente fiel à fonte editorial real.

G-220/G-230 provaram parsing, segurança, hashes e repetibilidade. G-240 responde uma pergunta diferente: **o conhecimento necessário foi efetivamente preservado, em ordem, sem invenção e com estrutura suficiente?**

## 2. Invariantes

A ferramenta de aceitação:

- é `manage_options`;
- é read-only;
- não altera `post_content`, `_elementor_data`, status, datas, revisões, termos ou metas editoriais;
- não executa shortcode callback;
- não renderiza dynamic blocks;
- não renderiza Elementor como parte do extractor;
- não persiste seleção, verdict, comentários, documento ou hashes;
- não envia conteúdo para rede externa/IA;
- pode exibir conteúdo ao administrador dentro do wp-admin porque a inspeção humana é o objetivo do gate;
- o JSON de evidência não exporta corpo editorial, título ou URL.

## 3. Amostra mínima determinística

A seleção deve tentar cobrir, sem duplicar posts quando houver alternativa:

1. `elementor_native_typical` — Elementor válido/native representativo;
2. `elementor_or_mixed_complex` — mixed ou Elementor mais estruturalmente rico;
3. `legacy_typical` — Legacy HTML próximo da complexidade mediana;
4. `legacy_complex` — Legacy HTML estruturalmente rico;
5. `gutenberg` — post com blocks;
6. `shortcode_or_table` — shortcode/tabela relevante;
7. `review_required` — caso que exige revisão para futura migração Elementor;
8. `empty_or_corrupt` — fonte vazia/corrompida quando disponível.

Se uma categoria não existir, registrar `not_available`; não substituir silenciosamente por categoria diferente.

## 4. Complexidade / seleção

Para seleção determinística, calcular por post apenas métricas estruturais derivadas, por exemplo:

`score = sections + 3*headings + 3*lists + 5*tables + images + links + 2*code_blocks + 2*shortcodes + 4*warnings`

Regras:

- `typical`: candidato cuja quantidade de sections esteja mais próxima da mediana da categoria;
- `complex`: maior score; desempate por menor post ID;
- demais slots: maior aderência ao slot e depois menor post ID;
- a seleção deve permanecer estável para corpus editorial idêntico.

## 5. Superfície de inspeção

Cada card de aceitação deve mostrar ao administrador:

### Fonte editorial

- post ID;
- título apenas na tela local;
- source flags;
- source kind efetivo;
- warnings/readiness;
- conteúdo fonte apropriado ao tipo:
  - Legacy/plain/Gutenberg: `post_content` escapado, sem execução;
  - Elementor: `_elementor_data` escapado/formatado, sem renderização;
- links administrativos de edição podem ser exibidos apenas na tela local.

### Knowledge Document

- schema;
- `source_hash`;
- `document_hash`;
- source kind;
- sections em ordem;
- facts estruturais;
- strategies/warnings/readiness.

Nenhum dado da tela de inspeção é persistido pelo plugin.

## 6. Critérios humanos obrigatórios

Para cada amostra, o revisor responde somente flags estruturadas:

- `coverage_complete` — informação relevante da fonte está representada;
- `order_preserved` — sequência semântica relevante foi mantida;
- `no_invented_text` — documento não introduz texto inexistente;
- `structure_adequate` — headings/listas/tabelas/código/limites relevantes estão adequadamente representados;
- `acceptable_for_knowledge_use` — documento pode ser usado por busca/IA sem reparo manual obrigatório.

Falhas podem receber razões enum-only:

- `missing_content`;
- `wrong_order`;
- `invented_text`;
- `structure_loss`;
- `shortcode_semantics_missing`;
- `source_corrupt`;
- `other_review_required`.

Sem campo livre no v1 para evitar exportação acidental de conteúdo.

## 7. Stale guard da revisão

Ao renderizar a amostra, gerar fingerprint bruto por post usando, no mínimo:

- post status;
- modified GMT;
- hash do título;
- hash de `post_content`;
- hash de `_elementor_data`.

O fingerprint viaja apenas como hidden field. No submit final:

- recomputar fingerprint;
- se divergir, marcar `stale=true`;
- verdict daquele post não pode contar como PASS;
- não tentar reconciliar automaticamente.

## 8. Evidência JSON

Pode exportar:

- schema/mode/generated_at;
- ambiente e versão;
- fingerprints agregados before/after da geração do relatório;
- slots selecionados;
- post IDs;
- source/document hashes;
- source kind;
- readiness;
- verdict flags;
- reason enums;
- stale flag;
- contagens agregadas.

Não exporta:

- `post_content`;
- `_elementor_data`;
- sections/texto;
- título;
- URL;
- conteúdo de shortcode.

## 9. Gate G-240

PASS exige simultaneamente:

- pelo menos um exemplar para cada categoria disponível do contrato;
- todos os exemplos não-stale;
- `coverage_complete=true`;
- `order_preserved=true`;
- `no_invented_text=true`;
- `structure_adequate=true`;
- `acceptable_for_knowledge_use=true`;
- zero mutação editorial causada pela ferramenta;
- hash/documento repetível durante a aceitação;
- qualquer categoria `not_available` explicitamente registrada.

Uma falha humana bloqueia G-240 e vira caso de correção do extractor/contract; não é mascarada por IA, renderização arbitrária ou migration Elementor.

## 10. Relação com G-245

G-240 valida o **knowledge plane**.

Ele não autoriza writer Elementor. A classificação `native/projectable/review_required/blocked` continua apenas informativa até G-245 implementar preflight, dry-run, journal/rollback, stale-source guard e canário.

**Estado:** `FROZEN v1.0.0`.
