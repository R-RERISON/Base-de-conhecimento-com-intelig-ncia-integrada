# Evidência S003 — Runtime classificatório

## Build

Versão: `0.2.0-dev.1`.

Runtime acrescentado sobre a baseline `0.1.0-rc.1`:

- `Classification_Contract`;
- `Classification_Store`;
- `Classification_Admin`;
- quatro taxonomias canônicas namespaced;
- formulário/handler de classificação separado do Summary;
- referência legada somente leitura.

## PHP lint

Resultado local do runtime: **8/8 PHP PASS**.

Arquivos lintados:

- bootstrap;
- Admin Page;
- Classification Admin;
- Classification Contract;
- Classification Store;
- Meta Contract;
- Plugin;
- Summary Store.

## Unitário determinístico Classification Store

Resultado: **15 PASS / 0 FAIL**.

Casos exercitados:

1. contrato exato das quatro taxonomias;
2. read side-effect free;
3. update parcial preserva conceitos omitidos;
4. unknown field falha com zero writes;
5. termo inexistente falha com zero writes;
6. termo fora da taxonomy esperada falha antes do write;
7. conceito single rejeita múltiplos IDs;
8. array vazio remove relações;
9. NO_CHANGE gera zero writes;
10. falha no primeiro write termina `FAIL_SAFE`;
11. falha no segundo write compensa o primeiro;
12. falha da compensação termina `PARTIAL_FAILURE_CRITICAL`;
13. post type `page` é rejeitado;
14. capability por objeto é obrigatória;
15. IDs são deduplicados/ordenados antes do diff.

## Contratos comprovados no código

- nenhuma criação implícita de termos;
- nenhuma gravação em `_bdc_es_*` ou `_kb2ops_*`;
- nenhuma gravação editorial;
- `show_in_rest=false`;
- meta boxes e quick edit de assignment desabilitados;
- Summary permanece em handler próprio;
- Classification usa POST/nonce/capability/read-after-write/compensação.

## Estado do gate

Esta evidência autoriza somente o **primeiro smoke em WordPress real**. G-001/G-030/G-070/G-110 continuam NOT_RUN para o runtime SPEC-002 até homologação no ambiente alvo.