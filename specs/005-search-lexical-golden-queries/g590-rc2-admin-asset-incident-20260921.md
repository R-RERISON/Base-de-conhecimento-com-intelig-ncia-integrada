# G-590 RC2 — Admin Asset Bootstrap Incident

**Status:** FAIL CONTROLADO DE UI BOOTSTRAP / GATE FUNCIONAL NÃO EXECUTADO  
**Data:** 2026-09-21  
**Candidato afetado:** `0.5.1-rc.2 / g590.2`

## Sintoma

A página **SPEC-005 — G-590 Section Retrieval & Deep-Link** renderizou corretamente, porém os botões **Iniciar / Retomar G-590** e **Reiniciar evidência** não produziram ação.

## Causa raiz

O RC2 chamava `wp_enqueue_script()` e `wp_add_inline_script()` dentro de `render_page()`.

No WordPress Admin, `render_page()` ocorre depois da fase normal de `admin_enqueue_scripts`/impressão dos scripts. O HTML podia portanto renderizar sem que o JavaScript do runner fosse emitido.

## Correção RC3

- asset dedicado `assets/js/search-section-g590.js`;
- hook `admin_enqueue_scripts`;
- carregamento limitado a `page=bdc-kb-spec005-g590-section`;
- configuração + nonce via `wp_localize_script`;
- remoção completa de late enqueue/inline browser script de `render_page()`;
- execução AJAX resumível preservada.

## Identidade RC3

- Product Version: `0.5.1-rc.3`
- Build ID: `g590.3-79734d820a5e`
- Source commit: `79734d820a5e2960fa4a0e9e69588418a6fe56bf`
- Runner blob: `e1ea5488537c3e11df82c793d6a9eb522d4984cf`
- Admin JS blob: `77c58e75ff6429ecfbf1acdc279f4815aa02e14f`
- ZIP SHA-256: `22f9caae295f30347676eb835dd879c78c8c7468915cfde272020a8b7911560d`

G-590 permanece OPEN até evidência ambiental completa.
