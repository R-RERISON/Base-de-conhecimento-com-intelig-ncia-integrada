# Baseline e Definition of Ready — SPEC-002

## Baseline funcional

A referência de regressão é o plugin `0.1.0-rc.1`, homologado ao final da SPEC-001.

Invariantes herdadas:

- Summary continua funcional;
- WordPress/Elementor continua fonte editorial;
- nenhum write em `_elementor_data`, `post_content`, `post_title`;
- segurança por objeto e nonce não regride;
- UI permanece no shell nativo do wp-admin;
- activation/deactivation é não destrutiva.

## Baseline de dados conhecida

A SPEC-000 identificou Classificação como owner futuro dos conceitos listados em `data-model.md`, porém não autorizou primitive física nem migração.

## DoR mínimo para começar runtime

Todos devem estar PASS:

- [ ] JSON de profiling real recebido e versionado.
- [ ] Cobertura e cardinalidade conhecidas para todos os stores candidatos.
- [ ] Representação e multi-value conhecidas.
- [ ] Colisões de normalização avaliadas.
- [ ] Audiência GRE↔KB2Ops comparada.
- [ ] `service`↔`affected_service` analisados sem merge por suposição.
- [ ] `technologies`↔`systems_involved` analisados sem merge por suposição.
- [ ] Primeiro slice limitado a no máximo quatro conceitos.
- [ ] Taxonomy vs Meta decidido com justificativa por conceito.
- [ ] Compatibilidade/rollback definidos.
- [ ] Matriz de Mutação e Matriz de Evidência fechadas.
- [ ] Nenhuma tabela própria necessária; caso contrário, nova decisão arquitetural obrigatória.

Enquanto algum item estiver pendente, estado = **NOT_READY para runtime**.
