# Timeweb hosting'ga yuklash qo'llanmasi

Bu loyiha **Timeweb "Optimo"** (umumiy hosting, PHP + MySQL) uchun tayyorlangan.
Quyidagi qadamlar bilan saytni ishga tushirasiz.

---

## 1. MySQL bazasini yaratish

1. Timeweb panelida **«Базы данных»** bo'limiga kiring.
2. **«Создать базу данных»** tugmasini bosing.
3. Baza nomi, login va parolni eslab qoling (masalan `cq55659_pln`).
4. **phpMyAdmin** ni oching → yaratilgan bazani tanlang → **«Импорт»** →
   loyihadagi **`db.sql`** faylini yuklang va bajaring.
   - Bu barcha jadvallarni va 3 ta demo foydalanuvchini yaratadi.

> **Demo kirish ma'lumotlari** (hammasi uchun parol: `pln12345`):
> - Talaba: `talaba@pln.uz`
> - O'qituvchi: `ustoz@pln.uz`
> - Ota-ona: `otaona@pln.uz`

---

## 2. Konfiguratsiyani sozlash

1. `api/config.example.php` faylidan nusxa oling va **`api/config.php`** deb nomlang.
2. Ichida `db` ma'lumotlarini 1-qadamdagi baza ma'lumotlari bilan to'ldiring:
   ```php
   'db' => [
     'host' => 'localhost',
     'name' => 'cq55659_pln',
     'user' => 'cq55659_pln',
     'pass' => 'BAZA_PAROLI',
     'charset' => 'utf8mb4',
   ],
   ```
3. (Ixtiyoriy) AI chatni jonli qilish uchun `anthropic_api_key` ga Claude API
   kalitini yozing. Bo'sh qoldirsangiz — AI soxta (kalit so'z) javoblar beradi.

---

## 3. Fayllarni FTP orqali yuklash

Panel → **«Доступ по FTP»** ma'lumotlari bilan (FileZilla yoki panel fayl
menejeri) saytning **public papkasiga** (odatda `public_html/` yoki
`/sayt-nomi/public_html/`) quyidagilarni yuklang:

```
public_html/
├── index.html          ← frontend (asosiy sahifa)
└── api/                ← butun papka
    ├── .htaccess
    ├── index.php
    ├── config.php       ← (siz yaratgan, maxfiy)
    ├── lib.php
    ├── ai.php
    └── config.example.php
```

> `db.sql` ni yuklash shart emas — u faqat phpMyAdmin importi uchun.

---

## 4. Tekshirish

1. Brauzerda saytingizni oching → kirish ekrani chiqadi.
2. `talaba@pln.uz` / `pln12345` bilan **Talaba** rolini tanlab kiring.
3. AI Yordamchi bo'limida xabar yozing — javob kelishi kerak.

API'ni alohida tekshirish (brauzer yoki terminal):
```
GET  https://sayt.uz/api/auth/me        → 401 (token yo'q) — bu normal
POST https://sayt.uz/api/auth/login     → token qaytaradi
```

---

## 5. Mumkin bo'lgan muammolar

| Muammo | Yechim |
|--------|--------|
| API 500 xatosi | `api/config.php` dagi baza ma'lumotlari to'g'rimi? |
| `404` API'da | `.htaccess` yuklanganmi? Apache `mod_rewrite` yoqilganmi? |
| AI faqat soxta javob | `anthropic_api_key` kiritilmagan yoki noto'g'ri |
| Kirish ishlamaydi | `db.sql` import qilinganmi? Demo parol `pln12345` |

> `.htaccess` ishlamasa, frontend `API_BASE` ni `/api/index.php?path=` ga
> o'zgartirib ham ishlatish mumkin (zaxira variant).

---

## Keyingi rivojlanish

Hozir `dashboard`, `sdl`, `knowledge`, `screentime`, `teacher` endpointlari
**demo ma'lumot** qaytaradi. Frontend ularni hali ishlatmaydi (o'z ichidagi
ma'lumot bilan ko'rsatadi). Keyingi bosqichda:
1. Bu endpointlarni `study_logs` va `screen_time` jadvallaridan haqiqiy
   hisob-kitob bilan to'ldiring.
2. Frontend `computeVals()` ichidagi soxta massivlarni `await api('/dashboard')`
   natijalari bilan almashtiring.

To'liq API ro'yxati: [`FRONTEND_BACKEND.md`](./FRONTEND_BACKEND.md).
