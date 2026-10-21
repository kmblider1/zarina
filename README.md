# PLN — Personal Learning Network (Shaxsiy O'rganish Tarmog'i)

Tarmoqli ta'lim va uni nazorat qilish (monitoring) tizimi. Talabalar bilimni
qayerdan o'rganayotganini kunlik belgilaydi, tizim esa ularning o'rganish
tarmog'ini (knowledge network) va ekran vaqtini tahlil qilib, o'qituvchi va
ota-onaga ko'rsatib beradi.

> Hozircha tizim **faqat oliy ta'lim muassasalari** uchun mo'ljallangan, chunki
> har bir o'quvchida telefon yoki elektron qurilma bo'lmasligi mumkin.

## G'oya

1. **Manba belgilash** — talaba uyga vazifani qaysi manbadan o'rganganini har
   kuni qayd etadi: platforma, o'qituvchi, YouTube, ijtimoiy tarmoqlar, kitob va
   boshqalar.
2. **Ekran vaqti nazorati (roziligi bilan)** — saytga kirganda o'quvchi ekran
   vaqti monitoringiga rozilik beradi. Bu o'qituvchi va ota-onaga farzandi
   qancha vaqtni nimaga sarflaganini ko'rsatadi.
3. **Foydali / befoyda kontent tahlili** — ijtimoiy tarmoqlardagi vaqt foydali
   (o'rganishga oid) yoki chalg'ituvchi (befoyda) toifalarga ajratiladi.
4. **Bilim tarmog'i (Connectivism)** — talaba bir mavzu bo'yicha qancha ko'p
   manbadan izlansa, shuncha ko'p **aloqa tugunlari (nodes & connections)**
   hosil bo'ladi. Tarmoq qanchalik zich bo'lsa, bilim shunchalik mustahkam.
5. **SDL (Self-Directed Learning)** — o'z-o'zini boshqarib o'rganish
   ko'nikmasi Zimmerman modeli asosida o'lchanadi: maqsad qo'yish, monitoring,
   strategiya tanlash, o'z-o'zini baholash, motivatsiya.

## Bo'limlar

| Bo'lim | Tavsifi |
|--------|---------|
| **Dashboard** | Umumiy ko'rinish: nodes, manbalar, o'rganish vaqti, SDL skori |
| **Manbalar** | O'rganilgan manbalarni qo'shish va AI bilan tahlil qilish |
| **Bilim tarmog'i** | Connectivism modeli — tugunlar va bog'lanishlar vizualizatsiyasi |
| **SDL Tracker** | Kunlik "bugun nima o'rgandim?" va o'z-o'zini baholash |
| **AI Tavsiya** | Shaxsiy o'rganish yo'l xaritasi (Claude AI) |
| **O'qituvchi paneli** | Guruh holati, har bir o'quvchi SDL darajasi |
| **Ekran vaqti** | Platformalar bo'yicha vaqt, limitlar, ogohlantirishlar, heatmap |

## Bo'limlar (yangi dizayn)

| Bo'lim | Tavsifi |
|--------|---------|
| **Kirish** | Email/parol + rol tanlash (Talaba / O'qituvchi / Ota-ona), 3 til (UZ/RU/EN) |
| **Rozilik** | Talaba ekran vaqti nazoratiga rasman rozilik beradi |
| **VARK Testi** | 6 savollik o'rganuvchi tipi testi (Vizual/Audial/O'qish/Amaliy) |
| **Dashboard** | Haftalik o'qish, top ilovalar, SDL gauge, AI tavsiya, maqsadlar |
| **SDL Grafigi** | 8 haftalik SDL chizig'i va batafsil jadval |
| **AI Yordamchi** | To'liq chat oynasi (UZ/RU/EN) |
| **Bilim Tarmog'i** | Tugunlar va bog'lanishlar grafigi, tugun tafsilotlari |
| **O'qituvchi Paneli** | Guruh statistikasi, o'quvchilar ro'yxati, xavf darajasi |
| **Ekran Vaqti** | Ilovalar tahlili, foydali/chalg'ituvchi taqsimot (donut) |

## Texnologiya

Frontend — bitta faylli ilova (`index.html`):
- Sof HTML + CSS + JavaScript (framework yo'q)
- Grafiklar to'liq SVG bilan chiziladi (bar, gauge, line, donut, knowledge graph)
- 3 tilli interfeys (UZ / RU / EN)
- Supabase sozlanmagan bo'lsa — demo rejimda mustaqil ishlaydi

Backend uchun **2 variant** (ikkalasi ham tayyor):

| Variant | Tavsifi | Qo'llanma |
|---------|---------|-----------|
| 🟢 **Supabase** (tavsiya) | Bepul Postgres + Auth + Edge Functions. Netlify bilan mukammal. | [`SUPABASE_SETUP.md`](./SUPABASE_SETUP.md) |
| 🔵 **PHP + MySQL** | Timeweb kabi umumiy hosting uchun. | [`DEPLOY.md`](./DEPLOY.md) |

> Frontend/backend bo'linishi va API ro'yxati: [`FRONTEND_BACKEND.md`](./FRONTEND_BACKEND.md).

## Joylashuv (deploy)

- **Demo (frontend):** Netlify — `netlify.toml` tayyor, faqat repo'ni ulang
- **To'liq versiya:** Netlify (frontend) + Supabase (backend) — `SUPABASE_SETUP.md`

## Ishga tushirish (lokal demo)

`index.html` faylini brauzerda oching, yoki oddiy server bilan:

```bash
python3 -m http.server 8000
# keyin brauzerda: http://localhost:8000
```

## Keyingi qadamlar (reja)

- [x] **Frontend** — 9 ekran, 3 til, grafiklar
- [x] **Backend (Supabase va PHP)** — auth, rozilik, AI proxy, baza
- [x] **Claude API xavfsizligi** — kalit faqat serverda (Edge Function / PHP)
- [x] **Foydalanuvchi rollari** — talaba / o'qituvchi / ota-ona
- [x] **Rozilik (consent) oqimi**
- [ ] **Haqiqiy ekran vaqti yig'ish** — qurilma/brauzer integratsiyasi
- [ ] **Dashboard grafiklarini real ma'lumotga ulash** (`study_logs`, `screen_time`)
- [ ] **Bilim tarmog'ini avtomatik qurish** — qo'shilgan manbalardan nodes hosil qilish
