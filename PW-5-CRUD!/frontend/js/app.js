/**
 * CRUD Mundo · interações de front end
 * - menu mobile
 * - modal de confirmação de exclusão
 * - filtro instantâneo das tabelas
 * - validações extras de formulário (datas e números)
 * - idade calculada a partir da data de nascimento
 * - busca dinâmica (AJAX) de países e cidades
 * - mensagens de feedback que se ocultam sozinhas
 */
(function () {
    "use strict";
    var BASE = window.BASE_URL || "";

    /* ------------------------- Menu mobile ------------------------- */
    var btn = document.querySelector(".nav-btn");
    var nav = document.querySelector(".topo nav");
    if (btn && nav) {
        btn.addEventListener("click", function () {
            var aberta = nav.classList.toggle("aberta");
            btn.classList.toggle("ativo", aberta);
            btn.setAttribute("aria-expanded", aberta ? "true" : "false");
        });
    }

    /* ----------------- Modal de confirmação de exclusão ------------ */
    var modal = document.getElementById("modal-confirmar");
    if (modal) {
        var txt = modal.querySelector(".modal-texto");
        var ok = modal.querySelector(".modal-ok");
        var cancel = modal.querySelector(".modal-cancelar");
        var callback = null;

        function abrir(mensagem, cb) {
            txt.textContent = mensagem;
            callback = cb;
            modal.hidden = false;
            requestAnimationFrame(function () { modal.classList.add("aberto"); });
            ok.focus();
        }
        function fechar() {
            modal.classList.remove("aberto");
            setTimeout(function () { modal.hidden = true; }, 180);
            callback = null;
        }
        ok.addEventListener("click", function () {
            var cb = callback;
            fechar();
            if (cb) { cb(); }
        });
        cancel.addEventListener("click", fechar);
        modal.addEventListener("click", function (e) { if (e.target === modal) { fechar(); } });
        document.addEventListener("keydown", function (e) {
            if (e.key === "Escape" && !modal.hidden) { fechar(); }
        });

        document.addEventListener("submit", function (e) {
            var form = e.target;
            if (!form.matches("[data-confirmar]")) { return; }
            if (form.dataset.confirmado === "1") { return; }
            e.preventDefault();
            abrir(form.dataset.confirmar || "Confirmar a exclusão?", function () {
                form.dataset.confirmado = "1";
                if (form.requestSubmit) { form.requestSubmit(); } else { form.submit(); }
            });
        });
    }

    /* ----------------- Filtro instantâneo das tabelas -------------- */
    document.querySelectorAll("[data-filtro]").forEach(function (inp) {
        var alvo = document.querySelector(inp.getAttribute("data-filtro"));
        if (!alvo) { return; }
        var linhas = Array.prototype.slice.call(alvo.querySelectorAll("tbody tr"));
        inp.addEventListener("input", function () {
            var q = inp.value.trim().toLowerCase();
            linhas.forEach(function (tr) {
                tr.style.display = tr.textContent.toLowerCase().indexOf(q) >= 0 ? "" : "none";
            });
        });
    });

    /* ------------------- Validações de formulário ------------------ */
    function mostrarErros(form, mensagens) {
        var caixa = form.querySelector(".erros-js");
        if (!caixa) {
            caixa = document.createElement("div");
            caixa.className = "alerta alerta-erro erros-js campo-total";
            form.insertBefore(caixa, form.firstChild);
        }
        caixa.innerHTML = "<div><strong>Verifique o formulário:</strong><ul><li>" +
            mensagens.join("</li><li>") + "</li></ul></div>";
        caixa.scrollIntoView({ behavior: "smooth", block: "center" });
    }

    document.querySelectorAll("form[data-validar]").forEach(function (form) {
        form.addEventListener("submit", function (e) {
            var erros = [];
            var ini = form.querySelector("#inicio_mandato");
            var fim = form.querySelector("#fim_mandato");
            if (ini && fim && ini.value && fim.value && fim.value < ini.value) {
                erros.push("O fim do mandato não pode ser anterior ao início.");
            }
            form.querySelectorAll("input[type=number]").forEach(function (n) {
                if (n.value !== "" && parseFloat(n.value) < 0) {
                    erros.push('O campo "' + (n.dataset.rotulo || n.name) + '" não pode ser negativo.');
                }
            });
            if (erros.length) {
                e.preventDefault();
                mostrarErros(form, erros);
            } else {
                var c = form.querySelector(".erros-js");
                if (c) { c.remove(); }
            }
        });
    });

    /* ---------------------- Troca de senha ------------------------- */
    document.querySelectorAll("form[data-senha]").forEach(function (form) {
        form.addEventListener("submit", function (e) {
            var nova = form.querySelector("#nova_senha");
            var confirmacao = form.querySelector("#confirmacao_senha");
            var atual = form.querySelector("#senha_atual");
            var minimo = nova ? parseInt(nova.getAttribute("minlength") || "8", 10) : 8;
            var erros = [];

            if (nova && nova.value.length < minimo) {
                erros.push("A nova senha deve ter pelo menos " + minimo + " caracteres.");
            }
            if (nova && confirmacao && nova.value !== confirmacao.value) {
                erros.push("A confirmação não coincide com a nova senha.");
            }
            if (nova && atual && nova.value && nova.value === atual.value) {
                erros.push("A nova senha deve ser diferente da senha atual.");
            }

            if (erros.length) {
                e.preventDefault();
                mostrarErros(form, erros);
            } else {
                var caixa = form.querySelector(".erros-js");
                if (caixa) { caixa.remove(); }
            }
        });
    });

    /* Evita duplo envio e dá retorno imediato durante a autenticação. */
    document.querySelectorAll("form[data-login]").forEach(function (form) {
        form.addEventListener("submit", function () {
            var botao = form.querySelector("button[type=submit]");
            if (botao) {
                botao.disabled = true;
                botao.textContent = "Entrando…";
                form.setAttribute("aria-busy", "true");
            }
        });
    });

    /* -------- Datas: limite de hoje + idade automática ------------- */
    var hoje = new Date().toISOString().slice(0, 10);
    document.querySelectorAll('input[type="date"][data-max-hoje]').forEach(function (d) {
        d.max = hoje;
    });

    var dn = document.getElementById("data_nascimento");
    var campoIdade = document.getElementById("idade");
    if (dn && campoIdade) {
        dn.addEventListener("change", function () {
            if (!dn.value) { return; }
            var nasc = new Date(dn.value + "T00:00:00");
            var agora = new Date();
            var anos = agora.getFullYear() - nasc.getFullYear();
            var m = agora.getMonth() - nasc.getMonth();
            if (m < 0 || (m === 0 && agora.getDate() < nasc.getDate())) { anos--; }
            campoIdade.value = anos >= 0 ? anos : "";
        });
    }

    /* -------- Mensagens de feedback que somem sozinhas ------------- */
    document.querySelectorAll(".alerta[data-auto]").forEach(function (a) {
        var fechar = function () { a.remove(); };
        var x = a.querySelector(".alerta-fechar");
        if (x) { x.addEventListener("click", fechar); }
        setTimeout(function () {
            a.classList.add("sumindo");
            setTimeout(fechar, 400);
        }, 5000);
    });

    /* --------------------- Busca dinâmica (AJAX) ------------------- */
    function escapar(s) {
        return String(s).replace(/[&<>"']/g, function (c) {
            return { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c];
        });
    }

    var campo = document.getElementById("busca-global");
    var lista = document.getElementById("resultados-busca");
    var timer = null;
    if (campo && lista) {
        campo.addEventListener("input", function () {
            clearTimeout(timer);
            var q = campo.value.trim();
            if (q.length < 2) { lista.hidden = true; lista.innerHTML = ""; return; }
            timer = setTimeout(function () {
                fetch(BASE + "/backend/api/buscar.php?q=" + encodeURIComponent(q), {
                    headers: { "Accept": "application/json" }
                })
                    .then(function (r) { return r.ok ? r.json() : []; })
                    .then(function (dados) {
                        if (!dados.length) {
                            lista.innerHTML = '<li class="sem-resultado">Nada encontrado para “' + escapar(q) + '”.</li>';
                        } else {
                            lista.innerHTML = dados.map(function (d) {
                                return '<li><a href="' + BASE + "/backend/" + d.entidade + "/index.php?destaque=" + d.id + '">' +
                                    '<span class="tipo tipo-' + d.entidade + '">' + d.tipo + "</span>" +
                                    '<span class="nome">' + escapar(d.nome) + "</span>" +
                                    (d.extra ? '<span class="extra">' + escapar(d.extra) + "</span>" : "") +
                                    "</a></li>";
                            }).join("");
                        }
                        lista.hidden = false;
                    })
                    .catch(function () { /* sem rede: ignora silenciosamente */ });
            }, 250);
        });
        document.addEventListener("click", function (e) {
            if (!lista.contains(e.target) && e.target !== campo) { lista.hidden = true; }
        });
        campo.addEventListener("keydown", function (e) {
            if (e.key === "Escape") { lista.hidden = true; }
        });
    }

    /* -------------- Destaque vindo da busca (rolagem) -------------- */
    var destaque = document.querySelector(".linha-destaque");
    if (destaque) {
        destaque.scrollIntoView({ behavior: "smooth", block: "center" });
    }
})();
