# ASI Quality Parity & Improvement Contract v1

**Estado:** ATIVO como guardrail da SPEC-005.  
**Referência:** Advanced Search Intelligence 4.6.8 @ `c0ddff89caad529ce1bcdc645eb795e4a9b187a1`.

## Princípio

O ASI é uma referência funcional madura e reconhecidamente forte de busca. A nova arquitetura **não tem licença para regredir qualidade em nome de simplificação**.

Simplificar significa remover dívida e acoplamento, não remover capacidade comprovadamente útil.

## Comportamentos ASI a preservar ou superar

### Query understanding
- normalização determinística;
- consulta original preservada;
- tokens bounded;
- distinção de consulta curta/composta/linguagem natural;
- plano de retrieval limitado;
- expansão governável quando houver evidência.

### Retrieval
- lexical funcional sem IA;
- FULLTEXT quando suportado/justificado;
- fallback lexical bounded;
- consulta longa não deve virar zero-result apenas por AND excessivo;
- resultado degradado deve ser explícito.

### Ranking
- título forte;
- cobertura de tokens;
- sinais explicáveis;
- conteúdo oficial/canônico pode ser promovido por contrato;
- empates determinísticos;
- score/algorithm version observáveis.

### Golden Queries
- expectativa de post;
- rank máximo;
- blocking/warning;
- suite hash;
- versão de algoritmo;
- suite vazia != PASS;
- evidence stale quando algoritmo muda;
- execução explícita;
- blocking fail = NO-GO.

### Segurança/UX
- WordPress revalida acesso;
- zero-result != erro;
- progressive disclosure;
- teclado/ARIA;
- cache nunca pode falsificar estado de qualidade.

## Onde a nova arquitetura deve melhorar o ASI

1. **Uma única representação semântica:** Content Extractor/KD substitui pipelines paralelos lendo HTML/Elementor diretamente.
2. **WordPress Core Blocks como destino editorial canônico:** Search não conhece formatos editoriais como autoridade.
3. **Menos persistência:** nenhuma reprodução automática das 12 tabelas do ASI.
4. **Sem equivalências hardcoded:** vocabulário de domínio futuro será governado/dado, não código.
5. **Separação de domínios:** Search Retrieval não carrega Analytics, Queue, IA ou migração sem SPEC própria.
6. **Projection explicitamente reconstruível:** nunca fonte de verdade.
7. **Build/release determinístico:** mesmo artefato testado/promovido.
8. **Golden antes de semantic/vector:** qualidade mensurada antes de aumentar complexidade.
9. **Explicabilidade como contrato:** sinais do ranker devem poder ser inspecionados.
10. **Privacidade por negação:** SPEC-005 não registra queries de usuários.

## Regra de paridade

Quando uma Golden Query ou expectativa ASI validada puder ser reproduzida no novo ambiente:

- o artigo esperado deve continuar sendo recuperável;
- `max_rank` aprovado deve ser preservado ou melhorado;
- regressão blocking não pode ser aceita silenciosamente;
- diferença intencional exige rationale humano e nova versão da expectativa.

A ausência de ambiente ASI executável não autoriza inventar rank legado. Nesse caso, usar expectativas humanas/Golden preservadas como referência.

## Critério de “melhoria”

Só afirmar que a nova busca é melhor quando houver evidência de pelo menos um:

- Golden rank melhor sem regressão blocking;
- maior cobertura semântica;
- menor zero-result em conjunto curado;
- menor p95 mantendo qualidade;
- explicabilidade superior;
- arquitetura operacional mais simples sem perda de qualidade;
- melhor segurança/privacidade.

“Menos código” isoladamente não é melhoria de Search.
