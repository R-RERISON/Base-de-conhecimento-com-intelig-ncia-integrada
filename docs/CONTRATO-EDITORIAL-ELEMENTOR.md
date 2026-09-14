# Contrato Editorial — WordPress / Elementor / Plugin

Este documento existe para evitar uma regressão conceitual grave durante a evolução do produto.

## Regra central

> **O novo plugin gerencia o conhecimento ao redor do post. Ele não faz a manutenção editorial do post.**

## Elementor continua responsável por

- criar conteúdo;
- alterar conteúdo;
- layout editorial;
- widgets;
- imagens no corpo do artigo;
- publicação/atualização editorial pelo usuário;
- estrutura visual do artigo oficial.

## O plugin pode

- listar posts;
- filtrar/segmentar posts;
- mostrar status de revisão;
- gerenciar Resumo Executivo;
- gerenciar classificação/taxonomias próprias;
- avaliar qualidade;
- extrair conteúdo somente leitura;
- criar Knowledge Document derivado;
- criar índice/chunks/embeddings;
- sugerir melhorias;
- identificar obsolescência;
- indexar para busca;
- medir uso;
- abrir o editor oficial do post quando o usuário quiser alterar conteúdo.

## O plugin não pode

- escrever em `_elementor_data`;
- substituir Elementor;
- editar texto editorial dentro do Knowledge Studio como fonte oficial;
- publicar post automaticamente;
- injetar correções de IA diretamente no conteúdo;
- manter uma cópia editorial concorrente.

## Fluxo correto quando IA sugere mudança de conteúdo

```text
IA detecta oportunidade
        ↓
Knowledge Studio mostra sugestão
        ↓
Analista avalia
        ↓
[Abrir no Elementor]
        ↓
Analista altera conteúdo oficial
        ↓
WordPress salva/publica
        ↓
plugin detecta mudança
        ↓
recalcula projeções necessárias
```

## Teste obrigatório de não mutação

Qualquer componente que leia Elementor deve possuir teste que prove que a execução não alterou:

- `_elementor_data`;
- `post_content`;
- `post_status`;
- data de publicação por efeito colateral;
- revisões editoriais por simples leitura/análise.

## Exceção

Nenhuma exceção é prevista. Alterar esta fronteira exige emenda constitucional explícita.