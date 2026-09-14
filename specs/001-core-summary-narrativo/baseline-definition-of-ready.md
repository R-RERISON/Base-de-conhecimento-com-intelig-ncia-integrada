# Baseline e Definition of Ready — SPEC-001

> Resultado: **PASS DOCUMENTAL — runtime pode começar em bloco posterior; release/produção/cutover continuam NÃO autorizados.**

## 1. HEAD/branch

Baseline de entrada confirmada: `main @ fced4a6015638b585d8817485fce8ef0fb8d7ccb`.

## 2. Autoridade documental

Lidos e confrontados: AGENTS, PROJECT_MANIFEST, Constituição, T095, T096, T097, DoD e CONTINUIDADE da SPEC-000.

Não foi encontrada contradição entre T095/T096/T097 quanto ao escopo da nova SPEC.

## 3. Divergência investigada

Foram encontrados placeholders antigos:

- `specs/001-core-shell-design-system/`;
- `specs/002-resumo-executivo-integrado/`.

Eles antecedem T097 e estavam apenas `Planejada`. T097 é decisão canônica posterior e reduz o primeiro slice. Eles são preservados para rastreabilidade, mas marcados como supersedidos e não executáveis.

## 4. Post types reais comprovados

### Evidência forte

GRE 0.6.0:

- Meta Contract fixa `POST_TYPE = 'post'`;
- Summary Store rejeita qualquer post cujo `post_type !== 'post'`.

### Evidência corroborante

KB2Ops e ASI operam sobre posts identificados por post ID e tratam o corpus de Base de Conhecimento como conjunto de posts.

### Decisão

SPEC-001 suporta exclusivamente `post`.

`page` e CPTs ficam fora até evidência e alteração formal. Esta prova de baseline habilita desenvolvimento/homologação, mas não substitui B-003 antes de produção/cutover.

## 5. Matriz de Mutação

Criada em `matriz-mutacao.md` com ator, capability, método, nonce, validação, persistência, confirmação e diagnóstico.

## 6. Matriz de Evidência

Criada em `matriz-evidencia.md` cobrindo G-001, G-020, G-070, G-110, G-130 e B-006.

Os testes ainda estão `NOT_RUN` porque não existe runtime. Isso não é PASS de evidência e bloqueará Homologação/Concluída; não bloqueia o início da implementação após este DoR documental.

## 7. Contratos dos campos

Fixados:

- string multiline;
- máximo 32768 bytes por campo;
- unknown/type/limit inválido rejeita o request inteiro antes de write;
- `wp_unslash` + `trim(sanitize_textarea_field())`;
- vazio sanitizado = delete;
- omitido = preservar;
- igual = NO_CHANGE;
- nunca truncar silenciosamente.

## 8. Fault injection B-006

Casos obrigatórios:

1. falha no primeiro write -> snapshot intacto;
2. falha no segundo após primeiro sucesso -> compensação restaura primeiro;
3. falha no terceiro após dois sucessos -> compensação restaura ambos;
4. falha em delete -> mismatch detectado;
5. falha durante compensação -> `PARTIAL_FAILURE_CRITICAL` + reread final;
6. `update_post_meta=false` por NO_CHANGE não vira falha falsa;
7. request inválido em qualquer campo -> zero writes;
8. mistura update+delete -> estado final integral confirmado.

## 9. UI/UX mínima / browser acceptance

### Tela

- integrada ao wp-admin;
- sem segunda sidebar;
- listagem paginada de `post` com seleção;
- editor com três textareas e título read-only;
- mensagens de sucesso/erro textuais;
- sem JS obrigatório.

### Browser acceptance

- abrir a página não gera write;
- paginação/seleção funciona;
- direct URL sem `edit_post` é bloqueada;
- salvar válido usa POST e redireciona para GET;
- reload não reenvia POST;
- releitura mostra estado persistido;
- vazio remove a meta;
- erro preserva contexto seguro sem refletir payload perigoso;
- labels/foco/teclado básicos passam;
- viewport administrativo estreito permanece utilizável;
- cor não é único sinal de estado.

## 10. Aceite/não aceite/rollback

Definidos em `spec.md`. Rollback: desativar o novo plugin; postmeta é preservado; sem purge/migration/schema.

## Checklist DoR

- [x] problema definido;
- [x] baseline existe;
- [x] usuário/fluxo claros;
- [x] WordPress-first avaliado;
- [x] alternativa simples escolhida;
- [x] dados mapeados;
- [x] target `post` comprovado;
- [x] matriz de mutação criada;
- [x] matriz de evidência criada;
- [x] contratos dos campos fixados;
- [x] fault injection definido;
- [x] UI/browser acceptance descrita;
- [x] aceite/não aceite definidos;
- [x] rollback definido;
- [x] fora de escopo explícito.

# Decisão DoR

**PASS — SPEC-001 está PRONTA para iniciar runtime mínimo em um próximo bloco controlado.**

Isto não é PASS de testes, Homologação, release nem produção.
