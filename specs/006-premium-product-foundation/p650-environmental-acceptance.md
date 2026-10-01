# P-650 — Install / Upgrade / Rollback Environmental Acceptance

**Status:** PREPARED  
**Build esperado:** `0.6.0-dev-p650.1`

## Pré-condições

- P640 local-only PASS;
- P650 local package validator PASS;
- usar exatamente o ZIP e SHA-256 produzidos pelo validador local;
- não reconstruir o ZIP depois do gate.

## Cenário A — fresh install

1. registrar baseline do WordPress/PHP;
2. instalar o ZIP candidato;
3. ativar;
4. confirmar ausência de fatal error;
5. confirmar carregamento de Core/Search/Public Preview/Word Cloud;
6. confirmar ausência de menus/runners de engenharia;
7. confirmar ausência de mutação editorial automática.

## Cenário B — upgrade

1. registrar versão/package anterior;
2. capturar fingerprints/contagens mínimas de dados;
3. atualizar usando o mesmo ZIP candidato;
4. validar ativação;
5. validar Search e jornadas administrativas;
6. comparar fingerprints/contagens;
7. confirmar ausência de rebuild/migração implícitos.

## Cenário C — rollback

1. reinstalar o package anterior;
2. reativar;
3. validar jornadas essenciais;
4. confirmar preservação dos dados.

## Critérios PASS

- instalação e ativação sem fatal;
- upgrade sem perda de dados;
- rollback funcional;
- Search/Public Preview/Word Cloud equivalentes;
- nenhum runner/smoke/profiler no package;
- nenhum `Engineering_Module_Loader` no runtime distribuído;
- nenhum `post_content` alterado automaticamente;
- nenhuma escrita em `_elementor_data`;
- nenhum plugin legado removido;
- sem cutover/retirement.

## Evidência

Registrar JSON com:
- ambiente;
- package SHA-256;
- versão anterior/nova;
- fresh_install;
- upgrade;
- rollback;
- fingerprints/contagens;
- módulos funcionais;
- mutações detectadas;
- erros.

O mesmo ZIP deve seguir para Plugin Check e demais gates.
