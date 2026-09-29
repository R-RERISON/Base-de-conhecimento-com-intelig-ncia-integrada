# P-620 — Plugin Check Disposition

**Data:** 2026-09-29  
**Status:** PASS — INVENTORY / DISPOSITION COMPLETE  
**Importante:** isto NÃO significa Plugin Check clean.

## Ferramenta

- WordPress official Plugin Check action: `wordpress/plugin-check-action@v1`;
- WordPress: latest;
- stable checks only;
- no global ignore-codes;
- no ignore-errors;
- no ignore-warnings;
- canonical distribution slug: `bdc-knowledge-base`.

## Execução inicial

Run: `36582124130`  
Artifact: `11040800303`

Resultado:
- 743 findings;
- 449 errors;
- 294 warnings;
- 60 files;
- 394 findings eram `WordPress.WP.I18n.TextDomainMismatch`.

Root cause:
- source folder = `base-conhecimento-inteligencia-integrada`;
- canonical Text Domain já era `bdc-knowledge-base`;
- Plugin Check inferiu slug a partir do diretório de desenvolvimento.

Decisão:
- não reescrever centenas de chamadas i18n;
- canonical production/distribution slug = `bdc-knowledge-base`;
- P-650 fará o ZIP/root production usar esse slug.

Readme blockers também foram corrigidos:
- Tested up to 7.1;
- readme em inglês;
- tags permitidas;
- short description reduzida.

## Reexecução após correção de identidade/readme

Run: `36583019202`  
Artifact: `11041080897`

Resultado:
- 343 findings;
- 52 errors;
- 291 warnings;
- 58 files.

Eliminados:
- 394 `TextDomainMismatch`;
- header text-domain mismatch;
- outdated Tested up to;
- non-official readme language;
- invalid tags;
- oversized short description.

## Findings remanescentes por disposição

### P-640 / P-650 — Engineering surface

**191 findings**
- 21 errors;
- 170 warnings.

Principalmente:
- runners;
- smoke tests;
- diagnostics;
- preflight;
- acceptance harnesses;
- canary/executor/regression classes.

Interpretação:
esses arquivos não devem automaticamente compor a superfície production final. P-640 define module/runtime ownership; P-650 define production allowlist e package pruning.

### P-660 / production remediation

**150 findings**
- 30 errors;
- 120 warnings.

Categorias principais:
- input unslash/sanitization;
- nonce verification;
- prepared SQL;
- direct DB/caching;
- output escaping;
- `suppress_filters=true`;
- development `error_log`;
- `strip_tags()`;
- slow meta query signals.

Regra:
nenhum finding de segurança do código production será silenciado por ignore global. Cada caso será corrigido ou receberá waiver técnico específico com evidência.

### Distribution waiver — Update URI

**1 error**
- `plugin_updater_detected`.

Disposition:
**EXPECTED_FOR_PRIVATE_DISTRIBUTION**.

P-600 exige Update URI explícito para distribuição privada/premium. A restrição do Plugin Check é destinada ao modelo WordPress.org. O BDC não está sendo preparado neste momento como plugin hospedado no diretório WordPress.org.

Isto não é um ignore genérico: é uma decisão de distribuição documentada.

### P-650 package pruning

**1 warning**
- `UPGRADE.md` unexpected markdown file.

O documento permanece no source/repositório por P-600. P-650 decide se ele entra ou não no ZIP production.

## Top remaining codes

- ValidatedSanitizedInput.InputNotSanitized: 105;
- ValidatedSanitizedInput.MissingUnslash: 44;
- NonceVerification.Missing: 40;
- NonceVerification.Recommended: 39;
- DirectDatabaseQuery.DirectQuery: 23;
- DirectDatabaseQuery.NoCaching: 23;
- PreparedSQL.NotPrepared: 21;
- EscapeOutput.OutputNotEscaped: 15;
- SuppressFilters_suppress_filters: 9;
- error_log: 5;
- DirectDB.UnescapedDBParameter: 4;
- slow meta query: 4;
- strip_tags: 3.

## P-620 closeout rule

P-620 está fechado porque:
- Plugin Check oficial executou;
- findings foram quantificados;
- blockers objetivos de identidade/readme foram corrigidos;
- nenhum ignore global foi utilizado;
- dívida de engineering surface foi encaminhada a P-640/P-650;
- dívida de security/runtime production foi encaminhada a P-660;
- Update URI possui disposição explícita;
- package warning possui owner explícito.

## O que P-620 NÃO declara

- plugin Plugin Check clean;
- WordPress.org submission ready;
- production ZIP ready;
- WPCS clean;
- security clean;
- ASI retirement autorizado.

A aceitação final de Plugin Check para o ZIP production permanece obrigatória em P-650/P-670.
