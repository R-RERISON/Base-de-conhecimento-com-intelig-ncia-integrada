# JSON de Browser Acceptance — contrato de evidência

O coletor temporário T043 gerou JSON sem persistência. No `0.1.0-dev.5`, o contrato foi `schema_version=2.2.0` e `mode=temporary_browser_acceptance_with_fixture`.

## Ambiente coletado automaticamente

- WordPress;
- PHP;
- versão do plugin;
- browser/versão inferidos do User-Agent;
- viewport atual;
- menor viewport observado durante a sessão;
- device pixel ratio;
- User-Agent sanitizado.

## Fixture

O relatório inclui `post_id`, marker `_bdc_kb_browser_fixture=spec001-browser-v1`, `state_sha256` e confirmação de hard-delete. Nenhum artigo real foi usado.

## Checks automáticos

- `G110-A01` persistência/releitura dos três campos;
- `G110-A02` preservação de `post_title`, `post_content` e `_elementor_data`;
- `G110-A03` POST-Redirect-GET com status `saved`;
- `G110-A04` coleta do viewport;
- `G110-A05` shell do wp-admin sem sidebar secundária própria;
- `G110-A06` estrutura da listagem/seleção e paginação quando necessária;
- `G110-A07` contexto read-only + labels associados;
- `G110-A08` feedback textual de sucesso;
- `G110-A09` observação objetiva de largura `<=782px`.

## Checks humanos obrigatórios

Somente:

- `G110-06` navegação por teclado / ordem de foco;
- `G110-07` usabilidade real no viewport estreito.

## Evidência aceita

A execução `0.1.0-dev.5` de 2026-09-14 fechou G-110 com `overall=PASS`, 2/2 checks humanos PASS, `auto_fail=0`, viewport mínimo `671x660` e zero resíduos.

O coletor é histórico de homologação e foi removido do runtime/package em T044A.
