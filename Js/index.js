//BLOCO DO BACKGROUND COSTUMIZADO

const bg = document.getElementById("bg");
let estado = "fixed";
let lastScroll = window.scrollY;

function vh(v) {
  return window.innerHeight * v;
}

function onScroll() {
  const scroll = window.scrollY;
  const winH = window.innerHeight;
  const pageH = document.documentElement.scrollHeight;
  const descendo = scroll > lastScroll;
  const subindo = scroll < lastScroll;
  lastScroll = scroll;
  const limite = pageH - winH - vh(0.6);

  if (estado === "fixed" && descendo && scroll >= limite) {
    const rect = bg.getBoundingClientRect();
    const topDoc = scroll + rect.top;
    bg.style.position = "absolute";
    bg.style.top = `${topDoc}px`;
    estado = "absolute";
    return;
  }

  if (estado === "absolute" && subindo && scroll < limite) {
    bg.style.position = "fixed";
    bg.style.top = "20vh";
    estado = "fixed";
    return;
  }
}

window.addEventListener("scroll", onScroll);

//BLOCO DO COKKIE

const cokkie = document.querySelector(".cokkie");
const aceitar = document.getElementById("sim");
const recusar = document.getElementById("nao");
const img = document.getElementById("img_cokkie");

if (localStorage.getItem("cokkie_aceito") === "true") {
  cokkie.style.display = "none";
  img.style.display = "block";
}

aceitar.addEventListener("click", () => {
  localStorage.setItem("cokkie_aceito", "true");
  cokkie.style.display = "none";
  img.style.display = "block";
});

recusar.addEventListener("click", () => {
  localStorage.setItem("cokkie_aceito", "false");
  cokkie.style.display = "none";
  img.style.display = "none";
});

img.addEventListener("click", () => {
  localStorage.setItem("cokkie_aceito", "false");
  cokkie.style.display = "flex";
  img.style.display = "none";
});