# G-580 — Lifecycle & Rebuild Contract v1

**Status:** FROZEN  
**Gate:** G-580  
**Tasks:** T580–T584

## 1. Objetivo

Validar o ciclo de vida da Search lexical da SPEC-005 sem reabrir ranking, Golden Queries, UX ou performance.

O G-580 deve provar:

- activation/update leves e idempotentes;
- rebuild sempre explícito;
- fallback WordPress durante estados não-ready;
- disable seguro do módulo Search;
- deactivation/uninstall não destrutivos por default;
- zero perda editorial;
- zero dependência operacional do ASI.

## 2. Invariantes de dados

A decisão de storage da ADR-005-003 permanece congelada:

- uma tabela derivada: `{$wpdb->prefix}bdc_kb_search_documents`;
- uma Option de estado: `bdc_kb_search_projection_state`, autoload=false;
- nenhuma nova tabela;
- nenhuma nova Option persistente para habilitar/desabilitar Search;
- nenhuma tabela Golden;
- nenhum FULLTEXT;
- nenhum query logging.

A Search Projection é derivada e reconstruível. WordPress permanece fonte canônica.

## 3. T580 — Activation / update

### Activation

A ativação pode:

1. criar/adequar o schema derivado via mecanismo versionado BDC;
2. inicializar o estado como `not_built` quando ainda não existir.

A ativação não pode:

- processar corpus;
- executar `Search_Document_Builder` em massa;
- executar upsert/reindex;
- remover stale rows;
- alterar posts, metadata editorial, taxonomias ou `_elementor_data`;
- executar rede, IA, vetores ou ASI.

### Update

Update pode executar somente preparação idempotente do schema.

Se o estado persistido for incompatível com as versões correntes, Search deve permanecer/degradar de forma segura e aguardar rebuild explícito.

Update não executa rebuild em massa.

## 4. T581 — Rebuild explícito + fallback

Rebuild deve existir como operação explícita e separada do bootstrap/activation/update.

Estados esperados:

`not_built|degraded|failed -> building -> ready`

Em falha:

`building -> failed`

Durante `not_built|building|degraded|failed`, ou quando versões da Projection estiverem incompatíveis:

- Search usa `wordpress_fallback`;
- resposta é `degraded`;
- nenhum rebuild é disparado pelo request de pesquisa.

Rebuild:

1. prepara schema;
2. marca `building`;
3. processa corpus canônico;
4. só remove stale rows após uma passagem completa sem erro;
5. executa segunda passagem de idempotência;
6. exige segunda passagem 100% `NO_CHANGE`;
7. valida row count contra corpus;
8. somente então marca `ready`.

Qualquer erro/throwable mantém a Projection não autoritativa e marca `failed`.

## 5. T582 — Disable seguro do módulo

O módulo Search deve aceitar kill switch sem alteração destrutiva de storage.

Contrato:

- constante opcional `BDC_KB_SEARCH_ENABLED`, default `true`;
- filtro `bdc_kb_search_enabled` permite disable operacional/testável;
- quando desabilitado, a Knowledge List continua funcional;
- consulta não vazia usa `wordpress_fallback` com estado `degraded`;
- tabela e Option são preservadas;
- nenhum rebuild/cleanup ocorre por efeito do disable.

Não será criada Option própria de enable/disable no v1.

## 6. T583 — Deactivation / uninstall retention

### Deactivation

Deactivation é não destrutiva:

- não remove tabela;
- não remove Option de estado;
- não altera posts;
- não altera metadata/taxonomias;
- não executa cleanup.

### Uninstall v1

Retenção por default:

- `uninstall.php` não remove tabela;
- `uninstall.php` não remove Option;
- `uninstall.php` não remove posts, metadata ou taxonomias;
- limpeza definitiva permanece fora do escopo e exige gate futuro explícito/autorizado.

## 7. Segurança

Mutações de engenharia do runner G-580 exigem:

- POST;
- `manage_options`;
- nonce válido.

Runtime Search continua revalidando capabilities.

Proibido:

- network call;
- query logging;
- export de identidade/IP/sessão;
- ASI como fallback;
- write editorial;
- DROP/TRUNCATE de Search Projection.

## 8. Evidência ambiental obrigatória

O runner G-580 deve gerar JSON machine-readable contendo no mínimo:

- ambiente/versões;
- schema existe;
- Projection state antes/depois;
- row count antes/depois;
- prova de activation/update preparation sem reindex implícito;
- prova de fallback com Projection não-ready;
- prova de fallback com Search desabilitado;
- rebuild explícito;
- pass1/pass2 e `NO_CHANGE`;
- fingerprint editorial before/after;
- prova de retention contract;
- erros/throwables;
- T580/T581/T582/T583/T584.

## 9. Critério de PASS

G-580 somente PASS quando:

- T580 PASS;
- T581 PASS;
- T582 PASS;
- T583 PASS;
- fingerprint editorial before = after;
- zero errors/throwables;
- zero ASI/network/query logging;
- Search volta a `ready` após rebuild explícito;
- nenhuma alteração no ranker/retrieval weights.

**G-580 PASS não autoriza RC nem produção. G-585 continua obrigatório antes do RC.**
