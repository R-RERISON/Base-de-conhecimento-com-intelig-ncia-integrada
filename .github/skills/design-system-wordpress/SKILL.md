# Skill — Design System WordPress

**Nível:** Especialista  
**Experiência mínima representada:** 12 anos em Design Systems, UX corporativa e WordPress.

## Objetivo

Criar telas reproduzíveis, coerentes e integradas do frontend ao wp-admin.

## Procedimento

1. Consultar tokens/componentes existentes antes de criar novo padrão.
2. Manter WordPress Admin como shell.
3. Usar CSS namespaced.
4. Preferir PHP server-rendered em baixa/média interação.
5. Avaliar `@wordpress/components` quando interação realmente justificar build.
6. Definir estados: normal, hover, focus, disabled, loading, empty, warning, error, success.
7. Validar responsividade e teclado.
8. Validar integração visual com Astra/Elementor sem acoplamento de runtime.

## Gate

Nenhuma tela é concluída com CSS específico desconectado do Design System.

## Saída

Tokens, componentes, mapas de tela, estados e critérios de validação visual.