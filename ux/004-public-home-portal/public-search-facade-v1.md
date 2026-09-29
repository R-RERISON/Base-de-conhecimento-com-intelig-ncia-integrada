# UX-004 — Public Search Facade v1.0.0

**Status:** FROZEN FOR H-030 HOMOLOGATION  
**Candidate:** `0.5.0-h030.1`

## Purpose

Expose the frozen SPEC-005 lexical engine to the Public Experience without reusing the administrative `edit_posts` authorization model.

This is an authorization facade, not a second Search engine.

## Engine reuse

The facade reuses:
- `Search_Query_Normalizer`;
- `Search_Projection_Repository`;
- `Lexical_Ranker`;
- `Search_Service::RESULT_VERSION`.

No second ranker or index is introduced.

## Visibility policy

Hard eligibility:
- post type must be the BDC canonical post type;
- only `publish` and `private` statuses can ever be considered;
- password-protected posts are rejected.

Published:
- allowed when WordPress considers the post publicly viewable;
- authenticated users may also pass canonical `read_post` capability.

Private:
- requires canonical `read_post` capability.

Draft/pending/future are not eligible for the public facade.

A filter may tighten the final decision, but it cannot bypass the hard status/password guards.

## Public AJAX

Action:
`bdc_kb_public_search`

Registered for:
- authenticated AJAX;
- nopriv AJAX.

Guards:
- WordPress nonce;
- minimum 2 characters;
- maximum 160 characters;
- maximum result limit 20;
- default result limit 8;
- approximate 60 requests/minute rate bound;
- rate fingerprint uses HMAC of the remote address; raw address is not persisted;
- no query log.

## Preview

The administrative Public Experience preview uses the same facade.

When preview mode is requested by an administrator, result URLs stay inside the candidate Article Reader. Public/non-preview responses use canonical post permalinks.

## Acceptance

H-030 must prove:
- anonymous facade results are publish/public only;
- status/password guards are present;
- AJAX auth+nopriv hooks are registered;
- Search/Golden regression remains PASS;
- no second ranker;
- no editorial writes;
- candidate Home works with ASI manually inactive.
