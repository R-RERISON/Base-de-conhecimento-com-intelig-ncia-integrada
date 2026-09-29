# G-590 RC7 / R-260A — Environmental Review

**Evidence:** `bdc-kb-spec005-g590-section-20260924-174920.json`  
**Environment:** WordPress 6.9.4 / PHP 8.5.10 / MariaDB 12.2.2  
**Product:** `0.5.1-rc.7`  
**Build:** `g590.7-4fd94fb372eb`

## Gate result

- T590-14 PASS
- T590-15 FAIL
- T590-16 PASS
- T590-17 PASS
- T590-18 PASS
- T590-19 FAIL / OPEN

T590-15 remains the only blocker.

## R-260A result

Deterministic candidates:
- total: 548;
- posts: 94;
- early: 255;
- later same label: 98;
- later same token: 116;
- body span >=2: 289.

Classification:
- `toc_signal`: 77;
- `body_signal`: 289;
- `uncertain`: 182.

The classes partition all 548 candidates.

Source-kind candidate posts:
- legacy_html: 71;
- Elementor: 20;
- Gutenberg: 1;
- mixed: 2.

## Search/deep-link baseline

Probe coverage:
- Elementor: present;
- Gutenberg: present;
- legacy_html: present;
- unprobed source kinds: none.

Section/deep-link probes:
- eligible: 12;
- section query failures: 0;
- deep-link failures: 0;
- visible text changes: 0.

T590-16 is therefore closed as PASS for the current heading-based Section runtime.

## Architectural finding

The current Anchor Manager injects ephemeral anchors only into unique rendered headings.

A Section derived from a paragraph could remain retrievable with `anchor_state=unresolved`, but it would not provide direct navigation.

Therefore R-260 cannot be closed by simply materializing virtual Search Sections.

## R-260A disposition

**PASS / DISCOVERY CLOSED.**

R-260B opens as shadow-only validation.

No Content Extractor/KD/Search runtime modification is authorized.

## R-260B questions

RC8 must measure:
- real-heading collisions;
- duplicate body-candidate labels;
- Section MAX=64 overflow;
- paragraph anchor-contract blockers.

Option C — dedicated structural projection shared by consumers — remains preferred but not yet frozen.
