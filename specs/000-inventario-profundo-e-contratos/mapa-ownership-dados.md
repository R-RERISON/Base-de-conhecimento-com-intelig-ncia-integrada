# Mapa de Ownership de Dados — SPEC-000 — T052

> Estado: **T052 concluída documentalmente**.  
> Baselines cruzadas: ASI `4.6.8 @ c0ddff89caad529ce1bcdc645eb795e4a9b187a1`, GRE `0.6.0 @ 1120a534d8eb2288460c2c675730deef0d67c365`, KB2Ops `0.2.1 @ f2d2aa659240b0c2ee86cebd3cc5bd0c00f9fc94`.
>
> Este documento define **propriedade semântica/lógica**, não schema físico. Decidir `postmeta` versus taxonomy versus tabela pertence a T056/T057. Nenhuma decisão aqui autoriza migração ou runtime.

## 1. Regra central

Cada conceito canônico deve possuir **um único owner lógico** no plugin unificado.

Um módulo pode:

- **ser owner**: define semântica, validação e contrato de escrita;
- **ser writer autorizado**: persiste por meio do owner, respeitando capability/nonce/validação;
- **ser reader**: consome o valor, sem redefinir sua semântica;
- **manter projection**: deriva índice, cache, score, agregado ou embedding reconstruível;
- **ser adapter de compatibilidade**: lê formato antigo durante cutover, sem virar nova fonte da verdade.

É proibido manter dois campos canônicos com o mesmo significado apenas porque vieram de plugins diferentes.

## 2. Bounded contexts lógicos propostos

A propriedade abaixo é conceitual e cabe dentro de **um único plugin WordPress modular**.

| Owner lógico | Responsabilidade | Não possui |
|---|---|---|
| **Editorial WordPress/Elementor** | título, corpo, estrutura editorial, publicação | índices, classificação sistêmica, telemetria |
| **Resumo Executivo** | metadata narrativa/executiva por post | classificação reutilizável quando houver owner próprio |
| **Classificação de Conhecimento** | atributos reutilizáveis para filtro, agrupamento e governança | conteúdo editorial, ranking de busca |
| **Revisão e Governança** | estado de revisão, decisão humana, notas, responsável, histórico | conteúdo editorial, Search rules |
| **Content Extraction** | representação derivada read-only de WordPress/Elementor | qualquer dado canônico persistido |
| **Search Knowledge** | vocabulary, bindings e relevance rules | classificação editorial do post |
| **Search Indexing** | projections lexical/item/deep-link reconstruíveis | fonte editorial ou classificação canônica |
| **Search Quality** | Golden Queries, expectativas e evidência de regressão | dados editoriais |
| **Analytics / Search Intelligence** | eventos, interações, outcomes e métricas observacionais | conteúdo/metadata canônicos |
| **Core Configuration** | settings/versionamento técnico mínimo | domínio editorial |
| **Operações / Lifecycle** | jobs, migração temporária, diagnóstico e rollback | ownership permanente de dados migrados |
| **AI Assist** | sugestões/evidências não canônicas | autoridade de persistência editorial |

## 3. Fonte editorial — ownership absoluto

| Dado | Owner atual | Escritores atuais | Leitores | Owner futuro | Decisão |
|---|---|---|---|---|---|
| `post_title` | WordPress/editorial | autores/editores WP | GRE, KB2Ops, ASI | **Editorial WordPress/Elementor** | MANTER; nunca duplicar em meta |
| `post_content` | WordPress/editorial | autores/editores WP | ASI, KB2Ops fallback | **Editorial WordPress/Elementor** | MANTER read-only para plugin |
| `_elementor_data` | Elementor/editorial | Elementor | KB2Ops extractor; Search provisória KB2Ops | **Editorial WordPress/Elementor** | MANTER read-only; proibir write derivado |
| status/publicação | WordPress | fluxo editorial WP | GRE/KB2Ops/ASI | **Editorial WordPress** | MANTER |
| categorias/tags WP existentes | WordPress/editorial | fluxo editorial atual | ASI/KB2Ops | **WordPress/editorial** | não sequestrar ownership; consumir como sinal |

### Regra

Nenhum módulo do novo plugin recebe permissão arquitetural para corrigir, enriquecer ou reescrever o corpo do post. Sugestão de IA termina em proposta humana; a edição continua no fluxo oficial WordPress/Elementor.

## 4. Resumo Executivo e classificação — resolução de ownership

A principal colisão GRE ↔ KB2Ops ocorre porque o GRE armazena oito campos sob o rótulo “Resumo Executivo”, enquanto alguns deles também são usados como **classificação reutilizável** no KB2Ops.

### 4.1 Campos narrativos do Resumo Executivo

| Conceito | Persistência histórica | Owner atual | Owner futuro | Consumidores futuros | Estado de storage |
|---|---|---|---|---|---|
| objetivo | `_bdc_es_objective` | GRE | **Resumo Executivo** | UI, Search, IA, cards | postmeta é baseline forte; confirmar T056 |
| escalonamento | `_bdc_es_escalation` | GRE | **Resumo Executivo** | resolvedor, Search detail, IA | postmeta é baseline forte; confirmar T056 |
| informação importante | `_bdc_es_important` | GRE | **Resumo Executivo** | resolvedor, Search detail, IA | postmeta é baseline forte; confirmar T056 |

Esses campos descrevem o artigo de forma local e textual. Não existe evidência de benefício em transformá-los em classificação global.

### 4.2 Campos classificatórios presentes no GRE

| Conceito | Persistência histórica | Owner futuro | Observação |
|---|---|---|---|
| equipe responsável | `_bdc_es_responsible_team` | **Classificação de Conhecimento / Ownership Organizacional** | GAC pode enriquecer via adapter, mas não vira owner do core |
| item de catálogo | `_bdc_es_catalog_item` | **Classificação de Conhecimento** | conceito reutilizável; storage ainda aberto |
| serviço afetado | `_bdc_es_affected_service` | **Classificação de Conhecimento** | manter semântica “afetado”; não fundir automaticamente com serviço KB2Ops |
| sistemas envolvidos | `_bdc_es_systems_involved` | **Classificação de Conhecimento** | manter eixo “sistemas”; não fundir automaticamente com tecnologia |
| público-alvo | `_bdc_es_target_audience` | **Classificação de Conhecimento** | coincide semanticamente com audiência KB2Ops; deve haver um único conceito canônico |

O **Resumo Executivo pode editar/exibir** esses valores na mesma tela por conveniência de produto, mas não precisa ser seu owner lógico.

### 4.3 Campos classificatórios KB2Ops

| Conceito | Persistência histórica | Owner futuro | Colisão |
|---|---|---|---|
| tipo de conhecimento | `_kb2ops_knowledge_type` | **Classificação de Conhecimento** | nenhuma GRE direta |
| tecnologias/contexto | `_kb2ops_technologies` | **Classificação de Conhecimento** | relação parcial com `systems_involved`; manter distinto até profiling |
| audiência | `_kb2ops_target_audience` | **Classificação de Conhecimento** | **mesmo conceito lógico** de `_bdc_es_target_audience` |
| serviço | `_kb2ops_service` | **Classificação de Conhecimento** | próximo de `affected_service`, mas não comprovadamente idêntico |
| palavras-chave | `_kb2ops_keywords` | **Classificação de Conhecimento** | Search consome, mas não é owner |
| versões | `_kb2ops_versions` | **Classificação de Conhecimento** | atributo de contexto/versionamento funcional |

### Decisão D-006

D-006 deixa de ter ownership aberto:

- **audiência** terá um único owner: **Classificação de Conhecimento**;
- **serviço** e **serviço afetado** ficam no mesmo bounded context, porém como conceitos distintos até prova de equivalência;
- **tecnologias** e **sistemas envolvidos** ficam no mesmo bounded context, porém como eixos distintos até profiling de dados;
- o Resumo Executivo será consumidor/editor de composição, não uma segunda fonte da verdade para classificações compartilhadas;
- Search nunca será owner desses campos; apenas indexa/consulta.

**Ainda não decidido:** chaves finais, taxonomy versus postmeta, cardinalidade e migração. Isso pertence a T056 e ao plano de coexistência futuro.

## 5. Revisão e Governança

| Conceito | Persistência histórica | Owner futuro | Natureza |
|---|---|---|---|
| estado de revisão | `_kb2ops_review_state` | **Revisão e Governança** | canônico de workflow |
| notas de revisão | `_kb2ops_review_notes` | **Revisão e Governança** | canônico local/humano |
| revisado em | `_kb2ops_reviewed_at` | **Revisão e Governança** | evidência de workflow |
| revisado por | `_kb2ops_reviewed_by` | **Revisão e Governança** | referência a usuário WP |
| incluir em IA | `_kb2ops_include_ai` | **Revisão e Governança** | decisão humana explícita |
| histórico de revisão | `_kb2ops_review_history` | **Revisão e Governança** | histórico; mecanismo final T056/T095 |

### Regra de evento

`review approved` ou qualquer evento downstream só pode ser emitido após:

`validar -> persistir -> reler/confirmar -> emitir evento`.

O owner da revisão não pode emitir estado “aprovado” apenas porque recebeu um submit válido.

## 6. Estados derivados que não são fonte da verdade

| Dado derivado | Fontes | Owner da regra | Persistir? |
|---|---|---|---|
| `AI READY` | publish + approved + Resumo 8/8 + `include_ai` | **Revisão/Governança** | preferir cálculo/projection; não duplicar canônico |
| completude 0/8–8/8 | oito campos | **Resumo Executivo** | derivar; cache apenas se medido |
| scores KB2Ops | checklist/facts | **Qualidade de Conteúdo** | derivar; não tratar como verdade editorial |
| suggestions locais | facts/checklist | **Revisão/Governança** | derivar |
| excerpt/quick steps | Content Extractor | **Content Extraction** | derivar |
| facts estruturais | Content Extractor | **Content Extraction** | derivar/cache reconstruível |

A regra `AI READY` histórica do runtime KB2Ops é a baseline de regressão. Mudança de gates exige SPEC explícita.

## 7. Content Extraction

| Artefato | Origem | Owner futuro | Regra |
|---|---|---|---|
| HTML derivado | KB2Ops extractor | **Content Extraction** | read-only, reconstruível |
| texto canônico derivado | KB2Ops extractor | **Content Extraction** | única entrada textual para downstream |
| estrutura (headings/images/tables/shortcodes) | KB2Ops extractor | **Content Extraction** | única interpretação estrutural compartilhada |
| hash futuro de conteúdo extraído | ainda não existe | **Content Extraction** | derivado/versionado; útil para NO_CHANGE |

Search, Item Knowledge, Word Cloud, auditoria, chunking e IA **não podem** reabrir `_elementor_data` ou `post_content` por caminhos independentes.

## 8. Search Knowledge — dados canônicos próprios da busca

| Conceito ASI | Owner futuro | Natureza | Observação |
|---|---|---|---|
| vocabulary | **Search Knowledge** | canônico/manual | equivalências/variantes de consulta |
| term bindings | **Search Knowledge** | canônico/manual | termo -> alvo/intenção |
| relevance rules | **Search Knowledge** | canônico/manual | promote/demote governado |

Esses dados **não** são classificação editorial do post. São conhecimento sobre como recuperar/ordenar conteúdo e devem continuar separados de audiência, serviço, tipo etc.

## 9. Search Indexing — projections, nunca ownership editorial

| Projection | Owner futuro | Reconstruível? | Fonte |
|---|---|---:|---|
| post index | **Search Indexing** | SIM | extractor + metadata/classificação canônica |
| item/chunk lexical index | **Search Indexing** | SIM | extractor versionado |
| item identity/deep-link | **Search Indexing** | SIM | estrutura extraída + contrato de identidade |
| embeddings/vectors futuros | **Search Indexing / semantic projection** | SIM | chunks extraídos + versão de modelo |
| Word Cloud snapshot, se existir | **Search/Analytics projection** | SIM | índice/telemetria canônicos |

**Regra:** projection pode falhar ou ficar stale sem alterar o dado canônico que a gerou.

## 10. Search Quality

| Dado | Owner futuro | Natureza |
|---|---|---|
| Golden Query | **Search Quality** | canônico de QA/governança |
| expected targets/ranks/blocking | **Search Quality** | canônico de regressão |
| última evidência de execução | **Search Quality** | evidência derivada/versionada |
| diagnósticos de qualidade | **Search Quality** | derivado |

Golden Queries não pertencem ao Analytics e não devem ser tratadas como telemetria de usuário.

## 11. Analytics / Search Intelligence

| Representação histórica | Owner atual | Owner futuro | Destino lógico |
|---|---|---|---|
| ASI `search_events` | ASI Analytics | **Analytics / Search Intelligence** | fatos mínimos de execução |
| ASI `search_interactions` | ASI Analytics | **Analytics / Search Intelligence** | interação correlacionada, se habilitada |
| ASI outcomes | ASI Analytics | **Analytics / Search Intelligence** | resultado da jornada |
| KB2Ops `kb2ops_search_analytics` | KB2Ops | **Analytics / Search Intelligence** | **não manter como store paralelo** |
| KB2Ops `_kb2ops_view_count` | KB2Ops | **Analytics / Search Intelligence** | substituir por fato/contador coerente se necessário |
| ASI `quality_daily` | ASI | derivação de Analytics | não nascer inicialmente |
| coverage/review metrics | GRE/KB2Ops | domínio respectivo + Analytics como leitor | calcular de dados canônicos; não duplicar owner |

### Regra de privacidade

Analytics possui fatos observacionais, não “dados do post”. Deve ter política explícita de minimização, acesso e retenção. Query text não ganha direito automático de persistência.

## 12. Audit, operações e lifecycle

| Dado | Owner futuro | Observação |
|---|---|---|
| audit de mutação sensível | **Governança/Audit** | somente fatos necessários; não duplicar logs genéricos |
| index queue, se necessária | **Search Operations** | estado operacional; não canônico de negócio |
| migration checkpoints | **Operações/Migração** | temporários ao cutover/upgrade |
| runtime version | **Core Configuration/Lifecycle** | Options API quando necessária |
| settings | **Core Configuration**, com seções por domínio | Options/Settings API primeiro |
| purge/migration evidence | **Operações/Migração** | evidência limitada/retenção definida |

Migration adapter nunca se torna owner do dado migrado. Após cutover, o dado pertence ao domínio canônico correspondente.

## 13. Integrações organizacionais externas

Roles/tabelas GAC observadas no ASI pertencem ao **sistema externo**, não ao plugin unificado.

Se houver requisito real:

- criar adapter configurável;
- usar dados externos como enriquecimento/contexto;
- documentar freshness/falha;
- não tornar GAC requisito de boot, busca ou edição;
- não substituir valores locais canônicos silenciosamente.

## 14. IA e vetores

| Artefato | Owner | Canônico? |
|---|---|---:|
| sugestão de IA | **AI Assist** | NÃO |
| evidência/prompt/model/version da sugestão | **AI Assist/Audit**, se necessária | evidência, não editorial |
| alteração aprovada | owner do domínio alvo | SIM após ação humana/persistência confirmada |
| chunk | Search semantic projection | NÃO |
| embedding/vector | Search semantic projection | NÃO |

IA nunca é owner de `objective`, classificação, review state ou conteúdo Elementor.

## 15. Matriz resumida de ownership futuro

| Família de dados | Owner futuro |
|---|---|
| post/título/conteúdo/Elementor | WordPress/Elementor |
| objetivo/escalonamento/importante | Resumo Executivo |
| audiência/equipe/item catálogo/serviços/sistemas/tecnologias/tipo/keywords/versões | Classificação de Conhecimento |
| review state/notas/revisor/data/include AI/histórico | Revisão e Governança |
| texto/estrutura extraídos | Content Extraction — projection read-only |
| vocabulary/bindings/rules | Search Knowledge |
| post/item/vector indexes | Search Indexing — projections |
| Golden Queries | Search Quality |
| eventos/interações/outcomes | Analytics / Search Intelligence |
| settings | Core Configuration |
| queue/migração | Operações — estado transitório |
| sugestões IA | AI Assist — não canônico |

## 16. Decisões fechadas em T052

1. **Um conceito canônico = um owner lógico.**
2. Audiência GRE/KB2Ops é um único conceito do domínio de Classificação.
3. Serviço versus serviço afetado **não** será fundido sem prova semântica.
4. Tecnologia versus sistemas envolvidos **não** será fundido sem profiling.
5. Campos classificatórios podem aparecer na UI de Resumo, mas seu owner é Classificação.
6. Search consome classificação; Search não a possui.
7. Analytics substitui os stores leves duplicados de KB2Ops como owner observacional futuro; storage final ainda será decidido.
8. Índices/chunks/vetores nunca são fonte da verdade.
9. Migration/compatibility adapters nunca ganham ownership permanente.
10. GAC não é owner do core.

## 17. Decisões explicitamente abertas após T052

T052 **não** decidiu:

- taxonomy versus postmeta por atributo;
- nomes/chaves finais;
- cardinalidade mono/multivalor;
- profiling e mapeamento de valores existentes;
- revisions/histórico final;
- storage de vocabulary/bindings/rules/Golden;
- schema de índice/telemetria/queue;
- retenção de analytics;
- plano de migração/coexistência.

Esses itens seguem para T050/T056/T057/T095, nessa ordem de dependência.
