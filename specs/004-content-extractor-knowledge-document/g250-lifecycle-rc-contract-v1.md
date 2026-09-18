# G-250 — Lifecycle / RC Contract v1

**Status:** RC1 BUILD / HOMOLOGAÇÃO PENDENTE  
**Build:** `0.4.0-spec004-rc1`

## Objetivo

Comprovar o ciclo de vida do plugin e os principais read paths antes do artefato final da SPEC-004.

## RC1

O RC1:
- desliga o runner temporário T100E-E6;
- mantém T100D OFF;
- mantém Elementor writer OFF;
- mantém Production Preflight oculto;
- habilita somente o runner oculto G-250.

Rota oculta:
`/wp-admin/admin.php?page=bdc-kb-g250-lifecycle`

## Sequência ambiental obrigatória

1. instalar RC1 sobre o build atual — comprova upgrade;
2. abrir a Workspace e confirmar carregamento normal;
3. desativar o plugin;
4. reativar o plugin;
5. confirmar que a Workspace continua carregando;
6. instalar o build anteriormente validado `0.4.0-g245-ux003.1` — rollback/downgrade controlado;
7. confirmar que a Workspace continua carregando e o post 358 permanece em Blocos do WordPress;
8. reinstalar RC1;
9. abrir a rota oculta G-250;
10. marcar as duas confirmações humanas;
11. executar e baixar o JSON.

## Checks automatizados G-250

No post 358:
- SPEC-001 Summary read;
- SPEC-002 Classification read;
- SPEC-003 Review read;
- SPEC-004 Content Extractor read;
- SPEC-004 Knowledge Document 2.1.0 read;
- Workspace Context read;
- source kind = gutenberg;
- operational = no_action_required;
- journal latest = applied;
- lock = free;
- Activity Registry íntegro;
- Preflight ausente do menu visível;
- T100D OFF;
- Elementor writer OFF;
- E6 runner OFF;
- fingerprint antes/depois inalterado.

## Segurança

O runner G-250 não:
- escreve post_content;
- escreve _elementor_data;
- escreve Summary/Classificação/Review;
- escreve journal;
- adquire lock;
- chama rede;
- executa shortcodes;
- renderiza blocks dinâmicos.

## PASS

PASS exige:
- confirmações humanas de lifecycle e rollback/downgrade;
- todos os checks automatizados PASS;
- fingerprint do post 358 inalterado;
- `gate_result.g250_lifecycle_rc_pass=true`.

## Depois do PASS

Gerar RC final limpo:
- G-250 runner OFF;
- E6 runner OFF;
- sem runners temporários no artefato;
- manifest + checksum;
- static regression runner PASS;
- build reproduzível;
- E7 CLOSED;
- G-245 CLOSED;
- SPEC-004 pronta para encerramento.
