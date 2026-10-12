import { observarAuth, sair, garantirPerfil } from "./app.js";

const PAGINAS = {
  dashboard: { titulo: "Painel de Agendamento", arquivo: "dashboard.html", papeis: ["admin"] },
  agenda: { titulo: "Agenda", arquivo: "agenda.html", papeis: ["admin", "comum"] },
  meus: { titulo: "Meus agendamentos", arquivo: "meus-agendamentos.html", papeis: ["admin", "comum"] },
  agendamentos: { titulo: "Agendamentos", arquivo: "agendamentos.html", papeis: ["admin"] },
  servicos: { titulo: "Servicos", arquivo: "servicos.html", papeis: ["admin"] },
  usuarios: { titulo: "Usuarios", arquivo: "usuarios.html", papeis: ["admin"] }
};

/** Pagina inicial de cada nivel de acesso. */
export const INICIO = { admin: "dashboard.html", comum: "agenda.html" };

let usuarioAtual = null;
let perfilAtual = null;

/** Bloqueia a tela para quem nao tem permissao. */
function bloquear(perfil, mensagem) {
  document.body.innerHTML = `
    <div class="login-body">
      <div class="login-wrap">
        <div class="login-cartao" style="text-align:center">
          <div class="login-marca"><i class="fas fa-user-lock"></i> Acesso restrito</div>
          <p class="login-sub">${mensagem}</p>
          <div class="alerta alerta-aviso" style="text-align:left">
            Voce esta autenticado como <strong>${(perfil?.email || "").replace(/[<>&"]/g, "")}</strong>
            (perfil: ${(perfil?.papel || "comum").replace(/[<>&"]/g, "")}).
          </div>
          <button class="btn btn-primario btn-bloco" id="btnIrAgenda">Ir para a Agenda</button>
          <button class="btn btn-secundario btn-bloco gap-top" id="btnSairBloq">Sair da conta</button>
        </div>
      </div>
    </div>`;
  const agenda = document.getElementById("btnIrAgenda");
  if (agenda) agenda.onclick = () => { location.href = INICIO[perfil?.papel] || "agenda.html"; };
  const b = document.getElementById("btnSairBloq");
  if (b) b.onclick = async () => { await sair(); location.href = "login.html"; };
}

/** Monta o topo, aplica guarda de login e marca o menu ativo. */
export async function iniciarPagina(chaveAtiva) {
  usuarioAtual = await new Promise((resolve) => {
    const unsub = observarAuth((u) => { unsub(); resolve(u); });
  });

  if (!usuarioAtual) {
    location.replace("login.html");
    return null;
  }

  perfilAtual = await garantirPerfil(usuarioAtual);

  if (!perfilAtual) {
    location.replace("login.html");
    return null;
  }

  const papel = perfilAtual.papel || "comum";
  const pagina = PAGINAS[chaveAtiva];
  const permitido = !pagina || pagina.papeis.includes(papel);

  if (!permitido) {
    bloquear(perfilAtual, "Somente administradores podem acessar esta area.");
    return null;
  }

  // comum so entra na agenda e nos meus agendamentos
  const COMUM = ["agenda", "meus"];
  if (papel !== "admin" && !COMUM.includes(chaveAtiva)) {
    location.replace(INICIO.comum);
    return null;
  }

  const topo = document.getElementById("topo");
  if (!topo) return usuarioAtual;

  const menu = Object.entries(PAGINAS)
    .filter(([, p]) => p.papeis.includes(papel))
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

export function getPerfil() {
  return perfilAtual;
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