# SPEC-005 — BDC Word Cloud Contract v1.0.0

**Status:** FROZEN FOR HOMOLOGATION  
**Gate context:** P-580A / UX-004 H-023  
**Version:** `word-cloud-v1.0.0`

## 1. Objective

Rebuild the mandatory Word Cloud capability inside the Base de Conhecimento plugin with zero ASI runtime/storage dependency.

The module is derived/rebuildable. WordPress + BDC semantic content remain the source of truth.

## 2. Storage

No new table in v1.

BDC-owned state:
- `bdc_kb_word_cloud_settings`;
- `bdc_kb_word_cloud_allowlist`;
- `bdc_kb_word_cloud_blocklist`;
- `bdc_kb_word_cloud_snapshot`;
- `bdc_kb_word_cloud_state`;
- `bdc_kb_word_cloud_history`;
- transient lock `bdc_kb_word_cloud_generation_lock`.

All Options are autoload=false when first created.

Uninstall/deactivation are non-destructive by default.
Deactivation clears only the Word Cloud cron hook.

## 3. Current sources

Available in v1:
- published post titles;
- semantic headings/fragments from Content Extractor;
- semantic body fragments when enabled;
- category/post_tag;
- canonical BDC classification taxonomies;
- allowlist;
- blocklist/stopwords.

Explicitly pending:
- search events;
- interactions/click correlation;
- governed vocabulary.

Pending signals are reported in health/state and are not simulated.

## 4. Generator

Generation is explicit/admin or scheduled.

Public requests never trigger a rebuild.

Boundaries:
- max posts configurable, bounded 25..1000;
- soft runtime budget 8s;
- snapshot may be marked partial when budget is reached;
- generation lock prevents concurrent runs;
- snapshot and last good state remain readable during generation/failure.

Default schedule:
- hourly WordPress cron;
- generation skipped while fresh.

## 5. Quality

Deterministic canonicalization:
- strip markup;
- lowercase;
- accent normalization;
- punctuation/whitespace collapse.

Noise guards:
- minimum token length;
- numeric-only exclusion;
- long hash/fragment exclusion;
- blocklist.

Maturity:
- `candidate`;
- `observed`;
- `mature`;
- `promoted`.

Public rendering only accepts:
- observed;
- mature;
- promoted.

Allowlist produces governed promotion; blocklist is authoritative exclusion.

## 6. Scoring sources

Initial relative signals:
- title: strong;
- heading: strong;
- taxonomy: strong + bounded usage count;
- body/content: low;
- allowlist: explicit boost.

These are Word Cloud generation weights, not Search ranking weights.
Changing them does not change `lexical-ranker-v1.0.0`.

## 7. Snapshot

Snapshot contains:
- snapshot contract version;
- generated timestamp;
- ranked terms;
- canonical form;
- score;
- count;
- source labels;
- maturity;
- public_allowed;
- display weight 1..6;
- quality summary;
- source availability.

Snapshot is a derived cache, never editorial truth.

## 8. Health/history

Health reports:
- status: not_built|ready|partial|failed;
- freshness;
- age;
- term counts;
- lock status;
- next cron;
- source availability.

Run history is bounded to the most recent 20 reports.

## 9. Admin operations

Page:
**Base de Conhecimento → Nuvem de Conhecimento**

Requires `manage_options`.

Mutations require POST + nonce.

Operations:
- generate snapshot now;
- enable/disable scheduling;
- bounded corpus size;
- include/exclude semantic body terms;
- max public terms;
- allowlist;
- blocklist;
- snapshot/health preview.

## 10. Public UX

The Public Home consumes the BDC snapshot only.

If snapshot is absent:
- Search/Home remain functional;
- no legacy Word Cloud fallback is used;
- no generation occurs in the request.

Clicking a term routes to the BDC candidate Search using the term as the query.

Visual presentation may remain compact/search-first; functional Word Cloud does not require a traditional variable-font cloud.

## 11. Safety

Prohibited:
- ASI table/option/runtime reads;
- network calls;
- Search query logging;
- post/meta/taxonomy editorial writes;
- public-request rebuild;
- second Search ranker.

## 12. Acceptance

Local:
- contract checks PASS;
- PHP lint PASS;
- active requires resolved;
- deterministic package;
- no ASI markers in Word Cloud runtime source.

Environmental:
- manual generation PASS;
- non-empty public snapshot;
- health ready or documented partial;
- click-to-search PASS;
- cron scheduled;
- deactivation removes cron but retains state;
- Home remains functional with ASI disabled;
- no editorial fingerprint change.

Telemetry/vocabulary source parity is not required for this v1 gate; those sources remain PLANNED and visible as pending.
