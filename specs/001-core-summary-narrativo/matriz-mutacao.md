# Matriz de Mutação — SPEC-001

| Ação | Ator | Capability | Método | Nonce | Validação | Persistência | Confirmação | Diagnóstico |
|---|---|---|---|---|---|---|---|---|
| listar/selecionar posts | usuário autenticado | entrada mínima administrativa + `edit_post` filtrado por objeto | GET | N/A | `post_type=post`, paginação, IDs válidos | nenhuma | render read-only | erro seguro/sem write |
| abrir Summary de um post | Analista de Conhecimento | `current_user_can('edit_post', post_id)` | GET | N/A | ID >0, post existe, `post_type=post` | nenhuma | snapshot relido das 3 metas | 403/erro seguro para objeto fora do scope |
| salvar Summary | Analista de Conhecimento | `current_user_can('edit_post', post_id)` no handler | POST | obrigatório, específico do fluxo/post | allowlist exata; tipos string; <=32768 bytes/campo; `wp_unslash`; sanitização integral antes do write | Metadata API; snapshot+diff+writes mínimos | read-after-write dos 3 campos + comparação + PRG | SUCCESS / FAIL_SAFE / PARTIAL_FAILURE_CRITICAL |
| limpar campo | Analista de Conhecimento | `edit_post` por objeto | POST | obrigatório | valor sanitizado vazio | `delete_post_meta` apenas no diff | `metadata_exists=false` + snapshot final | mesma semântica B-006 |

## Regras transversais

- capability do menu nunca substitui capability do objeto;
- GET nunca muta;
- nonce nunca substitui autorização;
- request com campo desconhecido, tipo inválido ou limite excedido falha inteiro antes de qualquer write;
- meta key física nunca é derivada de input externo;
- `post_type` nunca vem de input como autoridade; a implementação valida `post` no objeto resolvido;
- nenhum `$_POST` é passado em massa para Metadata API;
- toda saída dinâmica recebe escaping contextual;
- redirect pós-POST contém apenas códigos/IDs allowlisted, não payload cru.
