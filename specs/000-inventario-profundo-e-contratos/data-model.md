# Modelo de Dados do Inventário — SPEC-000

Esta SPEC não cria tabelas no WordPress. Este arquivo define a estrutura conceitual de cada registro de inventário e dos artefatos de cruzamento.

## Entidade: ArtefatoInventariado

Campos:

- `projeto_origem` — KB2Ops | ASI | Resumo Executivo;
- `baseline_sha`;
- `caminho`;
- `simbolo` — classe/função/método quando aplicável;
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

Criada no cruzamento T052 para separar **propriedade semântica** de **storage físico**.

Campos:

- `conceito` — significado do dado, independente de meta key/tabela;
- `fontes_historicas` — chaves/stores que hoje representam o conceito;
- `owner_atual` — plugin/domínio que hoje escreve ou define o dado;
- `writers_atuais`;
- `readers_atuais`;
- `natureza` — editorial | domínio canônico | governança | projection | observacional | operacional | configuração | evidência;
- `owner_futuro_logico`;
- `writers_futuros_autorizados`;
- `readers_futuros`;
- `reconstruivel`;
- `compatibilidade_necessaria`;
- `colisoes_semanticas`;
- `storage_final` — propositalmente pode permanecer `AINDA_NAO_SABEMOS` nesta SPEC;
- `evidencia`;
- `status_decisao`.

### Regra de ownership

`um conceito canônico -> um owner lógico`.

Projection, cache, índice, embedding, dashboard e adapter de migração nunca substituem o owner canônico.

## Entidade: SobreposicaoFuncional

Criada no cruzamento T053 para impedir que a futura arquitetura seja uma soma dos três plugins.

Campos:

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

### Regra de convergência

Duas superfícies parecidas não são fundidas automaticamente. Antes, deve-se provar se representam:

- a mesma responsabilidade;
- responsabilidades complementares;
- uma bridge temporária;
- uma dívida histórica.

Exemplo: `review_state=approved` e Apply de Search Knowledge são **complementares e separados**, não um único workflow.

## Classificação de decisão

Enum conceitual:

`MANTER | REDESENHAR | SUBSTITUIR_POR_WORDPRESS | EVOLUIR_COM_IA_VETOR | DESCARTAR | AINDA_NAO_SABEMOS`.

## Regra final

Este modelo é documental na SPEC-000. Não autoriza banco, JSON runtime, classes, taxonomy, schema persistente, endpoints ou serviços externos no novo plugin.
