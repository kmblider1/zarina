-- ════════════════════════════════════════════════════════════
--  PLN — DEMO O'QUVCHI uchun namuna ma'lumot (talaba@pln.uz)
--  Dashboard / SDL / Ekran vaqti grafiklari bo'sh bo'lmasligi uchun.
--
--  MUHIM: avval 3 ta foydalanuvchi yarating (schema.sql, 3-qadam),
--  SO'NG shu faylni SQL Editor'da RUN qiling. Qayta ishga tushirsa
--  bo'ladi — eski demo yozuvlar o'chirilib, qaytadan yoziladi.
-- ════════════════════════════════════════════════════════════

do $$
declare sid uuid;
begin
  select id into sid from auth.users where email = 'talaba@pln.uz';
  if sid is null then
    raise notice 'talaba@pln.uz topilmadi — avval foydalanuvchini yarating';
    return;
  end if;

  -- eski demo yozuvlarni tozalash (qayta ishga tushirish uchun)
  delete from public.study_logs  where user_id = sid;
  delete from public.screen_time where user_id = sid;
  delete from public.sdl_scores  where user_id = sid;

  -- ── Oxirgi 7 kunlik o'qish qaydi (dashboard ustunli grafik) ──
  insert into public.study_logs (user_id, topic, source, minutes, log_date) values
    (sid, 'Python',      'platform', 150, current_date - 6),
    (sid, 'Matematika',  'book',     192, current_date - 5),
    (sid, 'OOP',         'youtube',  108, current_date - 4),
    (sid, 'Algoritmlar', 'practice', 240, current_date - 3),
    (sid, 'SQL',         'platform', 210, current_date - 2),
    (sid, 'Git',         'teacher',  126, current_date - 1),
    (sid, 'Python',      'platform',  84, current_date);

  -- ── Bugungi ekran vaqti (Ekran vaqti sahifasi + donut) ──
  insert into public.screen_time (user_id, app_name, category, minutes, log_date) values
    (sid, 'VS Code',   'productive',  135, current_date),
    (sid, 'Notion',    'productive',   90, current_date),
    (sid, 'YouTube',   'neutral',      45, current_date),
    (sid, 'Instagram', 'distraction',  32, current_date),
    (sid, 'Coursera',  'productive',   28, current_date);

  -- ── 8 haftalik SDL ko'rsatkichi (SDL grafigi + jadval) ──
  insert into public.sdl_scores (user_id, week, score, study_minutes) values
    (sid, 1, 22, 200),
    (sid, 2, 38, 245),
    (sid, 3, 45, 195),
    (sid, 4, 41, 170),
    (sid, 5, 62, 270),
    (sid, 6, 74, 235),
    (sid, 7, 69, 190),
    (sid, 8, 87, 255);

  raise notice 'Demo ma''lumot muvaffaqiyatli yozildi ✅';
end $$;
