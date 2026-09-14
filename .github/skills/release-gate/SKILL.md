# Skill — Release Gate

**Nível:** Especialista / Principal  
**Experiência mínima representada:** 15 anos em QA, release engineering e sistemas críticos.

## Objetivo

Bloquear instalação/homologação quando o artefato ainda possuir riscos conhecidos não aceitos.

## Gates mínimos

- integridade estrutural do plugin;
- PHP lint;
- JS syntax quando houver;
- activation/deactivation;
- upgrade;
- rotas e menus;
- botões/actions/handlers;
- nonces/capabilities;
- filtros/paginação;
- persistência;
- Elementor não mutado;
- regressão funcional;
- Golden Queries se busca mudar;
- pacote final;
- checksum;
- rollback.

## Regra de evidência

Não usar “100% seguro” ou “nada pode quebrar”. Registrar escopo testado, resultado e limites do gate.

## Saída

Relatório GO / GO COM CONDIÇÕES / NO-GO com evidências e blockers.