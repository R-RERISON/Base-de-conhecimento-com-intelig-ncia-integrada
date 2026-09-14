# Skill — Migração e Rollback

**Nível:** Especialista  
**Experiência mínima representada:** 12 anos em modernização, migração de dados e cutover seguro.

## Objetivo

Mover comportamento/dados sem tornar a instalação irreversível.

## Procedimento

1. Fixar baseline e backup.
2. Identificar fonte da verdade.
3. Preferir coexistência a big-bang.
4. Projetar migração aditiva/idempotente.
5. Definir checkpoints para operações longas.
6. Separar PREPARE de VALIDATE.
7. Não limpar legado durante activation.
8. Definir rollback antes do GO.
9. Validar repetição e upgrade parcial.
10. Limpar legado somente após aceite explícito.

## Estados recomendados

`pendente | executando | pausado | bloqueado | falhou | concluído_com_condições | concluído`.

## Saída

Plano de migração, checkpoints, rollback e relatório de cutover.