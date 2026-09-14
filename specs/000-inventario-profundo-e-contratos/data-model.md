# Modelo de Dados do Inventário — SPEC-000

Esta SPEC não cria tabelas no WordPress. Este arquivo define a estrutura conceitual dos registros de inventário e cruzamento.

## Entidade: ArtefatoInventariado

Campos:

- `projeto_origem` — KB2Ops | ASI | Resumo Executivo;
- `baseline_sha`;
- `caminho`;
- `simbolo`;
- `categoria`;
- `responsabilidade`;
- `entradas`;
- `saidas`;
- `efeitos_colaterais`;
- `hooks_wordpress`;
- `persistencia_lida`;
- `persistencia_escrita`;
- `capabilities`;
- `nonces`;
- `rotas`;
- `assets`;
- `dependencias`;
- `falhas_fallbacks`;
- `testes_associados`;
- `comportamento_protegido`;
- `risco_remocao`;
- `decisao_preliminar`;
- `contrato_novo_proposto`;
- `evidencia`.

## Entidade: Persistencia

- tipo: postmeta | option | transient | taxonomy | table | cache | cron-state | outro;
- chave/nome;
- proprietário atual;
- leitores;
- escritores;
- volume estimado;
- reconstruível?;
- dado canônico?;
- sensibilidade;
- retenção;
- destino proposto.

## Entidade: Integracao

- tipo: action | filter | shortcode | admin-post | AJAX | REST | cron | HTTP externo | outro;
- nome;
- produtor;
- consumidor;
- autenticação/autorização;
- payload;
- idempotência;
- comportamento de falha;
- uso conhecido;
- destino proposto.

## Entidade: ContratoRegressao

- identificador;
- domínio;
- cenário;
- entrada;
- expectativa;
- severidade;
- teste atual;
- teste futuro;
- evidência baseline.

## Entidade: OwnershipDado

Criada em T052 para separar propriedade semântica de storage físico.

- `conceito`;
- `fontes_historicas`;
- `owner_atual`;
- `writers_atuais`;
- `readers_atuais`;
- `natureza` — editorial | domínio_canônico | governança | projection | observacional | operacional | configuração | evidência;
- `owner_futuro_logico`;
- `writers_futuros_autorizados`;
- `readers_futuros`;
- `reconstruivel`;
- `compatibilidade_necessaria`;
- `colisoes_semanticas`;
- `storage_final` — pode permanecer `AINDA_NAO_SABEMOS`;
- `evidencia`;
- `status_decisao`.

Regra: `um conceito canônico -> um owner lógico`.

## Entidade: SobreposicaoFuncional

Criada em T053.

- `capacidade`;
- `implementacao_asi`;
- `implementacao_gre`;
- `implementacao_kb2ops`;
- `tipo_sobreposicao` — duplicação_real | complementar | compat_transicao | referencia_implementacao | descartavel;
- `comportamento_que_precisa_sobreviver`;
- `implementacoes_que_nao_devem_sobreviver`;
- `owner_funcional_futuro`;
- `superficie_futura_preliminar`;
- `dependencias`;
- `riscos_convergencia`;
- `gate_futuro`;
- `evidencia`.

## Entidade: ContratoCompatibilidade

Criada em T054 para impedir compatibilidade permanente/acidental.

Campos:

- `id` — D-xxx, alias/hook/store ou identificador documental;
- `contrato_historico`;
- `produtor_historico`;
- `consumidor_historico`;
- `evidencia`;
- `classificacao_arquitetural` — corrigido_futuro | descartado | depende_preflight_profiling | blocker;
- `compat_temporario` — sim | não | condicional;
- `condicao_entrada` — consumidor/dado que justifica adapter;
- `owner_canonico_futuro`;
- `modo_compatibilidade` — read_only | dual_read | traducao_evento | alias_render | adapter_externo | nenhum;
- `dual_write_permitido` — sempre `não` para estado permanente;
- `observabilidade_uso`;
- `rollback`;
- `gate_remocao`;
- `regressao_futura`;
- `risco_se_ignorado`;
- `blocker_id` quando aplicável.

### Regra de compatibilidade

Nenhum adapter, alias ou bridge é aprovado sem:

1. entrada/consumidor comprovado;
2. owner canônico;
3. modo limitado de compatibilidade;
4. observabilidade;
5. rollback;
6. gate de remoção;
7. teste de equivalência.

Compatibilidade nunca ganha ownership do dado.

## Entidade: Blocker

Criada em T054 para separar risco impeditivo de dívida postergável.

- `id` — B-xxx;
- `capacidade_bloqueada`;
- `severidade`;
- `evidencia`;
- `risco`;
- `gate_resolucao`;
- `quando_aplicavel`;
- `pode_postergar_para_slice_futuro`;
- `responsavel_logico`;
- `teste_evidencia_requerida`.

## Classificação de decisão

`MANTER | REDESENHAR | SUBSTITUIR_POR_WORDPRESS | EVOLUIR_COM_IA_VETOR | DESCARTAR | AINDA_NAO_SABEMOS`.

## Regra final

Este modelo é documental. Não autoriza banco, JSON runtime, classes, taxonomy, schema, endpoints, aliases, migrations ou serviços externos no novo plugin.