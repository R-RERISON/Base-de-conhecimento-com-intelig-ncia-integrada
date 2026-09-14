# Modelo de Dados do Inventário — SPEC-000

Esta SPEC não cria tabelas no WordPress. Este arquivo define a estrutura conceitual de cada registro de inventário.

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

## Classificação de decisão

Enum conceitual:

`MANTER | REDESENHAR | SUBSTITUIR_POR_WORDPRESS | EVOLUIR_COM_IA_VETOR | DESCARTAR | AINDA_NAO_SABEMOS`.

## Regra

Este modelo é documental na SPEC-000. Não autoriza banco, JSON runtime ou schema persistente no novo plugin.