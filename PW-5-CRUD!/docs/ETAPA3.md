# Etapa 3 — Experiência & Extras ✨

> **Objetivo:** transformar o sistema funcional em algo agradável de usar:
> interface polida, interações em JavaScript, desafios extras e a entrega final.

## 📦 O que esta etapa contém

| Arquivo | Papel |
|---|---|
| `frontend/css/style.css` | identidade visual completa e **responsiva** (tabela vira cartões no celular) |
| `frontend/js/app.js` | modal de exclusão, filtro instantâneo, validações, busca AJAX, idade automática |
| `index.php` | **dashboard** com estatísticas (desafio extra) |
| `backend/api/buscar.php` | endpoint JSON da **busca dinâmica** (desafio extra) |
| `README.md` | documentação de entrega |

## ✨ Interações implementadas (JavaScript)

- 🗑️ **Modal de confirmação** personalizado antes de qualquer exclusão
  (requisito do enunciado: "confirmação de exclusão").
- 🔎 **Filtro instantâneo** em todas as listagens (digita → filtra sem recarregar).
- 🌐 **Busca global** no topo: países e cidades via `fetch()` + JSON, com link
  direto para o registro encontrado (**linha destacada** na listagem).
- 🎂 **Idade calculada** automaticamente a partir da data de nascimento.
- 📅 Datas: impedem valores futuros onde não fazem sentido e mandato fim < início.
- 💬 Mensagens de feedback que **se dispensam sozinhas** após 5 segundos.
- 📱 Menu "hambúrguer" e layout totalmente responsivo.

## 📊 Estatísticas do painel (desafio extra)

1. **Cidade mais populosa cadastrada** no sistema (com país e população).
2. **Cidades por continente** com barras proporcionais.
3. **Cidade mais populosa de cada país** (subconsulta com `MAX()` + `GROUP BY`).

## ✅ Checklist final de qualidade

- [x] HTML semântico (`header`, `nav`, `main`, `footer`, `table`, `form` com `label`)
- [x] Layout não quebra no celular (testar com F12 → modo responsivo)
- [x] Nenhum formulário envia dados inválidos (validado 2×: JS + PHP)
- [x] Toda exclusão pede confirmação
- [x] Integridade referencial respeitada em 100% dos caminhos

## 🚀 Entrega

1. Revisar o `README.md` (nome, descrição, prints das telas).
2. Conferir o histórico: commits descritivos + branch `dev` + tags `etapa-1/2/3`.
3. Subir para o GitHub:

```bash
git add .
git commit -m "Etapa 3 — Interface, validações JS, busca dinâmica e estatísticas"
git tag etapa-3
git checkout main && git merge --no-ff dev
git remote add origin https://github.com/SEU-USUARIO/crud-mundo.git
git push -u origin main --tags
git push origin dev
```

🎓 **Projeto pronto para entrega!**
