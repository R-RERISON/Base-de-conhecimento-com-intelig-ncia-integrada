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

- [ ] Nenhuma escrita em `_elementor_data` sem gate/autorização aplicável.
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

## 6. UI/UX — Visual Contract obrigatório

Para qualquer funcionalidade com UI nova ou materialmente alterada:

- [ ] `ux/002-mockup-visual-foundation/visual-contract-v2.md` foi lido e aplicado.
- [ ] Mockup aplicável em `scr/` foi identificado; se não existir referência direta, isso foi registrado.
- [ ] Tokens/componentes canônicos BDC foram reutilizados antes de criar variantes.
- [ ] WordPress permanece shell/plataforma sem aparência genérica do wp-admin como resultado final da superfície BDC.
- [ ] Navegação integrada e contexto do usuário foram preservados.
- [ ] Responsividade foi validada em desktop e nos breakpoints aplicáveis de 782px e 520px.
- [ ] Teclado/foco básicos foram validados.
- [ ] Contraste e legibilidade foram validados.
- [ ] Cor não é o único indicador de estado.
- [ ] Feedback de erro/sucesso/read-only/disabled aplicável é claro.
- [ ] Iconografia, quando usada, é consistente e não depende de biblioteca externa sem justificativa.
- [ ] Alteração visual material passou revisão humana contra o mockup/contrato aplicável.
- [ ] Divergência intencional do contrato possui justificativa, risco e decisão documentados.

**Gate visual:** qualquer item aplicável acima em FAIL/NOT_RUN bloqueia Done e merge. Uma UI funcional, porém visualmente divergente da baseline homologada `0.4.0-ux002.3`, não está concluída.

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
- [ ] A solução reduz esforço de leitura/tempo até resposta confiável; volume de conteúdo isoladamente não é métrica de sucesso.

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


## 14. Premium Product Gate

Aplicável a toda capability material após o Premium Rebaseline:

- [ ] O `docs/PREMIUM-PLUGIN-PRODUCT-STANDARD.md` foi lido e aplicado.
- [ ] O ID correspondente no `specs/MASTER-FUNCTIONAL-PARITY-LEDGER.md` foi atualizado quando aplicável.
- [ ] WPCS/PHPCS foi executado no runtime afetado.
- [ ] PHPUnit/integration tests aplicáveis foram executados.
- [ ] Análise estática aplicável foi executada ou possui baseline/waiver explícito.
- [ ] Plugin Check foi executado no artefato quando a fase da SPEC exigir.
- [ ] Metadata/licença/versionamento/readme/changelog permanecem coerentes.
- [ ] Compatibilidade mínima/corporativa/corrente foi considerada.
- [ ] Build/gate interno não foi confundido com versão pública do produto.
- [ ] O ZIP de produção não inclui runner/profiler/evidence/tool de laboratório sem necessidade operacional explícita.
- [ ] Assets estão limitados às superfícies que os consomem.
- [ ] Classes/composition roots grandes foram revisados por coesão.
- [ ] Não foi introduzido endpoint/store/framework apenas por modernidade.

## 15. Paridade e Cutover

- [ ] `PLANNED` não foi usado como sinônimo de paridade.
- [ ] Capability legada afetada está em PARITY_VERIFIED, IMPROVED_VERIFIED, SUPERSEDED_WITH_EVIDENCE ou RETIRED_BY_PRODUCT_DECISION.
- [ ] Consumidores reais foram verificados quando há shortcode/hook/filter/adapter histórico.
- [ ] Dados canônicos foram preservados/adotados ou a não migração foi justificada.
- [ ] Coexistência não cria dual-write permanente.
- [ ] Rollback/reactivação é conhecido e testável.
- [ ] Nenhum plugin legado é considerado aposentável apenas por ausência de dependência de código.

**Gate Premium:** FAIL, NOT_RUN, STALE, PARTIAL, GAP ou UNKNOWN_ENVIRONMENTAL aplicável bloqueia o respectivo cutover.
