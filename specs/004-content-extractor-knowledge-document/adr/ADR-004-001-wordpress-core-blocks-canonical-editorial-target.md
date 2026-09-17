# ADR-004-001 — WordPress Core Blocks como destino editorial canônico

**Status:** ACEITA  
**Data:** 2026-09-17  
**SPEC:** 004 — Content Extractor e Knowledge Document  
**Decisão:** substituir Elementor como destino editorial futuro por WordPress Core Block Editor APIs, mantendo Elementor somente como fonte legada durante a transição.

## 1. Contexto

A SPEC-004 foi iniciada quando a premissa operacional era que Elementor seria o padrão editorial futuro da equipe. A partir disso, G-245 passou a preparar normalização para `_elementor_data`, com Projection Plan, Gateway version-gated, journal, stale-source guard, dry-run, batches, lock e canário.

Durante o aprofundamento da arquitetura, essa premissa foi revisitada à luz do princípio WordPress-first.

O Block Editor não deve ser tratado como dependência do plugin Gutenberg. O produto deve consumir **somente APIs estáveis do WordPress Core** (`parse_blocks`, `serialize_blocks`, Block API e primitives correlatas). O plugin Gutenberg pode existir como canal upstream/experimental, mas não é dependência de produção do BDC.

## 2. Evidência do corpus

O T081 full-corpus sobre 622 posts registrou:

- `legacy_html`: 536;
- `plain_text`: 41;
- `elementor`: 34;
- `mixed`: 5;
- `gutenberg`: 4;
- `empty`: 2.

Logo, a base atual não é estruturalmente uma base Elementor. Elementor puro representa uma parcela pequena do corpus; o problema dominante é legado heterogêneo.

## 3. Problema arquitetural

Manter Elementor Free como destino canônico exigiria que o plugin assumisse dependência de uma estrutura editorial externa e privada (`_elementor_data`), versão específica, compatibilidade de widgets, schema e lifecycle do fornecedor.

Isso conflita com a preferência constitucional:

1. WordPress Core;
2. hooks/APIs do Core;
3. estrutura própria mínima;
4. serviço/dependência externa.

Além disso, para IA, busca e automação editorial governada, a árvore de Blocks fornece uma representação nativa, semântica e manipulável pelo próprio WordPress, reduzindo tradução entre domínio de conhecimento e layout de page builder.

## 4. Decisão

### 4.1 Fonte editorial canônica futura

A fonte editorial canônica passa a ser:

`WP_Post.post_content` contendo WordPress Core Blocks e, quando justificado, blocos próprios `bdc/*` registrados pela Block API estável do Core.

### 4.2 Elementor

Elementor passa a ter o papel de **fonte legada suportada para leitura e migração**, não de destino futuro.

Enquanto houver conteúdo dependente de Elementor:

- o plugin Elementor não deve ser removido automaticamente;
- `_elementor_data` deve ser preservado;
- `Elementor_Adapter` continua read-only;
- conteúdos Elementor/mixed entram no mesmo pipeline de extração → Knowledge Document → Block Projection;
- remoção física do plugin Elementor somente será avaliada após paridade, migração e inventário mostrarem dependência zero.

### 4.3 Plugin Gutenberg

O plugin Gutenberg **não será dependência do produto**. O BDC usa somente APIs/features presentes no WordPress Core homologado. APIs experimentais/plugin-only não entram em produção sem ADR específica.

### 4.4 IA

IA não escreve conteúdo editorial autonomamente. O modelo passa a ser:

`fonte atual → Content Extractor → Knowledge Document → Block Projection/Suggestion → humano revisa/decide → WordPress persiste`.

Migrações administrativas podem persistir Blocks somente sob gates explícitos, journal, lock, stale-source, rollback e autorização.

## 5. Consequências

### Positivas

- alinhamento real com WordPress-first;
- menor vendor lock-in;
- elimina necessidade de writer futuro em `_elementor_data`;
- reduz version-gating específico do Elementor no destino;
- melhor semântica para IA/retrieval;
- portabilidade e longevidade maiores;
- conteúdo editorial reside na primitive nativa `post_content`;
- Block Patterns/templates/locking podem padronizar edição sem criar editor próprio.

### Custos

- G-245 precisa ser rebaselined;
- Elementor Projection Plan deixa de ser o plano de destino;
- contratos e testes de destino devem migrar para Block Projection;
- corpus inteiro precisa ser perfilado quanto à projetabilidade para Blocks;
- paridade visual/editorial de posts precisa ser validada antes de remoção do Elementor.

## 6. O que é preservado

Não há descarte dos investimentos defensivos. Permanecem válidos e devem ser generalizados:

- Content Extractor;
- Knowledge Document 2.1;
- `Legacy_HTML_Adapter`;
- `Gutenberg_Adapter`;
- `Elementor_Adapter` como source adapter legado;
- hashes/canonical JSON;
- journal durável;
- stale-source guard;
- dry-run;
- batches retomáveis;
- lock exclusivo;
- metodologia de canário/rollback;
- runbook.

## 7. O que fica SUPERSEDED

A partir desta ADR:

- Elementor como destino editorial futuro;
- writer futuro em `_elementor_data`;
- T087C como executor mutável Elementor;
- qualquer interpretação de `Elementor_Projection_Plan` como plano de destino final.

Os artefatos ficam versionados como memória institucional/evidência, mas não devem evoluir para writer.

## 8. Novo fluxo alvo

```text
Legacy HTML ─┐
Plain text ──┤
Elementor ───┤
Mixed ───────┼─> Content Extractor -> Knowledge Document -> Block Projection Plan
Blocks ──────┘                                      |
                                                    v
                                      WordPress Core Blocks
                                                    |
                                                    v
                                           WP_Post.post_content
```

## 9. Critério para remover Elementor do WordPress

A remoção/desativação definitiva só poderá ser proposta quando houver evidência de:

- zero posts `elementor`;
- zero posts `mixed` dependentes de Elementor;
- zero widgets/shortcodes exclusivos necessários ao conteúdo ativo;
- nenhuma dependência runtime de `_elementor_data`;
- paridade estrutural e editorial aprovada;
- rollback/migração comprovados;
- inventário de referências/dependências atualizado;
- gate operacional explícito.

Até lá, Elementor é legado coexistente, não arquitetura-alvo.

## 10. Rollback da decisão

Se Blocks do Core não atenderem requisitos comprovados de conteúdo/edição, a decisão pode ser revista por nova ADR antes de qualquer remoção de Elementor. Como nenhum writer Elementor foi executado e o primeiro canário mutável ainda não ocorreu, o custo atual de pivot é baixo.

## 11. Resultado

**DECISÃO ACEITA.** G-245 passa de “Elementor Normalization” para **Canonical Block Normalization & Production Readiness**. O próximo trabalho é um Block Projection Contract read-only; não um writer Elementor.
