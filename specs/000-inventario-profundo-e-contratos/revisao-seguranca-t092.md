# T092 — Revisão de Segurança

> Estado: **CONCLUÍDA — PASS DE ARQUITETURA COM ENDURECIMENTOS OBRIGATÓRIOS E ZERO BLOQUEIOS GLOBAIS**.  
> Baseline revisada: `main @ 0e9feabc8607f6bb599e8c6cb0621599e1896416` (fechamento T091).  
> Objeto principal: `matriz-paridade-futura.md` + `revisao-wordpress-t090.md` + `revisao-simplicidade-t091.md`.

## 1. Objetivo

Transformar as superfícies previstas do produto em um threat model verificável antes de qualquer runtime novo.

A revisão segue a regra do agente de Segurança WordPress:

`ação -> ator -> capability -> método -> nonce -> validação -> persistência -> saída -> auditoria/diagnóstico`

Nenhuma decisão abaixo cria endpoint, taxonomy, tabela, provider, integração ou runtime.

## 2. Fontes e princípios verificados

Além da Constituição e dos agentes/skills internos, a revisão revalidou documentação oficial WordPress em 2026-09-14:

- Security API: https://developer.wordpress.org/apis/security/
- Nonces: https://developer.wordpress.org/apis/security/nonces/
- `current_user_can()`: https://developer.wordpress.org/reference/functions/current_user_can/
- Escaping: https://developer.wordpress.org/apis/security/escaping/
- `wp_safe_remote_post()`: https://developer.wordpress.org/reference/functions/wp_safe_remote_post/
- `wp_http_validate_url()`: https://developer.wordpress.org/reference/functions/wp_http_validate_url/

Princípios confirmados:

1. nonce reduz CSRF, mas **não** substitui autenticação/autorização;
2. capability deve ser verificada na ação e, quando aplicável, no objeto (`edit_post`, `edit_post_meta` etc.);
3. entrada, banco e respostas externas são não confiáveis;
4. validar/rejeitar é preferível quando há domínio fechado; sanitizar quando apropriado;
5. saída deve ser escapada tarde e conforme contexto;
6. URL externa arbitrária deve usar a variante segura da HTTP API e política de destino compatível com o threat model;
7. dados, secrets e telemetria seguem minimização.

---

## 3. Resultado executivo

**PASS de segurança arquitetural.** Não foi encontrado blocker global que impeça o candidato de primeiro slice `Core mínimo + Summary narrativo`.

Findings:

- **2 PASS**;
- **13 ENDURECER**;
- **5 POSTERGAR**;
- **0 BLOQUEAR global**.

Isso não significa “segurança resolvida por documentação”. Cada futura SPEC precisa demonstrar os controles aplicáveis em teste. Os padrões NO-GO deste documento tornam-se condições obrigatórias das futuras implementações.

---

# 4. Threat model por superfície

## SEC-001 — Capability no objeto

**Classificação:** ENDURECER.  
**Ameaças:** privilege escalation, IDOR, confiança apenas no menu.

Regras:

- capability é verificada no handler da ação;
- menu capability não autoriza automaticamente mutação;
- para Summary ligado a post, baseline é `current_user_can( 'edit_post', $post_id )` ou capability equivalente explicitamente justificada;
- não codificar autorização por nome de role;
- metadata registrada deve possuir contrato de autorização coerente quando `register_meta()`/`register_post_meta()` for usado.

**NO-GO:** handler que só valida login, role nominal, acesso ao menu ou nonce.

## SEC-002 — Método HTTP, CSRF e nonce

**Classificação:** ENDURECER.  
**Ameaças:** CSRF, mutação por GET, replay interpretado como exactly-once.

Regras:

- mutações administrativas usam POST;
- nonce é específico da ação/contexto e validado server-side;
- GET/visualização é side-effect free;
- nonce não é tratado como autenticação, autorização ou token de idempotência;
- ações que exigirem exatamente-once/expected-state devem possuir mecanismo de domínio além do nonce.

**NO-GO:** qualquer mutação por GET ou mutação protegida apenas por nonce.

## SEC-003 — IDOR e escopo de `post_id`

**Classificação:** ENDURECER.  
**Ameaças:** editar/ler outro objeto alterando parâmetro oculto/query string.

Regras:

- normalizar ID (`absint` ou equivalente);
- carregar objeto canônico no servidor;
- validar post type/status quando relevante à jornada;
- aplicar capability no objeto;
- nunca confiar em ID, owner, scope ou estado enviados pelo cliente.

**NO-GO:** aceitar `post_id` porque veio da tela legítima sem revalidação server-side.

## SEC-004 — Allowlist, validação e mass assignment

**Classificação:** ENDURECER.  
**Ameaças:** mass assignment, campos inesperados, payload excessivo, corrupção semântica.

Para o primeiro Summary slice:

- allowlist estrita: `objective`, `escalation`, `important`;
- campo omitido permanece intacto;
- vazio segue contrato explícito de delete/empty;
- tamanho/formato têm limites definidos pela SPEC;
- campos desconhecidos são rejeitados ou ignorados de forma explícita e testada;
- B-006 define a semântica da falha tardia no write composto;
- nunca retornar sucesso se o estado final divergir do solicitado sem diagnóstico explícito.

## SEC-005 — XSS e escaping contextual

**Classificação:** ENDURECER.  
**Ameaças:** stored/reflected XSS.

Regras:

- dado persistido continua não confiável na saída;
- texto HTML: `esc_html()` quando HTML não é permitido;
- atributos: `esc_attr()`;
- textarea: `esc_textarea()`;
- URLs: `esc_url()`;
- HTML deliberadamente permitido: allowlist via `wp_kses()`/contrato equivalente;
- escapar o mais tarde possível, no contexto final.

**NO-GO:** imprimir meta, termo, query, resposta externa ou diagnóstico sem escaping compatível com o contexto.

## SEC-006 — SQL e futura Search Projection

**Classificação:** PASS COM CONDIÇÕES.

No primeiro slice não existe SQL próprio: Metadata/Taxonomy/Options usam APIs WordPress.

Quando a Search Retrieval Projection nascer:

- parâmetros de valores passam por `$wpdb->prepare()`/APIs seguras;
- nomes de coluna/order/identificadores vêm de allowlists fixas, não do usuário;
- consultas são bounded/paginadas;
- fallback não executa `LIKE` irrestrito sobre `_elementor_data`;
- erros SQL não vazam query/secrets para UI pública.

## SEC-007 — Taxonomias sistêmicas e exposição pública

**Classificação:** ENDURECER.  
**Ameaças:** disclosure, superfície pública acidental, enumeração.

Regras:

- classificação interna nasce fail-closed para archive/rewrite/REST/query pública, salvo jornada aprovada;
- capability de gestão de termos é explícita;
- slug/route pública não é consequência automática de usar Taxonomy API;
- IA nunca cria termo automaticamente como efeito colateral de sugestão.

## SEC-008 — Shortcodes, aliases e compatibilidade

**Classificação:** POSTERGAR sob B-003.

Nenhum alias está autorizado sem consumidor comprovado. Se algum shortcode/adapter sobreviver ao preflight:

- comportamento é específico e temporário;
- leitura/mutação respeitam capability/scope aplicável;
- atributos são validados;
- output é escapado;
- shortcode de apresentação não ganha permissão de mutação;
- existe gate de remoção.

## SEC-009 — Search scope e projection não confiável

**Classificação:** ENDURECER.  
**Ameaças:** exposição de conteúdo stale, privado, despublicado ou fora do scope.

A futura projection é somente candidata de retrieval. Antes de expor resultado/detail:

- post canônico é relido/revalidado;
- status/scope/login/capability aplicáveis são confirmados no WordPress;
- detail route não pode contornar o filtro do resultado;
- linha stale nunca concede visibilidade;
- item sem destino seguro falha fechado.

**NO-GO:** tratar projection/index/cache/vector como autoridade de autorização/publicação.

## SEC-010 — Busca pública e abuso

**Classificação:** ENDURECER quando Search existir.

Definir na SPEC de Search:

- limite de tamanho/tokens da query;
- limite/paginação de resultados;
- timeout/bounds de fallback;
- rate limit/throttling quando superfície pública ou live UX justificar;
- distinção entre erro, zero-result e bloqueio;
- mensagens sem leak técnico;
- query text não é persistida por default.

## SEC-011 — Search Knowledge e Golden

**Classificação:** ENDURECER quando existirem.

Mutações de vocabulary/bindings/rules/Golden exigem:

- capability própria ou capability administrativa explicitamente escolhida;
- POST + nonce;
- validação estrutural;
- expected-state/version quando stale edit puder sobrescrever decisão posterior;
- Apply/read-after-write;
- editar Golden não concede permissão para editar o conteúdo alvo.

## SEC-012 — Provider HTTP e SSRF

**Classificação:** POSTERGAR até o primeiro caso real de IA/integrador.

Quando houver provider externo:

- endpoint não pode aceitar destino arbitrário sem validação;
- preferir endpoint/deployment pré-configurado e allowlistado;
- se URL for administrável, exigir `https`, host permitido e validação de redirects/destino;
- usar `wp_safe_remote_post()`/HTTP API segura quando o destino puder variar;
- timeout, redirects, response size e retries são bounded;
- credencial nunca vai na URL.

**NO-GO:** campo administrativo capaz de transformar o servidor WordPress em proxy para host arbitrário/LAN/metadata endpoint.

## SEC-013 — Secrets e data egress

**Classificação:** POSTERGAR até provider externo.

Antes da primeira chamada externa, a SPEC deve fechar:

- origem/armazenamento institucional da credencial;
- campos exatos enviados;
- finalidade;
- provider/deployment/região quando relevante;
- quem pode disparar;
- mascaramento de diagnostics;
- política de logs/receipt.

Secrets não entram em repositório, prompt, logs, exports ou mensagens de erro.

## SEC-014 — Prompt injection e tool abuse

**Classificação:** POSTERGAR com gates G-140F/G.

Conteúdo editorial/retrieved é **dado**, não instrução. Futuramente:

- conteúdo não redefine system/developer policy;
- conteúdo não autoriza tool/capability;
- tools são allowlisted/server-validated;
- agentes são read-only por default;
- mutação exige confirmação humana e handler canônico;
- budget de passos/tempo/custo e kill/timeout são obrigatórios.

## SEC-015 — Analytics, identidade e retenção

**Classificação:** POSTERGAR sob B-004.

Enquanto Analytics detalhado não for aprovado:

- query text não é persistida por default;
- raw IP/UA não fazem parte do baseline;
- identidade/session/journey não são coletadas “para usar depois”;
- nenhuma retenção é criada sem finalidade, acesso e prazo definidos.

## SEC-016 — Migração, purge e ações destrutivas

**Classificação:** ENDURECER.

Ações destrutivas futuras exigem:

- intenção explícita;
- capability alta/custom coerente;
- POST + nonce;
- preview/dry-run quando tecnicamente útil;
- confirmação reforçada para irreversibilidade;
- evidência de backup/rollback quando aplicável;
- registro mínimo de resultado;
- operação idempotente/retomável quando longa.

**NO-GO:** purge/limpeza automática em activation, upgrade silencioso ou uninstall default.

## SEC-017 — Activation, deactivation e uninstall

**Classificação:** ENDURECER.

- activation é mínima;
- não faz batch, IA, rebuild massivo ou remoção de dados;
- deactivation não destrói dados canônicos;
- uninstall é preservador por default; purge separado e deliberado;
- nenhuma operação de filesystem arbitrária está autorizada pelo baseline.

## SEC-018 — Replay, concorrência e idempotência

**Classificação:** ENDURECER conforme risco do slice.

Nonce não impede replay. Quando repetição puder causar efeito indevido:

- usar estado esperado/version/hash/idempotency key semântica quando necessário;
- consumers de hooks/eventos são idempotentes;
- evento só ocorre após persistência confirmada;
- duplicate submit não produz estado impossível.

## SEC-019 — Logs, diagnostics e erros

**Classificação:** ENDURECER.

Logs devem ser suficientes para diagnóstico, não dumps de contexto:

- não logar secrets;
- não logar payload inteiro por conveniência;
- minimizar PII/query text;
- UI recebe erro seguro e acionável;
- detalhe técnico fica em superfície administrativa protegida quando necessário;
- exports obedecem às mesmas regras de minimização.

## SEC-020 — Candidato SPEC-001: Summary narrativo

**Classificação:** PASS DE SEGURANÇA ARQUITETURAL, condicionado à implementação dos controles abaixo.

Matriz mínima:

| Ação | Ator | Capability | Método/CSRF | Entrada | Persistência | Saída/evidência |
|---|---|---|---|---|---|---|
| abrir tela Summary | usuário autenticado autorizado a editar o post | `edit_post` no `post_id` | GET, sem mutação; nonce não necessário para leitura | `post_id` normalizado + post/type/scope validados | nenhuma | meta lida sem side effect; escaping contextual |
| salvar Summary | usuário autorizado a editar o post | `edit_post` no `post_id`, checado no handler | POST + nonce específico | allowlist 3 campos + validação/sanitização/limites; B-006 | Metadata API; update/delete por contrato; read-after-write | feedback baseado no estado relido; sem payload/secrets em log |

O primeiro slice **não precisa** de tabela, REST, AJAX, taxonomy, Search, provider, telemetry ou fila para atender seu threat model.

---

# 5. Condições NO-GO consolidadas

É NO-GO para qualquer futura SPEC aplicável:

- mutação via GET;
- nonce sem capability;
- capability apenas no menu, sem recheck no handler;
- `post_id`/objeto controlado pelo cliente sem autorização por objeto;
- mass assignment sem allowlist;
- output não escapado;
- SQL dinâmico não preparado/allowlisted;
- projection/cache/vector usados como autoridade de segurança;
- endpoint HTTP arbitrário capaz de SSRF;
- secrets em log/export/repositório/prompt;
- query text/identidade/retention de Analytics sem B-004;
- destructive cleanup em activation/uninstall default;
- IA/tool persistindo owner canônico sem ação humana/handler autorizado.

---

# 6. Relação com gates existentes

T092 não cria uma família de gates concorrente. Ela endurece:

- **G-020** Summary;
- **G-030** Classificação;
- **G-040** Review;
- **G-050/G-060/G-070** Search/scope;
- **G-080** Analytics negativo;
- **G-090** queue negativa;
- **G-100** compatibilidade;
- **G-130** lifecycle;
- **G-140B/C/F/G/H** provider, data egress, human-in-the-loop, prompt/tool safety.

T093 deve transformar esses controles em matriz de evidência/testes por slice.

---

# 7. Gate T092

- threat model das superfícies previstas: **PASS**;
- candidato `Core mínimo + Summary`: **compatível com segurança**, sujeito a testes da SPEC;
- blocker global novo: **ZERO**;
- vulnerabilidade implementada encontrada: **N/A — runtime ainda inexistente**;
- capacidade postergada reaberta: **ZERO**;
- runtime criado: **NÃO**;
- SPEC-001 autorizada: **NÃO**.

## 8. Próximo passo

**T093 — Revisão de QA/Regressão.**

T093 deve transformar T055 + findings T090–T092 em uma matriz objetiva de evidência para o primeiro slice candidato e para capacidades posteriores, garantindo que `NOT_TESTED` nunca seja tratado como PASS.