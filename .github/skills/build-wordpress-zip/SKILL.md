# Skill — Build WordPress ZIP

**Nível:** Especialista  
**Experiência mínima representada:** 10 anos em release engineering, PHP/WordPress e automação de build.

## Objetivo

Gerar artefato instalável, determinístico e equivalente ao código validado.

## Regras de pacote

- uma única pasta na raiz;
- arquivo principal canônico;
- runtime apenas;
- sem `tests/`, `.github/`, `specs/`, tools ou documentação de engenharia;
- versão do header coincide com constante/readme;
- PHP lint passa após extrair o ZIP;
- JS syntax passa quando houver;
- checksum SHA-256 registrado.

## Procedimento

1. Validar versão.
2. Validar arquivos obrigatórios.
3. Copiar runtime para staging limpo.
4. Empacotar em ordem determinística quando possível.
5. Inspecionar conteúdo do ZIP.
6. Extrair em diretório temporário.
7. Rodar lint/checks sobre o extraído.
8. Calcular SHA-256.
9. Registrar evidência no release gate.

## Gate

O código testado, código publicado e código empacotado devem ser semanticamente idênticos; divergência bloqueia release.