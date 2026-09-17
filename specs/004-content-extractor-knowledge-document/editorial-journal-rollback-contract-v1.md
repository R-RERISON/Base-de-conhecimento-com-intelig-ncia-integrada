# Contrato de Journal e Rollback Editorial — SPEC-004 / G-245

**Versão:** `1.0.0`  
**Estado:** congelado para planejamento; nenhum writer autorizado  
**Escopo:** migration editorial Elementor futura, separada do runtime read-only.

## 1. Fronteira

O journal pertence ao plano de migration editorial. Ele não é usado pelo
Content Extractor, Knowledge Document, Projection Plan ou activation/update.

Nenhuma operação desta etapa cria tabela, option, transient, post meta, revisão
ou arquivo persistente.

## 2. Identidade do plano

Cada execução futura deverá possuir:

- `migration_job_id` único;
- `migration_plan_version`;
- `projection_schema_version`;
- versão WordPress/PHP/Elementor;
- operador e timestamps;
- estado geral do job;
- política de retenção e janela de rollback.

O `migration_job_id` não substitui o `post_id` nem o `source_hash_before`.

## 3. Registro por post

O journal administrativo protegido deverá registrar, no mínimo:

```text
post_id
source_hash_before
source_fingerprint_before
post_modified_gmt_before
projection_hash
projection_schema_version
elementor_version
status
warnings[]
error_code
started_at
finished_at
rollback_status
```

O corpo editorial completo não deve aparecer em logs gerais. O armazenamento
protegido do journal poderá conter snapshots necessários ao rollback somente
após decisão explícita de schema, capacidade, retenção e controle de acesso.

## 4. Snapshot de rollback

Antes de qualquer write, o item elegível deverá capturar e validar:

- `_elementor_data` bruto;
- `_elementor_edit_mode`;
- `_elementor_version`;
- metadados Elementor correlatos efetivamente alterados;
- `post_content` caso a operação o altere;
- `post_modified_gmt` e status editorial;
- hash do snapshot e tamanho.

O snapshot é ligado ao `migration_job_id` e ao post. Snapshot ausente,
incompleto ou ilegível bloqueia o write.

## 5. Estados

Estados mínimos por item:

- `PLANNED` — plano calculado, sem write;
- `ELIGIBLE` — precondições confirmadas;
- `APPLYING` — write em andamento, não pode ser tratado como sucesso;
- `APPLIED` — releitura e validação pós-write passaram;
- `NO_CHANGE` — fonte/projeção já correspondem ao plano;
- `STALE_SOURCE` — fonte mudou desde o snapshot;
- `REVIEW_REQUIRED` — revisão humana necessária;
- `BLOCKED` — precondição ou gateway incompatível;
- `FAILED` — write falhou;
- `ROLLBACK_PENDING` — aplicação não validada;
- `ROLLED_BACK` — estado anterior restaurado e relido;
- `ROLLBACK_FAILED` — restauração não comprovada.

`APPLYING`, `FAILED`, `ROLLBACK_PENDING` e `ROLLBACK_FAILED` nunca podem ser
exibidos como sucesso.

## 6. Invariantes de aplicação

Antes do write:

1. preflight do ambiente passou;
2. gateway Elementor confirmou versão/API;
3. snapshot foi persistido e relido;
4. `source_hash_before` e fingerprint continuam iguais;
5. capability e confirmação explícita foram verificadas;
6. item não está em outro job ativo.

Durante o write:

- somente o gateway version-gated pode salvar;
- nenhuma chamada genérica a `update_post_meta()` para dados Elementor;
- falha parcial muda o item para rollback, nunca para `APPLIED`.

Depois do write:

- reler Document Elementor e fonte editorial;
- recalcular Knowledge Document/Projection Plan;
- comparar hashes e critérios de fidelidade;
- somente então marcar `APPLIED`.

## 7. Rollback

Rollback por item deve ser idempotente:

1. bloquear novo processamento do item;
2. confirmar que o snapshot pertence ao post/job;
3. restaurar via gateway ou adapter aprovado;
4. reler todos os campos restaurados;
5. comparar hashes do snapshot;
6. registrar `ROLLED_BACK` somente com equivalência comprovada;
7. em divergência, registrar `ROLLBACK_FAILED` e interromper o lote.

Rollback do package do plugin e rollback editorial são operações independentes.

## 8. Concorrência e stale source

Qualquer alteração de `post_modified_gmt`, `source_hash` ou fingerprint depois
do dry-run torna o item `STALE_SOURCE`. O usuário/editor vence; não há merge
automático nem sobrescrita forçada.

## 9. Storage futuro

O storage só poderá ser escolhido após medir:

- tamanho médio/máximo dos snapshots;
- cardinalidade e retenção;
- concorrência e leases;
- necessidade de consulta operacional;
- backup/restore e privacidade.

Options globais não são storage aprovado para journal volumoso. Uma tabela
própria ou storage externo exige SPEC/ADR, migration, capability, retenção,
backup e rollback documentados.

## 10. Gate de aprovação

T086 só autoriza planejamento. Antes de qualquer writer ainda são obrigatórios:

- T087 dry-run sem writes;
- T088 stale-source guard;
- T089 batches retomáveis;
- T090 canário;
- T091 rollback real do canário;
- T092 runbook de operação;
- confirmação de storage e backup.

**Decisão:** journal/rollback definido; writer continua `BLOCKED`.