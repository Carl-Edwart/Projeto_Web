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
  const limite = pageH - winH - vh(1.3);

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

const img = document.getElementById("img_cokkie");

if (localStorage.getItem("cokkie_aceito") === "true") {
  img.style.display = "block";
}