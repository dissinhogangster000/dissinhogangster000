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
  serverTimestamp
} from "https://www.gstatic.com/firebasejs/11.0.1/firebase-firestore.js";
import { firebaseConfig } from "./config.js";

const app = initializeApp(firebaseConfig);
export const auth = getAuth(app);
export const db = getFirestore(app);

const googleProvider = new GoogleAuthProvider();

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
    await setDoc(ref, { ...dados, criadoEm: serverTimestamp() });
  }
}

/** Garante que o perfil existe ao abrir qualquer pagina do painel. */
export async function garantirPerfil(user) {
  if (!user) return;
  try {
    const ref = doc(db, "usuarios", user.uid);
    const snap = await getDoc(ref);
    if (!snap.exists()) {
      await setDoc(ref, {
        nome: user.displayName || (user.email || "").split("@")[0],
        email: user.email || "",
        tipoLogin: (user.providerData?.[0]?.providerId || "").includes("google") ? "google" : "email",
        criadoEm: serverTimestamp(),
        atualizadoEm: serverTimestamp()
      });
    }
  } catch (e) {
    console.warn("perfil:", e.message);
  }
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

/* ---------- Configuracao de horarios ---------- */

export const CONFIG_PADRAO = {
  horaInicio: "08:00",
  horaFim: "20:00",
  intervaloMin: 30,
  diasSemana: [1, 2, 3, 4, 5, 6],
  ativo: true
};

export async function lerConfiguracaoHorarios() {
  try {
    const snap = await getDoc(doc(db, "configuracoes", "horarios"));
    if (!snap.exists()) return { ...CONFIG_PADRAO };
    const d = snap.data() || {};
    return {
      ...CONFIG_PADRAO,
      ...d,
      diasSemana: Array.isArray(d.diasSemana) ? d.diasSemana : CONFIG_PADRAO.diasSemana
    };
  } catch (e) {
    console.warn("configuracao:", e.message);
    return { ...CONFIG_PADRAO };
  }
}

export async function salvarConfiguracaoHorarios(cfg) {
  await setDoc(doc(db, "configuracoes", "horarios"), {
    horaInicio: cfg.horaInicio || CONFIG_PADRAO.horaInicio,
    horaFim: cfg.horaFim || CONFIG_PADRAO.horaFim,
    intervaloMin: Number(cfg.intervaloMin) || CONFIG_PADRAO.intervaloMin,
    diasSemana: cfg.diasSemana || CONFIG_PADRAO.diasSemana,
    ativo: cfg.ativo !== false,
    atualizadoEm: serverTimestamp()
  });
}

/** Gera a lista de horarios ("HH:MM") conforme a configuracao. */
export function gerarSlots(cfg) {
  const c = { ...CONFIG_PADRAO, ...cfg };
  const [hi, mi] = String(c.horaInicio).split(":").map(Number);
  const [hf, mf] = String(c.horaFim).split(":").map(Number);
  const passo = Math.max(5, Number(c.intervaloMin) || 30);

  let inicio = hi * 60 + mi;
  const fim = hf * 60 + mf;
  const slots = [];

  while (inicio + passo <= fim) {
    slots.push(`${String(Math.floor(inicio / 60)).padStart(2, "0")}:${String(inicio % 60).padStart(2, "0")}`);
    inicio += passo;
  }
  return slots;
}

/** Dia da semana de uma data ISO (0 = domingo ... 6 = sabado). */
export function diaSemanaDe(iso) {
  if (!iso) return -1;
  const [a, m, d] = iso.split("-").map(Number);
  return new Date(a, m - 1, d).getDay();
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
  const ref = await addDoc(collection(db, "agendamentos"), { ...payload, criadoEm: serverTimestamp() });
  return ref.id;
}

export async function definirStatus(id, status) {
  await updateDoc(doc(db, "agendamentos", id), { status, atualizadoEm: serverTimestamp() });
}

export async function excluirAgendamento(id) {
  await deleteDoc(doc(db, "agendamentos", id));
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