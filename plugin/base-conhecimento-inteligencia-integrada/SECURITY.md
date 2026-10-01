# Security Policy

## Supported development line

A linha ativa de desenvolvimento é 0.6.x até o fechamento da SPEC-006.

## Reporting

Vulnerabilidades devem ser reportadas de forma privada ao mantenedor do repositório. Não publique credenciais, dados pessoais, tokens, dumps de banco ou informações sensíveis em issues públicas.

## Baseline

O produto segue:
- capability checks;
- nonces em ações mutáveis;
- sanitização/escaping;
- mínimo privilégio;
- zero persistência de raw query por padrão;
- nenhum segredo no código-fonte.

P-660 consolidará o baseline de segurança e privacidade da SPEC-006.
