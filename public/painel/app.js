import { initializeApp } from "https://www.gstatic.com/firebasejs/11.0.1/firebase-app.js";
import {
  getAuth,
  GoogleAuthProvider,
  signInWithPopup,
  signInWithEmailAndPassword,
  createUserWithEmailAndPassword,
  updateProfile,
  signOut,
  onAuthStateChanged
} from "https://www.gstatic.com/firebasejs/11.0.1/firebase-auth.js";
import {
  getFirestore,
  doc,
  getDoc,
  setDoc,
  updateDoc,
  collection,
  query,
  where,
  orderBy,
  limit,
  getDocs,
  addDoc,
  deleteDoc,
  serverTimestamp,
  runTransaction
} from "https://www.gstatic.com/firebasejs/11.0.1/firebase-firestore.js";
import { firebaseConfig, WHATSAPP_CONFIRMACAO, EMAIL_DESTINO, EMAIL_CHAVE } from "./config.js";
export { firebaseConfig };
export { WHATSAPP_CONFIRMACAO, EMAIL_DESTINO, EMAIL_CHAVE } from "./config.js";

const app = initializeApp(firebaseConfig);
export const auth = getAuth(app);
export const db = getFirestore(app);

const googleProvider = new GoogleAuthProvider();

/* ---------- WhatsApp ---------- */

function soDigitos(txt) {
  return String(txt || "").replace(/\D/g, "");
}

/** Numero do cliente no formato internacional (55 + DDD + numero). */
export function numeroCliente(ag) {
  const d = soDigitos(ag?.clienteTelefone);
  if (!d) return "";
  if (d.startsWith("55")) return d;
  if (d.length <= 11) return "55" + d;
  return d;
}

/** Link do WhatsApp com a mensagem pronta. */
export function linkWhatsApp(ag, mensagem) {
  const texto = encodeURIComponent(mensagem);
  const destino = numeroCliente(ag);
  return destino
    ? `https://wa.me/${destino}?text=${texto}`
    : `https://wa.me/?text=${texto}`;
}

/** Mensagem de confirmacao do agendamento. */
export function mensagemConfirmacao(ag) {
  const linhas = [
    "Olá" + (ag.clienteNome ? " " + ag.clienteNome : "") + "!",
    "",
    "Seu agendamento está confirmado:",
    `📅 Data: ${formatarDataBR(ag.data)}`,
    `🕐 Horário: ${ag.horaInicio} às ${ag.horaFim}`
  ];
  if (ag.servicoNome) linhas.push(`🧠 Serviço: ${ag.servicoNome}`);
  if (ag.valor) linhas.push(`💲 Valor: ${formatarMoeda(ag.valor)}`);
  linhas.push("", "Dra. Edilaine Silva — Psicologia", "Suzano - SP");
  return linhas.join("\n");
}

/* ---------- Agenda (helpers) ---------- */

/* ---------- Autenticacao ---------- */

export async function loginComGoogle() {
  const cred = await signInWithPopup(auth, googleProvider);
  await salvarPerfil(cred.user, "google");
  return cred.user;
}

export async function loginComEmail(email, senha) {
  const cred = await signInWithEmailAndPassword(auth, email, senha);
  await salvarPerfil(cred.user, "email");
  return cred.user;
}

export async function cadastrar(nome, email, senha) {
  const cred = await createUserWithEmailAndPassword(auth, email, senha);
  await updateProfile(cred.user, { displayName: nome });
  await salvarPerfil(cred.user, "email", nome);
  return cred.user;
}

async function salvarPerfil(user, tipo, nome) {
  const ref = doc(db, "usuarios", user.uid);
  const snap = await getDoc(ref);
  const dados = {
    nome: nome || user.displayName || (user.email || "").split("@")[0],
    email: user.email || "",
    tipoLogin: tipo,
    atualizadoEm: serverTimestamp()
  };
  if (snap.exists()) {
    await updateDoc(ref, dados);
  } else {
    await setDoc(ref, {
      ...dados,
      papel: ehDonoEmail(user.email) ? "admin" : "comum",
      criadoEm: serverTimestamp()
    });
  }
}

/** E-mail do dono do projeto: sempre admin. */
export const EMAIL_DONO = "dissinho.gangster000@gmail.com";

function ehDonoEmail(email) {
  return (email || "").toLowerCase() === EMAIL_DONO;
}

/** Garante que o perfil existe e devolve os dados (inclui papel). */
export async function garantirPerfil(user) {
  if (!user) return null;
  const ref = doc(db, "usuarios", user.uid);
  try {
    const snap = await getDoc(ref);
    if (!snap.exists()) {
      await setDoc(ref, {
        nome: user.displayName || (user.email || "").split("@")[0],
        email: user.email || "",
        papel: ehDonoEmail(user.email) ? "admin" : "comum",
        tipoLogin: (user.providerData?.[0]?.providerId || "").includes("google") ? "google" : "email",
        criadoEm: serverTimestamp(),
        atualizadoEm: serverTimestamp()
      });
      return { uid: user.uid, papel: ehDonoEmail(user.email) ? "admin" : "comum" };
    }

    const dados = snap.data() || {};
    // Dono sem papel definido vira admin.
    if (!dados.papel && ehDonoEmail(user.email)) {
      await updateDoc(ref, { papel: "admin", atualizadoEm: serverTimestamp() });
      return { ...dados, uid: user.uid, papel: "admin" };
    }
    return { ...dados, uid: user.uid };
  } catch (e) {
    console.warn("perfil:", e.message);
    return { uid: user.uid, papel: "comum" };
  }
}

/* ---------- Gestao de usuarios (somente admin) ---------- */

export async function listarUsuarios() {
  const snap = await getDocs(query(collection(db, "usuarios"), orderBy("nome")));
  return snap.docs.map((d) => ({ id: d.id, ...d.data() }));
}

export async function definirPapel(uid, papel) {
  await updateDoc(doc(db, "usuarios", uid), { papel, atualizadoEm: serverTimestamp() });
}

export async function excluirUsuario(uid) {
  await deleteDoc(doc(db, "usuarios", uid));
}

export function observarAuth(callback) {
  return onAuthStateChanged(auth, callback);
}

export function sair() {
  return signOut(auth);
}

export async function usuarioAtual() {
  return new Promise((resolve) => {
    const unsub = onAuthStateChanged(auth, (u) => {
      unsub();
      resolve(u);
    });
  });
}

/* ---------- Servicos ---------- */

export async function listarServicos(incluirInativos = false) {
  const base = collection(db, "servicos");
  const q = incluirInativos
    ? query(base, orderBy("nome"))
    : query(base, where("ativo", "==", true), orderBy("nome"));
  const snap = await getDocs(q);
  return snap.docs.map((d) => ({ id: d.id, ...d.data() }));
}

export async function salvarServico(dados) {
  const payload = {
    nome: dados.nome,
    descricao: dados.descricao || "",
    duracaoMin: Number(dados.duracaoMin) || 60,
    preco: Number(dados.preco) || 0,
    ativo: dados.ativo !== false
  };
  if (dados.id) {
    const { id, ...resto } = dados;
    await updateDoc(doc(db, "servicos", id), { ...resto, atualizadoEm: serverTimestamp() });
    return id;
  }
  const ref = await addDoc(collection(db, "servicos"), { ...payload, criadoEm: serverTimestamp() });
  return ref.id;
}

export async function excluirServico(id) {
  await deleteDoc(doc(db, "servicos", id));
}

/* ---------- Agendamentos ---------- */

export async function listarAgendamentos({ data, status, limite } = {}) {
  const partes = [collection(db, "agendamentos")];
  if (status) partes.push(where("status", "==", status));
  if (data) partes.push(where("data", "==", data));
  partes.push(orderBy("horaInicio", "asc"));
  if (limite) partes.push(limit(limite));
  const snap = await getDocs(query(...partes));
  return snap.docs.map((d) => ({ id: d.id, ...d.data() }));
}

/** Documento unico por data+hora: garante que o horario e travado no banco. */
export function idReserva(data, hora) {
  return `${data}_${String(hora).replace(":", "")}`;
}

export async function salvarAgendamento(dados) {
  const payload = {
    clienteNome: dados.clienteNome || "",
    clienteTelefone: dados.clienteTelefone || "",
    clienteEmail: dados.clienteEmail || "",
    servicoId: dados.servicoId || "",
    servicoNome: dados.servicoNome || "",
    data: dados.data || "",
    horaInicio: dados.horaInicio || "",
    horaFim: dados.horaFim || "",
    status: dados.status || "pendente",
    valor: Number(dados.valor) || 0,
    observacoes: dados.observacoes || ""
  };

  if (dados.id) {
    const { id, ...resto } = dados;
    await updateDoc(doc(db, "agendamentos", id), { ...resto, atualizadoEm: serverTimestamp() });
    return id;
  }

  // Reserva com transacao: se o horario ja estiver marcado, aborta.
  const ref = doc(db, "agendamentos", idReserva(payload.data, payload.horaInicio));
  await runTransaction(db, async (tx) => {
    const snap = await tx.get(ref);
    if (snap.exists() && snap.data().status !== "cancelado") {
      const e = new Error("ocupado");
      e.code = "horario-ocupado";
      throw e;
    }
    tx.set(ref, { ...payload, criadoEm: serverTimestamp(), atualizadoEm: serverTimestamp() });
  });

  return ref.id;
}

/** Agendamentos vinculados ao e-mail de um usuario (pagina "Meus agendamentos"). */
export async function listarMeusAgendamentos(email) {
  if (!email) return [];
  const alvo = String(email).trim().toLowerCase();
  const snap = await getDocs(
    query(
      collection(db, "agendamentos"),
      where("clienteEmail", "==", alvo),
      orderBy("data", "desc")
    )
  );
  return snap.docs
    .map((d) => ({ id: d.id, ...d.data() }))
    .filter((a) => a.status === "confirmado" || a.status === "concluido");
}

export async function definirStatus(id, status) {
  await updateDoc(doc(db, "agendamentos", id), { status, atualizadoEm: serverTimestamp() });
}

/* ---------- Envio de e-mail ---------- */

export function emailConfigurado() {
  return !!EMAIL_CHAVE && !EMAIL_CHAVE.startsWith("COLE_AQUI");
}

/**
 * Envia o e-mail de confirmacao para o cliente.
 * Silencioso: se nao houver chave configurada, apenas nao envia.
 */
export async function enviarEmailConfirmacao(ag, tipo = "confirmacao") {
  if (!emailConfigurado()) return { ok: false, motivo: "nao-configurado" };
  if (!ag?.clienteEmail) return { ok: false, motivo: "sem-email" };

  const quando = tipo === "alteracao"
    ? "Seu horario foi alterado"
    : tipo === "cancelamento"
      ? "Seu agendamento foi cancelado"
      : "Seu agendamento esta confirmado";

  const assunto = `${quando} - Dra. Edilaine Silva`;

  const corpo = `
<div style="font-family:Inter,Segoe UI,Arial,sans-serif;max-width:520px;margin:0 auto;background:#f7f7fb;padding:24px;border-radius:14px">
  <div style="background:#ffffff;border-radius:12px;padding:28px;border:1px solid #e6e6ef">
    <div style="text-align:center;margin-bottom:22px">
      <div style="display:inline-block;background:#7c5cbf;color:#fff;padding:10px 18px;border-radius:10px;font-weight:700">
        Edilaine Psicanálise
      </div>
    </div>

    <h1 style="font-size:20px;color:#1c1c28;margin:0 0 6px">Olá, ${escaparHtml(ag.clienteNome || "")}!</h1>
    <p style="font-size:15px;color:#6b6b7b;margin:0 0 22px">${quando}.</p>

    <table style="width:100%;border-collapse:collapse;font-size:14px;margin-bottom:22px">
      <tr>
        <td style="padding:10px 0;border-bottom:1px solid #f0f0f5;color:#6b6b7b;width:110px">Data</td>
        <td style="padding:10px 0;border-bottom:1px solid #f0f0f5;color:#1c1c28;font-weight:600">${formatarDataBR(ag.data)}</td>
      </tr>
      <tr>
        <td style="padding:10px 0;border-bottom:1px solid #f0f0f5;color:#6b6b7b">Horário</td>
        <td style="padding:10px 0;border-bottom:1px solid #f0f0f5;color:#1c1c28;font-weight:600">${escaparHtml(ag.horaInicio)} às ${escaparHtml(ag.horaFim)}</td>
      </tr>
      ${ag.servicoNome ? `<tr>
        <td style="padding:10px 0;border-bottom:1px solid #f0f0f5;color:#6b6b7b">Serviço</td>
        <td style="padding:10px 0;border-bottom:1px solid #f0f0f5;color:#1c1c28;font-weight:600">${escaparHtml(ag.servicoNome)}</td>
      </tr>` : ""}
      ${ag.valor ? `<tr>
        <td style="padding:10px 0;border-bottom:1px solid #f0f0f5;color:#6b6b7b">Valor</td>
        <td style="padding:10px 0;border-bottom:1px solid #f0f0f5;color:#1c1c28;font-weight:600">${formatarMoeda(ag.valor)}</td>
      </tr>` : ""}
    </table>

    <div style="background:#f0ebfb;border-radius:10px;padding:14px 16px;font-size:13.5px;color:#5f44a0">
      Precisa alterar ou cancelar? Fale pelo WhatsApp:
      <strong>(11) 99652-0207</strong>
    </div>

    <p style="margin:22px 0 0;font-size:13px;color:#6b6b7b">
      Dra. Edilaine Silva &middot; Psicóloga Clínica &middot; CRP 06/123456<br>
      Suzano - SP
    </p>
  </div>
</div>`;

  try {
    const res = await fetch("https://api.web3forms.com/submit", {
      method: "POST",
      headers: { "Content-Type": "application/json", Accept: "application/json" },
      body: JSON.stringify({
        access_key: EMAIL_CHAVE,
        from_name: "Edilaine Psicanálise",
        replyto: ag.clienteEmail,
        to: EMAIL_DESTINO,
        subject: assunto,
        html: corpo,
        text: `${quando}\n\nCliente: ${ag.clienteNome}\nData: ${formatarDataBR(ag.data)}\nHorário: ${ag.horaInicio} às ${ag.horaFim}\nServiço: ${ag.servicoNome || "-"}\n\nDra. Edilaine Silva - Suzano/SP`
      })
    });
    const j = await res.json().catch(() => ({}));
    return { ok: !!j.success, resposta: j };
  } catch (e) {
    return { ok: false, motivo: e.message };
  }
}

/* ---------- Solicitacoes do cliente (nao alteram o agendamento) ---------- */

export async function criarSolicitacao(dados) {
  const ref = await addDoc(collection(db, "solicitacoes"), {
    agendamentoId: dados.agendamentoId || "",
    tipo: dados.tipo || "alteracao",
    clienteNome: dados.clienteNome || "",
    clienteEmail: dados.clienteEmail || "",
    dataAtual: dados.dataAtual || "",
    horaAtual: dados.horaAtual || "",
    dataNova: dados.dataNova || "",
    horaNova: dados.horaNova || "",
    motivo: dados.motivo || "",
    status: "pendente",
    criadoEm: serverTimestamp()
  });
  return ref.id;
}

/** Solicitações de um agendamento (para o cliente acompanhar). */
export async function listarSolicitacoesDoAgendamento(agendamentoId) {
  const snap = await getDocs(query(collection(db, "solicitacoes"), where("agendamentoId", "==", agendamentoId)));
  return snap.docs.map((d) => ({ id: d.id, ...d.data() }));
}

/** Todas as solicitações pendentes (admin). */
export async function listarSolicitacoes() {
  const snap = await getDocs(collection(db, "solicitacoes"));
  return snap.docs
    .map((d) => ({ id: d.id, ...d.data() }))
    .sort((a, b) => (b.criadoEm?.seconds || 0) - (a.criadoEm?.seconds || 0));
}

export async function resolverSolicitacao(id, novoStatus) {
  await updateDoc(doc(db, "solicitacoes", id), { status: novoStatus, atualizadoEm: serverTimestamp() });
}

export async function excluirAgendamento(id) {
  await deleteDoc(doc(db, "agendamentos", id));
}

/* ---------- Bloqueios manuais de horario ---------- */

export async function listarBloqueios(data) {
  // Consulta simples (so um filtro) para nao depender de indice composto.
  const snap = await getDocs(query(collection(db, "bloqueios"), where("data", "==", data)));
  return snap.docs
    .map((d) => ({ id: d.id, ...d.data() }))
    .sort((a, b) => String(a.horaInicio).localeCompare(String(b.horaInicio)));
}

export async function fecharHorario(data, hora, motivo = "") {
  const existentes = await listarBloqueios(data);
  if (existentes.some((b) => b.horaInicio === hora)) return;
  await addDoc(collection(db, "bloqueios"), {
    data,
    horaInicio: hora,
    motivo,
    criadoEm: serverTimestamp()
  });
}

export async function reabrirHorario(id) {
  await deleteDoc(doc(db, "bloqueios", id));
}

/* ---------- Utilidades ---------- */

export function mensagemErro(erro) {
  const mapa = {
    "auth/invalid-credential": "E-mail ou senha invalidos.",
    "auth/user-not-found": "E-mail ou senha invalidos.",
    "auth/wrong-password": "E-mail ou senha invalidos.",
    "auth/invalid-email": "E-mail invalido.",
    "auth/email-already-in-use": "Este e-mail ja esta cadastrado.",
    "auth/weak-password": "A senha deve ter no minimo 6 caracteres.",
    "auth/too-many-requests": "Muitas tentativas. Aguarde um instante.",
    "auth/popup-closed-by-user": "Login cancelado.",
    "auth/popup-blocked": "O navegador bloqueou o popup. Permita popups e tente de novo.",
    "auth/unauthorized-domain": "Dominio nao autorizado no Firebase Authentication.",
    "auth/operation-not-allowed": "Login com Google nao ativado no Firebase Console.",
    "permission-denied": "Sem permissao para esta operacao.",
    "horario-ocupado": "Este horario acabou de ser agendado por outra pessoa.",
    "aborted": "Este horario acabou de ser agendado por outra pessoa.",
    "nao-autenticado": "Faca login novamente."
  };
  return mapa[erro?.code] || erro?.message || "Erro inesperado.";
}

export function hojeISO() {
  const d = new Date();
  const off = d.getTimezoneOffset();
  return new Date(d.getTime() - off * 60000).toISOString().slice(0, 10);
}

export function somarMinutos(hora, minutos) {
  const [h, m] = hora.split(":").map(Number);
  const total = h * 60 + m + Number(minutos || 0);
  const hh = String(Math.floor(total / 60) % 24).padStart(2, "0");
  const mm = String(total % 60).padStart(2, "0");
  return `${hh}:${mm}`;
}

export function formatarDataBR(iso) {
  if (!iso) return "";
  const [a, m, d] = iso.split("-");
  return `${d}/${m}/${a}`;
}

export function formatarMoeda(v) {
  return (Number(v) || 0).toLocaleString("pt-BR", { style: "currency", currency: "BRL" });
}

export function escaparHtml(txt) {
  return String(txt ?? "").replace(/[&<>"']/g, (c) => ({
    "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;"
  }[c]));
}