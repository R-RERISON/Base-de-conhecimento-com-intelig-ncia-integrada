# Checklist de Requisitos — SPEC-002

## S001 Profiling

- [x] Conceitos históricos identificados.
- [x] Stores históricos identificados.
- [x] Profiling desenhado como read-only.
- [x] Relatório minimiza dados e não lê conteúdo editorial.
- [x] Nenhuma configuração externa (`wp-config.php`) necessária.
- [ ] Execução no ambiente alvo.
- [ ] JSON versionado.
- [ ] C-001 PASS.

## Bloqueios de implementação

- [ ] Taxonomy vs Meta decidido.
- [ ] Cardinalidade decidida.
- [ ] Compatibilidade decidida.
- [ ] Mutação/segurança fechadas.
- [ ] Evidência/rollback fechados.

Enquanto estes bloqueios não forem resolvidos, **não implementar runtime permanente**.
