# G-560 Human Visual Acceptance — Search UX

**Data:** 2026-09-19  
**Gate:** G-560 / T564  
**Build observado:** `0.5.0-g560.1`  
**Superfície:** Knowledge List — estado Search `success`  
**Consulta observada:** `windows 11`  
**Decisão:** PASS HUMANO COM DIVERGÊNCIA VISUAL MOBILE DEFERRED/NON-BLOCKING

## Evidência humana

Foi fornecida captura desktop real da Knowledge List após execução da busca `windows 11`.

Dimensão da evidência recebida: **1726×412 px**.

Elementos visíveis revisados:
- product hero preservado;
- título `Artigos da Base de Conhecimento`;
- ação primária `Abrir novo artigo no WordPress`;
- label visível `Pesquisar artigos`;
- campo Search preenchido com `windows 11`;
- helper textual;
- ação primária `Pesquisar`;
- ação secundária `Limpar`;
- feedback `Pesquisa concluída`;
- contagem de resultados;
- explicação explícita de ordenação por relevância lexical.

## Avaliação visual desktop

PASS.

A captura demonstra:
- hierarquia visual coerente com UX-002.3;
- identidade BDC preservada;
- WordPress usado como shell, sem aparência genérica dominante do wp-admin;
- ação primária inequívoca;
- campo/label/helper coerentes;
- feedback Search claramente separado do formulário;
- tipografia e espaçamento legíveis;
- bordas/radius/superfícies consistentes com Visual Contract v2;
- estado de sucesso não depende apenas de cor;
- nenhum clipping, overlap ou overflow visível na largura observada;
- botões Search/Limpar possuem hierarquia visual adequada.

A tabela de resultados não aparece na área capturada. Isso não bloqueia T564 porque a tabela já pertence à baseline visual homologada UX-002.3; a mudança material desta etapa é o Search toolbar/feedback e a integração de relevância. O metadado de relevância foi validado estruturalmente no G-560 automatizado.

## Divergência explícita — revisão visual mobile

O Visual Contract v2 define revisão desktop + breakpoints aplicáveis como Definition of Visual Done.

O Product Owner decidiu explicitamente em 2026-09-19:

> “Não precisamos nos preocupar com mobile agora.”

Decisão da SPEC-005:
- revisão visual humana em 782/520: **DEFERRED**;
- impacto: possível regressão visual estreita ainda não observada por humano;
- mitigação existente:
  - source contract 782/520 PASS;
  - mobile search actions PASS;
  - accessibility contract PASS;
  - runtime Search state semantics PASS;
  - nenhum erro ambiental;
- classificação: **non-blocking para SPEC-005/G-560**;
- obrigação futura: revisar mobile antes de uma iniciativa que declare suporte mobile como requisito de release/produto.

Esta divergência é local à SPEC-005 e **não altera** o Visual Contract v2 permanente.

## T564

**PASS WITH DEFERRED MOBILE VISUAL REVIEW.**

Aceite humano desktop recebido e aprovado.

## T565

Com:
- T560 PASS;
- T561 PASS técnico + revisão visual mobile explicitamente deferred;
- T562 PASS;
- T563 PASS;
- T564 PASS humano desktop;

**G-560 = PASS/CLOSED.**

Próximo gate: **G-570 — Segurança/Performance**.
