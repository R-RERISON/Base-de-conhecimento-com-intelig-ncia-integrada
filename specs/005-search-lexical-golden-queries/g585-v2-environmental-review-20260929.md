# G-585 v2 — Environmental Review 2026-09-29

**Evidence:** `bdc-kb-spec005-g585-independence-20260929-084700.json`  
**Status:** FAIL CONTROLADO / DEPENDENCY FOUND  
**G-585:** OPEN

## Ambiente
- WordPress 6.9.4
- PHP 8.5.10
- Plugin 0.5.1-rc.13
- MariaDB 12.2.2
- schema 2.0.0

## Resultado do gate
- T585 static runtime dependency zero: FAIL
- T585.1 surface dependency zero: FAIL
- T586 legacy inactive: PASS
- T587 Search + Golden sem legacy: NOT RUN
- T588 rebuild sem legacy: NOT RUN
- T589 lifecycle/rollback sem legacy: NOT RUN
- T589.2 G-585: FAIL
- cutover_authorized: false

## Blocker 1 — símbolo legacy carregado por plugin externo
O ASI está desativado, mas o processo WordPress carregou:
- classe `BDC_KX_ASI_Adapter`
- source scope: `plugin`
- source path: `bdc-knowledge-explorer/includes/class-bdc-kx-asi-adapter.php`

Isso prova dependência/resíduo runtime externo ao plugin BDC principal. O runner corretamente bloqueou antes de rebuild/Golden.

Disposition:
**não alterar o detector e não criar waiver**. Identificar o plugin `bdc-knowledge-explorer`, seu consumidor e remover/desacoplar o adapter ASI no owner correto antes de reexecutar G-585.

## Blocker 2 — Home ainda contém marcador ASI
Página configurada como Home:
- post ID: `41395`
- marcador detectado: `legacy_prefix`

Não há shortcode legacy registrado, mas o conteúdo da Home ainda contém referência textual/estrutural compatível com prefixo ASI.

Disposition:
antes de qualquer mutação, inventariar exatamente onde o marcador aparece no post 41395 e classificar se é:
1. dependência executável;
2. shortcode legado não registrado;
3. HTML/JS/CSS/snippet legado;
4. texto inerte/false positive.

Nenhuma edição automática é autorizada por G-585.

## Segurança
- automatic_legacy_deactivation=false
- legacy_data_cleanup=false
- external_network=false
- query_logging=false
- editorial_write=false
- errors=[]
- throwables=[]

## Próximo passo
1. auditar `bdc-knowledge-explorer/includes/class-bdc-kx-asi-adapter.php` no repositório/owner correspondente;
2. inspecionar conteúdo real da Home post 41395;
3. remover/desacoplar somente dependências comprovadas no owner correto e com rollback;
4. reexecutar o mesmo pacote G-585 se o runtime BDC não mudar;
5. somente após T585/T585.1 PASS executar Search/Golden/rebuild/lifecycle.

## Critério
O próximo JSON deve apresentar:
- loaded_symbols=[];
- loaded_hooks=[];
- registered_legacy_shortcodes=[];
- front_page_legacy_markers=[];
- T585=true;
- T585.1=true;
- T586=true.

Só então T587–T589 serão avaliados.
