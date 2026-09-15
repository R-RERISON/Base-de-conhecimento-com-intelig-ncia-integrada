# Current State — SPEC-004 Content Extractor e Knowledge Document

## Baseline

- baseline de entrada: `0.3.0-rc.1`;
- SPEC-001 Summary: concluída;
- SPEC-002 Classificação: concluída;
- SPEC-003 Review & Governança: concluída;
- R-200: **PASS**;
- R-210: **PASS**;
- G-220: **IMPLEMENTED / LOCAL PASS / ENV SMOKE PENDING**;
- build atual: `0.4.0-dev.1`.

Contratos ativos:

- `extraction-contract-v1.md` — v1.0.0;
- `extraction-contract-v1.1.md` — amendment v1.1.0;
- `elementor-normalization-contract-v1.md`;
- `production-rollout-contract-v1.md`.

## Direção editorial

A descoberta ambiental mostrou forte legado histórico, mas a direção operacional foi esclarecida:

- Elementor é o editor padrão atual da equipe;
- Legacy HTML/Gutenberg/plain precisam continuar sendo compreendidos pelo extractor;
- o Knowledge Document deve continuar independente do editor;
- a convergência editorial para Elementor será migration explícita, versionada e reversível.

Portanto, **79,74% de legacy HTML descreve o passado do corpus, não a arquitetura editorial desejada**.

## Evidência R-200

Corpus real: 622 posts.

Distribuição estatística exclusiva:

- Legacy HTML: 496 (79,74%);
- Elementor: 74;
- Plain text: 31;
- Shortcode/plain: 10;
- Mixed Elementor + blocks: 6;
- Gutenberg: 3;
- Empty: 2.

Elementor:

- presente em 80;
- 39 JSON válidos;
- 41 inválidos;
- widgets confirmados: `text-editor` e `shortcode`.

Gutenberg:

- 9 posts com blocos;
- `core/freeform`, `core/heading`, `core/paragraph`, `core/list`, `core/table`.

Tamanhos máximos observados:

- `post_content`: 156.636 B;
- `_elementor_data`: 110.029 B.

Segurança do profiler:

- fingerprint editorial before/after idêntico;
- 0 posts alterados;
- corpus 622 → 622.

## Runtime G-220 implementado

O build `0.4.0-dev.1` adiciona:

- `Content_Normalizer`;
- `Shortcode_Inspector`;
- `Legacy_HTML_Adapter`;
- `Content_Source`;
- `Elementor_Adapter`;
- `Gutenberg_Adapter`;
- `Content_Extractor`.

O extractor é um serviço puro chamado explicitamente por futuros consumidores. Não possui hook próprio, cron, job, tabela ou writer.

### Comportamentos

- Elementor válido: traversal allowlisted;
- Elementor inválido: warning + fallback seguro;
- Gutenberg: parsing estrutural sem render dinâmico;
- Legacy HTML: boundaries/facts preservados;
- plain text: normalização determinística;
- shortcodes: reconhecimento controlado sem callback;
- hard limit: sem truncamento silencioso;
- saída inclui hashes da matéria-prima e readiness Elementor.

### Portabilidade

`DOMDocument` é opcional. Quando indisponível, Legacy adapter usa fallback estrutural determinístico e emite `HTML_DOM_UNAVAILABLE`.

Isso foi necessário porque o PHP CLI do runner local não possui `ext-dom`. O plugin não ganha uma dependência de instalação desnecessária por causa disso.

## Readiness para Elementor

A saída intermediária classifica cada post como:

- `native`;
- `projectable`;
- `review_required`;
- `blocked`.

Essa classificação **não é writer**. Ela serve de base para o futuro Projection Plan e migration controlada.

Elementor válido continua `native` mesmo que o corpus geral seja historicamente legado.

## Validação local G-220

`g220-local-validation.md` registra:

- lint dos arquivos novos: PASS;
- 14/14 unit tests: PASS;
- zero-write após cada cenário;
- repetibilidade: PASS;
- casos Legacy/Elementor/Gutenberg/plain/shortcode/empty/oversize cobertos.

## Profiler temporário

O source do profiler ainda existe até G-250, mas o build atual define:

`BDC_KB_SPEC004_PROFILE_BUILD=false`

Consequências:

- profiler não é carregado;
- submenu/runner não são registrados;
- não participa do runtime `0.4.0-dev.1`.

## Produção — novo requisito explícito

Homologação deriva de cópia da produção, mas promotion não deve assumir ambientes eternamente idênticos.

G-245 introduz production readiness com:

- target preflight;
- matriz WordPress/PHP/Elementor/plugins;
- versões independentes de runtime/schema/projection/migration;
- dry-run;
- journal/rollback;
- stale-source guard;
- canário;
- lotes retomáveis;
- runbook.

### Regra de activation/update

Activation/update pode futuramente executar apenas migrations pequenas e idempotentes de estado/schema **próprio do plugin**, se houver necessidade.

Activation/update nunca:

- converte posts para Elementor;
- percorre corpus inteiro para writer;
- executa IA;
- regrava `_elementor_data`/`post_content`.

## Elementor writer futuro

O writer ficará atrás de `Elementor_Gateway` version-gated e deverá usar o lifecycle oficial do Document do Elementor quando homologado.

Gravação direta dispersa em metas internas do Elementor é proibida por padrão.

Antes de writer são obrigatórios G-230, G-240 e os controles do G-245.

## Risco principal atual

Os maiores riscos agora estão explicitamente separados:

1. **perda semântica na leitura** — tratado pelo extractor e G-240;
2. **corrupção editorial na conversão para Elementor** — tratado por projection plan, canário, journal/rollback e gateway;
3. **diferença homologação/produção** — tratada por preflight e compatibility matrix;
4. **migration concorrente com edição humana** — tratada por source hash/`STALE_SOURCE`.

## Próximo passo

Gerar/validar o package `0.4.0-dev.1` e executar smoke read-only em homologação. Somente depois fechar G-220 ambiental e iniciar G-230.
