# BDC Premium Plugin Product Standard v1.0

**Status:** NORMATIVO PARA O REBASELINE 2026-09-21  
**Produto:** Base de Conhecimento com Inteligência Integrada

## 1. Premium é contrato, não estética

Uma capacidade BDC só é considerada premium quando combina:
1. valor de produto comprovado;
2. UX coerente, responsiva e acessível;
3. WordPress-first;
4. arquitetura modular sustentável;
5. segurança e privacidade por desenho;
6. performance mensurada;
7. testes reproduzíveis;
8. distribuição profissional;
9. observabilidade, fallback e rollback;
10. paridade explícita com capacidades legadas substituídas.

PLANNED não é paridade. Código local não é paridade. Paridade exige contrato, runtime, regressão, ambiente, consumidor e rollback quando aplicável.

## 2. Compatibilidade WordPress

Matriz mínima:
- WordPress mínimo declarado: 6.6;
- baseline corporativa observada: 6.9.4;
- WordPress 7.0;
- WordPress corrente: 7.1.x;
- PHP mínimo: 8.1;
- PHP moderno de teste: 8.3+;
- PHP corporativo observado: 8.5.x.

Recursos modernos devem entrar por feature detection e progressive enhancement. DataViews/DataForm, AI Client, Connectors e Abilities devem ser avaliados antes de infraestrutura própria equivalente.

## 3. Identidade do plugin

O artefato final deve declarar, quando aplicável:
- Plugin Name;
- Plugin URI;
- Description;
- Version;
- Requires at least;
- Requires PHP;
- Author;
- Author URI;
- License;
- License URI;
- Update URI;
- Text Domain;
- Domain Path;
- Requires Plugins somente para dependência real.

Licença padrão: GPL-2.0-or-later, salvo decisão jurídica compatível com WordPress.

## 4. Repositório profissional

Antes do primeiro RC premium:
- LICENSE;
- README.md;
- readme.txt no pacote;
- CHANGELOG.md;
- UPGRADE.md;
- SECURITY.md;
- CONTRIBUTING.md;
- .editorconfig;
- composer.json;
- phpcs.xml.dist;
- phpunit.xml.dist;
- análise estática quando o baseline estiver limpo;
- documentação de arquitetura, compatibilidade e release.

GitHub Actions não são requisito. Os gates devem ser locais, reproduzíveis e gerar evidência.

## 5. Versionamento

Versão pública e build/gate são conceitos diferentes.
- 0.x.y = pré-GA;
- beta/rc seguem SemVer/version_compare;
- BUILD_ID separado para gate/commit;
- 1.0.0 somente após a SPEC final de cutover;
- release recebe tag, manifest e SHA-256 coerentes.

Identificadores como h030/g580 pertencem à engenharia, não à identidade comercial final.

## 6. Runtime

- plugin único, modular internamente;
- namespace estável;
- composição por domínios;
- composition root pequeno;
- carregamento condicional;
- runners/profilers/acceptance de laboratório fora do ZIP de produção por default;
- nenhuma feature flag de homologação vira arquitetura permanente;
- APIs internas explícitas;
- projections reconstruíveis;
- classes acima de ~400–500 linhas exigem revisão de coesão.

## 7. UX premium

- arquitetura de informação orientada à tarefa;
- Search dominante quando encontrar conhecimento é a tarefa;
- progressive disclosure;
- reduzir campos, decisões e fricção;
- loading/vazio/erro/bloqueio/sucesso distintos;
- consistência entre admin e público;
- responsividade validada de verdade;
- teclado, foco, semântica e nomes acessíveis;
- contraste AA;
- nenhum estado somente por cor;
- leitura escaneável;
- independência de tema nas superfícies BDC;
- assets somente onde necessários.

Meta: WCAG AA em superfícies novas/alteradas.

## 8. WordPress moderno

Avaliar antes de criar camada própria:
- Metadata, Taxonomy, Comments, Settings/Options;
- Site Health;
- server-rendered PHP quando suficiente;
- @wordpress/components/Admin UI quando justificar;
- DataViews/DataForm quando estáveis e adequados;
- AI Client/Connectors em WordPress moderno;
- Abilities API para capacidades tipadas/discoverable futuras.

## 9. Segurança e privacidade

Toda mutação documenta:
ação -> ator -> capability -> método -> CSRF -> validação -> sanitização -> persistência -> confirmação -> diagnóstico.

Obrigatório:
- capability por objeto;
- prepared statements;
- escaping contextual;
- nenhuma destruição via GET;
- secrets fora do source;
- mitigação SSRF;
- IA nunca é owner editorial;
- data minimization e retention;
- query text não é telemetria neutra;
- exports redigidos/sanitizados.

## 10. Performance

Sem benchmark, não é arquitetura final:
- scans ilimitados em UI;
- posts_per_page=-1 recorrente;
- reprocessamento integral por request;
- Options como fila;
- Transients como garantia de durabilidade;
- assets globais de features locais.

Stores próprios são aceitáveis quando workload e ownership justificarem.

## 11. Quality Gate

O gate premium deve compor:
1. PHP lint;
2. WordPress Coding Standards;
3. análise estática;
4. unit tests;
5. integração WordPress real;
6. regressão;
7. Plugin Check;
8. JS/CSS checks;
9. acessibilidade/browser;
10. performance budget;
11. deterministic build;
12. package integrity;
13. lifecycle;
14. checksum;
15. environmental smoke.

## 12. Plugin Check

Mesmo em distribuição privada, o ZIP final deve passar pelo Plugin Check oficial. Findings são corrigidos ou recebem waiver explícito e versionado.

## 13. Release

O mesmo artefato testado é o distribuído. Deve:
- ter raiz única;
- excluir specs/tools/evidence/runners indevidos;
- possuir manifest e SHA-256;
- manter versão consistente header/constant/readme/tag;
- ter changelog, upgrade e rollback;
- não conter segredo nem artefato temporário.

## 14. Compatibilidade

Toda compatibilidade histórica:
consumidor comprovado -> adapter limitado -> observabilidade -> teste de equivalência -> gate de remoção.

Dual-write permanente é proibido.

## 15. Premium Done

Uma capability é Premium Done somente quando:
- resolve jornada real;
- atende Visual Contract;
- passa segurança/privacidade;
- passa quality gates;
- possui evidência ambiental;
- atende performance;
- possui fallback/rollback;
- atualiza documentação e Master Parity Ledger;
- não adiciona dívida de distribuição;
- não leva laboratório para produção sem justificativa.
