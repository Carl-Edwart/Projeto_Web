# Etapa 1 — Fundação 🏗️

> **Objetivo:** deixar a base do sistema pronta — banco de dados modelado,
> conexão segura e um primeiro CRUD completo servindo de "molde" para os demais.

## 📦 O que esta etapa contém

| Arquivo | Papel |
|---|---|
| `database/bd_mundo.sql` | Criação do banco `bd_mundo`, das 4 tabelas com PKs/FKs e dados iniciais |
| `backend/config/database.php` | Conexão **PDO** com tratamento de erro |
| `backend/config/semente.php` | Dados iniciais para o modo demonstração |
| `backend/helpers.php` | Funções comuns: `e()` (anti-XSS), `flash()`, `redirect()`, validações, formatação |
| `templates/header.php` / `footer.php` | Topo e rodapé compartilhados |
| `backend/continentes/` | **CRUD completo de Continentes** (listar, criar/editar, excluir) |

## 🎯 Decisões desta etapa

1. **PDO com prepared statements** em todas as queries → proteção contra SQL Injection.
2. **UNIQUE no nome** de continentes → evita cadastro duplicado (validado no PHP com mensagem amigável e reforçado no banco).
3. **Padrão PRG** (Post → Redirect → Get) + mensagens *flash* via sessão → o usuário nunca reenvia o formulário ao dar F5.
4. **Continente como primeiro CRUD** → é a única entidade sem chave estrangeira, ideal para validar a arquitetura antes de replicá-la.

## ✅ Como testar

1. Importar `database/bd_mundo.sql` no phpMyAdmin.
2. Acessar `backend/continentes/index.php`: cadastrar, editar e excluir um continente.
3. Tentar cadastrar continente com nome repetido → mensagem de erro amigável.
4. Tentar excluir a **América do Sul** (que tem países) → sistema deve bloquear.

## 🔖 Critérios de avaliação cobertos

- Organização da estrutura de pastas
- Início do CRUD em PHP com queries SQL corretas
- Tratamento de erros e validações server-side

## 🌱 Commit sugerido

```bash
git checkout -b dev
git add .
git commit -m "Etapa 1 — Fundação: banco bd_mundo, conexão PDO e CRUD de Continentes"
git tag etapa-1
```

## ➡️ Próxima etapa

[Etapa 2 — Núcleo relacional](ETAPA2.md): CRUDs de Países, Governantes e Cidades
com chaves estrangeiras e regras de integridade.
