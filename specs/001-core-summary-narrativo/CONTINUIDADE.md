# Prompt de Continuidade — SPEC-001 em implementação

## Referência versionada

- Repositório: `R-RERISON/Base-de-conhecimento-com-intelig-ncia-integrada`
- Branch: `main`
- SPEC ativa: `SPEC-001 — Core mínimo + Summary narrativo`
- Estado: **Em implementação / Evidência S003**.
- Confirme o HEAD atual antes de qualquer nova alteração.

## Estado comprovado

- SPEC-000 concluída.
- T097 autorizou SPEC-001, não release/produção/cutover.
- S001/Definition of Ready PASS documental.
- Runtime mínimo S002 implementado.
- T040 unitário PASS 15/15.
- **T040A instalação inicial/smoke em WordPress real: PASS parcial.**
- Plugin `Base de Conhecimento com Inteligência Integrada` 0.1.0-dev instalado e ativo no ambiente alvo.
- Menu `Base de Conhecimento` presente e tela principal carregada sem fatal error.
- Listagem de posts reais funcionando.
- Artigo real `Mensageria` (post ID 36431) aberto em modo de leitura/edição.
- Os três metadados preexistentes foram lidos corretamente pelo novo plugin, comprovando compatibilidade de leitura sem migration para `objective`, `escalation` e `important`.
- Nenhum save real pelo novo plugin foi executado nesta evidência inicial.
- Harness PHPUnit WordPress real: PREPARADO; execução NOT_RUN.
- Runner onclick técnico v2: IMPLEMENTADO; execução no ambiente alvo NOT_RUN.
- Browser acceptance guiado com JSON: IMPLEMENTADO; execução manual NOT_RUN.
- G-001/G-020/G-070/B-006 integração/G-110/G-130 continuam sem promoção completa.

## Runtime atual

`plugin/base-conhecimento-inteligencia-integrada/`

Arquivos de produto:
- `base-conhecimento-inteligencia-integrada.php`
- `includes/class-plugin.php`
- `includes/class-meta-contract.php`
- `includes/class-summary-store.php`
- `includes/class-admin-page.php`
- `assets/css/admin.css`

Arquivos temporários de homologação:
- `includes/class-diagnostics-runner.php`
- `includes/class-browser-acceptance.php`

Eles só são carregados quando:

```php
define( 'BDC_KB_ENABLE_DIAGNOSTICS', true );
```

Sem a flag, nenhum hook/botão/painel temporário é registrado.

## Contrato funcional

Usuário: Analista de Conhecimento.

Jornada: selecionar `post` -> ler -> editar -> salvar -> reler -> confirmar Summary.

Campos:
- `objective` -> `_bdc_es_objective`
- `escalation` -> `_bdc_es_escalation`
- `important` -> `_bdc_es_important`

Somente `post` está suportado pela SPEC-001.

## B-006 implementado

Fluxo:

`authorize -> validate all -> sanitize all -> snapshot -> diff -> writes mínimos -> reread -> compare`

Resultados:
- estado esperado -> `SUCCESS`;
- snapshot restaurado -> `FAIL_SAFE`;
- restauração incompleta -> `PARTIAL_FAILURE_CRITICAL`.

## Onclick técnico v2

- schema JSON `1.1.0`;
- marker `_bdc_kb_diagnostic_fixture=spec001-onclick-v2`;
- exige `manage_options` + nonce;
- cria apenas fixtures próprias e efêmeras;
- cleanup em lotes;
- resultado global PASS exige zero FAIL e zero resíduos;
- cobre G-001, G-020, parte de G-070 e B-006;
- não persiste relatório.

## Browser acceptance temporário

- usa a mesma flag;
- exige `manage_options` + nonce;
- coleta oito checks G-110;
- gera JSON sem persistência;
- é observação manual guiada, não automação E2E.

## Próximo passo exato no ambiente alvo

1. **Não salvar ainda um artigo real como primeira prova de write.**
2. Habilitar temporariamente `BDC_KB_ENABLE_DIAGNOSTICS=true` no ambiente de homologação.
3. Abrir `Base de Conhecimento`.
4. Executar `Executar diagnóstico e gerar JSON`.
5. Exigir `summary.overall=PASS` e `cleanup.residual_fixtures=0`.
6. Desabilitar a flag imediatamente se houver FAIL inesperado e analisar antes de continuar.
7. Se o diagnóstico técnico passar, executar a jornada controlada de Summary e o browser acceptance G-110.
8. Gerar o segundo JSON de browser acceptance.
9. Desabilitar/remover a flag ao fim da coleta.
10. Versionar/revisar os JSONs antes de promover gates.

## Regra de limpeza antes do release

T044/G-130 deve remover integralmente:
- `class-diagnostics-runner.php`;
- `class-browser-acceptance.php`;
- requires condicionais;
- hooks temporários;
- qualquer fixture `spec001-onclick-v2`.

Depois repetir lint/regressão e comprovar ausência dessas ferramentas no package final.

## Fora de escopo

Classificação; Review/AI READY; Extractor; Search; Analytics; queue; tabela/schema/migration; REST/AJAX/SPA; Foundry/LLM/vector; aliases legados; cutover produtivo.

## Regra

Constituição, Manifesto, T097, SPEC-001, código e evidências versionadas prevalecem sobre memória de chat. Se o HEAD divergir, investigue antes de escrever.
