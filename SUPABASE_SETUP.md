# Supabase bilan to'liq versiyani ishga tushirish

Bu qo'llanma PLN'ni **Supabase** (bepul Postgres baza + Auth + Edge Functions)
bilan to'liq ishlaydigan qilib ishga tushiradi. Frontend **Netlify**'da qoladi.

```
Netlify (index.html)  →  Supabase  (baza + login + AI proxy)  →  Claude API
```

> ⏱️ Taxminan 15-20 daqiqa. Kod tayyor — siz asosan tugma bosasiz.

---

## 1-qadam — Supabase loyiha yaratish

1. [supabase.com](https://supabase.com) → **Sign in** (GitHub bilan) → **New project**
2. Nomi: `pln`, parol o'ylab toping (eslab qoling), region: Europe (eng yaqini)
3. **Create new project** → ~2 daqiqa kutiladi

---

## 2-qadam — Bazani yaratish (SQL)

1. Chap menyu → **SQL Editor** → **New query**
2. Loyihadagi **`supabase/schema.sql`** faylini to'liq nusxalab, oynaga joylang
3. **Run** bosing → "Success" chiqishi kerak (jadvallar + xavfsizlik yaratiladi)

---

## 3-qadam — Demo foydalanuvchilarni yaratish

1. Chap menyu → **Authentication** → **Users** → **Add user** → **Create new user**
2. Quyidagi 3 tasini yarating (har birida **"Auto Confirm User"** ni yoqing):

   | Email | Parol |
   |-------|-------|
   | `talaba@pln.uz` | `pln12345` |
   | `ustoz@pln.uz`  | `pln12345` |
   | `otaona@pln.uz` | `pln12345` |

3. So'ng **SQL Editor** ga qayting → `schema.sql` oxiridagi `UPDATE ...` va
   `INSERT INTO relations ...` qismini qayta **Run** bosing (rollarni to'g'rilaydi).
   *(Yoki butun `schema.sql` ni qayta ishga tushiring — xatosiz qayta bajariladi.)*

---

## 4-qadam — AI chat funksiyasini qo'shish (Edge Function)

**Oson yo'l (panel orqali):**
1. Chap menyu → **Edge Functions** → **Create a function** → nom: `ai-chat`
2. Loyihadagi **`supabase/functions/ai-chat/index.ts`** kodini joylang → **Deploy**
3. **Edge Functions → Secrets** (yoki Settings) → **Add secret**:
   - Nomi: `ANTHROPIC_API_KEY`
   - Qiymati: Claude API kalitingiz (`sk-ant-...`) —
     [console.anthropic.com](https://console.anthropic.com) → API Keys

> Kalit qo'shmasangiz ham ishlaydi — AI oddiy (kalit so'z) javoblar beradi.

---

## 5-qadam — Frontend'ni Supabase'ga ulash

1. Supabase panel → **Settings** → **API** bo'limidan 2 narsani oling:
   - **Project URL** (masalan `https://abcdxyz.supabase.co`)
   - **anon public** kaliti (bu brauzerda ochiq bo'lishi xavfsiz)
2. `index.html` faylida quyidagi 2 qatorni to'ldiring (fayl boshidagi
   `SUPABASE BACKEND` bo'limida):
   ```js
   const SUPABASE_URL = 'https://abcdxyz.supabase.co';
   const SUPABASE_ANON_KEY = 'eyJhbGci...';   // anon public kaliti
   ```
3. O'zgarishni saqlang va GitHub'ga push qiling (yoki Netlify'ga qayta yuklang).
   Netlify avtomatik yangilaydi.

---

## 6-qadam — Tekshirish

1. Netlify saytingizni oching → `talaba@pln.uz` / `pln12345` bilan kiring
2. Endi login **haqiqiy** (Supabase Auth orqali) ishlaydi
3. **AI Yordamchi** da savol yozing → Claude javob beradi (kalit qo'shilgan bo'lsa)
4. Supabase panel → **Table Editor** → `chat_messages`, `consents`, `vark_results`
   jadvallarida yangi yozuvlar paydo bo'ladi ✅

---

## Hozir nima "haqiqiy" ishlaydi

| Funksiya | Holat |
|----------|:---:|
| Login (Supabase Auth) | ✅ haqiqiy |
| Rol (profil asosida) | ✅ haqiqiy |
| AI chat (Claude proxy) | ✅ haqiqiy |
| Rozilik, VARK natija, chat tarixi — bazaga saqlanadi | ✅ haqiqiy |
| Dashboard / SDL / Ekran vaqti grafiklari | ⏳ hozircha demo ko'rsatkich |

> Dashboard grafiklari hali demo, chunki yangi akkauntда haqiqiy o'quv
> ma'lumoti yo'q. Keyingi bosqichda `study_logs` va `screen_time` jadvallariga
> ma'lumot yig'ilib, grafiklar real hisobдан chiziladi.

---

## Muammolar

| Muammo | Yechim |
|--------|--------|
| Login ishlamaydi | 3-qadam bajarildimi? Email/parol to'g'rimi? |
| "Invalid API key" | 5-qadamdagi **anon** kalit (service_role EMAS) ekanini tekshiring |
| AI javob bermaydi | 4-qadam: funksiya deploy + `ANTHROPIC_API_KEY` sir qo'shildimi? |
| Ma'lumot saqlanmayapti | `schema.sql` to'liq Run bo'ldimi (RLS siyosatlari)? |

To'liq API/jadval tavsifi: [`FRONTEND_BACKEND.md`](./FRONTEND_BACKEND.md).
