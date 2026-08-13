# Etapa 2 — Núcleo Relacional 🔗

> **Objetivo:** implementar os CRUDs que envolvem **relacionamentos** —
> Países, Governantes e Cidades — garantindo a integridade referencial.

## 📦 O que esta etapa contém

| Pasta | Destaques |
|---|---|
| `backend/paises/` | país **obrigatoriamente** ligado a um continente; governante opcional |
| `backend/governantes/` | idade, datas de mandato com validação (fim ≥ início) |
| `backend/cidades/` | cidade **obrigatoriamente** ligada a um país existente; data de fundação não pode ser futura |

## 🧠 Regras de integridade implementadas

| Operação | Comportamento | Onde |
|---|---|---|
| Excluir continente com países | ❌ bloqueado, com aviso | `continentes/excluir.php` + `ON DELETE RESTRICT` |
| Excluir país com cidades | ❌ bloqueado, com aviso | `paises/excluir.php` + `ON DELETE RESTRICT` |
| Excluir governante | ✅ permitido — país/cidade ficam "sem vínculo" | `ON DELETE SET NULL` nas FKs |
| Cadastrar cidade sem país | ❌ bloqueado (front e back) | `cidades/form.php` + FK `NOT NULL` |

A verificação é feita **duas vezes** (PHP antes de deletar + constraint no banco):
assim o usuário recebe uma mensagem amigável e, mesmo que alguém tente pelo banco,
a integridade continua garantida.

## 🔍 Detalhes de implementação

- **SELECTs dinâmicos**: os formulários de País e Cidade carregam continente/país/governante
  do próprio banco (`helpers.php → opcoes()`), então um continente recém-cadastrado já aparece.
- **LEFT JOINs nas listagens**: exibem o *nome* do continente/país/governante em vez do ID.
- **Validação de FK existente**: o back end confere se o ID enviado realmente existe
  (evita vínculo com registro inexistente).
- **Datas**: validadas no servidor (`dataOpcional()`) e no cliente (`app.js`).

## ✅ Roteiro de testes desta etapa

1. Cadastrar um país → ele aparece na listagem com o continente certo.
2. Cadastrar cidade **sem escolher país** → formulário bloqueia.
3. Tentar excluir o **Brasil** (tem cidades) → bloqueado com explicação.
4. Excluir a cidade "Toronto" e depois o país "Canadá" → ambos removidos.
5. Excluir um governante vinculado → país/cidade permanecem com "sem vínculo".
6. Inserir fim de mandato **anterior** ao início → validação impede.

## 🌱 Commit sugerido

```bash
git add .
git commit -m "Etapa 2 — CRUDs de Países, Governantes e Cidades com integridade referencial"
git tag etapa-2
```

## ➡️ Próxima etapa

[Etapa 3 — Experiência e extras](ETAPA3.md): interface final, JavaScript,
busca dinâmica, estatísticas e entrega.
