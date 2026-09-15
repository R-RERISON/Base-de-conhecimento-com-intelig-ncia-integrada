# Production Rollout & Migration Contract v1 — SPEC-004

**Contract version:** `1.0.0`  
**Status:** FROZEN para arquitetura / implementação de writer pendente  
**Contexto:** desenvolvimento ocorre em homologação criada a partir de cópia de produção; instalação final ocorrerá no ambiente de produção.

## 1. Objetivo

Fazer com que a ida de homologação para produção seja previsível, idempotente, reversível e observável, evitando que instalação/update do plugin execute trabalho editorial pesado ou destrutivo.

## 2. Separação obrigatória

Existem três categorias de mudança:

### A. Runtime code upgrade

- arquivos PHP/JS/CSS do plugin;
- sem alteração editorial automática;
- deve ser reversível por rollback de package.

### B. Plugin-state/schema migration

- options/tabelas/metadados próprios do plugin quando existirem;
- idempotente;
- pequena e rápida;
- versionada;
- pode integrar o lifecycle normal do plugin somente após gate próprio.

### C. Editorial/content migration

- conversão de posts para Elementor;
- altera fonte editorial;
- **nunca** executada automaticamente por activation/update;
- exige operação administrativa explícita, dry-run, journal, canário e rollback.

## 3. Regra de ativação

Activation/update do plugin não pode:

- percorrer os 622+ posts para transformação editorial;
- regravar `_elementor_data`;
- regravar `post_content`;
- publicar/despublicar conteúdo;
- criar lote de revisões editoriais;
- rodar Foundry/IA;
- depender de rede externa.

Heavy jobs devem ser separados do request de activation/update.

## 4. Versionamento independente

O projeto deve evoluir para controlar separadamente:

- `plugin_version` — versão do package/runtime;
- `schema_version` — estado persistente próprio do plugin;
- `knowledge_document_schema_version` — contrato do documento derivado;
- `elementor_projection_schema_version` — contrato de projeção editorial;
- `migration_plan_version` — versão do algoritmo de migração de conteúdo.

Não usar apenas a versão do plugin como prova de que uma migration de conteúdo foi executada.

## 5. Upgrade Manager futuro

Quando houver schema persistente próprio, implementar um `Upgrade_Manager` com:

- registry explícito de migrations numeradas;
- `from_version -> to_version`;
- migrations idempotentes;
- lock contra concorrência;
- registro de sucesso/falha;
- retry seguro;
- fail-closed em migration parcial;
- nenhuma migration editorial massiva dentro do registry síncrono.

Enquanto não houver schema persistente, não criar framework complexo apenas por antecipação.

## 6. Preflight do ambiente alvo

Antes de instalar/promover release em produção, capturar ao menos:

- WordPress version;
- PHP version;
- plugin version atual e alvo;
- Elementor ativo/inativo;
- `ELEMENTOR_VERSION`;
- lista de plugins necessários ao conteúdo/shortcodes relevantes;
- multisite;
- memory limit;
- max execution time;
- DB engine/version quando relevante;
- disponibilidade de cron/loopback se jobs futuros dependerem disso;
- capacidade administrativa necessária;
- existência de backup recente.

Diferença entre homologação e produção deve ser classificada como:

- `compatible`;
- `review_required`;
- `blocking`.

## 7. Elementor compatibility matrix

A homologação de migrations deve registrar a versão exata do Elementor usada.

Writer de produção só pode executar quando:

- Elementor estiver carregado;
- versão estiver dentro da matriz homologada;
- gateway confirmar Document API esperada;
- post type/document estiver suportado.

Versão desconhecida => fail closed para content migration, sem bloquear necessariamente o Content Extractor read-only.

## 8. Backup e rollback

Antes de qualquer migration editorial em produção deve existir:

1. backup externo do banco validado;
2. manifest por post com hashes before;
3. journal de migration suficiente para rollback;
4. estratégia para restaurar `_elementor_data`, edit mode, settings e outros estados Elementor afetados;
5. estratégia para restaurar `post_content` caso seja alterado;
6. verificação pós-rollback.

A definição do storage do journal é obrigatória antes do writer. Não improvisar snapshots grandes em options globais.

## 9. Dry-run

Toda migration editorial deve possuir modo dry-run read-only.

Saída mínima:

- total elegível;
- `native`;
- `projectable`;
- `review_required`;
- `blocked`;
- warnings por categoria;
- estimativa de tamanho do journal;
- estimativa de lotes;
- nenhuma exportação desnecessária de conteúdo textual.

## 10. Canary rollout

Antes de lote amplo:

- selecionar amostra representativa de legacy HTML, Elementor inválido, Gutenberg, shortcode e plain text;
- executar migration em poucos posts de homologação;
- abrir no editor Elementor;
- validar frontend;
- comparar conteúdo/facts/hashes;
- testar edição e novo save no Elementor;
- testar rollback.

Só então liberar lotes maiores.

## 11. Batch migration

Diretrizes iniciais:

- lotes pequenos e retomáveis;
- checkpoint por item;
- uma falha não corrompe o restante do lote;
- retry explícito;
- pause/cancel suportados antes de produção em massa;
- nenhuma execução longa presa ao request HTTP do navegador;
- rate/budget configuráveis.

Tamanho exato do lote será definido com medição real, não por chute.

## 12. Concorrência editorial

Migration não deve sobrescrever post modificado após o dry-run/snapshot.

Antes de aplicar:

- comparar `post_modified_gmt` e/ou source hash;
- divergência => `STALE_SOURCE`, item não migrado;
- usuário/editor sempre vence sobre migration atrasada.

## 13. Idempotência

Reexecutar o mesmo migration plan sobre o mesmo source hash deve resultar em:

- `NO_CHANGE`, ou
- mesma projeção/hash;
- nunca duplicação progressiva de containers/widgets.

## 14. Observabilidade

Registrar sem vazar corpo editorial:

- migration/job id;
- plan version;
- post id apenas em storage administrativo protegido;
- source hash before;
- projection hash;
- status;
- warnings/errors;
- timestamps;
- operador quando aplicável;
- rollback status.

Logs gerais não devem conter conteúdo completo do artigo.

## 15. Produção — sequência desejada

1. package aprovado;
2. backup;
3. preflight produção;
4. instalar/atualizar plugin;
5. smoke das SPECs anteriores;
6. extractor read-only smoke;
7. Knowledge Document smoke;
8. content migration dry-run;
9. canário;
10. validação humana/técnica;
11. batches controlados;
12. relatório final;
13. manter janela de rollback conforme política operacional.

## 16. Rollback do plugin vs rollback editorial

São independentes.

Rebaixar o package do plugin não deve ser usado como mecanismo para desfazer posts já migrados. Content rollback usa seu próprio journal/snapshot.

## 17. Banco/tabela própria

SPEC-004 continua sem criar tabela apenas para extração/Knowledge Document.

Uma tabela de journal/job de migration poderá ser autorizada futuramente se G-245 demonstrar necessidade operacional. Isso é justificativa diferente de persistir Knowledge Document.

## 18. Gate proposto — G-245 Production Readiness

PASS somente quando existirem:

- preflight target-aware;
- version/schema strategy;
- Elementor gateway compatível com versão homologada;
- dry-run de migration;
- journal/rollback;
- stale-source guard;
- canário aprovado;
- procedimento de instalação/upgrade/rollback documentado.

G-245 deve ocorrer antes do G-250 package final de produção.
