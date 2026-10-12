import { observarAuth, sair, garantirPerfil } from "./app.js";

const PAGINAS = {
  dashboard: { titulo: "Painel de Agendamento", arquivo: "dashboard.html" },
  agenda: { titulo: "Agenda", arquivo: "agenda.html" },
  agendamentos: { titulo: "Agendamentos", arquivo: "agendamentos.html" },
  servicos: { titulo: "Servicos", arquivo: "servicos.html" },
  horarios: { titulo: "Horarios", arquivo: "horarios.html" }
};

let usuarioAtual = null;

/** Monta o topo, aplica guarda de login e marca o menu ativo. */
export async function iniciarPagina(chaveAtiva) {
  usuarioAtual = await new Promise((resolve) => {
    const unsub = observarAuth((u) => { unsub(); resolve(u); });
  });

  if (!usuarioAtual) {
    location.replace("login.html");
    return null;
  }

  await garantirPerfil(usuarioAtual);

  const topo = document.getElementById("topo");
  if (!topo) return usuarioAtual;

  const menu = Object.entries(PAGINAS)
    .map(([chave, p]) =>
      `<a href="${p.arquivo}" class="${chave === chaveAtiva ? "ativo" : ""}">${p.titulo}</a>`
    )
    .join("");

  topo.outerHTML = `
    <header class="topo">
      <div class="topo-inner">
        <div class="topo-marca"><i class="fas fa-calendar-check"></i> Painel de Agendamento</div>
        <nav class="menu">${menu}</nav>
        <div class="usuario-area">
          <span class="usuario-nome">${(usuarioAtual.displayName || usuarioAtual.email || "").trim()}</span>
          <button class="btn btn-secundario btn-sm" id="btnSair"><i class="fas fa-right-from-bracket"></i> Sair</button>
        </div>
      </div>
    </header>
  `;

  document.getElementById("btnSair").addEventListener("click", async () => {
    await sair();
    location.href = "login.html";
  });

  return usuarioAtual;
}

export function getUsuario() {
  return usuarioAtual;
}

export function toast(texto, tipo = "ok") {
  let box = document.getElementById("toast");
  if (!box) {
    box = document.createElement("div");
    box.id = "toast";
    box.style.cssText =
      "position:fixed;left:50%;transform:translateX(-50%);bottom:26px;z-index:99;" +
      "padding:12px 20px;border-radius:10px;font-size:14px;font-weight:600;" +
      "box-shadow:0 10px 30px rgba(20,20,40,.18);max-width:90vw;text-align:center";
    document.body.appendChild(box);
  }
  box.className = "alerta alerta-" + tipo;
  box.textContent = texto;
  box.style.display = "block";
  clearTimeout(box._t);
  box._t = setTimeout(() => { box.style.display = "none"; }, 3200);
}