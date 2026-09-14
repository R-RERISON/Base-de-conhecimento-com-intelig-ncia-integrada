# Skill — Privacidade e Telemetria

**Nível:** Especialista  
**Experiência mínima representada:** 12 anos em governança de dados, analytics e privacidade.

## Objetivo

Medir comportamento e qualidade sem coletar identidade ou dados além do necessário.

## Procedimento

1. Definir decisão que a métrica suporta.
2. Definir menor dado necessário.
3. Escolher modo de privacidade.
4. Definir retenção.
5. Separar evento de busca de interação.
6. Diferenciar erro, zero-result, no-engagement e pendência.
7. Evitar duplicação de dados sensíveis.
8. Proteger exportações.
9. Definir limpeza/retention job quando necessário.

## Regras

- raw IP/UA não são telemetria canônica por padrão;
- correlação deve usar identificador transitório/hash quando necessária;
- identidade só entra quando o caso de uso e autorização justificarem;
- cache não pode gerar evento sem uma execução real.

## Saída

Contrato de eventos, política de retenção e modelo de privacidade.