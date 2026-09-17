# Contrato de Execução em Lotes — SPEC-004 / G-245

**Versão:** `1.0.0`  
**Estado:** planejamento congelado; executor editorial ainda não autorizado  
**Dependências:** dry-run, stale-source guard, journal/rollback e canário aprovados.

## 1. Escopo

Este contrato define como uma futura operação explícita poderá organizar itens
de migration editorial. Ele não cria fila, tabela, cron, job ou writer.

Activation, update e carregamento do plugin nunca iniciam batches.

## 2. Identidade e checkpoint

Cada lote pertence a um `migration_job_id` e possui:

- `batch_id` monotônico;
- `plan_version`;
- `projection_schema_version`;
- `batch_size` configurado;
- cursor/checkpoint do último item finalizado;
- estado do lote;
- timestamps e operador;
- contagem por estado.

O checkpoint só avança depois que o item tiver resultado terminal persistido e
relerido. Item `APPLYING` nunca pode ser tratado como concluído por ausência de
erro HTTP.

## 3. Estados do lote

- `PLANNED` — composição read-only;
- `READY` — precondições e journal confirmados;
- `RUNNING` — processamento ativo;
- `PAUSED` — pausado explicitamente;
- `CANCELLING` — cancelamento solicitado;
- `CANCELLED` — cancelamento confirmado sem novo item iniciado;
- `COMPLETED` — todos os itens terminais e validados;
- `COMPLETED_WITH_ERRORS` — itens concluídos e falhos devidamente registrados;
- `BLOCKED` — precondição ausente ou gateway incompatível;
- `FAILED` — falha operacional do lote;
- `ROLLBACK_REQUIRED` — item aplicado não foi validado.

Somente `COMPLETED` pode ser apresentado como sucesso integral.

## 4. Seleção e tamanho

- seleção deriva de Projection Plans congelados;
- `STALE_SOURCE`, `REVIEW_REQUIRED` e `BLOCKED` não entram como elegíveis;
- tamanho inicial deve ser configurável e medido no canário;
- nenhum valor fixo é considerado seguro sem evidência de tempo/memória;
- ordenação estável por `post_id` ou cursor equivalente;
- um item só pode pertencer a um batch ativo.

## 5. Retry e falhas

- retry somente para erro classificado como transitório;
- limite de tentativas por item e por lote é obrigatório;
- erro de contrato, stale source ou incompatibilidade não recebe retry cego;
- item falho não bloqueia silenciosamente o restante, mas impede `COMPLETED`;
- qualquer falha após write exige `ROLLBACK_REQUIRED` e pausa segura.

## 6. Pause e cancelamento

Pause/cancel devem ser verificados entre itens e antes de iniciar novo write.

Não há cancelamento forçado no meio de `Document::save()`. Se o processo cair
durante `APPLYING`, o próximo operador deve reconciliar o journal antes de
retomar ou fazer rollback.

## 7. Concorrência

- lease/lock do job precisa expirar de forma recuperável;
- operador não pode iniciar dois writers para o mesmo post;
- source hash e `post_modified_gmt` são revalidados imediatamente antes do write;
- divergência vira `STALE_SOURCE` e o item é pulado sem sobrescrita.

## 8. Segurança

- capability administrativa específica e confirmação explícita;
- preflight alvo completo;
- backup externo validado;
- journal persistido e relido;
- gateway Elementor compatível;
- dry-run correspondente disponível;
- logs sem corpo editorial.

Ausência de qualquer pré-condição resulta em `BLOCKED`, sem fallback perigoso.

## 9. Gate

T089 é considerado definido quando o executor futuro puder provar checkpoint,
retomada, pausa, cancelamento, retry limitado, stale guard e rollback-required.

O contrato não autoriza T090 nem qualquer write. O canário precisa ser
implementado e aprovado separadamente.