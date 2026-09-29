# G-590 RC3 — Download URL / Nonce Incident

**Status:** GATE EXECUTADO / DOWNLOAD BLOQUEADO POR DUPLA CODIFICAÇÃO  
**Data:** 2026-09-21  
**Candidato afetado:** `0.5.1-rc.3 / g590.3`

## Evidência observada

O runner chegou a:

```text
Estado: Concluído — JSON disponível
Fase: Concluído
```

Ao clicar em **Baixar JSON final**, o navegador abriu URL contendo separadores literais:

```text
&amp;job_id=...
&amp;_wpnonce=...
```

e o WordPress retornou:

```text
Nonce inválido ou expirado.
```

## Causa raiz

`public_job_state()` usava `wp_nonce_url()`.

`wp_nonce_url()` devolve URL preparada para contexto HTML. Essa URL foi posteriormente submetida novamente ao boundary de escaping/renderização e atribuída pelo JavaScript ao `href`.

Resultado: os separadores de query foram enviados como `&amp;` literal, impedindo o WordPress de receber `job_id` e `_wpnonce` corretamente.

## Classificação

- execução funcional G-590: **CONCLUÍDA**;
- relatório persistido no job: **DISPONÍVEL**;
- download: **FAIL CONTROLADO**;
- Search/Section Retrieval/Golden/performance/fingerprint: não são reexecutados por esta correção.

## Correção RC4

`public_job_state()` passa a gerar URL de dados crua:

- `add_query_arg()`;
- nonce explícito por `wp_create_nonce()`;
- escaping apenas no boundary HTML;
- sem `wp_nonce_url()` no valor transportado por JSON/JavaScript.

## Persistência

O relatório concluído permanece em:

```text
Option: bdc_kb_spec005_g590_job
autoload=false
```

A atualização RC3 → RC4 não remove nem reinicia o job.

Se após o upgrade a tela continuar em **Concluído — JSON disponível**, não reiniciar a evidência; apenas baixar o JSON final.

## RC4

- Product Version: `0.5.1-rc.4`
- Build ID: `g590.4-5d74569e70bb`
- Source commit: `5d74569e70bb3e0a646a081ec2e3e855e581a452`
- Runner blob: `8cf78c31842aea1b6ec12be5e4010c27647e49a2`
- Admin JS blob: `77c58e75ff6429ecfbf1acdc279f4815aa02e14f`
- ZIP SHA-256: `c05211f1db3ee568403b5f3b74abd8f8d3335d11865e9b77197afaf20e33b3ee`
