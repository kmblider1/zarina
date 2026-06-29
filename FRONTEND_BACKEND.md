# Frontend / Backend Bo'linishi

Bu hujjat **siz backend yozishingiz** uchun yo'l xaritasi. Frontend (dizayn)
tayyor — `index.html`. Hozir undagi barcha ma'lumotlar **soxta (mock)**, ya'ni
kod ichiga yozib qo'yilgan. Backend vazifasi — shu ma'lumotlarni haqiqiy
bazadan API orqali yetkazib berish.

---

## 1. Umumiy arxitektura

```
┌─────────────────────┐         HTTPS / JSON          ┌──────────────────────┐
│   FRONTEND          │  ───────────────────────────► │   BACKEND (siz)      │
│   index.html        │  ◄─────────────────────────── │   API server         │
│   (brauzer)         │                                │                      │
│  - 9 ekran          │                                │  - Auth (login/rol)  │
│  - grafiklar        │                                │  - Ma'lumotlar bazasi│
│  - VARK test        │                                │  - AI proxy (Claude) │
└─────────────────────┘                                └──────────────────────┘
```

- **Frontend** faqat ko'rsatadi va so'rov yuboradi. Hech qanday maxfiy kalit
  (API key, parol) frontendda saqlanmaydi.
- **Backend** — autentifikatsiya, ma'lumotlar bazasi va Claude API'ga proxy.
  Texnologiyani o'zingiz tanlaysiz (Node.js/Express, Python/FastAPI, yoki PHP/Laravel).

---

## 2. Hozir mock bo'lgan ma'lumotlar (kodda qayerda)

`index.html` ichidagi `computeVals()` funksiyasi barcha ma'lumotni qaytaradi.
Backend ulanganda bu qiymatlar `fetch()` orqali serverdan kelishi kerak:

| Frontend o'zgaruvchisi | Ekran | Backend manbai |
|------------------------|-------|----------------|
| `statsCards`, `studyBarChart`, `appUsage`, `goalItems`, `aiTipText` | Dashboard | `GET /api/dashboard` |
| `sdlStatCards`, `sdlRows`, `sdlLineChart` | SDL Grafigi | `GET /api/sdl` |
| `chatMsgs` + `getAIReply()` | AI Yordamchi | `POST /api/ai/chat` |
| `KGD`, `KGE` (knowledge graph) | Bilim Tarmog'i | `GET /api/knowledge` |
| `students`, `teacherStats` | O'qituvchi Paneli | `GET /api/teacher/students` |
| `timeCards`, `screenApps`, `donutSegs` | Ekran Vaqti | `GET /api/screentime` |
| `VARK_QS` + natija | VARK Testi | `GET /api/vark/questions`, `POST /api/vark/submit` |

---

## 3. Tavsiya etilgan API endpointlar

### 3.1 Autentifikatsiya va rollar

```
POST /api/auth/login
  body:  { email, password, role }     // role: "student" | "teacher" | "parent"
  resp:  { token, user: { id, name, role, lang } }

POST /api/auth/logout
GET  /api/auth/me                       // joriy foydalanuvchi (token bo'yicha)
```

> Frontendda `handleLogin` hozir to'g'ridan-to'g'ri ekran almashtiradi. Backend
> ulanganda bu yerda `POST /api/auth/login` chaqiriladi va `token` saqlanadi
> (localStorage yoki cookie).

### 3.2 Rozilik (consent) — talaba uchun

```
POST /api/consent
  body:  { agreed: true, timestamp }
  resp:  { ok: true }
```

> Bu g'oyaning huquqiy asosi — o'quvchi ekran vaqti nazoratiga rasman rozilik
> berganini yozib qo'yish.

### 3.3 Manba / kunlik o'rganish qaydi (g'oyaning markazi)

```
POST /api/study-log
  body:  { topic, source, minutes, date }
         // source: "youtube" | "teacher" | "social" | "book" | "platform" | "practice"
  resp:  { ok: true, nodeId }          // qaysi bilim tuguniga bog'landi

GET  /api/study-log?from=&to=          // tarix
```

### 3.4 Dashboard / SDL / Knowledge / Screen time (o'qish)

```
GET /api/dashboard        // statsCards, haftalik soatlar, top ilovalar, SDL, maqsadlar
GET /api/sdl              // 8 haftalik SDL qiymatlari + jadval
GET /api/knowledge        // tugunlar (nodes) va bog'lanishlar (edges)
GET /api/screentime?date= // platformalar, foydali/chalg'ituvchi taqsimot
```

### 3.5 AI Yordamchi (eng muhim — xavfsizlik)

```
POST /api/ai/chat
  body:  { message, lang }              // lang: "uz" | "ru" | "en"
  resp:  { reply }
```

> ⚠️ **Muhim:** Frontenddagi `getAIReply()` — bu vaqtinchalik soxta javob
> beruvchi. Haqiqiy Claude API **backendda** chaqirilishi shart, chunki API
> kalit maxfiy. Frontend hech qachon Anthropic API'ga to'g'ridan-to'g'ri
> murojaat qilmasligi kerak (kalit ochilib qoladi + CORS bloklaydi).
>
> Backend oqimi: `frontend → POST /api/ai/chat → backend → Anthropic API → javob → frontend`.

### 3.6 VARK test

```
GET  /api/vark/questions
POST /api/vark/submit
  body:  { answers: ["V","A","R",...] }
  resp:  { topType: "V", scores: { V, A, R, K } }
```

### 3.7 O'qituvchi / Ota-ona

```
GET /api/teacher/students            // o'qituvchi guruhi
GET /api/teacher/students/:id        // bitta o'quvchi tafsiloti
GET /api/parent/child                // ota-onaning farzandi ma'lumoti
```

---

## 4. Ma'lumotlar bazasi (taklif qilingan jadvallar)

```
users           (id, name, email, password_hash, role, lang, created_at)
consents        (id, user_id, agreed, created_at)
study_logs      (id, user_id, topic, source, minutes, date, node_id)
knowledge_nodes (id, user_id, label, mastery, time_spent, conns)
node_edges      (id, source_node_id, target_node_id, strength)
screen_time     (id, user_id, app_name, category, minutes, date)
                 -- category: "productive" | "neutral" | "distraction"
sdl_scores      (id, user_id, week, score, study_minutes)
vark_results    (id, user_id, top_type, v, a, r, k, created_at)
chat_messages   (id, user_id, role, text, created_at)
relations       (id, teacher_id|parent_id, student_id)  -- kim kimni ko'radi
```

---

## 5. Rollar va ruxsatlar (kim nimani ko'radi)

| Rol | Ko'radigan bo'limlar | API ruxsati |
|-----|----------------------|-------------|
| **Talaba** | Dashboard, SDL, AI, Bilim Tarmog'i, VARK | faqat **o'z** ma'lumoti |
| **O'qituvchi** | Dashboard, Bilim Tarmog'i, O'qituvchi Paneli, Ekran Vaqti | **guruhidagi** o'quvchilar |
| **Ota-ona** | Dashboard, Ekran Vaqti | faqat **farzandi** |

> Frontendda bu menyu darajasida `navDefs` orqali ajratilgan. Backend ham har
> bir so'rovda rolni tekshirib, faqat ruxsat etilgan ma'lumotni qaytarishi shart
> (faqat frontendga ishonib bo'lmaydi).

---

## 6. Ekran vaqti ma'lumotini yig'ish (kelajak)

Eng murakkab qism — haqiqiy ekran vaqtini olish. Variantlar:
1. **Brauzer kengaytmasi** (extension) — qaysi saytda qancha vaqt o'tkazgani.
2. **Mobil ilova** — Android `UsageStatsManager`, iOS Screen Time API.
3. **Qo'lda kiritish** — o'quvchi o'zi belgilaydi (eng oddiy boshlang'ich variant).

> Boshlanishiga **3-variant** (qo'lda) bilan boshlash mumkin — `POST /api/study-log`
> orqali. Keyin avtomatik yig'ishga o'tasiz.

---

## 7. Birinchi qadamlar (tavsiya)

1. Backend tanlang (masalan Node.js + Express + PostgreSQL).
2. `POST /api/auth/login` va `GET /api/auth/me` ni yozing.
3. `POST /api/ai/chat` ni yozing (Claude API proxy) — frontenddagi `getAIReply`
   o'rniga.
4. `GET /api/dashboard` ni yozing — birinchi haqiqiy ma'lumot.
5. Qolgan endpointlarni asta-sekin ulang.

Frontend tomonida ulash uchun `index.html` da `computeVals()` ichidagi mock
massivlarni `await fetch('/api/...')` natijalari bilan almashtirish kifoya.
```

> Eslatma: hozir frontend mock bilan **mustaqil ishlaydi** — backend tayyor
> bo'lguncha demo sifatida ko'rsatib turish mumkin.
