# P-610 — WPCS Legacy Debt Inventory

**Data:** 2026-09-29  
**Resultado:** dívida histórica quantificada; não aplicar autofix global.

## Execução

Full-plugin PHPCS/WPCS audit:
- arquivos analisados: 121;
- errors: 11,718;
- warnings: 7,118;
- total findings: 18,836;
- autofixable findings: 16,191;
- PHPCS exit: 2.

## Interpretação

O resultado não representa 18.836 defeitos funcionais. Ele combina:
- estilo/formatação histórica;
- documentação ausente;
- padrões WordPress aplicados a runners/CLI;
- uso direto de APIs PHP onde WPCS prefere wrappers WordPress;
- escaping/capability conventions;
- classes de laboratório que não devem necessariamente existir no ZIP final.

## Regra de tratamento

**PROIBIDO PHPCBF global.**

Motivo:
- alterações massivas sem semântica de produto;
- risco de mudar arquivos fechados por SPEC-001–005;
- muitos runners serão excluídos do package em P-640/P-650;
- primeiro reduzir a superfície production, depois corrigir WPCS apenas no código que realmente permanecer.

## Estratégia

1. P-640 modulariza o runtime e identifica classes production.
2. P-650 define allowlist do ZIP final e exclui laboratório.
3. WPCS passa a ser obrigatório para:
   - novos arquivos;
   - arquivos modificados;
   - production allowlist final.
4. P-670 não fecha enquanto a superfície production remanescente não tiver disposição WPCS explícita.

## Baseline

A nova suíte da SPEC-006 está WPCS-clean e PHPStan level 5 clean.
