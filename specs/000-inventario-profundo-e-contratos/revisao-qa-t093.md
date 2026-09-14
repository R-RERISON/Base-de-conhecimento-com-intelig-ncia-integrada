# T093 — Revisão de QA e Regressão

> Estado: **CONCLUÍDA — PASS COM ENDURECIMENTOS DE EVIDÊNCIA E ZERO BLOQUEIOS GLOBAIS**.  
> Baseline revisada: `main @ d82d7ab2832c2657c45e1c0653c3ab608a15c0c8` (fechamento T092).  
> Fontes principais: `catalogo-testes-regressao.md`, `matriz-paridade-futura.md`, `revisao-wordpress-t090.md`, `revisao-simplicidade-t091.md`, `revisao-seguranca-t092.md` e `docs/DEFINITION-OF-DONE.md`.

## 1. Objetivo

Converter os contratos arquiteturais e de segurança em uma estratégia mínima de evidência por vertical slice, impedindo:

- PASS vazio;
- `N/A` usado para esconder gap;
- teste não executado tratado como sucesso;
- evidência de outra versão/release reaproveitada como atual;
- cobertura de implementação substituindo cobertura de comportamento.

Classificação dos findings:

- **COBERTO** — gate/evidência já está conceitualmente suficiente;
- **ENDURECER** — contrato correto, mas precisa de evidência mais explícita;
- **POSTERGAR** — teste/gate só nasce com a capacidade;
- **BLOQUEAR** — gap documental impede prosseguir para fechamento.

Nenhum runtime/teste executável foi criado nesta revisão.

---

## 2. Resultado executivo

**PASS de QA/Regressão da arquitetura documental.**

Contagem:

- **10 COBERTO**;
- **12 ENDURECER**;
- **7 POSTERGAR**;
- **0 BLOQUEAR global**.

A estratégia atual é suficiente para permitir T094/T095, desde que futuras SPECs materializem os testes aplicáveis antes do GO.

A principal conclusão é:

> **Cada vertical slice terá uma Matriz de Evidência própria. Gates não usados pelo slice ficam `N/A` ou `POSTERGADO` com justificativa explícita; jamais simplesmente ausentes.**

---

## 3. Estados de evidência canônicos

Toda verificação de release deve terminar em um dos estados abaixo:

- **PASS** — executado e aprovado contra a versão atual;
- **FAIL** — executado e reprovado;
- **NOT_RUN** — deveria ser executado, mas não foi;
- **NOT_CONFIGURED** — capacidade/suíte exigida não está configurada;
- **STALE** — evidência existe, mas não corresponde ao estado material atual;
- **N/A** — não aplicável ao slice, com justificativa versionada;
- **POSTERGADO** — capacidade deliberadamente fora do slice/release;
- **WAIVED** — exceção explícita, com owner, risco, prazo/condição de remoção; nunca permitido para finding crítico sem aprovação formal.

Regras:

- `NOT_RUN`, `NOT_CONFIGURED` ou `STALE` em gate MUST/CONDICIONAL ativo = **NO-GO**;
- `N/A` sem justificativa = **NO-GO documental**;
- `POSTERGADO` implementado silenciosamente = **NO-GO arquitetural**;
- `WAIVED` não transforma falha em PASS e deve aparecer no relatório final.

---

## 4. Pirâmide mínima de evidência

A escolha do tipo de teste depende do comportamento, não de meta de cobertura arbitrária.

### 4.1 Unitário puro

Usar para:

- normalização;
- validação determinística;
- state transitions;
- regras derivadas;
- ranking/scoring determinístico;
- parsing de contratos isolados quando integração WordPress não for necessária para provar o comportamento.

### 4.2 Integração WordPress

Obrigatória quando a garantia depende de:

- Metadata/Taxonomy/Options API;
- capabilities por objeto;
- hooks/eventos;
- lifecycle;
- read-after-write;
- persistência/migração;
- fixtures Elementor;
- Search projection/DB quando aplicável.

### 4.3 Browser/acceptance

Obrigatória quando somente o navegador prova:

- fluxo real de formulário;
- GET side-effect free;
- feedback de erro/sucesso;
- foco/teclado básico;
- escaping/renderização;
- bypass de URL/detail;
- responsividade crítica;
- coexistência visual/menus/shortcodes.

### 4.4 Benchmark

Só quando existe caminho crítico de performance. Não inventar benchmark para Summary simples nem reutilizar número ASI histórico como meta automática.

### 4.5 Package/release

Quando houver artefato instalável:

- build reproduzível;
- ZIP com raiz única;
- manifest/checksum;
- install/upgrade/deactivation/uninstall aplicáveis;
- diferença entre fonte testada e pacote publicado = NO-GO.

---

## 5. Matriz de evidência do candidato SPEC-001 — Core mínimo + Summary

A recomendação T091 permanece viável e possui uma matriz pequena.

### G-001 — Editorial/Elementor

**Estado T093:** COBERTO conceitualmente; MUST no slice.

Testes mínimos:

1. salvar Summary não altera `_elementor_data`;
2. salvar Summary não altera `post_content`/`post_title`;
3. abrir tela Summary não produz write;
4. post continua editável normalmente no WordPress/Elementor.

Evidência: integração WordPress + inspeção read-before/read-after; browser smoke para fluxo real.

### G-020 — Summary

**Estado T093:** ENDURECER.

Testes mínimos:

- leitura dos 3 campos;
- save válido;
- omitted field intacto;
- empty/delete conforme contrato;
- allowlist rejeita/ignora campo estranho explicitamente;
- sanitização/limites por campo;
- read-after-write;
- `post_title` nunca duplicado em meta;
- repeated identical submit não cria estado divergente;
- falha em um campo segue semântica B-006 e nunca retorna sucesso falso.

### G-070 — Segurança/scope

**Estado T093:** ENDURECER com cenários negativos obrigatórios.

Testes:

- usuário autorizado + nonce válido -> sucesso;
- usuário sem capability -> bloqueio;
- usuário pode abrir menu mas não editar post alvo -> bloqueio;
- nonce ausente -> bloqueio;
- nonce inválido -> bloqueio;
- GET tentando mutar -> nenhum efeito;
- `post_id` trocado por objeto fora do scope -> bloqueio;
- ID inexistente/tipo inválido -> erro seguro;
- mass assignment com meta extra -> não persiste;
- payload XSS -> não executa ao renderizar;
- output usa escaping contextual.

### G-110 — UI/UX

**Estado T093:** ENDURECER.

Browser/manual mínimo:

- tela integrada ao wp-admin;
- loading não necessário se server-rendered;
- feedback sucesso/erro visível;
- labels/inputs associados;
- navegação por teclado/foco básico;
- cor não é único indicador;
- campos preservam valor após erro quando seguro;
- nenhum segundo shell/sidebar concorrente.

### G-130 — Lifecycle/release

**Estado T093:** ENDURECER quando houver pacote.

Primeiro slice deve provar:

- activation sem trabalho pesado/destrutivo;
- deactivation não apaga canônico;
- uninstall default não purga dados;
- pacote instalável/reproduzível quando release candidate existir;
- upgrade path só é testado se houver versão anterior do novo plugin; antes disso `N/A` explícito.

### B-006 — write composto

**Estado T093:** NÃO FECHADO; gate da SPEC Summary, não blocker global.

A futura SPEC deve escolher estratégia e criar fault injection/fixture para provar que uma falha tardia não gera sucesso falso.

---

## 6. Matriz de segurança negativa transversal

Os findings T092 viram casos de teste sempre que a superfície existir.

| Risco | Evidência mínima |
|---|---|
| capability ausente | integração negativa |
| nonce ausente/inválido | integração/browser negativa |
| mutação por GET | browser/integration sem side effect |
| IDOR | trocar IDs/scope e provar bloqueio |
| mass assignment | enviar campo extra e provar zero write |
| stored/reflected XSS | payload controlado + renderização segura |
| SQL injection | teste de query parametrizada/fuzz bounded quando SQL próprio existir |
| projection stale | despublicar/restringir fonte e provar não exposição |
| destructive action | ausência de GET; capability/nonce/confirm/rollback aplicáveis |
| SSRF | URL proibida/redirect/host inválido quando provider existir |
| secret leak | inspeção de logs/HTML/exports quando integração existir |
| prompt injection/tool abuse | adversarial corpus/tool tests quando RAG/agente existir |
| shadow analytics | provar ausência de query/identity logging enquanto B-004 estiver aberto |

---

## 7. Classificação por futuros slices

### Classificação

**Gates:** G-001, G-030, G-070, G-110, G-130; B-002 somente para cutover/migração.

QA adicional:

- um eixo por slice;
- cardinalidade e primitive versionadas;
- capability de manage/assign terms;
- sem archive/rewrite/REST público por default;
- valores históricos preservados quando cutover existir;
- migration idempotente quando aplicável.

**Estado T093:** COBERTO/ENDURECER; não bloqueia Summary.

### Review/Governança

**Gates:** G-040 + G-070/G-110/G-130.

Testar:

- state transitions válidas/inválidas;
- reviewer derivado do usuário atual;
- history bounded quando existir;
- include_ai humano;
- evento após persistência confirmada;
- consumer idempotente quando houver consumidor.

**Estado:** POSTERGADO até slice Review.

### Content Extractor

**Gate:** G-010/B-001.

T055 já define fixtures mínimas. T093 reforça que B-001 só fecha com:

- fixtures versionadas + corpus real representativo;
- custom widget relevante;
- comparação de omissions/diagnóstico;
- zero write editorial.

**Estado:** POSTERGADO até consumidor Search/qualidade/RAG.

### Search post-level

**Gates:** G-010, G-050, G-060, Golden, G-070, G-120, G-130.

Testar:

- projection determinística/idempotente;
- NO_CHANGE;
- despublicação/restrição remove exposição;
- FULLTEXT e fallback bounded no ambiente real;
- ranking reproduzível;
- zero result != error;
- native/degraded fallback quando projection indisponível;
- scope revalidado;
- Golden ativo e atual;
- benchmark representativo.

Item/deep-link fica `POSTERGADO` se não estiver no slice.

### Item/deep-link

Adiciona:

- item identity/reconciliation;
- B-005;
- browser E2E de destino;
- fail-closed para item sem destino seguro.

**Estado:** POSTERGADO.

### Search Knowledge

Só nasce quando necessário ao ranker. Apply exige stale-state/expected-version tests; Golden precisa provar o efeito pretendido sem regressão blocking.

**Estado:** POSTERGADO.

### Analytics

G-080 permanece negativo enquanto B-004 aberto.

Evidência atual esperada: Search funciona sem Analytics e query text/identity não são persistidos silenciosamente.

**Estado:** POSTERGADO.

### Queue

G-090 negativo: nenhuma queue table/options/transients-as-queue. Se reaberta, adicionar lease/retry/dead/recovery/idempotency/concurrency tests.

**Estado:** POSTERGADO.

### IA P1

G-140A/B/C/D + owner gate.

Testar no futuro:

- provider off/timeout/quota/auth error degrada sem write;
- Generate != Apply;
- schema inválido é rejeitado;
- human Apply separado;
- budget/NO_CHANGE/receipt;
- secret leak negativo;
- egress minimizado.

**Estado:** POSTERGADO.

### RAG/semantic/agentes

Seguem G-140E–H e permanecem postergados. Não criar testes executáveis agora para feature inexistente; manter apenas gates/documentação.

---

## 8. Golden Queries — QA final

T055 já define o contrato. T093 reforça:

- Golden é obrigatória **antes do primeiro release de Search**, não antes do Summary;
- suite vazia = `NOT_CONFIGURED`, não PASS;
- mudança material de dataset/ranker/extractor invalida evidência;
- failure blocking = NO-GO;
- warning exige decisão explícita;
- leitura de status não executa suite;
- Golden não substitui segurança, B-001 ou benchmark.

Uma UI CRUD sofisticada continua não obrigatória: arquivo/entidade governada mínima pode atender o primeiro Search slice, desde que versionamento e evidência sejam reais.

---

## 9. Defect taxonomy e gates de release

### Severidade

- **P0 / Critical:** perda/corrupção canônica, bypass de autorização, write editorial indevido, secret exposure grave, release/package incorreto que compromete dados -> NO-GO imediato.
- **P1 / High:** fluxo principal quebrado, regressão blocking Golden, XSS/IDOR relevante, rollback inexistente para mudança destrutiva -> NO-GO.
- **P2 / Medium:** comportamento secundário degradado com workaround seguro -> decisão explícita; pode exigir correção/waiver antes do GO.
- **P3 / Low:** cosmético/documental sem risco funcional relevante -> pode seguir com backlog explícito.

### Bug reproduzido

Todo P0/P1 e todo bug que represente contrato deve ganhar teste de regressão quando tecnicamente viável antes de ser considerado encerrado.

---

## 10. Matriz de Evidência obrigatória por SPEC

Antes da implementação, a SPEC cria uma tabela com:

| Contrato/gate | Classe | Cenário | Tipo de teste | Evidência esperada | Estado | Artefato/execução |
|---|---|---|---|---|---|---|
| G-020 save Summary | MUST | save válido | WP integration | estado relido = esperado | NOT_RUN inicialmente | futura suite |

Regras:

1. todo gate aplicável aparece;
2. `N/A` traz justificativa;
3. `POSTERGADO` referencia decisão arquitetural;
4. estado inicial de teste não executado é `NOT_RUN`, nunca PASS;
5. resultado final referencia commit/build/dataset quando material;
6. browser/manual evidence registra ambiente e fluxo;
7. benchmark registra corpus/configuração.

---

## 11. Release evidence bundle conceitual

Para cada release candidate futuro, a evidência mínima aplicável deve permitir responder:

- qual commit/build foi testado?;
- quais gates eram aplicáveis?;
- quais suites foram executadas?;
- quais estados PASS/FAIL/NOT_RUN/N/A existem?;
- quais browser/manual checks ocorreram?;
- qual pacote/checksum foi validado?;
- quais defects/waivers permanecem?;
- qual rollback existe?;
- qual `CONTINUIDADE.md` corresponde ao estado?

Não é necessário criar plataforma de test management; arquivos versionados/relatórios simples bastam inicialmente.

---

## 12. Findings T093

### QA-001 — PASS vazio

**Classificação:** COBERTO.

T055/T093 proíbem explicitamente.

### QA-002 — N/A silencioso

**Classificação:** ENDURECER.

Toda não aplicabilidade exige justificativa versionada.

### QA-003 — evidência stale

**Classificação:** ENDURECER.

Release evidence precisa ligar commit/build/dataset/ranker/extractor quando aplicável.

### QA-004 — segurança negativa

**Classificação:** ENDURECER.

Future suites devem incluir cenário negativo, não somente happy path.

### QA-005 — UI só por inspeção de código

**Classificação:** ENDURECER.

Fluxo crítico exige browser/manual acceptance quando automação unit/integration não prova interação.

### QA-006 — métricas de cobertura artificiais

**Classificação:** COBERTO.

T093 não fixa percentage coverage. Cobertura é por risco/contrato.

### QA-007 — benchmark inventado

**Classificação:** COBERTO.

Metas só com baseline/corpus medidos na SPEC correspondente.

### QA-008 — testes de feature postergada

**Classificação:** POSTERGAR.

Não construir harness caro para IA/vector/queue inexistentes; preservar gates para quando forem ativados.

### QA-009 — package/source mismatch

**Classificação:** ENDURECER.

Release futuro deve testar o artefato realmente distribuído, não apenas working tree.

### QA-010 — first slice excessivo

**Classificação:** COBERTO.

Matriz Summary comprova que Search/IA/Classificação/Review não são requisitos de QA da SPEC-001 sugerida.

---

## 13. Gate T093

- estratégia QA por slice: **PASS**;
- gates T055 compatíveis com T090–T092: **SIM**;
- cenários negativos de segurança incorporados: **SIM**;
- blocker global novo: **ZERO**;
- primeiro slice sugerido possui matriz de evidência objetiva: **SIM**;
- runtime/testes executáveis criados: **NÃO**;
- SPEC-001 autorizada: **NÃO**.

## 14. Entrada para T094

T094 — Produto/Conhecimento deve agora responder se a ordem mínima sugerida por T091 e validada por T092/T093 realmente maximiza valor para os usuários.

Foco:

- quem usa o primeiro slice;
- qual dor real Summary resolve isoladamente;
- se Summary deve mesmo preceder Review/Classificação/Search;
- campos e UX mínimos;
- o que é paridade necessária versus herança histórica;
- sucesso observável sem Analytics detalhado;
- prioridade de Search para resolvedores;
- quando IA compra valor real.

## 15. Próximo passo

**T094 — Revisão de Produto/Conhecimento.**