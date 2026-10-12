import { getAuth } from "https://www.gstatic.com/firebasejs/11.0.1/firebase-auth.js";
import { GOOGLE_CLIENT_ID } from "./config.js";

const SCOPES = [
  "https://www.googleapis.com/auth/calendar.events",
  "https://www.googleapis.com/auth/calendar.readonly"
].join(" ");

const API = "https://www.googleapis.com/calendar/v3";
const TZ = "America/Sao_Paulo";

let tokenClient = null;
let gisPronto = null;

export function temGis() {
  return !!window.google?.accounts?.oauth2;
}

/** Carrega o Google Identity Services sob demanda (preciso em qualquer pagina). */
export function carregarGis() {
  if (temGis()) return Promise.resolve(true);
  if (gisPronto) return gisPronto;

  gisPronto = new Promise((resolve) => {
    if (document.querySelector('script[data-gis]')) {
      const t = setInterval(() => {
        if (temGis()) { clearInterval(t); resolve(true); }
      }, 100);
      setTimeout(() => { clearInterval(t); resolve(temGis()); }, 10000);
      return;
    }
    const s = document.createElement("script");
    s.src = "https://accounts.google.com/gsi/client";
    s.async = true;
    s.defer = true;
    s.dataset.gis = "1";
    s.onload = () => resolve(true);
    s.onerror = () => resolve(false);
    document.head.appendChild(s);
  });

  return gisPronto;
}

async function garantirTokenClient() {
  if (!GOOGLE_CLIENT_ID || GOOGLE_CLIENT_ID.startsWith("COLE_AQUI")) {
    const e = new Error("Configure o GOOGLE_CLIENT_ID em config.js");
    e.code = "missing-client-id";
    throw e;
  }

  await carregarGis();
  if (!temGis()) {
    const e = new Error("Google Identity Services nao carregou. Verifique a conexao e tente de novo.");
    e.code = "gis-nao-carregado";
    throw e;
  }

  if (!tokenClient) {
    tokenClient = window.google.accounts.oauth2.initTokenClient({
      client_id: GOOGLE_CLIENT_ID,
      scope: SCOPES,
      callback: () => {}
    });
  }
  return tokenClient;
}

function temEscopos(resultado) {
  const s = resultado?.scopes || [];
  return SCOPES.split(" ").every((esc) => s.includes(esc));
}

/**
 * Pega access token do Google.
 * Se o login com Google ja trouxer os escopos de calendario, usa direto.
 * Senao pede autorizacao extra ao Google (GIS).
 */
export async function obterToken(cached = false) {
  const user = getAuth().currentUser;
  if (!user) {
    const e = new Error("Faca login primeiro.");
    e.code = "nao-autenticado";
    throw e;
  }

  const resultado = await user.getIdTokenResult();
  if (temEscopos(resultado)) return resultado.accessToken;
  if (cached) return null;

  return new Promise((resolve, reject) => {
    garantirTokenClient()
      .then((client) => {
        client.callback = (resp) => {
          if (resp?.error) reject(erroOAuth(resp));
          else if (resp?.access_token) resolve(resp.access_token);
          else reject(new Error("Nao foi possivel obter autorizacao do Google."));
        };
        client.requestAccessToken({ prompt: cached ? "" : "consent" });
      })
      .catch(reject);
  });
}

const EXPLICACOES = {
  access_denied: "Voce negate o acesso, ou o app ainda nao foi autorizado no Google Cloud.",
  invalid_scope: "O escopo do calendario nao esta liberado na tela de consentimento do Google Cloud.",
  interaction_required: "E necessario aceitar a permissao. Tente novamente e clique em 'Permitir'.",
  consent_required: "E necessario aceitar a permissao. Tente novamente.",
  popup_closed_by_user: "A janela de autorizacao foi fechada antes de concluir.",
  unauthorized_client: "O Client ID nao pertence a este projeto ou a API do Calendar esta desativada.",
  invalid_client: "GOOGLE_CLIENT_ID invalido. Confira o valor em config.js.",
  access_blocked: "O Google bloqueou o acesso. Revise a tela de consentimento e adicione seu e-mail como usuario de teste."
};

function erroOAuth(resp) {
  const codigo = resp.error || "desconhecido";
  const detalhe = resp.error_description || EXPLICACOES[codigo] || "Verifique a configuracao no Google Cloud Console.";
  const e = new Error(`${codigo}: ${detalhe}`);
  e.code = codigo;
  return e;
}

function corpoEvento(ag) {
  return {
    summary: `${ag.servicoNome || "Consulta"} - ${ag.clienteNome}`,
    description: [
      `Cliente: ${ag.clienteNome}`,
      ag.clienteTelefone ? `Telefone: ${ag.clienteTelefone}` : "",
      ag.clienteEmail ? `E-mail: ${ag.clienteEmail}` : "",
      ag.observacoes ? `Obs: ${ag.observacoes}` : ""
    ].filter(Boolean).join("\n"),
    location: "Consultorio - Dra. Edilaine Silva | Suzano - SP",
    start: { dateTime: `${ag.data}T${ag.horaInicio}:00`, timeZone: TZ },
    end: { dateTime: `${ag.data}T${ag.horaFim}:00`, timeZone: TZ },
    attendees: ag.clienteEmail ? [{ email: ag.clienteEmail }] : [],
    reminders: { useDefault: true }
  };
}

export async function criarEvento(ag, token) {
  const res = await fetch(`${API}/calendars/primary/events`, {
    method: "POST",
    headers: { Authorization: `Bearer ${token}`, "Content-Type": "application/json" },
    body: JSON.stringify(corpoEvento(ag))
  });
  if (!res.ok) throw new Error(await erroApi(res));
  return res.json();
}

export async function atualizarEvento(ag, token) {
  if (!ag.googleEventId) return criarEvento(ag, token);
  const res = await fetch(`${API}/calendars/primary/events/${ag.googleEventId}`, {
    method: "PATCH",
    headers: { Authorization: `Bearer ${token}`, "Content-Type": "application/json" },
    body: JSON.stringify({
      summary: `${ag.servicoNome || "Consulta"} - ${ag.clienteNome}`,
      start: { dateTime: `${ag.data}T${ag.horaInicio}:00`, timeZone: TZ },
      end: { dateTime: `${ag.data}T${ag.horaFim}:00`, timeZone: TZ }
    })
  });
  if (res.status === 404 || res.status === 410) return criarEvento(ag, token);
  if (!res.ok) throw new Error(await erroApi(res));
  return res.json();
}

export async function removerEvento(eventId, token) {
  if (!eventId) return true;
  const res = await fetch(`${API}/calendars/primary/events/${eventId}`, {
    method: "DELETE",
    headers: { Authorization: `Bearer ${token}` }
  });
  if (res.ok || res.status === 404 || res.status === 410) return true;
  throw new Error(await erroApi(res));
}

/** Agenda do dia direto do Google (para conferir lado a lado). */
export async function listarEventosDoDia(data, token) {
  const ini = `${data}T00:00:00`;
  const fim = `${data}T23:59:59`;
  const url = `${API}/calendars/primary/events?timeMin=${ini}:00${encodeURIComponent(TZ)}` +
    `&timeMax=${fim}:00${encodeURIComponent(TZ)}&singleEvents=true&orderBy=startTime`;
  const res = await fetch(url, { headers: { Authorization: `Bearer ${token}` } });
  if (!res.ok) throw new Error(await erroApi(res));
  const data2 = await res.json();
  return data2.items || [];
}

async function erroApi(res) {
  try {
    const j = await res.json();
    return j?.error?.message || `Erro ${res.status} na Google Agenda.`;
  } catch {
    return `Erro ${res.status} na Google Agenda.`;
  }
}

export function resumoEvento(ev) {
  const inicio = ev.start?.dateTime || ev.start?.date || "";
  const hora = inicio.includes("T") ? inicio.split("T")[1].slice(0, 5) : "";
  return {
    id: ev.id,
    titulo: ev.summary || "(sem titulo)",
    hora,
    local: ev.location || ""
  };
}