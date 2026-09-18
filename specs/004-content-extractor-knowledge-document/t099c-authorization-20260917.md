# T099C — Human Authorization Record

Data: 2026-09-17
SPEC: SPEC-004 / G-245
Status: AUTHORIZED / EXECUTION PENDING

## Escopo autorizado

- post_id: `358`
- título: `LIA | Laboratório de Inteligência Analítica`
- source_kind: `legacy_html`
- authorization_id: `1557c1ee50e1a7a46df7d7952032cb1dd0cb374c7222de8656bb9c055f561bc9`
- expected block: `core/freeform`
- operação: apply + verify + rollback imediato

A autorização humana foi concedida na sessão do projeto após a apresentação do Authorization Pack T099B e é válida exclusivamente para a identidade acima. Qualquer drift de fidelity hash, serialization hash, dry-run hash ou serialized post_content invalida a autorização antes do write.

## Condições obrigatórias de execução

1. `manage_options`, `edit_post` e `unfiltered_html`;
2. zero journal/lock Block Migration preexistente;
3. revalidar `authorization_id` antes do write;
4. adquirir lock exclusivo;
5. persistir journal durável antes da mutação;
6. stale-source recheck imediatamente antes do write;
7. escrever apenas `WP_Post.post_content` via WordPress Core API;
8. `_elementor_data` não é destino de write;
9. verificar SHA-256 e `core/freeform` após apply;
10. executar rollback imediato para o `post_content` original;
11. verificar SHA-256 original após rollback;
12. liberar lock;
13. preservar journal `prepared → applied/partial_failure → rolled_back` como trilha de auditoria.

## Side effects WordPress aceitos para o canário

Como a mutação usa `wp_update_post()`, o WordPress pode atualizar `post_modified` e criar revisões normais. O contrato de rollback exige restauração byte-exata de `post_content` e preservação byte-exata de `_elementor_data`; a trilha de journal permanece intencionalmente persistida.

## Build autorizada

- `0.4.0-g245-canary-t099c.1`
- execução manual em homologação;
- nenhum batch autorizado;
- T100 continua bloqueado até PASS ambiental do T099C.

> Quem não sabe onde está, não sabe para onde quer ir.
