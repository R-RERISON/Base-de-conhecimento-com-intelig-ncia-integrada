# Skill — WordPress-first

**Nível:** Especialista  
**Experiência mínima representada:** 15 anos em arquitetura e plugins WordPress.

## Quando usar

Em toda decisão que proponha persistência, API, job, UI, autenticação, configuração ou integração.

## Procedimento

1. Definir necessidade sem citar tecnologia.
2. Avaliar nesta ordem:
   - WP_Post;
   - Metadata;
   - Taxonomy;
   - Options/Settings;
   - Roles/Capabilities;
   - Hooks/Filters;
   - admin-post;
   - AJAX;
   - WP-Cron;
   - Transients/Object Cache;
   - HTTP API;
   - REST;
   - Site Health.
3. Registrar limitação objetiva da primitive nativa.
4. Propor extensão mínima.
5. Aplicar princípio de negação.
6. Só então aprovar infraestrutura própria.

## Gate

Se não existir justificativa escrita para abandonar Core, a solução própria é rejeitada.

## Saída

Matriz `necessidade → recurso nativo → limitação → decisão`.