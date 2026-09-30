# P-650 / P-660 — Final Local Gates Runbook

**Artefato congelado:** `base-conhecimento-inteligencia-integrada-0.6.0-dev-p650.3.zip`  
**SHA-256:** `985091a289f11c0ae449e6f93e2f4090ddd3790762df42cff4a8a97fc775c231`

## Regra

Não reconstruir o ZIP durante estes gates.

Se o SHA divergir, a execução deve falhar e o resultado não é admissível.

## 1. Plugin Check oficial

Pré-requisitos locais:

- WordPress funcional;
- WP-CLI;
- plugin oficial Plugin Check instalado;
- exatamente o ZIP p650.3.

Executar:

```bash
python tools/homologation/spec006/run-p650-p660-plugin-check-local.py \
  --wp-path=/caminho/do/wordpress \
  --zip=/caminho/base-conhecimento-inteligencia-integrada-0.6.0-dev-p650.3.zip
```

O runner:

- valida o SHA do p650.3;
- usa o ZIP diretamente como target;
- executa `--format=strict-json`;
- executa `--mode=update`;
- carrega `plugin-check/cli.php` para runtime checks;
- grava `evidence/spec006-p650-p660-plugin-check-current.json`;
- não usa GitHub Actions.

### Resultado

- PASS: seguir para rollback;
- FAIL: versionar o JSON, fazer disposition por finding e corrigir somente problemas concretos.

Não criar waiver global.

## 2. Rollback

Usar como pacote anterior um ZIP previamente homologado, preferencialmente p650.2, e como candidato exatamente p650.3.

Executar:

```bash
python tools/homologation/spec006/run-p650-rollback-local.py \
  --wp-path=/caminho/do/wordpress \
  --previous-zip=/caminho/base-conhecimento-inteligencia-integrada-0.6.0-dev-p650.2.zip \
  --candidate-zip=/caminho/base-conhecimento-inteligencia-integrada-0.6.0-dev-p650.3.zip \
  --output=evidence/spec006-p650-rollback-current.json
```

Fluxo:

1. fingerprint inicial;
2. reinstala pacote anterior;
3. confirma plugin ativo;
4. fingerprint após rollback;
5. reinstala p650.3;
6. confirma plugin ativo;
7. fingerprint final.

Fingerprints não exportam conteúdo. Comparam:

- quantidade de posts;
- SHA-256 determinístico de `post_content`;
- contagens das metas BDC relevantes;
- SHA-256 determinístico dos valores dessas metas.

### PASS

PASS exige:

- pacote anterior instalável/ativo;
- p650.3 restaurável/ativo;
- contagens iguais;
- hash editorial igual;
- hash das metas BDC igual.

## 3. Fechamento

Somente após ambos os JSONs:

- P650 rollback PASS;
- Plugin Check analisado;
- P660 disposition final;
- P640 local admissível resolvido.

Então:

1. fechar P650;
2. fechar P660;
3. atualizar PROD-004/005/006 apenas se a evidência suportar;
4. executar P670 Premium Foundation Acceptance;
5. não autorizar cutover/retirement/1.0.0.


## 4. Executor único no Windows

Para executar a cadeia completa em uma workstation Windows com WordPress/WP-CLI:

```powershell
powershell -ExecutionPolicy Bypass -File tools/homologation/spec006/run-spec006-final-gates.ps1 `
  -WpPath "C:\caminho\wordpress" `
  -PreviousZip "C:\caminho\base-conhecimento-inteligencia-integrada-0.6.0-dev-p650.2.zip" `
  -CandidateZip "C:\caminho\base-conhecimento-inteligencia-integrada-0.6.0-dev-p650.3.zip"
```

Ordem executada:

1. valida Git/plugin tree;
2. exige tooling Composer já materializado em `vendor` por padrão;
3. P640 local completo, gerando `p640.2`;
4. P650 local package quality, preso ao SHA do p650.3;
5. P660 local security/privacy quality;
6. Plugin Check oficial no p650.3;
7. rollback p650.2 -> p650.3 com fingerprints;
8. gera `evidence/spec006-final-local-gates-summary-current.json`.

### Composer

O repositório atualmente não versiona `composer.lock`.

Por isso o executor **não** resolve dependências automaticamente por padrão.

Se for necessário bootstrap explícito:

```powershell
... -BootstrapComposerDependencies
```

Essa opção deve ser tratada como bootstrap de tooling e o lock gerado deve ser revisado antes de ser usado como base de reprodutibilidade futura.

### Proveniência

P650 não aceita mais apenas um JSON P640 com PASS. O `plugin_tree_sha` do P640 deve ser idêntico ao source atual do plugin.

Mudanças apenas em docs/tools não invalidam a evidência; mudanças no subtree do plugin invalidam.


## 5. P670 no mesmo executor

Após P640/P650/P660, Plugin Check e rollback, o executor também roda:

1. `tests/unit/spec006-p670-preflight-contract.php`;
2. `tools/homologation/spec006/validate-p670-preflight.py`;
3. se e somente se o preflight retornar `PASS_PRECONDITIONS`, executa `generate-p670-closeout-candidate.py`.

Outputs adicionais:

- `evidence/spec006-p670-preflight-current.json`;
- `evidence/spec006-p670-closeout-candidate-current.json`;
- `specs/006-premium-product-foundation/p670-closeout-candidate-current.md`.

O closeout candidate é uma proposta. O Master Ledger não é alterado automaticamente.

### Critério para revisão final

Somente abrir a revisão final do P670 quando:

- P670 preflight = `PASS_PRECONDITIONS`;
- closeout candidate = `READY_FOR_HUMAN_LEDGER_REVIEW`;
- Plugin Check e rollback estiverem PASS;
- o mesmo `plugin_tree_sha` for mantido;
- o artifact SHA continuar igual ao p650.3 congelado.
