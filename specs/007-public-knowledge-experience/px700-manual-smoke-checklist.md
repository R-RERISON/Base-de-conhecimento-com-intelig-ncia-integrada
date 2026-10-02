# PX-700 — Manual Smoke Checklist

**Status:** REQUIRED BEFORE FORMAL PX-700 CLOSEOUT  
**Date:** 2026-10-02

Automated PX-700 inventory is already TECHNICAL_PASS.

Complete only these three human checks in homologation.

## 1. Home preview

Open:
- Base de Conhecimento -> Prévia Pública -> Home Search-first.

Confirm:
- header/brand renders;
- five quick links are visible/usable;
- Entra profile area renders;
- primary Search is visible and usable;
- category/latest/popular exploration renders;
- no raw shortcode appears;
- no broken layout/clipping.

Result:
- [ ] PASS
- [ ] FAIL

## 2. Article Reader preview

Open representative articles from the Preview screen for the available source kinds.

At minimum verify:
- one legacy_html;
- one plain_text;
- one Elementor;
- one mixed;
- one Gutenberg/Core Blocks.

Confirm:
- title/metadata render;
- article content remains readable;
- no duplicated legacy title/chrome;
- Helpful Tips appear when present;
- Executive Summary rail renders when data exists;
- GAC/WPUI content-path behavior is not visibly broken;
- no overlap/clipping;
- Search remains available in article header.

Result:
- [ ] PASS
- [ ] FAIL

## 3. Entra profile

While authenticated in homologation, confirm on Home/Reader preview:
- profile/menu renders;
- department/job title appear when available;
- logout works as expected;
- admin link behavior remains appropriate for the current user.

Result:
- [ ] PASS
- [ ] FAIL

## Acceptance

PX-700 can close only when all three checks are PASS.

This checklist does not authorize public cutover.
