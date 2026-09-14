# SPEC-004 — Content Extractor e Knowledge Document

**Status:** Planejada  
**Pré-requisito:** SPEC-003 concluída.

## Problema
Busca, IA e vetores precisam de uma representação semântica confiável do post sem usar JSON/HTML bruto como conhecimento.

## Resultado esperado
Extrator read-only para Elementor/Gutenberg/HTML legado e uma projeção `Knowledge Document` reconstruível, versionada e baseada em hash.

## WordPress-first
Ler APIs e metadados do WordPress; renderização completa somente como fallback controlado.

## Princípio de negação
Knowledge Document não vira segundo CMS nem fonte editorial.

## Gate
Post real → extração semântica → projeção → hash → validação de conteúdo → confirmação de que `post_content` e `_elementor_data` permaneceram inalterados.
