-- ════════════════════════════════════════════════════════════
--  PLN — Supabase (Postgres) sxemasi + xavfsizlik (RLS)
--  Supabase panel → SQL Editor → shu faylni to'liq joylab, RUN bosing.
-- ════════════════════════════════════════════════════════════

-- ───────── PROFILLAR (auth.users bilan bog'langan) ─────────
create table if not exists public.profiles (
  id         uuid primary key references auth.users(id) on delete cascade,
  name       text not null default '',
  role       text not null default 'student' check (role in ('student','teacher','parent')),
  lang       text not null default 'uz' check (lang in ('uz','ru','en')),
  created_at timestamptz default now()
);

-- Foydalanuvchi ro'yxatdan o'tganda profil avtomatik yaratiladi
create or replace function public.handle_new_user()
returns trigger language plpgsql security definer set search_path = public as $$
begin
  insert into public.profiles (id, name)
  values (new.id, coalesce(new.raw_user_meta_data->>'name', split_part(new.email, '@', 1)))
  on conflict (id) do nothing;
  return new;
end; $$;

drop trigger if exists on_auth_user_created on auth.users;
create trigger on_auth_user_created
  after insert on auth.users
  for each row execute function public.handle_new_user();

-- ───────── MA'LUMOT JADVALLARI ─────────
create table if not exists public.consents (
  id uuid primary key default gen_random_uuid(),
  user_id uuid not null references auth.users(id) on delete cascade,
  agreed boolean not null default false,
  created_at timestamptz default now()
);

create table if not exists public.study_logs (
  id uuid primary key default gen_random_uuid(),
  user_id uuid not null references auth.users(id) on delete cascade,
  topic text not null,
  source text not null check (source in ('youtube','teacher','social','book','platform','practice')),
  minutes int not null default 0,
  log_date date not null default current_date,
  node_id uuid
);

create table if not exists public.knowledge_nodes (
  id uuid primary key default gen_random_uuid(),
  user_id uuid not null references auth.users(id) on delete cascade,
  label text not null,
  mastery int not null default 0,
  time_spent text default '0',
  conns int not null default 0
);

create table if not exists public.node_edges (
  id uuid primary key default gen_random_uuid(),
  source_node_id uuid not null references public.knowledge_nodes(id) on delete cascade,
  target_node_id uuid not null references public.knowledge_nodes(id) on delete cascade
);

create table if not exists public.screen_time (
  id uuid primary key default gen_random_uuid(),
  user_id uuid not null references auth.users(id) on delete cascade,
  app_name text not null,
  category text not null check (category in ('productive','neutral','distraction')),
  minutes int not null default 0,
  log_date date not null default current_date
);

create table if not exists public.sdl_scores (
  id uuid primary key default gen_random_uuid(),
  user_id uuid not null references auth.users(id) on delete cascade,
  week int not null,
  score int not null,
  study_minutes int not null default 0
);

create table if not exists public.vark_results (
  id uuid primary key default gen_random_uuid(),
  user_id uuid not null references auth.users(id) on delete cascade,
  top_type char(1) not null,
  v int default 0, a int default 0, r int default 0, k int default 0,
  created_at timestamptz default now()
);

create table if not exists public.chat_messages (
  id uuid primary key default gen_random_uuid(),
  user_id uuid not null references auth.users(id) on delete cascade,
  role text not null check (role in ('user','ai')),
  text text not null,
  created_at timestamptz default now()
);

-- O'qituvchi/Ota-ona ↔ talaba bog'lanishi
create table if not exists public.relations (
  id uuid primary key default gen_random_uuid(),
  observer_id uuid not null references auth.users(id) on delete cascade,  -- o'qituvchi/ota-ona
  student_id  uuid not null references auth.users(id) on delete cascade   -- talaba
);

-- ════════════════════════════════════════════════════════════
--  XAVFSIZLIK — Row Level Security (RLS)
--  Har kim faqat o'z ma'lumotini ko'radi; o'qituvchi/ota-ona
--  o'ziga biriktirilgan talabalarnikini ko'radi.
-- ════════════════════════════════════════════════════════════
alter table public.profiles        enable row level security;
alter table public.consents        enable row level security;
alter table public.study_logs      enable row level security;
alter table public.knowledge_nodes enable row level security;
alter table public.node_edges      enable row level security;
alter table public.screen_time     enable row level security;
alter table public.sdl_scores      enable row level security;
alter table public.vark_results    enable row level security;
alter table public.chat_messages   enable row level security;
alter table public.relations       enable row level security;

-- Profil: o'zini ko'rish/tahrirlash + kuzatuvchi talaba profilini ko'radi
create policy "profiles self select" on public.profiles for select using (auth.uid() = id);
create policy "profiles self update" on public.profiles for update using (auth.uid() = id);
create policy "profiles observer select" on public.profiles for select using (
  exists (select 1 from public.relations r where r.student_id = public.profiles.id and r.observer_id = auth.uid())
);

-- Kuzatuvchi o'z bog'lanishlarini ko'radi
create policy "relations observer" on public.relations for select using (observer_id = auth.uid());

-- Umumiy shablon: har bir jadval uchun "o'zinikini to'liq boshqarish" +
-- kuzatuvchiga "o'qish" ruxsati. (user_id bo'lgan jadvallar uchun)
do $$
declare t text;
begin
  foreach t in array array['consents','study_logs','knowledge_nodes','screen_time','sdl_scores','vark_results','chat_messages']
  loop
    execute format('create policy "%1$s own all" on public.%1$s for all using (auth.uid() = user_id) with check (auth.uid() = user_id);', t);
    execute format('create policy "%1$s observer read" on public.%1$s for select using (exists (select 1 from public.relations r where r.student_id = public.%1$s.user_id and r.observer_id = auth.uid()));', t);
  end loop;
end $$;

-- node_edges: tegishli tugun egasiga ruxsat
create policy "node_edges own" on public.node_edges for all using (
  exists (select 1 from public.knowledge_nodes n where n.id = node_edges.source_node_id and n.user_id = auth.uid())
);

-- ════════════════════════════════════════════════════════════
--  DEMO FOYDALANUVCHILAR ROLLARI
--  Avval Supabase panelidan 3 ta foydalanuvchi yarating
--  (Authentication → Users → Add user, parol: pln12345, "Auto Confirm"):
--    talaba@pln.uz , ustoz@pln.uz , otaona@pln.uz
--  So'ng SHU SQL ni ishga tushiring (rollarni to'g'rilaydi):
-- ════════════════════════════════════════════════════════════
update public.profiles p set role='student', name='Alisher Karimov'
  from auth.users u where u.id = p.id and u.email = 'talaba@pln.uz';
update public.profiles p set role='teacher', name='Malika Xoliqova'
  from auth.users u where u.id = p.id and u.email = 'ustoz@pln.uz';
update public.profiles p set role='parent', name='Aziz Karimov'
  from auth.users u where u.id = p.id and u.email = 'otaona@pln.uz';

-- O'qituvchi va ota-onani talabaga biriktirish (demo)
insert into public.relations (observer_id, student_id)
select t.id, s.id from auth.users t, auth.users s
where t.email in ('ustoz@pln.uz','otaona@pln.uz') and s.email = 'talaba@pln.uz'
on conflict do nothing;
