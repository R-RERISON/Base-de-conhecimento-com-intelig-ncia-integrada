# G-590 — HTTP 504 Incident & Resumable Execution Decision

**Status:** FAIL CONTROLADO DE ORQUESTRAÇÃO / FUNCIONALIDADE NÃO AVALIADA  
**Data:** 2026-09-21  
**Candidato afetado:** `0.5.1-rc.1 / g590.1`

## Sintoma observado

Ao executar **Base de Conhecimento → Section Retrieval G-590 → Executar G-590 e baixar JSON**, o ambiente retornou:

```text
504 Gateway Time-out
The server didn't respond in time.
```

Nenhum JSON ambiental foi produzido.

## Causa raiz

O runner G-590.1 agregava em uma única requisição HTTP síncrona:

1. fingerprint editorial de todo o corpus;
2. lifecycle/schema;
3. rebuild explícito de duas passagens;
4. coverage estrutural de todo o corpus;
5. section/deep-link probes;
6. benchmark;
7. Golden regression;
8. fingerprint editorial final;
9. serialização/download do JSON.

Cada subcomponente possui valor técnico, porém o acoplamento numa única requisição tornou o gate dependente do timeout do proxy/webserver.

Isso é uma falha de **orquestração do teste**, não evidência de falha do Search Retrieval, Section Retrieval, Deep-Link ou Golden.

## Decisão

O G-590 passa a usar execução **resumable/chunked**.

Contrato:
- AJAX autenticado;
- `manage_options` obrigatório;
- nonce;
- job state persistido em Option com `autoload=false`;
- lock anti-concorrência;
- fingerprints em lotes de 50 posts;
- coverage em lotes de 25 posts;
- rebuild permanece explícito e isolado em fase própria;
- retomada após reload/erro HTTP;
- download do JSON somente após `status=complete`;
- nenhum write editorial;
- nenhum timeout HTTP isolado pode ser interpretado automaticamente como falha funcional do gate.

## Proteção das SPECs fechadas

O `Search_Rebuild_Service::rebuild()` não foi alterado.

Motivo:
- o mesmo core já obteve PASS ambiental no G-580;
- a causa do 504 foi o acoplamento de múltiplas operações G-590 numa mesma requisição;
- alterar o rebuild neste momento ampliaria desnecessariamente a superfície de regressão de SPECs já fechadas.

## Identidade corretiva

Novo candidato:

```text
Product Version: 0.5.1-rc.2
Build label:     g590.2
Execution:       g590-resumable-v1.0.0
```

O RC1 permanece histórico e não deve ser reutilizado.

## Critério de fechamento

G-590 continua OPEN.

Somente o JSON completo do RC2, validado pelo machine validator, pode promover T590-14..T590-19.
