# Package de homologação 0.1.0-dev.2

## Objetivo

Permitir a execução de T041B/T042/T043B em ambiente onde o operador não possui acesso ao `wp-config.php`.

## Ativação

O próprio package de homologação declara:

```php
define( 'BDC_KB_HOMOLOGATION_BUILD', true );
define( 'BDC_KB_ENABLE_DIAGNOSTICS', true );
```

Portanto, após instalar/ativar este build, as ferramentas temporárias aparecem automaticamente para usuários com `manage_options` na tela **Base de Conhecimento**.

Não há dependência de configuração externa ao plugin.

## Segurança

As ações temporárias continuam exigindo:

- usuário autenticado;
- `manage_options`;
- método POST;
- nonce próprio;
- fixtures marcadas e efêmeras;
- limpeza automática;
- JSON baixável sem persistência do relatório.

## Regra de remoção

Este é um build de homologação, não release.

T044/G-130 deve remover integralmente antes do package final:

- `BDC_KB_HOMOLOGATION_BUILD`;
- `BDC_KB_ENABLE_DIAGNOSTICS`;
- `class-diagnostics-runner.php`;
- `class-browser-acceptance.php`;
- requires e hooks temporários;
- qualquer fixture `_bdc_kb_diagnostic_fixture=spec001-onclick-v2`.

O package final não pode conter ferramentas onclick/browser de teste.
