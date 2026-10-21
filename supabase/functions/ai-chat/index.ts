// ════════════════════════════════════════════════════════════
//  PLN — AI chat Edge Function (Supabase / Deno)
//  Claude API'ga proxy. ANTHROPIC_API_KEY maxfiy sir sifatida
//  faqat serverda bo'ladi, brauzerga hech qachon chiqmaydi.
//
//  Deploy:
//    1) Supabase panel → Edge Functions → "ai-chat" yarating, shu kodni joylang
//    2) Yoki CLI: supabase functions deploy ai-chat
//  Sirni qo'shish:
//    Supabase panel → Edge Functions → Secrets → ANTHROPIC_API_KEY = sk-ant-...
// ════════════════════════════════════════════════════════════
import { serve } from "https://deno.land/std@0.224.0/http/server.ts";

const ANTHROPIC_API_KEY = Deno.env.get("ANTHROPIC_API_KEY") ?? "";
const MODEL = "claude-sonnet-4-6";

const CORS = {
  "Access-Control-Allow-Origin": "*",
  "Access-Control-Allow-Headers": "authorization, x-client-info, apikey, content-type",
  "Access-Control-Allow-Methods": "POST, OPTIONS",
};

function jsonResponse(obj: unknown, status = 200): Response {
  return new Response(JSON.stringify(obj), {
    status,
    headers: { ...CORS, "content-type": "application/json" },
  });
}

serve(async (req: Request): Promise<Response> => {
  if (req.method === "OPTIONS") return new Response("ok", { headers: CORS });

  try {
    const { message, lang } = await req.json();
    const language = ["uz", "ru", "en"].includes(lang) ? lang : "uz";
    if (!message || typeof message !== "string") {
      return jsonResponse({ error: "message required" }, 400);
    }

    // Kalit yo'q bo'lsa — soxta (kalit so'z) javob
    if (!ANTHROPIC_API_KEY) {
      return jsonResponse({ reply: mockReply(message, language) });
    }

    const langName: Record<string, string> = { uz: "o'zbek", ru: "rus", en: "ingliz" };
    const system =
      `Sen PLN (Personal Learning Network) platformasining shaxsiy o'quv ` +
      `yordamchisisan. Connectivism va SDL (mustaqil o'rganish) nazariyasi ` +
      `asosida qisqa, amaliy javob ber. Javobni ${langName[language]} tilida yoz. 2-4 gap.`;

    const resp = await fetch("https://api.anthropic.com/v1/messages", {
      method: "POST",
      headers: {
        "content-type": "application/json",
        "x-api-key": ANTHROPIC_API_KEY,
        "anthropic-version": "2023-06-01",
      },
      body: JSON.stringify({
        model: MODEL,
        max_tokens: 600,
        system,
        messages: [{ role: "user", content: message }],
      }),
    });

    if (!resp.ok) return jsonResponse({ reply: mockReply(message, language) });
    const data = await resp.json();
    const reply = data?.content?.[0]?.text ?? mockReply(message, language);
    return jsonResponse({ reply });
  } catch (e) {
    return jsonResponse({ error: String(e) }, 500);
  }
});

// Kalit bo'lmaganda ishlaydigan zaxira javoblar
function mockReply(message: string, lang: string): string {
  const lower = message.toLowerCase();
  const R: Record<string, Array<[string[], string]>> = {
    uz: [
      [["python", "kod", "dastur"], "Python uchun: Codecademy, freeCodeCamp va repl.it. Kuniga 1 soat amaliy mashq qiling! 🐍"],
      [["sdl", "mustaqil"], "SDL ko'rsatkichingiz bu hafta 87 ga yetdi! Maqsad qo'yib, taraqqiyotingizni kuzating. 🎯"],
      [["vaqt", "rej", "soat", "plan"], "Pomodoro: 25 daqiqa o'qish + 5 daqiqa dam. Samaradorlik 40% oshadi! ⏱️"],
      [["salom", "assalom"], "Assalomu alaykum! O'qish yoki rejalashtirish haqida so'rang. 😊"],
    ],
    ru: [
      [["python", "код"], "Для Python: Codecademy, freeCodeCamp, repl.it. Практикуйтесь час в день! 🐍"],
      [["sdl", "само"], "Ваш SDL достиг 87! Ставьте цели и отслеживайте прогресс. 🎯"],
      [["время", "план"], "Помодоро: 25 мин работы + 5 мин отдыха. +40% продуктивности! ⏱️"],
      [["привет"], "Привет! Спрашивайте об учёбе или планировании. 😊"],
    ],
    en: [
      [["python", "code"], "For Python: Codecademy, freeCodeCamp, repl.it. Practice 1 hour daily! 🐍"],
      [["sdl", "self"], "Your SDL score hit 87! Keep setting goals and tracking progress. 🎯"],
      [["time", "plan"], "Pomodoro: 25 min focus + 5 min break. +40% productivity! ⏱️"],
      [["hi", "hello"], "Hello! Ask about studying or planning. 😊"],
    ],
  };
  const list = R[lang] ?? R.en;
  for (const [keys, reply] of list) {
    if (keys.some((k) => lower.includes(k))) return reply;
  }
  const def: Record<string, string> = {
    uz: "Qiziq savol! Qo'shimcha ma'lumot bering, ko'proq yordam beraman. 🤔",
    ru: "Интересный вопрос! Дайте больше деталей. 🤔",
    en: "Interesting! Give me more details and I'll help better. 🤔",
  };
  return def[lang] ?? def.en;
}
