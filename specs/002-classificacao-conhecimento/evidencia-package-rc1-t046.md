# Evidência T046A — Package limpo `0.2.0-rc.1`

Package preparado após PASS de G-030/B-006/G-070/G-110.

- versão: `0.2.0-rc.1`;
- PHP lint: PASS 8/8;
- runtime files: 9;
- SHA-256 ZIP: `a5120299ea907d271bc39b318857ba033cf0cf340fe1836289f42a4c717b8fb5`;
- sem `BDC_KB_HOMOLOGATION_BUILD`;
- sem `Classification_Diagnostics`;
- sem `Classification_HTTP_Diagnostics`;
- sem `Classification_Browser_Acceptance`;
- sem fixture markers/force-deny de homologação;
- sem rotina destrutiva de uninstall.

Mudança visual de baixo risco após G-110: CSS comprime somente `select[multiple]:empty` para 38px, evitando caixa vazia alta quando o vocabulário ainda não possui termos. Não altera markup, POST, contrato, persistência ou comportamento quando há termos.

G-130 permanece pendente até instalação/deactivate/reactivate no WordPress real.
