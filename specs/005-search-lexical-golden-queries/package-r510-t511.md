# Package — SPEC-005 R-510/T511

## 0.5.0-r510-t511.1

**SUPERSEDED / NÃO INSTALAR.**

Motivo: apesar de read-only, o runner consultava `asi_golden_queries` em runtime de diagnóstico. Após ADR-005-001, isso viola o princípio de zero dependência do ASI.

O T510 já capturou e versionou as 6 Golden candidates necessárias; não há razão para continuar lendo storage ASI.

## Build substituto

`0.5.0-r510-t511.2`

Características:
- Golden Candidate Seed próprio;
- zero tabela ASI;
- zero option ASI;
- zero classe/função/hook ASI;
- três baselines WP_Query mantidos;
- read-only;
- sem persistência;
- fingerprint editorial.

O pacote/checksum da v2 deve ser usado para homologação.
