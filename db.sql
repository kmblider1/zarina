-- ════════════════════════════════════════════════════════════
--  PLN — Personal Learning Network · MySQL sxema + boshlang'ich ma'lumot
--  Timeweb "Optimo" hosting uchun (phpMyAdmin orqali import qiling)
-- ════════════════════════════════════════════════════════════
SET NAMES utf8mb4;
SET time_zone = '+00:00';

-- ───────── Foydalanuvchilar ─────────
CREATE TABLE IF NOT EXISTS users (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  name          VARCHAR(120) NOT NULL,
  email         VARCHAR(160) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role          ENUM('student','teacher','parent') NOT NULL,
  lang          ENUM('uz','ru','en') NOT NULL DEFAULT 'uz',
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ───────── Sessiyalar (token autentifikatsiya) ─────────
CREATE TABLE IF NOT EXISTS sessions (
  token      CHAR(64) PRIMARY KEY,
  user_id    INT NOT NULL,
  expires_at DATETIME NOT NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ───────── Rozilik (consent) ─────────
CREATE TABLE IF NOT EXISTS consents (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  user_id    INT NOT NULL,
  agreed     TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ───────── Kunlik o'rganish qaydi (g'oyaning markazi) ─────────
CREATE TABLE IF NOT EXISTS study_logs (
  id       INT AUTO_INCREMENT PRIMARY KEY,
  user_id  INT NOT NULL,
  topic    VARCHAR(200) NOT NULL,
  source   ENUM('youtube','teacher','social','book','platform','practice') NOT NULL,
  minutes  INT NOT NULL DEFAULT 0,
  log_date DATE NOT NULL,
  node_id  INT NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ───────── Bilim tugunlari va bog'lanishlari ─────────
CREATE TABLE IF NOT EXISTS knowledge_nodes (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  user_id    INT NOT NULL,
  label      VARCHAR(120) NOT NULL,
  mastery    INT NOT NULL DEFAULT 0,
  time_spent VARCHAR(40) DEFAULT '0',
  conns      INT NOT NULL DEFAULT 0,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS node_edges (
  id             INT AUTO_INCREMENT PRIMARY KEY,
  source_node_id INT NOT NULL,
  target_node_id INT NOT NULL,
  strength       FLOAT NOT NULL DEFAULT 0.5
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ───────── Ekran vaqti ─────────
CREATE TABLE IF NOT EXISTS screen_time (
  id        INT AUTO_INCREMENT PRIMARY KEY,
  user_id   INT NOT NULL,
  app_name  VARCHAR(120) NOT NULL,
  category  ENUM('productive','neutral','distraction') NOT NULL,
  minutes   INT NOT NULL DEFAULT 0,
  log_date  DATE NOT NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ───────── SDL ko'rsatkichlari ─────────
CREATE TABLE IF NOT EXISTS sdl_scores (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  user_id       INT NOT NULL,
  week          INT NOT NULL,
  score         INT NOT NULL,
  study_minutes INT NOT NULL DEFAULT 0,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ───────── VARK natijalari ─────────
CREATE TABLE IF NOT EXISTS vark_results (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  user_id    INT NOT NULL,
  top_type   CHAR(1) NOT NULL,
  v INT DEFAULT 0, a INT DEFAULT 0, r INT DEFAULT 0, k INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ───────── Chat tarixi ─────────
CREATE TABLE IF NOT EXISTS chat_messages (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  user_id    INT NOT NULL,
  role       ENUM('user','ai') NOT NULL,
  text       TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ───────── O'qituvchi/Ota-ona ↔ talaba bog'lanishi ─────────
CREATE TABLE IF NOT EXISTS relations (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  observer_id INT NOT NULL,   -- o'qituvchi yoki ota-ona
  student_id  INT NOT NULL,   -- kuzatilayotgan talaba
  FOREIGN KEY (observer_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (student_id)  REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ════════════════════════════════════════════════════════════
--  BOSHLANG'ICH MA'LUMOT (demo)
--  Hamma uchun parol: pln12345
-- ════════════════════════════════════════════════════════════
INSERT INTO users (name, email, password_hash, role, lang) VALUES
  ('Alisher Karimov',  'talaba@pln.uz',  '$2y$12$dGaSMGtXPVIFddmxD2/r6.88tFA/JPCv1un9u5JhX628JXMlpHJ1q', 'student', 'uz'),
  ('Malika Xoliqova',  'ustoz@pln.uz',   '$2y$12$dGaSMGtXPVIFddmxD2/r6.88tFA/JPCv1un9u5JhX628JXMlpHJ1q', 'teacher', 'uz'),
  ('Aziz Karimov',     'otaona@pln.uz',  '$2y$12$dGaSMGtXPVIFddmxD2/r6.88tFA/JPCv1un9u5JhX628JXMlpHJ1q', 'parent',  'uz');
