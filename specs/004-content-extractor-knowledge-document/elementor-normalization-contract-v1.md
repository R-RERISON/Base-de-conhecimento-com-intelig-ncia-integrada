# Elementor Normalization Contract v1 — SPEC-004

**Contract version:** `1.0.0`  
**Status:** FROZEN para planejamento / writer ainda NÃO autorizado  
**Data:** 2026-09-15  
**Origem da decisão:** ressalva arquitetural após R-200/R-210: Elementor é o editor operacional padrão atual da equipe, enquanto a maioria do corpus legado resulta de migrações históricas.

## 1. Objetivo

Estabelecer como o projeto deverá convergir o corpus editorial para uma condição **contratualmente compatível com Elementor**, sem transformar o plugin em CMS/editor próprio e sem violar o caráter read-only do Content Extractor.

Há dois planos independentes:

1. **Knowledge normalization plane** — read-only, determinístico, usado por busca/IA/Knowledge Document;
2. **Editorial migration plane** — writer explícito, administrativo e futuro, usado para converter conteúdo legado para uma estrutura editável pelo Elementor.

O plano 1 nunca executa o plano 2 como efeito colateral.

## 2. Princípio central

`extração != migração editorial`

Ler, classificar, normalizar ou construir Knowledge Document:

- não altera `_elementor_data`;
- não altera `post_content`;
- não marca post como construído com Elementor;
- não cria revisão;
- não muda publicação/status/data;
- não executa migration automaticamente.

A normalização editorial para Elementor só poderá ocorrer por operação explícita, auditável e reversível.

## 3. Compatibilidade Elementor — estados

O extractor pode classificar cada post, sem escrever, em:

- `native` — `_elementor_data` válido e utilizável como fonte Elementor;
- `projectable` — fonte não-Elementor pode ser convertida de forma determinística sem dependências dinâmicas conhecidas;
- `review_required` — conversão parece possível, mas há conteúdo dinâmico/corrompido/shortcode/widget/bloco que exige revisão ou adapter especializado;
- `blocked` — não há caminho seguro dentro dos limites atuais.

Essa classificação é readiness de migração; ela não altera `source_kind` do Knowledge Document.

## 4. Meta de convergência

A meta de longo prazo é que todo post editorial elegível tenha uma das seguintes condições:

1. Elementor nativo válido; ou
2. projection plan Elementor aprovado e migrado; ou
3. exceção formal documentada (`review_required`/`blocked`) com motivo rastreável.

Não será considerado sucesso apenas gravar JSON em `_elementor_data`. O critério é o post permanecer semanticamente íntegro e editável no Elementor.

## 5. Estrutura Elementor de referência

A documentação oficial do Elementor define:

- dados estruturados em JSON;
- `content` como árvore recursiva de elementos;
- containers como elementos de layout;
- widgets com `elType=widget`, `widgetType`, `settings` e `elements`;
- estrutura moderna baseada em containers, sem exigir o legado section/column.

Referências:

- https://developers.elementor.com/docs/data-structure/
- https://developers.elementor.com/docs/data-structure/general-structure/
- https://developers.elementor.com/docs/data-structure/container-element/
- https://developers.elementor.com/docs/data-structure/widget-element/

A implementação não deve depender de um dump histórico específico de `_elementor_data`.

## 6. Gateway Elementor obrigatório

Qualquer writer futuro deve ficar atrás de um `Elementor_Gateway` versionado.

Responsabilidades mínimas:

- detectar `ELEMENTOR_VERSION` real no ambiente alvo;
- confirmar carregamento do Elementor;
- obter o Document correspondente ao post;
- validar se o post type/document é editável;
- salvar por API/document lifecycle do Elementor quando compatível;
- marcar built-with-Elementor pelo Document lifecycle;
- invalidar caches/CSS pelos mecanismos do Elementor;
- falhar fechado quando a versão/API não estiver homologada.

Não permitir writer de produção espalhado por chamadas diretas a `update_post_meta()`.

Referência de implementação atual do Elementor Core: `Elementor\Core\Base\Document::save()` e `set_is_built_with_elementor()`.

## 7. Direct-meta write

Escrita direta em `_elementor_data`, `_elementor_edit_mode`, template type, version e metadados correlatos fica **proibida por padrão**.

Exceção futura exige:

- incompatibilidade comprovada da API de Document;
- adapter por versão;
- teste de round-trip no editor;
- teste de frontend;
- rollback integral;
- autorização explícita no gate de migração.

## 8. Projection Plan

Antes de qualquer write, cada post deve produzir um plano read-only contendo no mínimo:

```text
post_id
source_kind
source_hash_before
elementor_compatibility.status
elementor_compatibility.reasons[]
projection_schema_version
projection_strategy
projection_hash
warnings[]
requires_review
```

O plano não precisa exportar conteúdo editorial para logs.

## 9. Estratégias iniciais de projeção

### 9.1 Elementor válido

- `native`;
- nenhuma migração por padrão;
- apenas validação de integridade/round-trip em G-240/G-245.

### 9.2 Legacy HTML

É o principal caminho de migração devido ao corpus real.

Estratégia inicial preferida:

- preservar HTML editorial relevante;
- projetar para estrutura moderna de container + widgets controlados;
- evitar reescrita semântica;
- não converter automaticamente markup desconhecido em layout visual sofisticado;
- priorizar fidelidade do conteúdo sobre embelezamento.

### 9.3 Plain text

- projetável deterministicamente;
- normalizar somente boundaries/encoding necessários;
- sem reescrever texto.

### 9.4 Gutenberg

- converter apenas blocos estáticos com mapping homologado;
- dynamic blocks => `review_required` até adapter específico;
- nunca chamar render dinâmico arbitrário como forma de migration.

### 9.5 Shortcodes

- shortcodes sem adapter homologado => `review_required`;
- nunca materializar resultado via `do_shortcode()` genérico;
- adapters devem preservar semântica e dependência necessária.

### 9.6 Elementor JSON inválido

- não reparar JSON por heurística destrutiva;
- usar `post_content`/fonte alternativa quando suficiente;
- manter warning `ELEMENTOR_JSON_INVALID`;
- exigir review antes de substituir o estado editorial inválido.

## 10. Fidelity contract

Uma migração só pode ser promovida quando houver prova de:

- texto preservado;
- headings preservados;
- links preservados;
- listas/tabelas preservadas;
- imagens e referências relevantes preservadas;
- códigos/pre preservados;
- shortcodes/blocos dinâmicos tratados explicitamente;
- editor Elementor abre o documento sem erro;
- frontend não perde conteúdo relevante;
- hashes/manifest permitem explicar a transformação.

## 11. Identidade dos elementos

IDs Elementor gerados devem ser:

- únicos dentro do documento;
- estáveis para a mesma projection plan quando tecnicamente possível;
- gerados pelo gateway/normalizer, nunca reutilizados de outro post;
- considerados detalhe de implementação, não semântica do Knowledge Document.

## 12. IA

A convergência editorial para Elementor beneficia trabalhos futuros de IA por reduzir heterogeneidade, mas a IA não participa do writer inicial.

A primeira migração deve ser determinística. IA poderá futuramente sugerir melhoria visual/estrutura somente em fluxo separado com revisão humana.

## 13. Gate para writer

Nenhum writer de normalização Elementor pode ser habilitado até existirem:

1. G-220 extractor estável;
2. G-230 Knowledge Document/hash;
3. G-240 aceitação em conteúdo real;
4. projection plan read-only;
5. production preflight;
6. journal/rollback aprovado;
7. canário aprovado em homologação;
8. matriz de compatibilidade da versão Elementor de produção.

## 14. Regra de produção

Instalar/atualizar o plugin **não migra conteúdo automaticamente**.

Content migration é uma operação administrativa separada, explícita e controlada por lote.
