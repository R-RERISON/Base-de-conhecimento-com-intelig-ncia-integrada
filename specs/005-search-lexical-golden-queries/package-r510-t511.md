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


## 0.5.0-r510-t511.2 — VALIDATED LOCAL

SHA-256: `f34cb1bdffb369efdfbdd886d86cd2798835b41829466da278436b002df7ffcb`.

Validação:
- 46 arquivos;
- 41 PHP;
- 41/41 PHP lint;
- 40/40 active requires;
- missing requires = 0;
- deterministic rebuild PASS;
- T510 OFF;
- T511 ON;
- legacy discovery ausente do ZIP;
- Golden Candidate Seed própria presente;
- static dependency scan: 0 referências técnicas `asi_golden_queries`, `asi4_*`, `ASI4_*`, `asi_*`;
- Elementor writer OFF.

**Este é o único pacote T511 autorizado para homologação.**
