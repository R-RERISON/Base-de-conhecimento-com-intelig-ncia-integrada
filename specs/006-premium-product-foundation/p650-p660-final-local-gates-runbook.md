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
