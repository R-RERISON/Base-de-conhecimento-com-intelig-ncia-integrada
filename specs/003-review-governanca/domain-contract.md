# SPEC-003 — Domain Contract R-010 — Review & Governança

**Status:** APROVADO PARA IMPLEMENTAÇÃO DA PRIMEIRA SLICE  
**Dependência:** R-001 PASS  
**Baseline funcional:** `0.2.0-rc.1`

## 1. Owner canônico

O domínio **Review & Governança** é o único owner de decisões humanas sobre o estado de governança do conhecimento.

Ele não possui autoridade sobre:

- `post_status` editorial;
- `post_content`;
- `_elementor_data`;
- Summary da SPEC-001;
- Classificação da SPEC-002;
- Search/IA.

## 2. Primitiva canônica

### Decisão

A primeira slice NÃO terá uma meta separada de `current_state`.

A fonte canônica será um **event log append-only usando WordPress Comments API com `comment_type` dedicado**.

Tipo canônico:

`bdc_kb_review_event`

Cada evento pertence a um `post` e registra, em uma única inserção lógica:

- `from`;
- `to`;
- `note` sanitizada;
- schema version do evento;
- `user_id` nativo do comentário;
- timestamp GMT nativo do comentário;
- `comment_post_ID` nativo.

### Razão

Usar o evento como fonte da verdade elimina a divergência clássica `estado atual != histórico`. A leitura do estado atual deriva do último evento válido do artigo.

Nenhuma tabela customizada, post meta de estado ou dual-write é necessária para o volume atual.

## 3. Estado inicial

Se o artigo não possui evento `bdc_kb_review_event`, seu estado lógico é:

`unreviewed`

Esse estado é **implícito** e não gera row apenas por leitura.

## 4. Estados da primeira slice

| Estado | Significado |
|---|---|
| `unreviewed` | ausência de decisão canônica; estado inicial derivado |
| `in_review` | artigo submetido ou reaberto para revisão |
| `needs_changes` | revisor decidiu que são necessárias correções |
| `approved` | revisor aprovou explicitamente o conhecimento |
| `excluded` | revisor decidiu que o artigo não deve participar da base governada neste momento |

`approved` NÃO significa publicado, indexado, AI Ready ou incluído em IA.

`excluded` NÃO apaga o post e NÃO altera `post_status`.

## 5. Máquina de transições

Transições permitidas:

- `unreviewed -> in_review`
- `unreviewed -> approved`
- `unreviewed -> excluded`
- `in_review -> approved`
- `in_review -> needs_changes`
- `in_review -> excluded`
- `needs_changes -> in_review`
- `needs_changes -> approved`
- `needs_changes -> excluded`
- `approved -> in_review`
- `approved -> needs_changes`
- `approved -> excluded`
- `excluded -> in_review`

Mesmo estado -> mesmo estado é `NO_CHANGE` e não cria evento.

Qualquer outra transição é inválida e produz zero write.

## 6. Capabilities / atores

Toda mutação exige:

1. post válido do tipo `post`;
2. `current_user_can('edit_post', $post_id)`.

Além disso, decisões de reviewer exigem `current_user_can('edit_others_posts')`:

- destino `approved`;
- destino `needs_changes`;
- destino `excluded`;
- reabertura de `approved` ou `excluded` para `in_review`.

Submissão simples `unreviewed/needs_changes -> in_review` pode ser feita por quem já possui `edit_post` no artigo.

A primeira slice não cria custom role/capability e não altera roles existentes.

## 7. Reviewer / responsável

Não haverá `_reviewed_by` canônico separado.

O ator da decisão é `user_id` do evento canônico.

Não haverá `_reviewed_at` canônico separado.

O instante da decisão é `comment_date_gmt` do evento canônico.

Isso evita duplicação de fatos deriváveis.

## 8. Nota da decisão

Campo `note`:

- texto plano sanitizado;
- máximo 2000 bytes antes da sanitização;
- opcional para `in_review` e `approved`;
- obrigatório para `needs_changes` e `excluded`;
- nunca contém HTML executável.

## 9. Contrato de evento

Payload lógico mínimo do `comment_content`:

```json
{
  "schema_version": 1,
  "from": "unreviewed",
  "to": "in_review",
  "note": ""
}
```

O comentário usa:

- `comment_type = bdc_kb_review_event`;
- `comment_approved = 1`;
- `comment_post_ID = post_id`;
- `user_id = actor`;
- datas preenchidas pelo WordPress.

A aplicação deve rejeitar evento cujo JSON/schema/state seja inválido.

## 10. Leitura

`Review_Store::read(post_id)` deve retornar, no mínimo:

- `post_id`;
- `state`;
- `last_event_id` ou `0`;
- `last_actor_id` ou `0`;
- `last_decision_at` ou `null`;
- histórico paginável/limitado quando solicitado separadamente.

A leitura comum não cria dados.

Se o último evento existir mas estiver malformado, o store não deve silenciosamente recuar para evento anterior; deve retornar erro de integridade para tornar corrupção visível.

## 11. Atomicidade / consistência

A transição canônica é uma única inserção de evento.

Ordem:

`authorize -> validate post -> read current -> validate transition -> validate note -> build event -> wp_insert_comment -> reread latest -> compare`

Resultados:

- `SUCCESS`: evento inserido e releitura confirma exatamente `from/to/actor`;
- `NO_CHANGE`: estado alvo igual ao atual; zero write;
- `FAIL_SAFE`: inserção falhou e o estado anterior permanece intacto;
- `PARTIAL_FAILURE_CRITICAL`: evento foi inserido mas a releitura não confirma o estado esperado e a remoção compensatória do evento não restaura a leitura anterior.

Se a inserção ocorrer, mas a releitura divergir, o store deve tentar remover somente o evento recém-criado e reler o estado anterior. Nenhum evento histórico anterior pode ser alterado.

## 12. Histórico

Histórico é append-only no uso normal.

- não há limite destrutivo de 50 eventos;
- UI pode paginar/limitar leitura;
- eventos não são editados;
- exclusão só é permitida como compensação imediata de uma transição que falhou antes de ser confirmada ou em tooling de teste explicitamente temporário.

## 13. Relação com editorial

Review/Governança não altera `post_status`.

Um post draft pode ser aprovado em governança; consumidores futuros que exigirem publicação devem avaliar separadamente `post_status=publish`.

Nenhuma regra de Search/IA é inferida nesta SPEC.

## 14. Legado

Profiling real encontrou zero rows nos seis stores históricos em 622 posts.

Política:

- migração: não necessária;
- fallback legado: proibido;
- dual-read: desnecessário;
- dual-write: proibido;
- estados KB2Ops históricos: somente referência semântica;
- `_kb2ops_include_ai`: fora do domínio.

## 15. UX

Review & Governança entra no Knowledge Workspace como domínio próprio.

A UI da primeira slice deve exibir apenas fatos contratados:

- estado atual;
- última decisão: ator/data quando existir;
- ações de transição permitidas ao usuário atual;
- nota da decisão;
- histórico real.

Não exibir score, SLA, AI Ready ou responsável permanente sem SPEC própria.

## 16. Gate

Todos os itens T020–T029 foram decididos.

**Gate R-010: PASS.**

S003 — Runtime mínimo está autorizado.
