# G-240 — Real Content Acceptance em homologação

Build temporário: `0.4.0-acceptance.1`.

## Objetivo

Validar visualmente, em conteúdo real, se a fonte editorial foi representada de forma fiel pelo Knowledge Document.

Este teste não mede somente determinismo: ele procura omissão, ordem incorreta, texto inventado e perda estrutural.

## Procedimento

1. Instalar/substituir o plugin pelo package `0.4.0-acceptance.1`.
2. Confirmar a versão na tela de plugins.
3. Confirmar que Profiler SPEC-004, Smoke G-220 e Smoke G-230 não aparecem.
4. Abrir **Base de Conhecimento → Aceitação G-240**.
5. A ferramenta percorre o corpus apenas para selecionar deterministicamente a amostra e materializa conteúdo completo somente dos slots selecionados.
6. Para cada categoria disponível, comparar a coluna **Fonte editorial** com **Knowledge Document**.
7. Marcar cada critério somente se estiver objetivamente correto:
   - Cobertura completa;
   - Ordem semântica preservada;
   - Nenhum texto inventado;
   - Estrutura adequada;
   - Aceitável para busca/IA.
8. Se houver qualquer divergência, deixar o critério correspondente desmarcado e escolher a reason enum mais próxima. **Não force PASS.**
9. Evitar editar os posts selecionados enquanto a tela estiver aberta. Se ocorrer alteração, o stale guard invalidará o item.
10. Clicar **Gerar evidência G-240 (JSON)**.
11. Retornar `bdc-kb-spec004-g240-acceptance-*.json`.

## Segurança

- `manage_options`;
- POST + nonce;
- nenhuma persistência de seleção/veredictos;
- nenhuma execução de shortcode;
- nenhum `render_block()`;
- nenhuma renderização Elementor;
- nenhum writer editorial;
- nenhuma IA/rede externa;
- o conteúdo é mostrado somente no wp-admin local;
- o JSON não exporta `post_content`, `_elementor_data`, sections/texto, títulos ou URLs;
- o JSON inclui post IDs apenas para rastreabilidade da amostra.

## Slots esperados

Até oito categorias:

- Elementor nativo típico;
- Elementor/Mixed complexo;
- Legacy HTML típico;
- Legacy HTML complexo;
- Gutenberg;
- Shortcode/Tabela;
- Review Required;
- Vazio/Corrompido.

Categoria inexistente fica explicitamente `not_available`.

## Gate esperado no JSON

- `acceptance.selection_mismatches = 0`;
- `acceptance.stale_slots = 0`;
- `acceptance.repeatability_failures = 0`;
- `acceptance.reviewed_slots = acceptance.expected_available_slots`;
- `acceptance.passed_slots = acceptance.expected_available_slots`;
- `acceptance.gate_pass = true`;
- `safety.editorial_fingerprint_equal = true`;
- `safety.changed_posts_during_report_generation = 0`.

Uma falha humana é resultado válido do teste e bloqueia G-240 até correção; ela não deve ser mascarada.
