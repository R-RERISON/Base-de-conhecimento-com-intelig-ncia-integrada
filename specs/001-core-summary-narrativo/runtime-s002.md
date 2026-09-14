# S002 — Runtime mínimo da SPEC-001

> Estado: **IMPLEMENTADO — ainda não homologado**.  
> Baseline de entrada: `main @ 7bbe347f0f3fe0156f9c25c7564bfbc66c02bda9`.  
> Escopo: T020–T027. Gates funcionais/WordPress/browser permanecem `NOT_RUN` até S003.

## 1. Piso técnico

- WordPress: **6.6 ou superior**.
- PHP: **8.1 ou superior**.

O piso não tenta prometer compatibilidade histórica não testada. Ele mantém o baseline já comprovado pelas referências WordPress-first do projeto e permite tipagem moderna sem adicionar dependências externas.

## 2. Árvore mínima

```text
plugin/base-conhecimento-inteligencia-integrada/
├── base-conhecimento-inteligencia-integrada.php
├── assets/
│   └── css/
│       └── admin.css
└── includes/
    ├── class-admin-page.php
    ├── class-meta-contract.php
    ├── class-plugin.php
    └── class-summary-store.php
```

Não há Composer obrigatório, JavaScript, REST, AJAX, tabela, schema, migration, queue, IA ou integração externa.

## 3. Bootstrap/lifecycle

O bootstrap somente:

1. valida `ABSPATH`;
2. declara versão/caminhos;
3. carrega quatro classes;
4. registra hooks mínimos.

Não existe activation hook, deactivation hook, uninstall destrutivo, upgrade runner ou trabalho pesado de lifecycle.

## 4. Meta Contract

Somente `post` e exatamente três metas:

- `objective` -> `_bdc_es_objective`;
- `escalation` -> `_bdc_es_escalation`;
- `important` -> `_bdc_es_important`.

`show_in_rest=false`, `single=true`, string, sem revisions. A autorização de metadata usa `user_can($user_id, 'edit_post', $object_id)`.

## 5. Summary Store

A leitura é side-effect free.

A escrita implementa:

- validação integral antes de qualquer write;
- allowlist exata;
- somente strings;
- limite de 32768 bytes antes da sanitização;
- `trim(sanitize_textarea_field())`;
- omitido preservado;
- vazio sanitizado = delete;
- valor idêntico = NO_CHANGE;
- writes somente no diff;
- releitura integral após writes.

### B-006

Fluxo implementado:

`authorize -> validate all -> sanitize all -> snapshot -> diff -> writes mínimos -> reread -> compare`.

Se houver mismatch:

`compensação best-effort dos campos alterados -> reread`.

Resultados:

- esperado -> `SUCCESS`;
- snapshot restaurado -> `FAIL_SAFE`;
- restauração incompleta -> `PARTIAL_FAILURE_CRITICAL`.

O estado crítico registra somente `post_id` e nomes lógicos dos campos no `error_log`; conteúdo dos campos não é logado.

## 6. Superfície administrativa

- menu único no wp-admin;
- capability de entrada `edit_posts`;
- listagem paginada de `post`, 20 por página;
- cada objeto é revalidado com `edit_post`;
- editor com título read-only e três textareas;
- GET é apenas leitura;
- mutação por `admin-post` autenticado;
- POST obrigatório;
- nonce vinculado ao post;
- payload `summary[...]` passa pelo store e sua allowlist;
- POST-Redirect-GET;
- mensagens são códigos allowlisted, sem refletir payload cru;
- escaping contextual em toda saída dinâmica.

## 7. UI mínima

A UI usa o shell e componentes nativos do wp-admin (`wrap`, `widefat`, `button`, notices e `submit_button`). CSS próprio trata apenas espaçamento, largura, textarea, paginação e viewport administrativo estreito. Não existe segunda sidebar nem framework visual externo.

## 8. Validação executada neste bloco

PHP lint local executado nos cinco arquivos PHP do runtime com PHP `8.4.23`:

- bootstrap: PASS;
- Admin_Page: PASS;
- Meta_Contract: PASS;
- Plugin: PASS;
- Summary_Store: PASS.

Essa evidência prova apenas sintaxe. Não substitui integração WordPress, fault injection, segurança negativa ou browser acceptance.

## 9. Estado dos gates

- G-001: `NOT_RUN`;
- G-020: `NOT_RUN`;
- G-070: `NOT_RUN`;
- G-110: `NOT_RUN`;
- G-130: `NOT_RUN`/condicional até package;
- B-006 fault injection: `NOT_RUN`.

A SPEC permanece **Em implementação**, não Homologação.

## 10. Próximo passo

S003/T040–T046: materializar testes, integração WordPress, fault injection B-006, browser acceptance e evidências. Nenhum GO de produção/cutover é derivado deste runtime.
