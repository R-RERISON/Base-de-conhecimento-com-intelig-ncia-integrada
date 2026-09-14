# T094 — Revisão de Produto e Conhecimento

> Estado: **CONCLUÍDA — APROVADA COM ORDEM DE ENTREGA AJUSTADA, ZERO BLOQUEIOS GLOBAIS**.  
> Baseline revisada: `main @ b9de85ef04ddd4320f191fb2dd5a394bf3662802` (fechamento T093).  
> Objeto principal: valor para usuários, jornada de conhecimento e ordem das futuras vertical slices.

## 1. Objetivo

Validar se a arquitetura mínima já aprovada entrega valor real para usuários concretos e se a ordem proposta de implementação faz sentido do ponto de vista de gestão do conhecimento, não apenas de simplicidade técnica.

Classificação:

- **MANTER** — ordem/capacidade compra valor suficiente;
- **REORDENAR** — capacidade é válida, mas deve nascer em outro momento;
- **SIMPLIFICAR** — valor existe, porém o escopo precisa diminuir;
- **POSTERGAR** — valor insuficiente para agora;
- **BLOQUEAR** — proposta não deve avançar sem resolver risco/produto.

Nenhum runtime foi criado.

---

## 2. Usuários e resultados desejados

### Analista de Conhecimento

Precisa transformar conteúdo editorial já existente em conhecimento mais governável sem reescrever o artigo.

Resultados relevantes:

- registrar objetivo, escalonamento e informação importante;
- classificar e revisar de forma consistente;
- saber o que está incompleto;
- preparar conteúdo confiável para Search/IA futura.

### Resolvedor / Atendimento

Precisa encontrar rapidamente informação correta e acionável.

Resultados relevantes:

- encontrar o artigo certo;
- entender objetivo, passos/limites e escalonamento;
- não receber conteúdo fora do scope/confiabilidade.

### Gestor de Conhecimento

Precisa enxergar qualidade/cobertura/lacunas sem construir uma plataforma analítica antes de possuir dados confiáveis.

### Administrador WordPress

Precisa configurar e operar o plugin sem duplicar o WordPress, sem manutenção frágil e sem surpresa destrutiva.

---

## 3. Resultado executivo

**PASS de Produto/Conhecimento.**

Findings:

- **8 MANTER**;
- **3 REORDENAR**;
- **6 SIMPLIFICAR**;
- **7 POSTERGAR**;
- **0 BLOQUEAR global**.

### Decisão principal

A recomendação T091/T093 é mantida:

> **SPEC-001 candidata = Core mínimo + Summary narrativo (`objective`, `escalation`, `important`) para o Analista de Conhecimento.**

Ela entrega valor real porque permite curar três informações operacionais estruturadas sem tocar no conteúdo editorial e cria o primeiro owner canônico funcional do produto.

Porém, T094 adiciona uma regra de produto:

> **SPEC-001 não deve fingir entregar valor ao Resolvedor ainda. Seu usuário primário é o Analista de Conhecimento.**

Resolver busca/consumo operacional pertence a slices posteriores.

---

## 4. Findings

### P-001 — Summary narrativo como primeiro slice

**Classificação:** MANTER.

Valor comprovado:

- domínio pequeno e compreensível;
- três campos operacionais importantes;
- já existe referência GRE;
- zero dependência de Search/IA/queue/Analytics;
- permite provar shell, segurança, persistência, Design System e lifecycle com risco baixo.

Usuário primário: **Analista de Conhecimento**.

Resultado esperado:

`selecionar artigo -> ler Summary -> editar 3 campos -> salvar -> reler -> confirmar estado`

### P-002 — Não incluir os oito campos GRE no primeiro Summary slice

**Classificação:** SIMPLIFICAR.

Os cinco campos classificatórios históricos não pertencem ao owner Summary. Incluí-los na SPEC-001 reintroduziria D-006 e B-002 sem necessidade.

### P-003 — Tela e navegação do primeiro slice

**Classificação:** SIMPLIFICAR.

Uma única superfície integrada ao wp-admin é suficiente. Não criar Home/Cockpit/portal completo antes de existir mais de uma jornada real.

A tela deve deixar claro o artigo/contexto e oferecer retorno ao fluxo WordPress, sem criar CMS paralelo.

### P-004 — Classificação como próxima capacidade de domínio

**Classificação:** MANTER, mas incremental.

Após Summary, Classificação agrega governança e prepara filtros/Search.

T094 recomenda que a primeira SPEC de Classificação priorize **`knowledge_type`** como eixo inicial, porque:

- vocabulário tende a ser finito/controlado;
- semântica é distinta e madura;
- possui valor de governança mesmo antes de Search;
- reduz risco de começar por campos organizacionais/externos mais ambíguos.

`audience`, `service` e `technologies` entram depois conforme valor/profiling. B-002 governa cutover dos dados legados.

### P-005 — Review/Governança

**Classificação:** REORDENAR para depois de pelo menos Summary + um eixo classificatório estáveis.

Review isolado sem dados sistêmicos mínimos corre risco de aprovar um conteúdo ainda pouco estruturado.

Quando nascer, Review pode começar com state/notes/reviewer/time. `include_ai` e AI READY podem entrar somente quando os pré-requisitos de confiança existirem.

### P-006 — AI READY

**Classificação:** POSTERGAR dentro do domínio Review.

A regra histórica `publish + approved + 8/8 + include_ai` não deve ser implementada antes de os owners dos oito conceitos relevantes estarem disponíveis.

Não reduzir 8/8 para 3/3 apenas para caber no primeiro runtime.

### P-007 — Search é valor estratégico, mas não primeiro slice

**Classificação:** MANTER como prioridade posterior.

Para o Resolvedor, Search provavelmente será a capacidade de maior valor direto. Ainda assim, antecipá-la antes de:

- Content Extractor confiável;
- owners básicos estabilizados;
- Golden;
- segurança/scope;
- benchmark;

criaria risco maior que o valor do primeiro release.

T094 recomenda tratar Search post-level como **primeira grande capacidade de consumo**, não como fundação da SPEC-001.

### P-008 — Content Extractor

**Classificação:** REORDENAR para nascer junto da primeira Search/qualidade que o consuma.

Extractor sozinho não é jornada de usuário. Evitar SPEC puramente infra sem resultado homologável.

### P-009 — Search post-level antes de item-level

**Classificação:** MANTER.

Para o primeiro valor do Resolvedor, encontrar o artigo certo pode ser suficiente. Item-level/deep-link só entram quando casos reais/Golden demonstrarem que abrir o artigo inteiro não atende.

### P-010 — Search Knowledge

**Classificação:** POSTERGAR até haver lacunas observadas no ranker lexical mínimo.

Vocabulary/bindings/rules são poderosos, mas devem resolver problemas reais, não existir por paridade histórica.

### P-011 — Golden Queries

**Classificação:** MANTER como requisito de qualidade, SIMPLIFICAR como produto.

Golden é necessária para Search, mas não precisa de um produto/portal próprio. O primeiro conjunto pode ser pequeno e governado, focado nos casos de negócio críticos.

### P-012 — Analytics detalhado

**Classificação:** POSTERGAR.

O primeiro valor pode ser homologado por conclusão de tarefa, integridade de dados e feedback de usuários sem persistir query text/identity.

Métricas de adoção/uso podem ser reavaliadas quando houver pergunta de produto clara e B-004 estiver fechado.

### P-013 — Cockpit/ dashboards gerenciais

**Classificação:** POSTERGAR.

Não criar dashboard vazio antes de possuir dados canônicos e perguntas decisórias estáveis. Views simples/contagens bounded podem nascer junto de um caso real.

### P-014 — IA Assistiva P1

**Classificação:** POSTERGAR até owner e jornada manual estarem estáveis.

IA deve reduzir esforço comprovado, não definir o processo.

Ordem de valor sugerida para experimento futuro:

1. Summary assistido, se edição manual demonstrar esforço relevante;
2. Classificação assistida, se vocabulário e Apply humano já estiverem estáveis.

A escolha final deve usar dados/feedback reais; T094 não congela qual P1 vem primeiro.

### P-015 — RAG/semantic/agentes

**Classificação:** POSTERGAR.

Não há produto confiável de resposta generativa antes de retrieval confiável. T058 permanece correta.

### P-016 — Compatibilidade

**Classificação:** POSTERGAR até consumidor comprovado.

Do ponto de vista do usuário, preservar uma interface antiga só agrega valor se ela ainda estiver em uso. B-003 continua sendo o gate.

### P-017 — Design System

**Classificação:** MANTER como contrato, SIMPLIFICAR como execução.

Primeiro slice precisa parecer parte do produto, mas somente com tokens/componentes necessários à tela real. Não construir kit visual completo por antecipação.

### P-018 — Produto integrado sem big-bang

**Classificação:** MANTER.

A experiência final deve ser integrada, mas integração visual/funcional cresce conforme os slices. “Produto único” não significa “entregar tudo na primeira versão”.

---

## 5. Ordem recomendada das futuras SPECs

Sujeito a T095/T097, T094 recomenda:

### SPEC-001 — Core mínimo + Summary narrativo

Usuário: Analista de Conhecimento.  
Resultado: gerir `objective`, `escalation`, `important` com segurança e integridade.

### SPEC-002 — Classificação mínima: `knowledge_type`

Usuário: Analista/Gestor de Conhecimento.  
Resultado: classificar artigo num eixo governado/reutilizável.

### SPEC-003 — Review mínimo

Usuário: Analista/Revisor.  
Resultado: estado, notas, reviewer/time com fluxo humano. AI READY fica fora até pré-requisitos completos.

### SPEC-004 — Search post-level + Content Extractor + Golden mínimo

Usuário: Resolvedor.  
Resultado: encontrar artigos relevantes com ranking lexical confiável e scope seguro.

Esta é a primeira grande entrega direta ao resolvedor e pode ser priorizada antes de expansões classificatórias adicionais se a demanda do produto exigir.

### Depois

- audience/service/technologies por necessidade;
- item-level/deep-link;
- Search Knowledge;
- dashboards/Analytics;
- IA P1;
- RAG/vector/agentes somente por evidência.

Os números finais só serão congelados após T097; a sequência é recomendação de produto.

---

## 6. Critério de sucesso do primeiro slice sem Analytics

SPEC-001 não precisa de telemetria detalhada para provar valor.

Sucesso observável em homologação:

1. usuário autorizado encontra o artigo alvo;
2. lê valores existentes;
3. altera os três campos;
4. salva sem alterar conteúdo editorial;
5. recarrega e encontra exatamente o estado esperado;
6. usuário não autorizado é bloqueado;
7. erro/falha não produz estado enganoso;
8. usuário consegue explicar para que servem os três campos e usá-los no fluxo real de curadoria.

A última condição exige homologação humana simples; não precisa Analytics.

---

## 7. Paridade necessária versus legado

### Necessário preservar

- valores narrativos GRE reais;
- comportamento de leitura/edição segura;
- owner correto;
- dados de Review/Classificação reais quando seus slices nascerem;
- Search Knowledge/Golden manuais úteis quando a Search os consumir.

### Não é paridade de produto obrigatória

- mesma disposição de telas legadas;
- mesmos menus;
- oito campos no mesmo formulário;
- mesmos shortcodes;
- dashboards históricos;
- side panel GRE;
- estruturas ASI internas;
- IA/vector apenas porque o legado/roadmap possuía capacidade equivalente.

---

## 8. Findings de risco de produto

### PK-001 — primeiro slice tecnicamente correto, mas sem usuário declarado

**Tratamento:** SPEC-001 tem usuário primário explícito: Analista de Conhecimento.

### PK-002 — prometer valor ao Resolvedor antes de Search

**Tratamento:** não posicionar SPEC-001 como solução de descoberta/resolução; esse valor chega na Search.

### PK-003 — AI READY prematuro

**Tratamento:** postergar até owners/8 campos requeridos existirem; não mudar regra por conveniência.

### PK-004 — construir dashboards antes das decisões

**Tratamento:** dashboard só com pergunta de gestão comprovada.

### PK-005 — priorizar paridade visual/menus sobre jornada

**Tratamento:** preservar resultado e Design System, não clone de plugins antigos.

### PK-006 — Search atrasada demais

**Tratamento:** após Summary/Classificação mínima/Review mínimo, Search post-level torna-se prioridade estratégica; T095/T097 podem ajustar sequência se houver urgência comprovada.

---

## 9. Gate T094

- usuários principais revisados: **SIM**;
- valor do primeiro slice: **COMPROVADO conceitualmente**;
- candidato SPEC-001 mantido: **SIM**;
- AI READY prematuro evitado: **SIM**;
- Search reconhecida como prioridade estratégica posterior: **SIM**;
- paridade histórica não confundida com valor de produto: **SIM**;
- blocker global novo: **ZERO**;
- runtime criado: **NÃO**.

## 10. Entrada para T095

T095 deve agora avaliar **somente os blockers/unknowns que afetam a SPEC-001 candidata**, separando-os dos blockers de slices futuros.

Pergunta central:

> **Existe algum B-001–B-007, finding T090–T094 ou unknown técnico/produto que impeça formalmente `Core mínimo + Summary narrativo` de entrar em SPEC-001 após T097?**

Para cada item, decidir:

`FECHAR_AGORA | NÃO_APLICÁVEL_A_SPEC001 | POSTERGAR_PARA_SLICE_CORRETO | BLOCKER_SPEC001`.

## 11. Próximo passo

**T095 — Fechamento de unknowns e blockers por slice.**