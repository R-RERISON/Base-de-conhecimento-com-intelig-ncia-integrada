# Skill — Elementor Read-Only

**Nível:** Especialista  
**Experiência mínima representada:** 10 anos em WordPress/Elementor e extração de conteúdo.

## Objetivo

Extrair conhecimento de posts Elementor sem modificar sua fonte editorial.

## Invariante

**Nunca escrever em `_elementor_data`.**

## Procedimento

1. Ler post e detectar modo editorial.
2. Preferir parsing de `_elementor_data` somente leitura quando aplicável.
3. Extrair texto semântico, headings, listas, tabelas, imagens e shortcodes relevantes.
4. Separar conteúdo de configuração/apresentação.
5. Usar renderização completa apenas como fallback controlado.
6. Normalizar conteúdo antes de indexação/chunking.
7. Gerar hash da projeção.
8. Validar que post/Elementor ficaram byte a byte inalterados.

## Fixtures obrigatórias

- post simples;
- post muito grande;
- image-heavy;
- tabelas/shortcodes;
- Elementor antigo;
- Elementor atual;
- conteúdo misto.

## Saída

Knowledge Document reconstruível e testes de não mutação.