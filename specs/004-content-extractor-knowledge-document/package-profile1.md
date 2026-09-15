# Package — `0.4.0-profile.1`

## Objetivo

Build temporário exclusivo do gate R-200 para perfilar a composição real do corpus antes de implementar o Content Extractor permanente.

## Runtime

Diferença em relação à baseline `0.3.0-rc.1`:

- bootstrap promovido para `0.4.0-profile.1`;
- flag temporária `BDC_KB_SPEC004_PROFILE_BUILD=true`;
- novo `includes/class-content-profile.php`;
- submenu administrativo **Profiler SPEC-004**;
- nenhuma alteração em Summary, Classificação, Review, Histórico, Workspace ou seus Stores/Contracts.

## Segurança do profiler

- capability `manage_options`;
- POST + nonce;
- somente leitura;
- sem execução de shortcodes;
- sem renderização Elementor;
- sem renderização de dynamic blocks;
- sem persistência de progresso/resultado;
- sem exportação de conteúdo, títulos, URLs ou IDs de posts;
- fingerprint editorial before/after no relatório.

## Validações locais

- PHP: **12/12 lint PASS** em PHP `8.4.23`;
- `assets/js/workspace.js`: syntax PASS;
- source parity: **16/16** arquivos do package coincidem com os blobs esperados do `main` — os 14 arquivos permanentes da baseline, bootstrap `0.4.0-profile.1` e profiler temporário;
- bootstrap blob: `091055f20377e29f9271986afe9473deced8034b`;
- profiler blob: `e878fdde84ffd8931decb326abf70f22afa65bce`;
- ZIP integrity: PASS;
- estrutura instalável WordPress: PASS.

## Artefato

Arquivo:

`base-conhecimento-inteligencia-integrada-0.4.0-profile.1.zip`

SHA-256:

`eeae2f7a5c37dead27bd21f486bea7a64b75d510392a35b742ec6eb338a59bdd`

Tamanho:

`38.142 bytes`

## Uso ambiental

1. instalar/substituir a baseline pelo `0.4.0-profile.1`;
2. acessar **Base de Conhecimento → Profiler SPEC-004** como administrador;
3. clicar **Executar profiler read-only e baixar JSON**;
4. retornar o arquivo `bdc-kb-spec004-content-profile-*.json`;
5. não editar conteúdo durante a janela de execução para evitar falso positivo no fingerprint concorrente.

## Critério de avanço

R-200 permanece NOT_RUN até o JSON real confirmar, no mínimo:

- `environment.plugin = 0.4.0-profile.1`;
- `safety.editorial_fingerprint_equal = true`;
- `safety.changed_posts_during_run = 0`;
- corpus before/after com mesma contagem;
- métricas suficientes de Elementor/Gutenberg/legacy/shortcodes para fechar o Extraction Contract v1.

O profiler é temporário e deve ser removido no G-250.