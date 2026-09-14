# Definition of Done — DoD

Uma funcionalidade não está pronta porque “funciona na máquina do desenvolvedor”. Ela está pronta quando o produto, os dados e o ambiente permanecem coerentes.

## 1. Funcional

- [ ] Fluxo ponta a ponta funciona.
- [ ] Estados de sucesso, vazio, erro, bloqueio e permissão estão tratados.
- [ ] Critérios de aceite da SPEC foram comprovados.
- [ ] Critérios de NÃO aceite não ocorreram.

## 2. WordPress-first

- [ ] Recurso nativo foi usado quando suficiente.
- [ ] Infraestrutura própria possui justificativa explícita.
- [ ] O plugin não reconstrói funcionalidade já adequada do WordPress.

## 3. Elementor / Editorial

- [ ] Nenhuma escrita em `_elementor_data`.
- [ ] Nenhuma alteração silenciosa de `post_content`.
- [ ] Post continua editável/publicável no Elementor.
- [ ] Dados derivados podem ser reconstruídos.

## 4. Clean Code

- [ ] Responsabilidades são coesas.
- [ ] Dependências são explícitas.
- [ ] Não há abstração sem necessidade concreta.
- [ ] Classes grandes foram revisadas criticamente.
- [ ] Código de domínio e documentação relevante estão em pt-BR quando tecnicamente adequado.

## 5. Segurança

- [ ] Capabilities corretas.
- [ ] Nonces em mutações.
- [ ] Métodos HTTP corretos.
- [ ] Sanitização e validação de entrada.
- [ ] Escaping de saída.
- [ ] Menor privilégio.
- [ ] Dados sensíveis e telemetria avaliados.

## 6. UI/UX

- [ ] Design System aplicado.
- [ ] Navegação integrada.
- [ ] Responsividade validada.
- [ ] Teclado/foco básicos validados.
- [ ] Cor não é o único indicador de estado.
- [ ] Feedback de erro/sucesso é claro.
- [ ] Não existe visual de “plugin diferente” dentro da plataforma.

## 7. Testes

- [ ] PHP lint.
- [ ] Testes unitários aplicáveis.
- [ ] Integração aplicável.
- [ ] Regressão dos contratos anteriores.
- [ ] Golden Queries quando a busca é afetada.
- [ ] Teste manual/browser quando necessário.
- [ ] Performance para caminhos críticos.

## 8. Dados e migração

- [ ] Migração é idempotente.
- [ ] Dados canônicos são preservados.
- [ ] Ativação não executa limpeza destrutiva.
- [ ] Rollback é conhecido.
- [ ] Compatibilidade/coexistência está documentada.

## 9. IA / Vetores

Quando aplicável:

- [ ] IA não é autoridade editorial.
- [ ] Prompt/modelo/provedor são rastreáveis.
- [ ] Custo/uso é observável.
- [ ] Operação em massa possui orçamento e limite.
- [ ] Hash/NO_CHANGE evita retrabalho.
- [ ] Ausência de semantic/vector não derruba o core.
- [ ] Retrieval e evidências são verificáveis.

## 10. Operação

- [ ] Logs/diagnóstico suficientes.
- [ ] Estado saudável/degradado/falhou é distinguível.
- [ ] Job assíncrono, se existir, é retomável ou explicitamente idempotente.
- [ ] Site Health/diagnóstico foi considerado antes de criar painel técnico próprio.

## 11. Release

- [ ] Build reproduzível.
- [ ] ZIP instalável possui uma única raiz.
- [ ] Artefato não contém arquivos de engenharia indevidos.
- [ ] Checksum é registrado.
- [ ] Instalação/upgrade foram testados.
- [ ] Documentação e changelog estão atualizados.

## 12. Homologação

- [ ] Evidência de homologação registrada.
- [ ] Gaps conhecidos estão documentados.
- [ ] Nenhum blocker conhecido permanece oculto.

## 13. Continuidade entre chats

Esta seção é obrigatória para toda implementação material, mesmo quando não houver release.

- [ ] A pasta da SPEC ativa contém `CONTINUIDADE.md` atualizado.
- [ ] O arquivo foi baseado em `.specify/templates/continuity-prompt-template.md`.
- [ ] Branch e commit de referência foram registrados.
- [ ] Estado comprovado foi separado de intenção/planejamento.
- [ ] Implementações concluídas foram registradas.
- [ ] Decisões arquiteturais e invariantes foram registradas.
- [ ] Arquivos, dados, hooks, rotas e contratos afetados foram registrados.
- [ ] Testes/gates executados e seus resultados foram registrados.
- [ ] Gaps, blockers, riscos e dívidas conhecidas foram registrados sem omissão.
- [ ] Próximo passo está descrito de forma concreta, pequena e verificável.
- [ ] Critério de conclusão do próximo passo está explícito.
- [ ] O Prompt de Continuidade está pronto para ser colado em um novo chat.
- [ ] O prompt instrui o novo chat a reler Constituição, Manifesto, SPEC, DoD e confirmar o GitHub antes de agir.

**Gate:** se qualquer item desta seção aplicável estiver pendente, a implementação não está concluída.
