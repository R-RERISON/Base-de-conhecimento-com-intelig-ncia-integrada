# UX-001 — Estratégia UI as Code

**Status:** ATIVA  
**Decisão:** Figma deixa de ser dependência operacional da UX-001.  
**Motivo:** o projeto não deve depender de limites de MCP, planos pagos ou ferramentas externas para avançar.

## 1. Nova abordagem canônica

A baseline visual passa a ser **UI as Code**: protótipos HTML/CSS/JS executáveis, versionados no mesmo repositório e alinhados aos contratos reais do plugin WordPress.

O protótipo deve:

- abrir diretamente no navegador;
- não exigir npm, build, servidor, conta externa ou licença;
- reutilizar tokens do Design System v1;
- representar somente contratos atuais ou hipóteses explicitamente rotuladas;
- suportar desktop largo, 782px e mobile de contingência;
- permitir navegação por teclado e inspeção de foco;
- servir como contrato visual para implementação futura no plugin;
- permanecer separado do runtime até SPEC de implementação autorizada.

## 2. Fonte de verdade da UX

Ordem de autoridade:

1. contratos funcionais das SPECs;
2. documentação UX-001;
3. protótipos UI as Code versionados;
4. evidência KB2Ops/Heritage Pack;
5. screenshots exportados do protótipo;
6. preferência estética.

Figma passa a ser somente **artefato histórico/arquivado**. Nenhum gate depende dele.

## 3. Estrutura de artefatos

Diretório canônico:

`ux/001-product-experience-knowledge-workspace/prototype/`

Arquivos previstos:

- `index.html` — protótipo navegável;
- `README.md` — execução e escopo;
- `visual-checklist.md` — QA manual;
- `screenshots/` — evidências opcionais geradas localmente quando houver ferramenta disponível.

## 4. Telas da Wave 1

O protótipo executável deve conter:

1. Knowledge List;
2. Knowledge Workspace — Visão geral;
3. Knowledge Workspace — Summary;
4. Knowledge Workspace — Classificação;
5. Estados transversais: empty, success, validation error, system error, permission/read-only e loading conceitual.

Review & Governança aparece apenas como reserva arquitetural até SPEC-003.

## 5. Design System como código

Tokens devem existir como CSS Custom Properties, por exemplo:

- `--brand-navy`;
- `--brand-blue-500`;
- `--bg-app`;
- `--surface`;
- `--text-primary`;
- `--border`;
- `--success`, `--warning`, `--danger`;
- radius e spacing canônicos.

Padrões recorrentes devem ser implementados por classes CSS reutilizáveis: buttons, badges, tabs, panels, fields, dense rows, notices, empty states e context panels.

## 6. Gate UX-010 revisado

UX-010 passa quando:

- Knowledge Workspace Overview executável estiver aprovado;
- Summary e Classificação estiverem representados no mesmo shell;
- Knowledge List estiver navegável;
- estados obrigatórios estiverem visíveis;
- layout funcionar em 1440px e <=782px;
- foco/teclado forem verificáveis;
- nenhum domínio futuro parecer funcional;
- o protótipo estiver versionado no Git.

## 7. Handoff para implementação

Quando uma SPEC autorizar implementação:

1. o protótipo fornece estrutura, tokens e comportamento visual;
2. a implementação converte o HTML conceitual para PHP/wp-admin e CSS do plugin;
3. nenhum JavaScript do protótipo é copiado automaticamente para produção;
4. writers, nonce, capability, PRG e persistência continuam obedecendo aos contratos funcionais;
5. browser acceptance compara runtime real contra o contrato UI as Code.

## 8. Regra de independência

A UX-001 deve continuar avançando com ferramentas locais e Git. Figma, Canva, serviços de design e MCPs podem ser usados opcionalmente, mas nunca podem ser requisito para gate, build, teste ou continuidade do projeto.
