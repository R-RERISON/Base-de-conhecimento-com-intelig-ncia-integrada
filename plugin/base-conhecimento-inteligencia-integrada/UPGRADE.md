# Upgrade

## Princípios

- promover primeiro em homologação;
- preservar dados e conteúdo editorial;
- não executar rebuild implícito como efeito colateral de upgrade;
- validar Search Projection e lifecycle após atualização;
- não remover plugins legados apenas por instalar uma nova versão do BDC;
- usar sempre o mesmo ZIP aprovado nos gates de package/install/upgrade;
- manter rollback por reinstalação do pacote anterior enquanto a SPEC-014 não autorizar retirement.

## 0.5.x -> 0.6.0-dev

A mudança inicia a Product Foundation e não autoriza:

- ASI retirement;
- troca da Home de produção;
- limpeza de storage legado;
- release 1.0.0;
- migração em massa;
- remoção do Elementor ou de metadata legada.

As projeções Search existentes devem permanecer preservadas.

### Procedimento recomendado

1. registrar versão instalada e checksum do pacote atual;
2. executar backup operacional conforme processo do ambiente;
3. instalar o ZIP candidato em homologação;
4. confirmar ativação sem fatal error;
5. validar Search, Public Experience Preview, Word Cloud e Knowledge Workspace;
6. confirmar que não houve alteração automática de `post_content`, `_elementor_data` ou dados legados;
7. executar smoke ambiental da SPEC ativa;
8. somente depois repetir a atualização no ambiente seguinte.

## Fresh install

Uma instalação nova deve:

- ativar sem exigir plugins legados;
- não executar rebuild pesado na ativação;
- não criar ou alterar conteúdo editorial;
- não disparar migração histórica automaticamente;
- registrar apenas hooks/capacidades pertencentes ao runtime distribuído.

## Rollback

Rollback de pacote:

1. desativar o pacote candidato se necessário;
2. reinstalar o ZIP previamente aprovado;
3. reativar;
4. validar Search e jornadas administrativas;
5. conferir que dados e conteúdo permanecem preservados.

P-650 não autoriza rollback destrutivo de dados porque seu escopo de packaging não possui migração irreversível.

## Package de produção

O source repository pode conter ferramentas de engenharia, mas o ZIP de produção deve excluir runners, smokes, profilers e acceptance harnesses.

O bootstrap distribuído não deve carregar nem referenciar `Engineering_Module_Loader`.

A construção do package é local e determinística; GitHub Actions não é executor de gate.
