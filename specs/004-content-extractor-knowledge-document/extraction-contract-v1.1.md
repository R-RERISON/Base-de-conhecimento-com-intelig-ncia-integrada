# Extraction Contract v1.1 — SPEC-004

**Contract version:** `1.1.0`  
**Base:** `extraction-contract-v1.md` v1.0.0  
**Natureza:** amendment compatível; preserva integralmente o read-only contract e adiciona readiness para normalização Elementor/produção.

## 1. Regra de herança

Tudo em `extraction-contract-v1.md` v1.0.0 continua válido salvo onde este documento explicitamente amplia o contrato.

Nenhuma ampliação abaixo autoriza write editorial pelo Content Extractor.

## 2. Nova decisão arquitetural

Embora o R-200 tenha identificado predominância estatística de legacy HTML, o **editor operacional padrão atual da equipe é Elementor**. A heterogeneidade observada é tratada como dívida histórica de migração, não como direção editorial futura.

Consequência:

- o extractor continua multi-source e read-only;
- o Knowledge Document continua editor-independent;
- a saída intermediária passa a informar readiness para convergência editorial Elementor;
- a conversão real permanece em fluxo de migration separado.

## 3. `elementor_compatibility`

A saída intermediária do extractor passa a incluir:

```text
elementor_compatibility
  status
  reasons[]
```

Estados:

- `native` — Elementor JSON válido;
- `projectable` — fonte segura para futura projeção determinística;
- `review_required` — há warnings que tornam writer automático inadequado;
- `blocked` — limites/condições atuais impedem projection segura.

Esse campo é diagnóstico de migration readiness e não precisa integrar o corpo final do Knowledge Document.

## 4. DOM não é requisito rígido

`ext-dom` melhora parsing estrutural, mas não será requisito obrigatório para instalar/executar o extractor.

Quando `DOMDocument` não estiver disponível:

- usar fallback estrutural determinístico;
- preservar headings, listas, tabelas, links, code/pre e boundaries suportados;
- emitir `HTML_DOM_UNAVAILABLE`;
- não recorrer a renderização de tema/Elementor/shortcodes.

Isso reduz diferença operacional entre homologação e produção.

## 5. Gutenberg parse failure

Quando `parse_blocks()` não estiver disponível ou não devolver estrutura utilizável:

- emitir `GUTENBERG_PARSE_UNAVAILABLE`;
- não executar dynamic rendering;
- fallback posterior só segue caminhos já autorizados pelo contrato.

## 6. Warnings ampliados

Adicionar ao conjunto v1:

- `HTML_DOM_UNAVAILABLE`;
- `GUTENBERG_PARSE_UNAVAILABLE`.

Warnings de oversize podem carregar o nome da representação:

- `SOURCE_OVERSIZE_SOFT:post_content`;
- `SOURCE_OVERSIZE_SOFT:elementor_data`;
- `SOURCE_OVERSIZE_HARD:post_content`;
- `SOURCE_OVERSIZE_HARD:elementor_data`.

## 7. Migration readiness — classificação inicial

A classificação não promete fidelidade de writer; apenas orienta o próximo gate.

### `native`

- `_elementor_data` válido;
- não exige migration apenas por ser Elementor.

### `projectable`

Exemplos iniciais:

- legacy HTML estático sem dependência dinâmica detectada;
- plain text;
- conteúdo editorial vazio.

### `review_required`

Motivos iniciais:

- `ELEMENTOR_JSON_INVALID`;
- `SHORTCODE_NOT_EXPANDED:*`;
- `GUTENBERG_DYNAMIC_NOT_RENDERED:*`;
- `GUTENBERG_BLOCK_UNSUPPORTED:*`;
- `ELEMENTOR_WIDGET_UNSUPPORTED:*` quando relevante ao plano editorial.

### `blocked`

Exemplo inicial:

- source não-Elementor acima do hard safety limit sem alternativa segura.

## 8. Separação de writers

É proibido adicionar ao `Content_Extractor`:

- `update_post_meta()`;
- `wp_update_post()`;
- `wp_insert_post()`;
- chamadas ao Document save do Elementor;
- criação de revisões;
- enqueue de migration.

O futuro writer usa componente separado e gate próprio conforme:

- `elementor-normalization-contract-v1.md`;
- `production-rollout-contract-v1.md`.

## 9. Production-safe API

O extractor deve poder ser carregado em produção sem hooks editoriais obrigatórios. Ele funciona como serviço chamado por consumidores futuros.

Não criar job em background, tabela, cron ou processamento de corpus apenas por carregar/ativar a classe.

## 10. G-220 — cobertura adicional

Além do contrato v1.0.0, testar:

- fallback sem `DOMDocument`;
- classificação `native/projectable/review_required/blocked`;
- warnings source-specific de tamanho;
- ausência de writes em todos os paths testados;
- repetibilidade da saída intermediária.

## 11. Relação com produção

Instalar ou atualizar o plugin não converte conteúdo automaticamente para Elementor.

A convergência editorial será uma operação separada:

`preflight → dry-run → canário → batch → validação → rollback window`

## 12. Estado

**FROZEN v1.1.0 para implementação de G-220.**
