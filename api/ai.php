<?php
// ────────────────────────────────────────────────────────────
//  AI javob generatori
//  - Anthropic kaliti bo'lsa: Claude API'ga so'rov
//  - Bo'lmasa: kalit so'z asosida soxta javob (frontend bilan bir xil)
// ────────────────────────────────────────────────────────────
declare(strict_types=1);

function ai_reply(string $message, string $lang): string {
  $key = cfg()['anthropic_api_key'] ?? '';
  if ($key) {
    $r = ai_claude($message, $lang, $key);
    if ($r !== null) return $r;
    // Claude xatosi bo'lsa — soxta javobga qaytamiz
  }
  return ai_mock($message, $lang);
}

function ai_claude(string $message, string $lang, string $key): ?string {
  $langName = ['uz' => "o'zbek", 'ru' => 'rus', 'en' => 'ingliz'][$lang] ?? "o'zbek";
  $system = "Sen PLN (Personal Learning Network) platformasining shaxsiy o'quv "
          . "yordamchisisan. Connectivism va SDL (mustaqil o'rganish) nazariyasi "
          . "asosida qisqa, amaliy javob ber. Javobni $langName tilida yoz. 2-4 gap.";

  $payload = json_encode([
    'model'      => cfg()['anthropic_model'] ?? 'claude-sonnet-4-6',
    'max_tokens' => 600,
    'system'     => $system,
    'messages'   => [['role' => 'user', 'content' => $message]],
  ], JSON_UNESCAPED_UNICODE);

  $ch = curl_init('https://api.anthropic.com/v1/messages');
  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $payload,
    CURLOPT_TIMEOUT        => 30,
    CURLOPT_HTTPHEADER     => [
      'Content-Type: application/json',
      'x-api-key: ' . $key,
      'anthropic-version: 2023-06-01',
    ],
  ]);
  $resp = curl_exec($ch);
  $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);

  if ($resp === false || $http >= 400) return null;
  $data = json_decode($resp, true);
  return $data['content'][0]['text'] ?? null;
}

function ai_mock(string $message, string $lang): string {
  $lower = mb_strtolower($message);
  $R = [
    'uz' => [
      [['python','kod','dastur','program'], "Python o'rganish uchun: Codecademy, freeCodeCamp va repl.it dan foydalaning. Kuniga 1 soat amaliy mashq qiling! 🐍"],
      [['sdl','mustaqil'], "SDL ko'rsatkichingiz bu hafta 87 ballga yetdi! Maqsad qo'yib, taraqqiyotingizni kuzatib boring. 🎯"],
      [['vaqt','rej','kun','soat','plan'], "Pomodoro usuli: 25 daqiqa o'qish + 5 daqiqa dam. Samaradorlik 40% oshadi! ⏱️"],
      [['salom','hi','hello','assalom'], "Assalomu alaykum! O'qish, rejalashtirish yoki ma'lumot haqida so'rang. 😊"],
      [['math','matema','fizika','kimyo'], "Aniq fanlar uchun Khan Academy juda yaxshi! Vizual va bepul darsliklar bor. 📐"],
      [['vark','uslub'], "VARK testini topshirib o'z o'rganish uslubingizni aniqlang! Chap menyu → VARK Testi 🧠"],
      [['rahmat','raxmat'], "Rahmat! Siz zo'r o'qiyapsiz. Davom eting! 🌟"],
    ],
    'ru' => [
      [['python','код','программ'], "Для Python: Codecademy, freeCodeCamp, repl.it. Практикуйтесь минимум час в день! 🐍"],
      [['sdl','само','независ'], "Ваш SDL-балл достиг 87 на этой неделе! Ставьте цели и отслеживайте прогресс. 🎯"],
      [['время','план','час'], "Техника Помодоро: 25 мин работы + 5 мин отдыха. Продуктивность +40%! ⏱️"],
      [['привет','hi','hello'], "Привет! Спрашивайте об учёбе, планировании или ресурсах. 😊"],
      [['матем','физик','химия'], "Для точных наук — Khan Academy. Визуально и бесплатно. 📐"],
      [['vark','стиль','обучен'], "Пройдите тест VARK, чтобы узнать свой стиль обучения! 🧠"],
      [['спасибо'], "Пожалуйста! Вы отлично занимаетесь. Продолжайте! 🌟"],
    ],
    'en' => [
      [['python','code','program'], "For Python: Codecademy, freeCodeCamp, repl.it. Practice at least 1 hour daily! 🐍"],
      [['sdl','self','direct'], "Your SDL score hit 87 this week! Keep setting goals and tracking progress. 🎯"],
      [['time','schedule','plan','hour'], "Pomodoro technique: 25 min focus + 5 min break. Boosts productivity by 40%! ⏱️"],
      [['hi','hello','hey'], "Hello! Ask about studying, planning, or resources. 😊"],
      [['math','physics','science','chemistry'], "For science subjects, Khan Academy is excellent. Visual and free! 📐"],
      [['vark','learn','style'], "Take the VARK test to discover your learning style! 🧠"],
      [['thanks','great','awesome','good'], "You're welcome! You're doing great. Keep it up! 🌟"],
    ],
  ];
  $list = $R[$lang] ?? $R['en'];
  foreach ($list as [$keys, $reply]) {
    foreach ($keys as $kw) if (mb_strpos($lower, $kw) !== false) return $reply;
  }
  $def = [
    'uz' => "Qiziq savol! Qo'shimcha ma'lumot bering, ko'proq yordam bera olaman. 🤔",
    'ru' => "Интересный вопрос! Дайте больше деталей. 🤔",
    'en' => "Interesting! Give me more details and I'll help better. 🤔",
  ];
  return $def[$lang] ?? $def['en'];
}
