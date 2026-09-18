# Checklist de Requisitos — SPEC-005

## Arquitetura
- [x] SPEC-004 CLOSED/main.
- [x] branch dedicada criada.
- [x] lexical precede semantic/vector.
- [x] ASI usado como referência de contrato, não código.
- [x] WordPress-first decision gate definido.
- [x] projection classificada como derivada/reconstruível.
- [ ] R-500 PASS.
- [ ] R-510 PASS.
- [ ] G-520 PASS antes de runtime.

## Golden
- [ ] suite real não vazia.
- [ ] expected posts revisados por humano.
- [ ] blocking/warning definidos.
- [ ] set_hash determinístico.
- [ ] algorithm/normalizer version.
- [ ] NOT_CONFIGURED/NOT_RUN/STALE explícitos.

## Segurança
- [x] Search read-only por default.
- [x] projection não autoriza acesso.
- [x] query logging fora de escopo.
- [ ] matriz final capability/surface fechada em G-520.
- [ ] SQL bounds testados quando engine existir.

## UX
- [x] Visual Contract v2 obrigatório.
- [ ] superfície inicial decidida em R-500.
- [ ] zero-result separado de erro.
- [ ] degraded/fallback visível.
- [ ] desktop/782/520.
- [ ] teclado/foco/ARIA.

## Fora de escopo protegido
- [x] IA.
- [x] embeddings/vectors.
- [x] telemetry detalhada.
- [x] durable queue.
- [x] item/deep-link sem evidência.
- [x] AUTH-UX-001.
- [x] remoção Elementor.
