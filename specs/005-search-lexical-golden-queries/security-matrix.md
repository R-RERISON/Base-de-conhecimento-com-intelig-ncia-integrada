# Matriz de Segurança — SPEC-005

## Read path administrativo

| Ação | Ator | Capability | Método | Mutação | Regra |
|---|---|---|---|---|---|
| buscar artigos | usuário autenticado | `edit_posts` + visibilidade do post | GET | não | WordPress revalida post |
| abrir resultado | usuário autenticado | capability efetiva do alvo | GET | não | URL oficial/Workspace |

## Read path público futuro

**NOT_READY.** A superfície pública não está autorizada até R-500 decidir escopo.

Requisitos mínimos:
- somente status publicável;
- nenhuma informação de post privado/draft;
- rate/abuse bounds se interação dinâmica existir;
- output escaping;
- sem confiar em Projection para autorização.

## Golden management

| Ação | Método | Segurança |
|---|---|---|
| criar/editar expectativa | POST | capability + nonce + allowlist |
| ativar/desativar | POST | capability + nonce |
| executar suite | POST | capability + nonce; execução explícita |
| exportar evidência | GET/POST conforme desenho | capability; sem identidade/telemetria |

## SQL

- `$wpdb->prepare()` para input variável;
- nomes de tabela/coluna nunca vêm do request;
- query length limitada;
- token count limitado;
- result limit rígido;
- LIKE usa `esc_like()` quando aplicável.

## Dados sensíveis

SPEC-005 não cria query logging. Golden Queries contêm somente consultas explicitamente curadas.
