<?php
// ────────────────────────────────────────────────────────────
//  PLN backend — umumiy kutubxona (DB, helperlar, auth)
// ────────────────────────────────────────────────────────────
declare(strict_types=1);

function cfg(): array {
  static $c = null;
  if ($c === null) {
    $local = __DIR__ . '/config.php';
    $c = file_exists($local) ? require $local : require __DIR__ . '/config.example.php';
  }
  return $c;
}

function db(): PDO {
  static $pdo = null;
  if ($pdo === null) {
    $d = cfg()['db'];
    $dsn = "mysql:host={$d['host']};dbname={$d['name']};charset={$d['charset']}";
    $pdo = new PDO($dsn, $d['user'], $d['pass'], [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      PDO::ATTR_EMULATE_PREPARES => false,
    ]);
  }
  return $pdo;
}

function send_cors(): void {
  $o = cfg()['cors_origin'] ?? '';
  if ($o) {
    header("Access-Control-Allow-Origin: $o");
    header('Access-Control-Allow-Credentials: true');
  }
  header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
  header('Access-Control-Allow-Headers: Content-Type, Authorization');
}

function json_out($data, int $code = 200): never {
  http_response_code($code);
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
  exit;
}

function fail(string $msg, int $code = 400): never {
  json_out(['error' => $msg], $code);
}

function body(): array {
  $raw = file_get_contents('php://input');
  if (!$raw) return $_POST ?: [];
  $j = json_decode($raw, true);
  return is_array($j) ? $j : [];
}

function bearer_token(): ?string {
  $h = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
  if (!$h && function_exists('apache_request_headers')) {
    $hs = apache_request_headers();
    $h = $hs['Authorization'] ?? $hs['authorization'] ?? '';
  }
  if (preg_match('/Bearer\s+(\S+)/i', $h, $m)) return $m[1];
  return null;
}

// Joriy foydalanuvchini token bo'yicha qaytaradi (yoki null)
function current_user(): ?array {
  $tok = bearer_token();
  if (!$tok) return null;
  $st = db()->prepare(
    'SELECT u.* FROM sessions s JOIN users u ON u.id = s.user_id
     WHERE s.token = ? AND s.expires_at > NOW()'
  );
  $st->execute([$tok]);
  $u = $st->fetch();
  return $u ?: null;
}

// Avtorizatsiyani talab qiladi; aks holda 401
function require_user(): array {
  $u = current_user();
  if (!$u) fail('Avtorizatsiya talab qilinadi', 401);
  return $u;
}

// Faqat berilgan rol(lar)ga ruxsat
function require_role(array $roles): array {
  $u = require_user();
  if (!in_array($u['role'], $roles, true)) fail('Ruxsat yo\'q', 403);
  return $u;
}
