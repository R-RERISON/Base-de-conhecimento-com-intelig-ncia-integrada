# G-520 / T520 — Query Normalization Contract v1

**Status:** FROZEN  
**Normalizer version:** `search-normalizer-v1.0.0`

## Objetivo

Produzir uma representação lexical determinística, bounded e compatível com a normalização dos Search Documents, sem expansão semântica.

## Limites

- máximo de entrada: 256 caracteres Unicode;
- máximo de entrada: 1024 bytes UTF-8;
- máximo de tokens distintos: 16;
- máximo por token: 128 caracteres;
- limites excedidos => `invalid_query`; nunca truncar silenciosamente.

## Pipeline

1. aceitar somente string;
2. remover controles NUL/C0 incompatíveis;
3. decodificar entidades HTML;
4. `wp_strip_all_tags()`;
5. normalizar CR/LF/whitespace para espaço;
6. `trim`;
7. preservar essa forma em `original`;
8. `remove_accents()`;
9. lowercase UTF-8;
10. substituir tudo que não seja letra ASCII/dígito por espaço;
11. colapsar espaços;
12. gerar `tokens[]` únicos, preservando a primeira ocorrência.

## Saída

```json
{
  "original": "Windows 11",
  "normalized": "windows 11",
  "tokens": ["windows", "11"],
  "detected_type": "multi_token",
  "normalizer_version": "search-normalizer-v1.0.0"
}
```

`detected_type`:
- `single_token`;
- `multi_token`;
- `empty`.

## Invariantes

- sem stopword removal;
- sem stemming;
- sem fuzzy matching;
- sem spell correction;
- sem synonyms/aliases;
- sem equivalências hardcoded;
- sem IA;
- sem rede;
- sem persistência;
- dígitos são preservados;
- query original nunca é usada em SQL sem preparação.

## Compatibilidade documental

Search Document usa o mesmo perfil de normalização de caracteres. Os limites de 256/16 são exclusivos da query; documentos não são truncados por esses limites.

## Testes mínimos G-530

- acentos;
- caixa;
- whitespace;
- HTML;
- pontuação;
- números;
- query vazia;
- 257 caracteres;
- >16 tokens;
- determinismo em duas passagens.
