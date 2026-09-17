# Runbook de Instalação, Upgrade, Migration e Rollback — SPEC-004

**Versão:** `1.0.0`  
**Estado:** aprovado como procedimento; migration editorial ainda bloqueada  
**Ambiente de referência:** homologação local WordPress `7.0`, PHP `8.5.10`, Elementor `4.1.0`.

## 1. Regra de parada

Não iniciar migration editorial se qualquer pré-condição estiver ausente.
`NOT_CONFIGURED`, `NOT_VERIFIED`, `BLOCKED`, erro de backup, stale source ou
rollback não comprovado interrompem o fluxo.

O plugin pode ser instalado/atualizado como código read-only sem migration.

## 2. Instalação/upgrade do package

1. Confirmar branch, commit, artefato e checksum.
2. Confirmar que o package contém uma única raiz de plugin e não contém `tests`,
   `specs`, `scr`, logs ou segredos.
3. Executar lint PHP e `node --check` em container quando as ferramentas não
   existirem no host.
4. Preservar banco, uploads e plugin anterior.
5. Instalar/atualizar o código.
6. Confirmar que activation/update não iniciou job editorial.
7. Executar smoke das SPECs anteriores e do Content Extractor/Knowledge Document.

Comando de regressão local:

```bash
docker exec wordpress-from-repo sh -lc \
  'cd /var/www/html/BASE-CONHECIMENTO-GITHUB && sh tools/validate-local.sh'
```

JavaScript sem Node local:

```bash
docker run --rm -v "$PWD/plugin/base-conhecimento-inteligencia-integrada:/src:ro" \
  node:22-alpine node --check /src/assets/js/workspace.js
```

## 3. Production Preflight

Capturar e revisar:

- WordPress, PHP, plugin e Elementor;
- banco, multisite, memory limit e execution time;
- plugins ativos relevantes;
- cron/loopback;
- backup externo validado;
- capabilities do operador;
- perfil alvo completo `BDC_KB_PREFLIGHT_TARGET_*`.

Resultado `NOT_CONFIGURED` não é aprovação. Versão Elementor divergente ou
desconhecida bloqueia migration, embora possa permitir leitura read-only.

## 4. Dry-run

Executar Projection Plan/Migration Dry-run sem writes e registrar somente
agregados:

- total, native, projectable, review_required e blocked;
- estratégias e warnings;
- estimativa de journal/batches;
- fingerprint editorial antes/depois.

Não exportar corpo editorial, títulos, URLs ou IDs em logs gerais.

## 5. Preparação do canário

Selecionar amostra representativa contendo legacy HTML, plain text, Gutenberg,
shortcodes/tabelas, Elementor inválido e Elementor nativo. Para cada item:

1. congelar Projection Plan;
2. criar e reler snapshot do journal;
3. verificar capability, confirmação e backup;
4. executar Stale Source Guard;
5. confirmar Gateway Elementor version-gated;
6. somente então considerar aplicação explícita.

No estado atual, o gateway retorna `BLOCKED` para save. Portanto o canário
editorial não pode ser executado ainda; apenas o canário técnico read-only foi
validado.

## 6. Execução de lote futuro

- lote pequeno, configurável e medido;
- cursor/checkpoint somente após item terminal validado;
- pause/cancel entre itens;
- retry apenas transitório e limitado;
- stale source sem retry cego;
- falha pós-write vira `ROLLBACK_REQUIRED`;
- `COMPLETED` somente com todos os itens aprovados;
- um post não pode estar em dois jobs ativos.

Activation/update nunca iniciam batches.

## 7. Rollback editorial

1. Pausar o lote e bloquear novos itens.
2. Confirmar `migration_job_id`, post e snapshot correspondente.
3. Restaurar pelo Gateway/adapter aprovado.
4. Reler `_elementor_data`, metadados correlatos, `post_content`, status e data.
5. Recalcular hashes e comparar com o snapshot.
6. Marcar `ROLLED_BACK` somente com equivalência comprovada.
7. Divergência vira `ROLLBACK_FAILED` e exige intervenção; não retomar lote.

Rollback do package não desfaz migration editorial.

## 8. Pós-operação

- repetir extractor/Knowledge Document;
- comparar facts, seções, links, headings, listas, tabelas e imagens;
- abrir editor Elementor e frontend;
- validar edição e novo save;
- registrar relatório sem corpo editorial em logs gerais;
- manter janela de rollback;
- atualizar continuidade, evidências e estado do gate.

## 9. Estado atual e bloqueios

- G-220/G-230/G-240: PASS;
- T082–T090 técnico: PASS;
- writer Elementor: `BLOCKED`;
- storage de journal: `NOT_CONFIGURED`;
- rollback editorial real: `NOT_RUN`;
- produção: `NOT_VERIFIED`;
- G-250: `NOT_RUN`.

**Conclusão:** o runbook está pronto, mas não autoriza migration. T091 só pode
ser executado depois que o writer, storage, canário editorial e backup forem
formalmente autorizados e testados.