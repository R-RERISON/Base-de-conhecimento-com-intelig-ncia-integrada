# T088 — Runbook de Homologação e Produção v1

**Status:** FROZEN PROCEDURE / EXECUTION BLOCKED  
**SPEC:** 004 — G-245  
**Aplicação:** primeiro canário, rollback, batches e futura ida a produção

## 1. Objetivo

Definir a sequência operacional obrigatória para qualquer normalização Elementor futura, com fail-closed, evidência before/after e rollback comprovável.

Este runbook não concede autorização de writer. Ele define **como** uma operação autorizada deverá ocorrer.

## 2. Princípios invioláveis

1. WordPress + Elementor continuam fonte editorial da verdade.
2. Nenhuma escrita ocorre sem Projection Plan determinístico e elegível.
3. Journal `prepared` deve estar duravelmente persistido antes do primeiro byte editorial alterado.
4. Stale-source é revalidado no último instante antes do write.
5. Primeiro canário = exatamente 1 artigo.
6. Qualquer divergência estrutural, editorial, visual ou de integridade implica abort/rollback.
7. `review_required`, `blocked` ou dependência legada desconhecida nunca é autoaprovada.
8. GO homologação não é GO produção.
9. UX-002 permanece contrato visual do plugin; G-245 não pode alterá-la incidentalmente.

## 3. Pré-condições para iniciar um canário

Todas devem estar verdadeiras:

- T080 PASS WITH REVIEW ITEMS sem blocker;
- T081 PASS ambiental;
- T082 Gateway PASS e Elementor na versão homologada;
- T083/T084/T085/T086 PASS;
- T083B journal storage com **smoke ambiental PASS**;
- T087A `technical_preconditions_satisfied=true`;
- candidato `projectable` e `requires_review=false`;
- dry-run `ready`;
- source `fresh`;
- rollback capsule íntegro;
- payload de journal dentro do limite do contrato;
- usuário executor com `manage_options`;
- autorização humana específica para o post canário;
- executor mutável mínimo revisado e version-gated;
- nenhum arquivo visual UX-002 alterado no diff da release.

Se uma condição falhar: **ABORT**.

## 4. Critérios do primeiro artigo canário

Preferir artigo de homologação com menor superfície de risco:

- source compatível e Projection Plan `projectable`;
- sem `faq_wd`;
- sem `wpt`;
- sem shortcode não registrado;
- sem warning que force revisão;
- sem conteúdo privado/sensível quando houver alternativa equivalente;
- tamanho moderado;
- hierarquia já validada pelo KD 2.1.0;
- artigo que possa ser aberto no Elementor e comparado manualmente before/after.

Não escolher o artigo apenas por conveniência técnica.

## 5. Authorization Pack do canário

Antes de executar, registrar e apresentar:

- post ID;
- título apenas para identificação humana;
- `source_hash_before`;
- `projection_hash`;
- `dry_run_hash`;
- estratégia do Projection Plan;
- warnings = vazio ou explicitamente aceito;
- resultado T087A;
- tamanho estimado do rollback capsule;
- versão WordPress/PHP/Elementor/plugin;
- operação exata que o executor pretende realizar;
- procedimento de rollback.

A autorização deve ser específica para esse canário. Não reutilizar autorização de outro post/run.

## 6. Sequência transacional obrigatória

### Fase A — Freeze e releitura

1. adquirir lock exclusivo do artigo;
2. reconstruir Knowledge Document;
3. reconstruir Projection Plan;
4. recalcular dry-run;
5. revalidar Gateway;
6. revalidar stale-source;
7. confirmar que hashes continuam iguais aos do Authorization Pack.

Qualquer mismatch: **ABORT antes de journal/write**.

### Fase B — Write-ahead journal

1. capturar byte-a-byte `post_content` e `_elementor_data`;
2. criar record `prepared`;
3. persistir via `Elementor_Migration_Journal_Store`;
4. reler por `meta_id`;
5. validar `journal_hash` e `rollback_payload_hash`;
6. confirmar `journal_persisted=true`.

Se persistência/readback falhar: **ABORT sem write editorial**.

### Fase C — Último stale check

Após journal persistido e imediatamente antes do write:

1. reconstruir source hash atual;
2. comparar com `source_hash_before`;
3. se divergente, registrar abort e liberar lock.

Não reutilizar stale check anterior.

### Fase D — Write controlado

Somente o executor version-gated poderá escrever.

Regras:

- nenhuma escrita genérica de array arbitrário;
- somente operações explicitamente suportadas pelo contrato de projeção;
- não executar `do_shortcode()`;
- não publicar/despublicar;
- não alterar título, slug, autor, taxonomias ou status;
- não alterar conteúdo fora do escopo da projeção;
- não executar batch no primeiro canário.

### Fase E — Verificação pós-write

Obrigatório antes de considerar o canário aplicado:

1. reler `post_content` e `_elementor_data`;
2. reconstruir Knowledge Document;
3. calcular `source_hash_after`;
4. validar integridade estrutural/hierárquica;
5. persistir evento `applied` encadeado ao `prepared`;
6. abrir visualização pública/interna do artigo;
7. abrir o artigo no Elementor;
8. comparação humana before/after;
9. confirmar ausência de texto inventado, perda de conteúdo, reordenação ou quebra visual.

Qualquer FAIL: iniciar rollback imediato.

## 7. Rollback obrigatório do primeiro canário

O primeiro canário só fecha T087 quando o rollback for realmente exercitado.

Sequência:

1. manter lock do artigo;
2. reler `source_hash_after` atual;
3. executar `rollback_decision()`;
4. exigir `rollback_allowed=true`;
5. restaurar exatamente `post_content` e `_elementor_data` do capsule;
6. reconstruir Knowledge Document;
7. exigir `source_hash_restored == source_hash_before`;
8. persistir evento `rolled_back` encadeado;
9. validar Elementor e visualização novamente;
10. comparação humana final;
11. liberar lock.

Se o alvo estiver stale após o write, rollback automático deve falhar fechado e a operação passa para incidente manual controlado.

## 8. Abort conditions

Abortar imediatamente se ocorrer qualquer um:

- Elementor ausente ou versão fora da homologada;
- Gateway `blocking`/`review_required`;
- Projection Plan diferente de `projectable`;
- `requires_review=true`;
- `faq_wd`, `wpt` ou shortcode sem handler no candidato;
- source hash divergente;
- Projection Plan/dry-run hash divergente;
- journal não persistido ou capsule inválido;
- payload > limite do storage;
- capability/nonce/autorização inválida;
- lock não adquirido;
- erro/throwable antes ou durante write;
- pós-write estrutural/hierárquico FAIL;
- visual/humano FAIL;
- persistência do evento `applied` falhar;
- tentativa de execução em mais de um item no primeiro canário.

## 9. Batches após o canário

Não escalar automaticamente.

Após T087 PASS completo:

1. revisar evidência do canário e rollback;
2. definir tamanho inicial pequeno;
3. usar T086 cursor/cohort hash;
4. journal por artigo;
5. stale check por artigo;
6. checkpoint operacional somente após item verificado;
7. interromper batch no primeiro erro crítico;
8. permitir retomada apenas com cohort/cursor íntegros.

Loopback/WP-Cron devem ser validados antes de qualquer execução assíncrona em produção. O primeiro canário pode permanecer síncrono/manual.

## 10. Produção

Pré-requisitos adicionais:

- backup validado e restauração testada;
- janela de mudança definida;
- versão exata do plugin congelada;
- checksum do pacote;
- WordPress/PHP/Elementor compatíveis;
- loopback/WP-Cron validados se batches assíncronos forem usados;
- observabilidade e critérios de abort definidos;
- responsável técnico identificado;
- comunicação de rollback definida;
- autorização de produção separada da homologação.

## 11. Evidência mínima por execução

Registrar sem exportar conteúdo editorial bruto:

- run id;
- post id ou identificador controlado;
- timestamps;
- versões;
- hashes before/plan/dry-run/after/restored;
- journal event ids;
- resultados stale/gateway;
- operação executada;
- verificação pós-write;
- resultado humano;
- rollback result;
- duração;
- erros/abort reason.

## 12. Estado atual

O runbook está congelado como procedimento, porém sua seção mutável continua bloqueada.

Antes do T087 canário faltam:

1. smoke ambiental T083B com `gate.t083b_storage_pass=true`;
2. lock exclusivo de artigo;
3. executor mutável mínimo e version-gated;
4. seleção do canário;
5. Authorization Pack específico.

Nenhuma dessas pendências autoriza atalhos.
