# Matriz de Segurança — SPEC-005 / G-520

**Status:** FROZEN  
**Gate:** T526

## Search administrativo

| Ação | Método | Capability mínima | Mutação | Regras |
|---|---|---|---|---|
| pesquisar | GET | `edit_posts` | não | query bounded; output escaped |
| retornar resultado | interno | `edit_post(post_id)` | não | WordPress revalida cada objeto |
| abrir resultado | GET | capability efetiva do alvo | não | URL oficial/Workspace |
| usar fallback | interno | mesma do Search | não | `WP_Query` scoped; nunca ASI |

Regras:
- Projection nunca autoriza acesso;
- documentos não autorizados são descartados **antes do ranking final**;
- resultado não revela contagem/título/score de documento descartado;
- `post_status` é revalidado a partir do WordPress;
- superfície inicial é ADMIN-FIRST;
- busca pública permanece NOT_READY.

## Projection

| Ação | Método | Capability | Mutação | Segurança |
|---|---|---|---|---|
| ensure schema | lifecycle interno | sistema | tabela derivada | versionado, aditivo, sem rebuild implícito |
| rebuild explícito | POST | `manage_options` | Projection/Option | nonce + bounds + estado building |
| ler estado | GET/interno | `manage_options` para diagnóstico detalhado | não | não expor conteúdo |
| desabilitar módulo | config/build flag | sistema/admin | config | fallback WordPress |

Projection armazena somente conteúdo **normalizado derivado**, nunca credenciais ou identidade de usuário.

## Golden Runner

| Ação | Método | Capability | Mutação |
|---|---|---|---|
| executar Golden/Challenge | POST | `manage_options` | não editorial |
| baixar relatório | resposta da execução | `manage_options` | não |
| editar fixtures runtime | não existe no v1 | N/A | N/A |

Requisitos:
- nonce;
- execução explícita;
- version/hash current;
- nenhuma leitura ASI;
- relatório sem identidade/IP/session/journey.

## Query bounds

T520:
- <=256 caracteres Unicode;
- <=1024 bytes UTF-8;
- <=16 tokens distintos;
- <=128 caracteres/token;
- limite excedido => `invalid_query`;
- sem truncamento silencioso.

Retrieval:
- candidate cap 200;
- result default 20;
- result hard cap 50.

## SQL

- `$wpdb->prepare()` obrigatório para valores;
- `$wpdb->esc_like()` obrigatório para LIKE;
- tabela/coluna nunca vem do request;
- nomes de campos são allowlist do código;
- nenhum fragmento SQL fornecido pelo usuário;
- sem FULLTEXT no v1;
- sem query contra tabelas ASI.

## XSS / output

- título/URL final vêm do WordPress e são escapados no contexto;
- matched signals são allowlist técnica;
- nenhuma Projection raw é injetada como HTML;
- nenhum snippet HTML no v1;
- erro não retorna SQL, stack trace ou conteúdo editorial bruto.

## CSRF

- Search GET read-only: nonce não requerido;
- rebuild/Golden/qualquer mutação: POST + nonce;
- nenhum rebuild via GET.

## Privacidade

SPEC-005:
- não cria query logging;
- não armazena identidade, IP, session ou journey;
- Golden Queries são consultas explicitamente curadas;
- Technical Challenges são corpus-derived/synthetic e não são rotulados como comportamento real.

## Failure safety

- tabela ausente/stale/building/failed => `wordpress_fallback`;
- erro de Projection não altera post/meta/taxonomia;
- erro de permissão não degrada para acesso mais amplo;
- fallback nunca usa ASI.

## ASI

Proibido:
- `asi_*`;
- `asi4_*`;
- tabelas/classes/functions/hooks do ASI;
- ASI como fallback;
- ASI como fonte de permissão.

G-585 provará isso com o ASI ausente/desativado antes do RC.
