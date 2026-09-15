# SPEC-003 — Review & Governança do Conhecimento

**Status:** PLANEJAMENTO ATIVO / IMPLEMENTAÇÃO AINDA NÃO AUTORIZADA  
**Baseline funcional:** `0.2.0-rc.1`  
**Baseline UX:** `ux/001-product-experience-knowledge-workspace/ux-baseline-v1.md`

## Mantra

> Quem não sabe onde está, não sabe para onde quer ir.

A SPEC-003 começa por inventário real de stores, writers, estados, capabilities e fluxos históricos. Nenhum estado, score, reviewer ou SLA será criado apenas porque apareceu em mockup antigo.

## 1. Problema

Summary e Classificação já possuem owners canônicos. Ainda não existe owner canônico para responder, de forma auditável:

- qual é o estado de governança do conhecimento;
- quem pode revisar/decidir;
- quem é responsável por uma decisão quando esse conceito for necessário;
- quando ocorreu a última decisão válida;
- qual decisão anterior existia;
- quais mudanças de estado são permitidas;
- como distinguir estado editorial do WordPress de estado de governança do conhecimento.

Sem esse domínio, futuras capacidades de qualidade, Search, IA e elegibilidade podem inferir confiança a partir de sinais frágeis ou conflitantes.

## 2. Objetivo

Criar um domínio mínimo, explícito e auditável de Review & Governança sem:

- substituir `post_status` editorial do WordPress;
- alterar `post_content` ou `_elementor_data`;
- criar score ornamental;
- criar `AI Ready` por implicação;
- importar automaticamente estados legados;
- manter dual-write permanente;
- transformar hipótese UX em regra de negócio.

## 3. Fonte da verdade e fronteiras

### Continua fora do domínio

- conteúdo editorial: `WP_Post` + Elementor;
- Summary: contrato SPEC-001;
- Classificação: contrato SPEC-002;
- Search/IA: SPECs posteriores.

### Domínio a definir nesta SPEC

Apenas após R-001/R-010:

- owner do estado de governança;
- conjunto mínimo de estados;
- transições válidas;
- actor/capability por transição;
- representação do responsável/revisor, se necessária;
- registro auditável da decisão;
- leitura consolidada para o Knowledge Workspace.

## 4. Princípios obrigatórios

1. **Editorial != governança.** `publish/draft/private/...` não será usado como substituto do estado de governança.
2. **Decisão humana explícita.** IA futura pode sugerir; não aprova conteúdo por inferência.
3. **Histórico não é decorativo.** Se a SPEC autorizar histórico, ele deve representar eventos reais e rastreáveis.
4. **Menor primitiva WordPress adequada.** Preferir APIs nativas antes de tabela customizada, desde que preservem integridade, consulta e auditoria necessárias.
5. **Sem migração implícita.** Dados históricos só migram com evidência de semântica equivalente.
6. **Capability por objeto.** Não confiar apenas em menus/páginas; o handler revalida autorização no artigo alvo.
7. **PRG e read-after-write.** Fluxos administrativos seguem a disciplina já homologada nas SPECs anteriores.
8. **Falha parcial é crítica.** Estado atual e histórico não podem divergir silenciosamente.
9. **Sem score até haver fórmula + owner + ação.** “Qualidade 92” não existe apenas porque é visualmente atraente.
10. **UX-001 é contrato de experiência, não de dados.** A SPEC-003 deve caber no Workspace existente sem recuperar o antigo Knowledge Studio monolítico.

## 5. Hipóteses controladas — NÃO CONTRATUAIS

Estas primitivas serão avaliadas, não assumidas:

- estado atual como post meta enum canônico;
- reviewer/responsável como user ID canônico quando o caso de uso provar necessidade;
- histórico append-only por primitiva WordPress adequada (por exemplo, custom comment type) antes de considerar tabela customizada;
- estado inicial neutro para artigos sem decisão canônica;
- UI de Review como nova tab do Knowledge Workspace somente após domínio autorizado.

Nenhuma hipótese acima pode virar runtime antes do Gate R-010.

## 6. Escopo da primeira slice

A primeira slice deve ser deliberadamente pequena:

- leitura do estado de governança;
- uma máquina de transição mínima e comprovada;
- decisão humana explícita;
- auditoria mínima suficiente para responder quem/quando/de qual estado/para qual estado;
- visualização no Knowledge Workspace;
- nenhum score de qualidade;
- nenhum `AI Ready`;
- nenhum workflow complexo de SLA/escalation automática.

## 7. Evidência obrigatória antes de definir estado

O profiler read-only deve responder pelo menos:

- quais metas/taxonomias/tabelas históricas parecem representar review, aprovação, revisor, qualidade ou elegibilidade;
- cobertura por posts e distribuição de valores;
- writers/consumers identificáveis no código legado disponível;
- capabilities/roles históricas relacionadas;
- se há timestamps/atores confiáveis;
- sobreposição ou conflito com `post_status`;
- se algum legado possui semântica forte o bastante para migração;
- volume esperado de eventos para escolher primitiva de histórico.

## 8. UX autorizada nesta fase

A baseline UX v1 já provou que Review & Governança cabe no Knowledge Workspace. Durante S001/S002 pode existir apenas:

- placeholder/conceito visual claramente rotulado;
- documentação de estados candidatos;
- nenhuma ação falsa no runtime.

A tab real só entra quando o domínio e o writer forem aprovados.

## 9. Segurança

Quando houver writer:

- POST only;
- nonce vinculado ao post e à ação;
- `current_user_can()` no objeto alvo;
- allowlist exata de transição;
- payload tipado e limitado;
- validação completa antes da primeira mutação;
- snapshot do estado atual;
- write mínimo;
- append auditável quando aplicável;
- read-after-write;
- compensação quando possível;
- estado crítico explícito quando não for possível restaurar consistência;
- logs sem conteúdo editorial bruto.

## 10. Gates

### R-001 — Current-state evidence

PASS somente com inventário read-only suficiente para decidir o domínio sem adivinhação.

### R-010 — Domain Contract

PASS somente quando owner, estados, transições, capabilities, primitivas e política de legado estiverem fechados.

### G-001 — Bootstrap/registration

Registro mínimo sem side effects e sem regressão SPEC-001/002.

### G-030 — Deterministic domain/store

Unitários para transições, no-op, payload inválido, autorização, read-after-write e consistência estado/histórico.

### G-070 — HTTP Security

GET, nonce, nonce-post binding, mass assignment, capability/IDOR, payload inválido e PRG real.

### G-110 — Browser Acceptance

Workspace, estados, feedback, teclado, viewport estreito e regressão Summary/Classificação.

### G-130 — Lifecycle/Clean package

Sem fixture/runner residual, deactivate/activate limpo e package RC reproduzível.

## 11. Critério de saída

SPEC-003 só é concluída quando:

- o estado de governança possui owner único;
- transições são determinísticas e autorizadas;
- decisões são auditáveis de modo proporcional ao escopo;
- editorial/Summary/Classificação permanecem íntegros;
- legado não é promovido silenciosamente;
- UX respeita a baseline UX-001;
- todos os gates MUST estão PASS.

**GO de desenvolvimento/homologação != GO de produção.**
