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

## Texnologiya

Hozircha bitta faylli prototip (`index.html`):
- Sof HTML + CSS + JavaScript (framework yo'q)
- Grafiklar uchun Canvas va SVG (`d3.js` ulangan)
- AI javoblari uchun Claude API (`claude-sonnet-4`)

## Ishga tushirish

`index.html` faylini brauzerda oching, yoki oddiy server bilan:

```bash
python3 -m http.server 8000
# keyin brauzerda: http://localhost:8000
```

## Keyingi qadamlar (reja)

- [ ] **Backend** — ma'lumotlarni saqlash (hozir hammasi statik test ma'lumoti)
- [ ] **Claude API xavfsizligi** — API kalit brauzerda emas, backend orqali
      (hozirgi `callClaude` to'g'ridan-to'g'ri chaqirilmoqda va ishlamaydi)
- [ ] **Foydalanuvchi rollari** — talaba / o'qituvchi / ota-ona uchun alohida kirish
- [ ] **Rozilik (consent)** oqimi — ekran vaqti monitoringiga rasmiy rozilik
- [ ] **Haqiqiy ekran vaqti yig'ish** — qurilma/brauzer integratsiyasi
- [ ] **Bilim tarmog'ini avtomatik qurish** — qo'shilgan manbalardan nodes hosil qilish
