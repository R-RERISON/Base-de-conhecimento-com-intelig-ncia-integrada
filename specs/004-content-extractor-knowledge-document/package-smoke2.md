# Package — SPEC-004 `0.4.0-smoke.2`

## Objetivo

Validar ambientalmente o G-230 — Knowledge Document canônico — sobre a cópia de produção em homologação.

## SHA-256

`1466cd4fcd18120d0b2405bf04ec629230f23c2a2e759869a8123c45cedaf204`

## Conteúdo temporário

- Content Extractor permanente da SPEC-004;
- `Canonical_JSON` permanente;
- `Knowledge_Document` permanente;
- runner temporário `Knowledge_Document_Smoke`;
- profiler R-200 ausente do package;
- runner G-220 ausente do package.

## Segurança do runner

- somente `manage_options`;
- POST + nonce;
- nenhuma persistência;
- não exporta conteúdo editorial;
- não exporta post IDs;
- não exporta títulos/URLs;
- constrói documentos em memória;
- executa duas passagens completas;
- compara `source_hash`, `document_hash` e SHA-256 do JSON canônico;
- mede fingerprint editorial antes/depois.

## Validação local do artefato

- arquivos no ZIP: 25;
- PHP lint após extração: **21/21 PASS**;
- JavaScript syntax: **PASS**;
- ZIP integrity: **PASS**;
- source parity do package: **25/25 PASS**;
- G-220 unit tests sobre package extraído: **14/14 PASS**;
- G-230 unit tests sobre package extraído: **10/10 PASS**.

## Gate ambiental esperado

Exigir:

- `editorial_fingerprint_equal=true`;
- `changed_posts_during_run=0`;
- corpus count before = after;
- `first_pass_documents=622`;
- `second_pass_documents=622`;
- erros primeira/segunda passagem = 0;
- throwables primeira/segunda passagem = 0;
- `hash_mismatches=0`;
- `canonical_json_mismatches=0`.

O runner deve ser removido antes do RC.
