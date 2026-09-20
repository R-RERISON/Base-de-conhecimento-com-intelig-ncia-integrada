# SPEC-005 — BDC Word Cloud Contract v1.1.0

**Status:** FROZEN FOR HOMOLOGATION  
**Gate context:** P-580A / UX-004 H-023  
**Version:** `word-cloud-v1.1.0`  
**Quality profile:** `semantic-balanced-v2`

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

Available:
- published post titles;
- semantic headings/fragments from Content Extractor;
- semantic body fragments as explicit opt-in;
- category/post_tag;
- canonical BDC classification taxonomies;
- governed allowlist;
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
- snapshot and last good state remain retained during generation/failure.

Default schedule:
- hourly WordPress cron;
- generation skipped while fresh.

## 5. Quality

Deterministic canonicalization:
- strip markup;
- lowercase;
- accent normalization;
- punctuation/whitespace collapse.

Quality profile `semantic-balanced-v2`:
- semantic body terms are disabled by default;
- PT-BR stopwords are applied before scoring;
- corporate/generic noise is blocked through a governed blocklist;
- title/heading phrases are preserved as candidates, including bounded multi-word expressions and product/version forms such as `Windows 11`;
- default allowlist is a governed label/boost source only when the term is observed in corpus material;
- document/structural evidence is tracked;
- content-only frequency never makes a term public.

Maturity:
- `candidate`;
- `observed`;
- `mature`;
- `promoted`.

Public rendering only accepts:
- observed;
- mature;
- promoted.

Allowlist produces governed promotion only after observation.
Blocklist remains authoritative for the current content-derived profile.

## 6. Scoring sources

Relative signals:
- title phrase: strong;
- title token: strong;
- heading phrase: strong;
- heading token: strong;
- taxonomy: strong + bounded usage count;
- body/content: low and opt-in;
- observed allowlist hit: governed boost.

These are Word Cloud generation weights, not Search ranking weights.
Changing them does not change `lexical-ranker-v1.0.0`.

## 7. Snapshot

Snapshot contains:
- snapshot contract version;
- quality profile version;
- generated timestamp;
- ranked terms;
- canonical form;
- score;
- count;
- source labels;
- document count;
- maturity;
- quality reason;
- public_allowed;
- display weight 1..6;
- quality summary;
- source availability.

A public request ignores snapshots created by an older snapshot contract or quality profile.

Snapshot is a derived cache, never editorial truth.

## 8. Migration from v1.0

When `p580wc.2` encounters v1.0 state:
- settings receive the new quality profile;
- body terms are disabled by default;
- new governed allowlist is merged with current entries;
- new blocklist is merged with current entries;
- previous snapshot is retained physically but marked stale by contract/profile;
- previous snapshot is not used by the Public Home;
- manual/cron regeneration produces a current v1.1 snapshot.

No editorial data is changed.

## 9. Health/history

Health reports:
- status: not_built|ready|partial|failed|stale_quality_profile;
- freshness;
- snapshot_current;
- quality_profile;
- age;
- term counts;
- lock status;
- next cron;
- source availability.

Run history is bounded to the most recent 20 reports.

## 10. Admin operations

Page:
**Base de Conhecimento → Nuvem de Conhecimento**

Requires `manage_options`.

Mutations require POST + nonce.

Operations:
- generate snapshot now;
- enable/disable scheduling;
- bounded corpus size;
- optional semantic body terms;
- max public terms;
- allowlist;
- blocklist;
- snapshot/health preview.

The admin surface warns when an older snapshot must be regenerated.

## 11. Public UX

The Public Home consumes the BDC snapshot only.

If snapshot is absent or stale:
- Search/Home remain functional;
- no legacy Word Cloud fallback is used;
- no generation occurs in the request.

Clicking a term routes to the BDC candidate Search using the term as the query.

Until Search Events/Interactions exist, the public label is **Assuntos em destaque**, not “mais consultados”. A consumption-based label is authorized only when real telemetry exists.

## 12. Safety

Prohibited:
- ASI table/option/runtime reads;
- network calls;
- Search query logging;
- post/meta/taxonomy editorial writes;
- public-request rebuild;
- second Search ranker.

## 13. Acceptance

Local:
- contract checks PASS;
- quality harness PASS;
- PHP lint PASS;
- active requires resolved;
- deterministic package;
- no ASI markers in Word Cloud runtime source.

Environmental:
- manual generation PASS;
- non-empty public snapshot;
- obvious generic/noise terms from the v1 failure are absent from the public top set;
- meaningful title/heading/taxonomy concepts are visible;
- health ready or documented partial;
- click-to-search PASS;
- cron scheduled;
- deactivation removes cron but retains state;
- Home remains functional with ASI disabled;
- no editorial fingerprint change.

Telemetry/vocabulary source parity is not required for this v1.1 quality gate; those sources remain PLANNED and visible as pending.
