# Riscos, Drifts e Dívidas — SPEC-000

> Estado após T096.

## Encerramento de risco da SPEC-000
A arquitetura documental foi revisada por WordPress, simplicidade, segurança, QA e produto. Não há blocker aberto para criação da SPEC-001 candidata.

## Riscos residuais por slices futuros
- B-001 / extração parcial e custom widgets -> Search/RAG.
- B-002 / profiling e cutover classificatório -> Classificação.
- B-003 / aliases, coexistência e single-writer -> cutover/removal.
- B-004 / query logging, identidade, retenção -> Analytics.
- B-005 / item identity, anchors, deep-link -> item-level Search.
- B-007 / stale, concorrência e durable queue -> async Search.
- SSRF/secrets/data egress/prompt injection -> IA/provider futuro.
- semantic/vector drift -> P3 futuro.
- benchmark/escala real -> Search e workloads intensivos.

## Risco aplicável à SPEC-001
B-006 foi fechado conceitualmente, mas sua implementação/fault tests continuam obrigatórios na SPEC-001. A decisão arquitetural não substitui evidência executável.

## Riscos de cutover
SPEC-001 reutilizará as três meta keys GRE. Em homologação isso reduz migration. Em produção, coexistência com writer legado exige B-003 e definição de single-writer/coexistência segura antes de retirada/uso concorrente não controlado.

## Recomendação T096
GO condicionado em T097 para **abrir a SPEC-001**, mantendo todos os riscos não aplicáveis vinculados aos seus slices futuros.
