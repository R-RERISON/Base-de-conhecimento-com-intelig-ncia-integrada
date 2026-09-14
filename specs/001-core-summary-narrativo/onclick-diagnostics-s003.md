# S003 — Diagnóstico onclick temporário com JSON

> Estado: **IMPLEMENTADO COMO FERRAMENTA TEMPORÁRIA — desabilitada por padrão**.  
> Objetivo: permitir execução assistida no WordPress real sem transformar testes em funcionalidade permanente.

## Regra de ativação

O runner só é carregado quando existir explicitamente no ambiente:

```php
define( 'BDC_KB_ENABLE_DIAGNOSTICS', true );
```

Sem essa constante, o arquivo de diagnóstico não é carregado, nenhum hook é registrado e nenhum botão aparece.

A execução exige `manage_options` e nonce próprio.

## Fluxo onclick

1. ativar temporariamente `BDC_KB_ENABLE_DIAGNOSTICS=true`;
2. abrir **Base de Conhecimento** no `wp-admin`;
3. clicar **Executar diagnóstico e gerar JSON**;
4. o runner cria somente fixtures efêmeras marcadas;
5. executa checks reais de G-001/G-020/G-070 e fault injection B-006;
6. remove filtros temporários em `finally`;
7. remove posts/metadados de teste com `wp_delete_post(..., true)`;
8. verifica resíduos;
9. devolve `bdc-kb-diagnostics-YYYYmmdd-HHMMSS.json` sem persistir o relatório no WordPress;
10. remover/desativar imediatamente a flag após a coleta.

## Limpeza automática

Marker exclusivo:

`_bdc_kb_diagnostic_fixture = spec001-onclick-v1`

Antes de cada execução, o runner remove fixtures antigas que tenham esse marker. Ao final, todas as fixtures criadas na execução são hard-deleted em `finally`.

O JSON contém `cleanup.residual_fixtures`. O resultado global só pode ser `PASS` quando:

- nenhum check falha; e
- `cleanup.residual_fixtures == 0`.

Não são criados options, transients, tabelas, cron, usuários, taxonomias ou logs com conteúdo narrativo.

## Cobertura do JSON

- fingerprint SHA-256 do runtime relevante;
- versão WordPress/PHP/plugin;
- preservação de `post_title`, `post_content` e `_elementor_data`;
- update parcial;
- empty/delete;
- allowlist antes de write;
- NO_CHANGE sem write;
- sanitização anti-XSS;
- Unicode/multiline/backslash;
- capability por objeto/IDOR;
- rejeição de `page`;
- nonce válido/inválido na primitive vinculada ao post;
- B-006 `FAIL_SAFE`;
- B-006 `PARTIAL_FAILURE_CRITICAL`;
- resultado de cleanup.

## Limitações

Este onclick **não substitui G-110/browser acceptance**. O nonce é verificado in-process na primitive WordPress; o fluxo HTTP completo do handler continua sujeito ao browser acceptance.

## Gate de remoção

Antes de T044/G-130/package/release é obrigatório:

1. remover `BDC_KB_ENABLE_DIAGNOSTICS` do ambiente;
2. remover `includes/class-diagnostics-runner.php` do runtime distribuível;
3. remover o require condicional do bootstrap;
4. remover o registro condicional de `Diagnostics_Runner`;
5. confirmar zero posts com `_bdc_kb_diagnostic_fixture=spec001-onclick-v1`;
6. executar PHP lint e regressão novamente após a remoção.

O package final da SPEC-001 não pode conter botão onclick de teste nem runner diagnóstico temporário.
