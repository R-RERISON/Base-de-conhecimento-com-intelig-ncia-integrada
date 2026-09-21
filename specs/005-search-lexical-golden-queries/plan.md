# Plano — SPEC-005 Search Lexical e Golden Queries

## Estratégia

Evoluir em evidência crescente, sem criar infraestrutura antes do baseline.

### Fase A — Descoberta
1. congelar baseline da `main`;
2. perfilar busca nativa `WP_Query s`;
3. comparar resultados com conteúdo semântico do Content Extractor;
4. identificar consultas reais;
5. classificar gaps;
6. decidir superfície inicial.

### Fase B — Golden v1
1. selecionar consultas representativas;
2. validar expected post humano;
3. definir max rank e severity;
4. versionar/hash;
5. criar runner puramente determinístico.

### Fase C — Engine lexical
**AUTORIZADA após R-500 + R-510 + G-520 PASS:**
1. Query Normalizer;
2. Search Document;
3. Retrieval;
4. Ranker;
5. Search Result;
6. fallback state;
7. integração UI mínima.

### Fase D — Corpus/ambiente
1. full corpus;
2. determinismo;
3. benchmark;
4. Golden gate;
5. segurança;
6. human acceptance.

### Fase E — RC
1. regression;
2. lifecycle;
3. build determinístico;
4. manifest/checksum;
5. PR.

## Ordem de decisão de persistência

`WP_Query nativo -> hooks mínimos -> projection post-level -> FULLTEXT`.

Não criar item index, queue ou analytics nesta SPEC.

## Critério de parada

Se a busca nativa atingir Golden/latência/cobertura aceitáveis, não construir engine própria. Se falhar, registrar exatamente a lacuna antes de aumentar complexidade.
