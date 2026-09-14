# Checklist — SPEC-XXX

## Baseline
- [ ] Estado atual documentado.
- [ ] Projetos de referência relacionados lidos.
- [ ] Dados e dependências mapeados.

## WordPress-first
- [ ] APIs nativas avaliadas.
- [ ] Tabela própria justificada, se existir.
- [ ] REST/AJAX/JS justificados, se existirem.

## Princípio de negação
- [ ] Alternativa mais simples descrita.
- [ ] Camadas removíveis identificadas.
- [ ] Complexidade residual justificada.

## Segurança
- [ ] Capability.
- [ ] Nonce.
- [ ] Sanitização/validação.
- [ ] Escaping.
- [ ] Método HTTP.
- [ ] Privacidade.

## Dados
- [ ] Fonte da verdade identificada.
- [ ] Dados derivados são reconstruíveis.
- [ ] Migração é idempotente.
- [ ] Rollback existe.

## UI/UX
- [ ] Design System respeitado.
- [ ] Responsividade.
- [ ] Foco/teclado.
- [ ] Estados de erro/vazio/carregamento/sucesso.
- [ ] Nenhuma sidebar paralela ao wp-admin.

## Elementor
- [ ] Plugin não escreve em `_elementor_data`.
- [ ] Post continua editável/publicável pelo Elementor.

## IA/Vetores, quando aplicável
- [ ] IA é assistiva.
- [ ] Evidência/fonte é rastreável.
- [ ] Custos/limites registrados.
- [ ] NO_CHANGE/hash evita processamento repetido.
- [ ] Falta de vetor não quebra a solução.

## Regressão
- [ ] Contratos anteriores relevantes passam.
- [ ] Golden Queries passam, quando aplicável.
- [ ] Testes manuais obrigatórios foram executados.

## Release
- [ ] Build reproduzível.
- [ ] ZIP possui uma raiz.
- [ ] Runtime não contém testes/docs de engenharia.
- [ ] Checksum registrado.
- [ ] Ativação não é destrutiva.

## Continuidade entre chats
- [ ] `CONTINUIDADE.md` existe na pasta da SPEC ativa.
- [ ] Branch e commit de referência registrados.
- [ ] Estado comprovado separado de planejamento.
- [ ] Decisões e invariantes vigentes registrados.
- [ ] Arquivos, dados e contratos afetados listados.
- [ ] Testes/gates e resultados registrados.
- [ ] Gaps, riscos e blockers conhecidos explícitos.
- [ ] Próximo passo exato definido.
- [ ] Critério objetivo do próximo passo definido.
- [ ] Prompt autossuficiente pronto para novo chat.
- [ ] Novo chat é instruído a reler o repositório antes de agir.
