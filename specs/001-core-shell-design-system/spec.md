# SPEC-001 — Core WordPress, Shell e Design System

**Status:** Planejada  
**Pré-requisito:** SPEC-000 concluída.

## Problema
O novo produto precisa existir como plugin WordPress instalável e visualmente coerente antes de receber domínios complexos.

## Resultado esperado
Bootstrap mínimo, lifecycle seguro, navegação única, Design System próprio inspirado no KB2Ops, página Visão Geral real, health básico e build local reproduzível.

## WordPress-first
Usar wp-admin como shell; Settings/Capabilities/Site Health e APIs nativas antes de infraestrutura própria.

## Princípio de negação
Nenhuma SPA, framework CSS, REST ou tabela própria nesta SPEC sem necessidade comprovada.

## Invariante editorial
Nenhuma manutenção de posts. Elementor permanece intocado.

## Gate
`instalar → ativar → navegar → validar saúde → desativar` sem erro e sem alteração editorial.
