# Current State — UX-002

## Baseline

`main`: `72f26121373b12fa08f08ea8d38b4c8d73f8637c`.

O commit adicionou 10 referências PNG em `scr/` sem alterar runtime.

## Diagnóstico

UX-001 já está fechado e contém Design System v1 + protótipo UI-as-Code. O runtime atual já usa parte dos tokens, porém ainda apresenta drift:

- page title e tabelas ainda carregam forte leitura visual de wp-admin;
- controles `.button`, `.widefat`, notices e campos mantêm aparência nativa em vários estados;
- shell do Workspace aproxima o protótipo, mas a linguagem não é suficientemente dominante/consistente;
- inexistia regra constitucional explícita dizendo que os mockups atuais devem chegar ao runtime.

## Decisão

Criar UX-002 como slice visual transversal, sem alterar domínio/persistência. A primeira entrega modifica CSS compartilhado e build de homologação; novas telas futuras devem nascer sob o mesmo contrato.

## Estado

- Constituição 1.2.0: preparada nesta branch;
- Visual Contract v2: criado;
- CSS foundation: próxima etapa do mesmo slice;
- homologação humana contra `scr/`: pendente;
- merge: bloqueado até revisão visual/responsiva.
