# R-510 — Revisão humana dos Golden Candidates v1

**Estado:** T513 PENDING / T514 PENDING; R-510 OPEN.

**Fontes:** seed T510 + baseline independente T511.2 PASS AMBIENTAL em 2026-09-18.

**Fixture preservada:** [golden-candidates-legacy-v1.json](fixtures/golden-candidates-legacy-v1.json).

**Análise:** [r510-t5112-environmental-findings-v1.md](r510-t5112-environmental-findings-v1.md).

## T513 — Decisão concreta para o revisor

Proposta, **ainda não aceita**: manter as seis consultas/post IDs e `max_rank=3`; considerar `blocking` para proteger paridade, conforme proposta anterior. Ranking medido não confirma sozinho qual artigo é a resposta oficial.

| ID | Consulta | Expected post proposto | Max rank proposto | Rank atual / relevância / publish | Severity proposta | Decisão humana |
|---|---|---:|---:|---|---|---|
| GQ-LEGACY-001 | pendrive | 527 | 3 | 10 / 1 / 1 | blocking | Pendente |
| GQ-LEGACY-002 | MSTeams | 579 | 3 | 1 / 1 / 1 | blocking | Pendente |
| GQ-LEGACY-003 | Windows 11 | 583 | 3 | Fora Top-20 / 1 / 1 | blocking | Pendente |
| GQ-LEGACY-004 | Termo de assinatura | 45855 | 3 | 1 / 1 / 1 | blocking | Pendente |
| GQ-LEGACY-005 | Estrutura | 36620 | 3 | 5 / 2 / 2 | blocking, se a intenção for confirmada | Pendente |
| GQ-LEGACY-006 | SCCM | 412 | 3 | 7 / 1 / 1 | blocking | Pendente |

Todas as severidades históricas são `warning`. A proposta acima não altera a fixture, não é aceite humano e não entra no runtime. A escolha `warning` continua possível mediante justificativa de impacto.

Para cada linha, confirmar ou corrigir:

1. A consulta representa uma necessidade real e continua válida?
2. O post indicado continua disponível/autorizado e contém a resposta oficial para essa intenção?
3. O limite até a terceira posição é adequado?
4. Uma regressão deve bloquear a liberação (`blocking`) ou gerar aviso (`warning`)?
5. Qual é a justificativa da expectativa? Registrar também data e referência do aceite humano, sem coletar identidade de usuários da busca.

**Atenção a “Estrutura”:** 516 é o primeiro resultado nativo; 36620 é o segundo. Revisar ambos no WordPress. Não trocar o expected automaticamente. Se a consulta for ambígua, explicitar a intenção e justificar manter/corrigir a expectativa.

Resposta em lote é suficiente se identificar as seis linhas e confirmar expressamente posts, max_rank e severity. Alterações devem citar o ID e a justificativa. Uma aprovação genérica para “continuar” não fecha T513.

Modelo de registro por caso:

```text
ID:
Decisão: aceitar / corrigir / excluir com justificativa
Consulta:
Expected post ID:
Max rank:
Severity: blocking / warning
Justificativa: por que esse artigo atende à intenção e qual o impacto da regressão
Data e referência do aceite humano:
```

## Proveniência e limites

T510 capturou seis linhas manuais ativas, post-level, com expected posts existentes/publicados, post type `post`, sem item-level e sem review flag estrutural. O último run legado registrava PASS 6/6 em algorithm 4.5.0; isso é histórico, não reexecução do ASI nesta etapa.

T511.2 mediu esses mesmos candidates a partir de seed próprio. Os resultados não incluem títulos ou conteúdo dos artigos. A validade semântica não foi verificada nesta revisão offline. O ASI não precisa ser consultado novamente para T513/T514.

O hash `source_candidate_set_hash` permanece proveniência T510. O dataset aprovado receberá versão e `set_hash` próprios em T515.

## T514 — Diversidade a completar

| Classe | Cobertura atual | Evidência ainda necessária |
|---|---|---|
| Termo simples | pendrive | Revisão T513 |
| Token de produto | MSTeams | Revisão T513 |
| Termo composto/versão | Windows 11 | Revisão T513 |
| Frase | Termo de assinatura | Revisão T513 |
| Termo genérico/ambíguo | Estrutura | Confirmar intenção e artigo oficial |
| Sigla | SCCM | Revisão T513 |
| Pergunta em linguagem natural | Ausente | Consulta realmente usada + expected post + justificativa |
| Variação/erro de digitação | Não demonstrada | Exemplo real, se existente; não inventar erro sintético como uso real |
| Alias/sinônimo | Não demonstrado | Termo real alternativo + artigo oficial revisado |
| Resposta dependente de Summary | Não demonstrada | Consulta real + post cujo Summary contenha o sinal necessário |
| Gap Elementor/Content Extractor | Não demonstrado | Consulta real + post cuja fonte semântica exponha o gap R-500 |

Para cada nova consulta, fornecer o mesmo registro de T513 e indicar origem (`human`, incidente ou outra fonte curada), classe e campo/fonte que sustenta a resposta. Não exportar logs brutos, IP, sessão ou conteúdo privado desnecessário. A mesma consulta pode cobrir mais de uma classe, com justificativa.

Nenhuma quantidade arbitrária substitui cobertura. Onde uma classe não tiver exemplo real, registrar a lacuna e submeter a decisão explícita, em vez de marcar cobertura automaticamente. Para R-510, linguagem natural e variação/erro quando real seguem os critérios da SPEC; Summary/Elementor precisam ser tratados porque R-500 já demonstrou gaps.

## Critério de conclusão

- T513: cada candidate recebeu decisão humana rastreável sobre validade, expected, max_rank, severity e rationale; nenhum campo de aprovação permanece presumido.
- T514: conjunto não vazio com classes requeridas demonstradas e lacunas tratadas explicitamente.
- T515: somente depois, congelar dataset aprovado com versão e hash determinístico próprios.
- T516: fechamento explícito de R-510 após os três passos; engine ainda depende de G-520.
