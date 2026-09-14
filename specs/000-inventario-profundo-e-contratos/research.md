# Pesquisa Consolidada — SPEC-000

## Objetivo

Registrar fatos comprovados e decisões do cruzamento e das revisões finais. Hipótese não vira fato sem evidência versionada; simplicidade não remove controles de segurança.

## 1. Baselines fixadas

- KB2Ops `0.2.1 @ f2d2aa659240b0c2ee86cebd3cc5bd0c00f9fc94`.
- ASI `4.6.8 @ c0ddff89caad529ce1bcdc645eb795e4a9b187a1`.
- GRE `0.6.0 @ 1120a534d8eb2288460c2c675730deef0d67c365`.

## 2. Arquitetura e revisões

- T059: WordPress/Elementor fonte editorial; uma Search Retrieval Projection reconstruível é a única família própria aprovada; Analytics/queue/vector/agentes postergados.
- T090: PASS WordPress-first.
- T091: PASS simplicidade; execução reduzida a slices mínimos; candidato inicial `Core mínimo + Summary narrativo`.
- T092: PASS de segurança arquitetural com endurecimentos obrigatórios e zero blockers globais.

## 3. Evidência oficial WordPress revalidada em T092

Snapshot 2026-09-14:

- Security API: entrada e dados externos não são confiáveis; validar/sanitizar e escapar saída.
  https://developer.wordpress.org/apis/security/
- Nonces: mitigam CSRF, mas não são autenticação/autorização nem proteção exactly-once; usar `current_user_can()`.
  https://developer.wordpress.org/apis/security/nonces/
- `current_user_can()` aceita meta capabilities por objeto como `edit_post`/`edit_post_meta`.
  https://developer.wordpress.org/reference/functions/current_user_can/
- Escaping deve ocorrer conforme contexto e o mais tarde possível.
  https://developer.wordpress.org/apis/security/escaping/
- `wp_safe_remote_post()` valida URL e redirects para reduzir SSRF em destinos variáveis.
  https://developer.wordpress.org/reference/functions/wp_safe_remote_post/

## 4. Decisões de segurança T092

### Autorização e CSRF

- capability no handler e no objeto;
- nonce não substitui capability;
- mutação via POST; leitura GET sem side effects;
- role string não é contrato de autorização.

### Integridade e entrada

- `post_id`/scope/state enviados pelo cliente são revalidados;
- allowlist de campos;
- validação/sanitização por campo e limits;
- mass assignment proibido;
- B-006 permanece no write composto.

### Saída

- escaping contextual tardio;
- dado do banco/projection/provider continua não confiável;
- diagnostics não vazam secrets/payload.

### Search

- projection não concede visibilidade;
- resultado/detail revalidam WordPress canônico;
- query/result bounds e abuso devem ser tratados no slice de Search;
- query logging continua desligado sem B-004.

### IA/provider

- provider HTTP fica postergado;
- endpoint arbitrário é NO-GO;
- URL variável exige HTTPS/host allowlist/validação e HTTP API segura;
- secrets/data egress precisam de política antes da primeira chamada;
- conteúdo recuperado é dado, não instrução/tool authority.

### Lifecycle/destrutivo

- activation mínima e não destrutiva;
- uninstall preservador por default;
- purge é ação separada, autorizada, explícita e com rollback/evidência quando aplicável.

## 5. Segurança do candidato SPEC-001

O candidato `Core mínimo + Summary narrativo` é compatível com o threat model sem infraestrutura adicional:

- abrir tela: objeto validado + `edit_post` no post;
- salvar: POST + nonce + `edit_post` no objeto;
- allowlist `objective|escalation|important`;
- Metadata API;
- read-after-write;
- escaping contextual;
- nenhuma tabela/REST/AJAX/Search/provider/telemetria.

T094/T095/T097 ainda precisam autorizar esse escopo.

## 6. Próximo passo

**T093 — Revisão de QA/Regressão.**

Converter T055 e os achados T090–T092 em evidência executável por slice. `NOT_TESTED`, PASS vazio e evidência stale são NO-GO.

## Estado

T050–T059 + T090–T092 concluídos documentalmente. Nenhum runtime/schema/provider/vector foi criado. SPEC-001 permanece bloqueada até T097.