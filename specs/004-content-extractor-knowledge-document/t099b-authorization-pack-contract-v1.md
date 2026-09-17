# T099B — Authorization Pack Contract v1

## Objetivo

Selecionar deterministicamente **um único artigo de baixo risco** para o primeiro canário Block Migration e produzir um pacote de autorização humano, sem mutação de estado.

## Status

IMPLEMENTADO / HOMOLOGAÇÃO PENDENTE.

## Seleção

O candidato precisa simultaneamente:

- `source_kind=legacy_html`;
- `Block_Migration_Dry_Run.dry_run_status=ready`;
- zero `_bdc_kb_block_migration_journal` residual;
- lock `_bdc_kb_block_migration_lock` livre;
- `_elementor_data` vazio;
- `post_content` entre 1 e 30.000 bytes;
- zero shortcode registrado detectado;
- zero `script`, `iframe`, `form`, `object`, `embed` ou `style`;
- zero comentário `<!-- wp:`;
- até 10 links;
- até 1 imagem;
- até 1 tabela.

O post 358, utilizado com sucesso no T099A, é preferido somente se continuar satisfazendo todos os critérios. Caso contrário, seleciona-se o menor `risk_score`, com desempate por `post_id`.

## Conteúdo do Authorization Pack

- identificação humana do artigo (`post_id`, título, status);
- `source_kind` e perfil de risco agregado;
- `fidelity_hash_before`;
- `serialization_hash`;
- `dry_run_hash`;
- SHA-256 de `post_content` e `_elementor_data` antes;
- SHA-256 esperado do `post_content` serializado;
- block names esperados;
- `authorization_id` determinístico vinculado ao candidato e hashes;
- operação exata planejada para T099C;
- precondições de journal, lock, stale recheck e verificação;
- rollback imediato obrigatório;
- texto sugerido de autorização específica.

## Segurança

T099B é estritamente read-only:

- não persiste journal;
- não adquire lock;
- não escreve `post_content`;
- não escreve `_elementor_data`;
- não executa shortcode;
- não renderiza blocks;
- não chama rede externa;
- não exporta corpo editorial nem URLs.

O título pode ser exportado apenas para identificação humana do único candidato; o corpo editorial nunca é incluído.

## Gate

PASS somente quando:

- existe candidato low-risk válido;
- recheck final continua `ready`;
- journal count permanece 0;
- lock permanece `free`;
- `authorization_id` é calculado;
- `gate_result.t099b_authorization_pack_pass=true`.

## Consequência

T099B PASS **não autoriza T099C**. A mutação do canário exige aprovação humana explícita que cite o `post_id` e o `authorization_id` gerados pelo pack.
