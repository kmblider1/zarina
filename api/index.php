<?php
// ════════════════════════════════════════════════════════════
//  PLN backend — asosiy router (front controller)
//  Barcha so'rovlar shu fayl orqali o'tadi (.htaccess yo'naltiradi).
//  Manzil ko'rinishi:  /api/auth/login , /api/dashboard , ...
// ════════════════════════════════════════════════════════════
declare(strict_types=1);

require __DIR__ . '/lib.php';
require __DIR__ . '/ai.php';

send_cors();
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS') { http_response_code(204); exit; }

// ───────── Manzilni aniqlash ─────────
$path = $_GET['path'] ?? ($_SERVER['PATH_INFO'] ?? '');
if ($path === '') {
  // /api/auth/login ko'rinishidagi URL'dan yo'lni ajratib olamiz
  $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '';
  $uri = preg_replace('#^.*/api/?#', '', $uri);
  $path = $uri;
}
$path = '/' . trim($path, '/');
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$route = $method . ' ' . $path;

try {
  switch (true) {

    // ───────── AUTH ─────────
    case $route === 'POST /auth/login': {
      $b = body();
      $email = trim($b['email'] ?? '');
      $pass  = (string)($b['password'] ?? '');
      if (!$email || !$pass) fail('Email va parol kerak');
      $st = db()->prepare('SELECT * FROM users WHERE email = ?');
      $st->execute([$email]);
      $u = $st->fetch();
      if (!$u || !password_verify($pass, $u['password_hash'])) fail('Email yoki parol xato', 401);
      $token = bin2hex(random_bytes(32));
      $days  = (int)(cfg()['token_days'] ?? 30);
      db()->prepare('INSERT INTO sessions (token, user_id, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL ? DAY))')
          ->execute([$token, $u['id'], $days]);
      json_out(['token' => $token, 'user' => [
        'id' => (int)$u['id'], 'name' => $u['name'], 'role' => $u['role'], 'lang' => $u['lang'],
      ]]);
    }

    case $route === 'POST /auth/logout': {
      $tok = bearer_token();
      if ($tok) db()->prepare('DELETE FROM sessions WHERE token = ?')->execute([$tok]);
      json_out(['ok' => true]);
    }

    case $route === 'GET /auth/me': {
      $u = require_user();
      json_out(['user' => [
        'id' => (int)$u['id'], 'name' => $u['name'], 'role' => $u['role'], 'lang' => $u['lang'],
      ]]);
    }

    // ───────── CONSENT ─────────
    case $route === 'POST /consent': {
      $u = require_user();
      $agreed = !empty(body()['agreed']) ? 1 : 0;
      db()->prepare('INSERT INTO consents (user_id, agreed) VALUES (?, ?)')->execute([$u['id'], $agreed]);
      json_out(['ok' => true]);
    }

    // ───────── STUDY LOG (kunlik o'rganish qaydi) ─────────
    case $route === 'POST /study-log': {
      $u = require_user();
      $b = body();
      $topic   = trim($b['topic'] ?? '');
      $source  = $b['source'] ?? 'platform';
      $minutes = (int)($b['minutes'] ?? 0);
      $date    = $b['date'] ?? date('Y-m-d');
      $allowed = ['youtube','teacher','social','book','platform','practice'];
      if (!$topic) fail('Mavzu kerak');
      if (!in_array($source, $allowed, true)) fail('Notog\'ri manba turi');
      db()->prepare('INSERT INTO study_logs (user_id, topic, source, minutes, log_date) VALUES (?,?,?,?,?)')
          ->execute([$u['id'], $topic, $source, $minutes, $date]);
      json_out(['ok' => true, 'id' => (int)db()->lastInsertId()]);
    }

    case $route === 'GET /study-log': {
      $u = require_user();
      $st = db()->prepare('SELECT id, topic, source, minutes, log_date FROM study_logs WHERE user_id = ? ORDER BY log_date DESC, id DESC LIMIT 100');
      $st->execute([$u['id']]);
      json_out(['items' => $st->fetchAll()]);
    }

    // ───────── DASHBOARD / SDL / KNOWLEDGE / SCREENTIME (hozircha demo) ─────────
    // Eslatma: bular hozir namuna ma'lumot qaytaradi. Keyinchalik study_logs va
    // screen_time jadvallaridan haqiqiy hisob-kitob bilan almashtiriladi.
    case $route === 'GET /dashboard': {
      require_user();
      json_out(demo_dashboard());
    }
    case $route === 'GET /sdl': {
      require_user();
      json_out(demo_sdl());
    }
    case $route === 'GET /knowledge': {
      require_user();
      json_out(demo_knowledge());
    }
    case $route === 'GET /screentime': {
      require_user();
      json_out(demo_screentime());
    }

    // ───────── AI CHAT (Claude proxy) ─────────
    case $route === 'POST /ai/chat': {
      $u = require_user();
      $b = body();
      $msg  = trim($b['message'] ?? '');
      $lang = in_array($b['lang'] ?? 'uz', ['uz','ru','en'], true) ? $b['lang'] : 'uz';
      if (!$msg) fail('Xabar bo\'sh');
      db()->prepare('INSERT INTO chat_messages (user_id, role, text) VALUES (?, "user", ?)')->execute([$u['id'], $msg]);
      $reply = ai_reply($msg, $lang);
      db()->prepare('INSERT INTO chat_messages (user_id, role, text) VALUES (?, "ai", ?)')->execute([$u['id'], $reply]);
      json_out(['reply' => $reply]);
    }

    // ───────── VARK ─────────
    case $route === 'GET /vark/questions': {
      require_user();
      json_out(['count' => 6]); // savol matnlari frontendda; backend faqat natijani saqlaydi
    }
    case $route === 'POST /vark/submit': {
      $u = require_user();
      $ans = body()['answers'] ?? [];
      if (!is_array($ans) || !$ans) fail('Javoblar kerak');
      $c = ['V'=>0,'A'=>0,'R'=>0,'K'=>0];
      foreach ($ans as $a) if (isset($c[$a])) $c[$a]++;
      arsort($c);
      $top = array_key_first($c);
      db()->prepare('INSERT INTO vark_results (user_id, top_type, v, a, r, k) VALUES (?,?,?,?,?,?)')
          ->execute([$u['id'], $top, $c['V'], $c['A'], $c['R'], $c['K']]);
      json_out(['topType' => $top, 'scores' => $c]);
    }

    // ───────── O'QITUVCHI / OTA-ONA ─────────
    case $route === 'GET /teacher/students': {
      require_role(['teacher']);
      json_out(demo_teacher());
    }
    case $route === 'GET /parent/child': {
      require_role(['parent']);
      json_out(['child' => demo_dashboard()]);
    }

    // ───────── Topilmadi ─────────
    default:
      fail("Endpoint topilmadi: $route", 404);
  }
} catch (Throwable $e) {
  fail('Server xatosi: ' . $e->getMessage(), 500);
}

// ════════════════════════════════════════════════════════════
//  DEMO MA'LUMOTLAR (frontend kutgan tuzilma)
// ════════════════════════════════════════════════════════════
function demo_dashboard(): array {
  return [
    'stats' => [
      ['icon'=>'📚','value'=>'3h 20min','label'=>"Bugungi o'qish",'change'=>'+12%'],
      ['icon'=>'🎯','value'=>'87','label'=>'SDL','change'=>'+5'],
      ['icon'=>'⚡','value'=>'12','label'=>'Streak','change'=>'kun'],
      ['icon'=>'🔗','value'=>'23','label'=>'Mavzular','change'=>'+3'],
    ],
    'weeklyHours' => [2.5,3.2,1.8,4.0,3.5,2.1,1.4],
    'apps' => [
      ['icon'=>'💻','name'=>'VS Code','mins'=>135,'type'=>'productive'],
      ['icon'=>'📓','name'=>'Notion','mins'=>90,'type'=>'productive'],
      ['icon'=>'▶️','name'=>'YouTube','mins'=>45,'type'=>'neutral'],
      ['icon'=>'📱','name'=>'Instagram','mins'=>32,'type'=>'distraction'],
      ['icon'=>'📖','name'=>'Coursera','mins'=>28,'type'=>'productive'],
    ],
    'sdl' => 87,
  ];
}
function demo_sdl(): array {
  return [
    'values' => [22,38,45,41,62,74,69,87],
    'times'  => ['3h 20min','4h 05min','3h 15min','2h 50min','4h 30min','3h 55min','3h 10min','4h 15min'],
  ];
}
function demo_knowledge(): array {
  return [
    'nodes' => [
      ['id'=>'python','label'=>'Python','mastery'=>85,'time'=>'12h 30min','conns'=>5],
      ['id'=>'vars','label'=>"O'zgaruvchilar",'mastery'=>95,'time'=>'4h 20min','conns'=>2],
      ['id'=>'funcs','label'=>'Funksiyalar','mastery'=>80,'time'=>'5h 15min','conns'=>2],
      ['id'=>'oop','label'=>'OOP','mastery'=>62,'time'=>'6h','conns'=>3],
    ],
    'edges' => [['python','vars'],['python','funcs'],['python','oop']],
  ];
}
function demo_screentime(): array {
  return [
    'apps' => [
      ['icon'=>'💻','name'=>'VS Code','mins'=>135,'type'=>'productive'],
      ['icon'=>'📓','name'=>'Notion','mins'=>90,'type'=>'productive'],
      ['icon'=>'▶️','name'=>'YouTube','mins'=>45,'type'=>'neutral'],
      ['icon'=>'📱','name'=>'Instagram','mins'=>32,'type'=>'distraction'],
    ],
    'distribution' => ['productive'=>64,'neutral'=>15,'distraction'=>21],
  ];
}
function demo_teacher(): array {
  return [
    'stats' => ['total'=>6,'avgSdl'=>69,'avgScore'=>72,'atRisk'=>1],
    'students' => [
      ['name'=>'Alisher Karimov','today'=>'3h 20min','sdl'=>82,'score'=>87,'risk'=>'low'],
      ['name'=>"Malika Yo'ldosheva",'today'=>'2h 10min','sdl'=>68,'score'=>72,'risk'=>'medium'],
      ['name'=>'Bobur Toshmatov','today'=>'45min','sdl'=>38,'score'=>45,'risk'=>'high'],
      ['name'=>'Zulfiya Nazarova','today'=>'4h 15min','sdl'=>94,'score'=>91,'risk'=>'none'],
      ['name'=>'Jasur Abdullayev','today'=>'1h 50min','sdl'=>58,'score'=>63,'risk'=>'medium'],
      ['name'=>'Nilufar Hamidova','today'=>'2h 40min','sdl'=>75,'score'=>78,'risk'=>'low'],
    ],
  ];
}
