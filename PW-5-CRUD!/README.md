# 🌍 CRUD Mundo

> **Aluno(a):** _escreva seu nome aqui_
> **Projeto:** CRUD Mundo — Sistema de gerenciamento de países, cidades, continentes e governantes
> **Disciplina:** Programação Web · **Curso:** Desenvolvimento de Sistemas — São José dos Campos

---

## 📖 Descrição do projeto

Aplicação web completa (front end + back end + banco de dados) para gerenciar
informações geográficas do mundo. Implementa as quatro operações do **CRUD**
(Create, Read, Update, Delete) para quatro entidades relacionadas:

| Entidade | Campos | Relacionamentos |
|---|---|---|
| **Continentes** | nome, população, área (km²), total de países | possui vários países (1:N) |
| **Países** | nome, população, área (km²), idioma, clima, regime político, moeda | pertence a um continente; tem um governante; possui várias cidades |
| **Cidades** | nome, população, área (km²), clima, data de fundação | pertence a um país; pode ter um governante |
| **Governantes** | nome, partido, nascimento, idade, início e fim do mandato | governa um país **ou** uma cidade |

### Funcionalidades

- ✅ Cadastrar, listar, editar e excluir as **4 entidades**
- ✅ Filtro instantâneo nas listagens (JavaScript)
- ✅ Validação de formulários no **front end** (JS) e no **back end** (PHP)
- ✅ Modal de **confirmação de exclusão**
- ✅ Mensagens de feedback após cada operação
- ✅ Idade do governante calculada automaticamente (JS)
- ✅ **Integridade referencial** tratada (ver seção abaixo)
- ✅ Autenticação por sessão com logout e proteção das páginas
- ✅ Bloqueio persistente após 3 tentativas consecutivas de senha inválida
- ✅ Troca obrigatória de senha no primeiro acesso e manutenção voluntária
- ✅ Auditoria de autenticação e alteração de senha na tabela `logs`
- ⭐ **Extra:** busca dinâmica global (AJAX) de países e cidades
- ⭐ **Extra:** estatísticas no painel inicial (cidade mais populosa, cidades por continente, cidade mais populosa de cada país)

## 🛠️ Tecnologias utilizadas

- **Front end:** HTML5 semântico, CSS3 (responsivo, sem frameworks), JavaScript
- **Back end:** PHP 8+ com **PDO** (prepared statements em todas as queries)
- **Banco de dados:** MySQL (`bd_mundo`)
- **Versionamento:** Git + GitHub

## 🗄️ Modelagem e integridade referencial

```
continentess 1 ─── N paises 1 ─── N cidades
governantes  1 ─── N paises      governantes 1 ─── N cidades
```

Regras adotadas (decisão documentada no código e nas FKs):

| Ação | Regra | Justificativa |
|---|---|---|
| Excluir continente com países | **Bloqueada** (`RESTRICT` + verificação em PHP) | evita países "órfãos" |
| Excluir país com cidades | **Bloqueada** (`RESTRICT` + verificação em PHP) | o usuário é avisado para excluir as cidades antes |
| Excluir governante | **Permitida** (`SET NULL` nas FKs) | país/cidade ficam sem governante, sem perder dados |

> 💡 Para comportamento em cascata, basta trocar `ON DELETE RESTRICT` por
> `ON DELETE CASCADE` na FK `fk_cidades_pais` (arquivo `database/bd_mundo.sql`).

## 📁 Estrutura de pastas

```
crud-mundo/
├── index.php                  ← painel inicial (dashboard + estatísticas)
├── login.php / logout.php     ← autenticação e encerramento da sessão
├── database/
│   └── bd_mundo.sql           ← script de criação do banco + dados iniciais
├── backend/
│   ├── helpers.php            ← funções comuns (validação, flash, URL…)
│   ├── config/
│   │   ├── database.php       ← conexão PDO (MySQL)
│   │   └── semente.php        ← seed usado apenas no modo demonstração
│   ├── auth/senha.php          ← troca obrigatória ou voluntária de senha
│   ├── api/buscar.php         ← endpoint JSON da busca dinâmica
│   ├── continentes/  (index.php, form.php, excluir.php)
│   ├── paises/       (index.php, form.php, excluir.php)
│   ├── governantes/  (index.php, form.php, excluir.php)
│   └── cidades/      (index.php, form.php, excluir.php)
├── frontend/
│   ├── css/style.css          ← todo o visual (responsivo)
│   └── js/app.js              ← validações, modal, busca dinâmica…
├── templates/ (header.php, footer.php)
└── docs/     (ETAPA1.md, ETAPA2.md, ETAPA3.md — como o sistema foi construído)
```

## ⚙️ Instalação e execução

### 🪟 Windows (XAMPP)

1. Instale o **XAMPP** e inicie **Apache** + **MySQL**.
2. Copie a pasta do projeto para `C:\xampp\htdocs\crud-mundo`.
3. Abra o **phpMyAdmin** (http://localhost/phpmyadmin) e **importe** o arquivo
   `database/bd_mundo.sql` (ele cria o banco e já insere dados de exemplo).
4. Se necessário, ajuste usuário/senha em `backend/config/database.php`
   (padrão XAMPP: `root` sem senha).
5. Acesse: **http://localhost/crud-mundo/**

### Primeiro acesso

O SQL inclui uma conta inicial para a demonstração:

| Usuário | Senha inicial |
|---|---|
| `admin` | `Mundo@123` |

Essa senha existe apenas para permitir o primeiro login e deve ser trocada na tela obrigatória. O banco armazena somente o hash produzido por `password_hash()`.

### 🐧 Linux (XAMPP)

```bash
# 1. Baixe o instalador em apachefriends.org e instale:
chmod +x xampp-linux-x64-*.run && sudo ./xampp-linux-x64-*.run

# 2. Inicie os serviços:
sudo /opt/lampp/lampp start

# 3. Copie o projeto para o htdocs (no Linux o XAMPP fica em /opt/lampp):
sudo cp -r crud-mundo /opt/lampp/htdocs/
sudo chmod -R 755 /opt/lampp/htdocs/crud-mundo

# 4. Importe o banco em http://localhost/phpmyadmin (botão Importar → database/bd_mundo.sql)
# 5. Acesse: http://localhost/crud-mundo/
```

### 🐧 Linux (sem XAMPP — jeito rápido)

```bash
# Ubuntu/Debian — instala só o PHP:
sudo apt install php-cli php-sqlite3

# Roda direto (sem banco: entra o "modo demonstração" automático com SQLite):
cd crud-mundo && php -S localhost:8000
# Acesse: http://localhost:8000

# Para usar MySQL/MariaDB de verdade:
sudo apt install php-mysql mariadb-server
sudo mysql < database/bd_mundo.sql
```

> ⚡ **Modo demonstração:** se o MySQL não for encontrado, o sistema cria
> automaticamente um banco SQLite local com os mesmos dados — útil para uma
> pré-visualização rápida (os dados de verdade ficam no MySQL da entrega). A conta
> `admin` também é criada no SQLite e exige troca da senha no primeiro acesso.

## 🔐 Autenticação e segurança

- Todas as páginas do dashboard e dos quatro CRUDs exigem uma sessão autenticada.
- Após três senhas consecutivas incorretas, a conta é bloqueada no banco e não pode entrar novamente, mesmo com a senha correta.
- Um login correto antes do terceiro erro zera o contador de tentativas.
- Enquanto `primeiro_acesso` estiver ativo, qualquer acesso direto às URLs do sistema redireciona para a troca de senha.
- Senhas são verificadas com `password_verify()` e armazenadas com `password_hash()`; nenhum hash ou senha é gravado em `logs`.
- Formulários POST usam token CSRF armazenado na sessão e as saídas HTML passam por escaping.

Para testar rapidamente o fluxo: faça três tentativas inválidas com `admin`, confirme o bloqueio; recrie o banco demo ou limpe a conta para testar o primeiro acesso; depois altere a senha e valide o acesso normal, logout e login novamente.

## 🌿 Versionamento

O desenvolvimento foi dividido em **3 etapas**, registradas no histórico Git:

| Etapa | Conteúdo | Tag |
|---|---|---|
| **1 — Fundação** | banco de dados, conexão PDO, helpers, CRUD de Continentes | `etapa-1` |
| **2 — Núcleo relacional** | CRUDs de Países, Governantes e Cidades + integridade referencial | `etapa-2` |
| **3 — Experiência e extras** | interface, validações JS, busca dinâmica, estatísticas, README | `etapa-3` |

Branches utilizadas: `main` (estável) e `dev` (desenvolvimento das etapas 2 e 3).

## 📸 Telas

_Adicione aqui prints das telas principais (início, listagem, formulário, modal de exclusão) após rodar o projeto._

---

**Entrega individual** · Projeto disponível no GitHub.
