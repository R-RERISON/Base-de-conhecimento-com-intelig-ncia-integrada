# SpecKit do Projeto

O diretório `.specify/` contém a governança executável do projeto.

## Estrutura

```text
.specify/
├── PROJECT_MANIFEST.md
├── memory/
│   └── constitution.md
└── templates/
    ├── spec-template.md
    ├── plan-template.md
    ├── tasks-template.md
    ├── checklist-template.md
    └── adr-template.md
```

## Ordem de autoridade

1. Constituição;
2. Manifesto;
3. ADRs aceitas;
4. SPEC ativa;
5. Plano/tarefas/checklists;
6. implementação.

Nenhuma SPEC pode contrariar a Constituição silenciosamente.

## Fluxo

```text
Ideia
 ↓
Baseline
 ↓
SPEC Rascunho
 ↓
WordPress-first
 ↓
Princípio de negação
 ↓
Definition of Ready
 ↓
SPEC Pronta
 ↓
Vertical slice
 ↓
Testes / regressão / segurança / UI
 ↓
Homologação
 ↓
Definition of Done
```

## Idioma

Todo artefato do SpecKit é escrito em pt-BR.