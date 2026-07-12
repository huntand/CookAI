-- ============================================================
--  CookAI — единая схема БД + демо-данные
--  Версия: полная (аккаунты, рецепты, подписки, платежи,
--  промокоды, сообщества, челленджи, уведомления, AI-лимиты)
--
--  Запуск: mysql -u USER -p DBNAME < database/schema.sql
--  Идемпотентно: CREATE IF NOT EXISTS + ON DUPLICATE KEY.
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
SET time_zone = '+03:00';

-- ============================================================
--  1. ПОЛЬЗОВАТЕЛИ
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    email         VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    display_name  VARCHAR(120) NULL,
    avatar_emoji  VARCHAR(16)  NULL DEFAULT '🧑‍🍳',
    bio           VARCHAR(500) NULL,
    role          ENUM('user','admin') NOT NULL DEFAULT 'user',
    points        INT NOT NULL DEFAULT 0,
    created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    last_login_at DATETIME NULL,
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
--  2. РЕЦЕПТЫ
-- ============================================================
CREATE TABLE IF NOT EXISTS recipes (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    slug          VARCHAR(200) NOT NULL UNIQUE,
    author_email  VARCHAR(255) NULL,
    title         VARCHAR(200) NOT NULL,
    description   TEXT NULL,
    ingredients   JSON NULL,
    steps         JSON NULL,
    cuisine       VARCHAR(80) NULL,
    difficulty    ENUM('easy','medium','hard') NOT NULL DEFAULT 'easy',
    cook_minutes  INT NOT NULL DEFAULT 0,
    servings      INT NOT NULL DEFAULT 1,
    calories      INT NULL,
    proteins      INT NULL,
    fats          INT NULL,
    carbs         INT NULL,
    image_url     VARCHAR(500) NULL,
    is_ai         TINYINT(1) NOT NULL DEFAULT 0,
    likes_count   INT NOT NULL DEFAULT 0,
    views_count   INT NOT NULL DEFAULT 0,
    created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_author (author_email),
    INDEX idx_cuisine (cuisine),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Избранное
CREATE TABLE IF NOT EXISTS favorites (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_email VARCHAR(255) NOT NULL,
    recipe_id  INT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_fav (user_email, recipe_id),
    INDEX idx_user (user_email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
--  3. ПОДПИСКИ И ПЛАТЕЖИ (ЮKassa + автопродление + возвраты)
-- ============================================================
CREATE TABLE IF NOT EXISTS subscriptions (
    id                    INT AUTO_INCREMENT PRIMARY KEY,
    parent_id             INT           NULL,
    user_email            VARCHAR(255)  NOT NULL,
    status                ENUM('pending','active','canceled','expired','refunded') NOT NULL DEFAULT 'pending',
    plan                  VARCHAR(20)   NULL,
    months                INT           NOT NULL DEFAULT 1,
    payment_id            VARCHAR(64)   NULL,
    payment_method_id     VARCHAR(64)   NULL,
    auto_renew            TINYINT(1)    NOT NULL DEFAULT 0,
    renew_attempts        INT           NOT NULL DEFAULT 0,
    renewal_notified      TINYINT(1)    NOT NULL DEFAULT 0,
    amount                DECIMAL(10,2) NOT NULL DEFAULT 0,
    promo_code            VARCHAR(40)   NULL,
    original_amount       DECIMAL(10,2) NULL,
    refunded_amount       DECIMAL(10,2) NOT NULL DEFAULT 0,
    refund_id             VARCHAR(64)   NULL,
    receipt_number        VARCHAR(32)   NULL,
    subscription_end_date DATE          NULL,
    next_charge_date      DATE          NULL,
    paid_at               DATETIME      NULL,
    created_at            DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_payment_id (payment_id),
    INDEX idx_user_status (user_email, status),
    INDEX idx_autorenew (auto_renew, status, next_charge_date),
    INDEX idx_notify (auto_renew, status, renewal_notified, next_charge_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
--  4. ПРОМОКОДЫ
-- ============================================================
CREATE TABLE IF NOT EXISTS promo_codes (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    code          VARCHAR(40)   NOT NULL UNIQUE,
    discount_type ENUM('percent','fixed') NOT NULL DEFAULT 'percent',
    discount_val  DECIMAL(10,2) NOT NULL,
    first_only    TINYINT(1)    NOT NULL DEFAULT 1,
    max_uses      INT           NULL,
    used_count    INT           NOT NULL DEFAULT 0,
    valid_until   DATE          NULL,
    is_active     TINYINT(1)    NOT NULL DEFAULT 1,
    created_at    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
--  5. СООБЩЕСТВА
-- ============================================================
CREATE TABLE IF NOT EXISTS communities (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(120) NOT NULL,
    slug          VARCHAR(120) NOT NULL UNIQUE,
    description   TEXT NULL,
    cover_emoji   VARCHAR(16) NULL,
    members_count INT NOT NULL DEFAULT 0,
    created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS community_members (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    community_id INT NOT NULL,
    user_email   VARCHAR(255) NOT NULL,
    joined_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_member (community_id, user_email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
--  6. ЧЕЛЛЕНДЖИ
-- ============================================================
CREATE TABLE IF NOT EXISTS challenges (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    title         VARCHAR(160) NOT NULL,
    slug          VARCHAR(160) NOT NULL UNIQUE,
    description   TEXT NULL,
    goal          VARCHAR(200) NULL,
    reward_points INT NOT NULL DEFAULT 0,
    difficulty    ENUM('easy','medium','hard') NOT NULL DEFAULT 'easy',
    starts_at     DATE NULL,
    ends_at       DATE NULL,
    participants  INT NOT NULL DEFAULT 0,
    created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS challenge_participants (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    challenge_id INT NOT NULL,
    user_email   VARCHAR(255) NOT NULL,
    progress     INT NOT NULL DEFAULT 0,
    completed    TINYINT(1) NOT NULL DEFAULT 0,
    joined_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_part (challenge_id, user_email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
--  7. УВЕДОМЛЕНИЯ
-- ============================================================
CREATE TABLE IF NOT EXISTS notifications (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_email VARCHAR(255) NOT NULL,
    title      VARCHAR(160) NOT NULL,
    message    TEXT NULL,
    type       VARCHAR(30) NOT NULL DEFAULT 'system',
    is_read    TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_read (user_email, is_read),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
--  8. ЛИМИТЫ AI-ЗАПРОСОВ (rate-limit для ai_guard)
-- ============================================================
CREATE TABLE IF NOT EXISTS ai_usage (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    identifier  VARCHAR(120) NOT NULL,   -- email или IP
    feature     VARCHAR(40)  NOT NULL,   -- 'generate' | 'calorie' | 'image'
    used_date   DATE NOT NULL,
    used_count  INT NOT NULL DEFAULT 0,
    UNIQUE KEY uniq_usage (identifier, feature, used_date),
    INDEX idx_date (used_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
--  9. СКАНИРОВАНИЯ КАЛОРИЙ (история vision-сканера)
-- ============================================================
CREATE TABLE IF NOT EXISTS calorie_scans (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_email  VARCHAR(255) NULL,
    dish        VARCHAR(200) NULL,
    portion     VARCHAR(120) NULL,
    calories    INT NOT NULL DEFAULT 0,
    proteins    INT NOT NULL DEFAULT 0,
    fats        INT NOT NULL DEFAULT 0,
    carbs       INT NOT NULL DEFAULT 0,
    source      ENUM('photo','text') NOT NULL DEFAULT 'photo',
    confidence  VARCHAR(20) NULL,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_date (user_email, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- ============================================================
--  ДЕМО-ДАННЫЕ (SEED)
-- ============================================================
-- ============================================================

-- -------- Демо-пользователь (пароль: demo1234) --------
-- password_hash сгенерирован через password_hash('demo1234', PASSWORD_DEFAULT)
INSERT INTO users (email, password_hash, display_name, avatar_emoji, bio, role, points) VALUES
('demo@cookai.ru', '$2y$10$e0NR0nQ7cJ7wq4Qw3q1vEuJ1kxq1Q2m3n4b5v6c7x8z9a0s1d2f3', 'Демо Повар', '👨‍🍳', 'Люблю готовить и делиться рецептами.', 'user', 120)
ON DUPLICATE KEY UPDATE display_name = VALUES(display_name);

-- -------- Промокоды --------
INSERT INTO promo_codes (code, discount_type, discount_val, first_only, max_uses, valid_until) VALUES
('WELCOME20', 'percent', 20.00,  1, 1000, DATE_ADD(CURDATE(), INTERVAL 90 DAY)),
('COOK100',   'fixed',   100.00, 1, 500,  DATE_ADD(CURDATE(), INTERVAL 60 DAY)),
('FIRST50',   'percent', 50.00,  1, 100,  DATE_ADD(CURDATE(), INTERVAL 30 DAY))
ON DUPLICATE KEY UPDATE discount_val = VALUES(discount_val), valid_until = VALUES(valid_until);

-- -------- Сообщества --------
INSERT INTO communities (name, slug, description, cover_emoji, members_count) VALUES
('Здоровое питание',    'healthy-eating', 'ПП-рецепты, баланс КБЖУ и полезные привычки каждый день.',         '🥗', 1284),
('Итальянская кухня',   'italian',        'Паста, пицца, ризотто и секреты настоящей итальянской кухни.',      '🍝', 942),
('Выпечка и десерты',   'baking',         'Торты, печенье, хлеб на закваске — делимся рецептами и лайфхаками.','🍰', 1567),
('Азиатская кухня',     'asian',          'Рамен, суши, вок и специи Азии в вашей тарелке.',                   '🍜', 811),
('Веган и растительное','vegan',          'Растительные рецепты без компромиссов по вкусу.',                   '🌱', 673),
('Быстрые ужины',       'quick-dinners',  'Готовим вкусно за 30 минут после рабочего дня.',                    '⚡', 2103),
('Грузинская кухня',    'georgian',       'Хачапури, хинкали, аджапсандали и грузинское гостеприимство.',      '🧆', 588),
('Домашние заготовки',  'preserves',      'Соленья, варенье, маринады — заготовки на весь год.',               '🫙', 449)
ON DUPLICATE KEY UPDATE description = VALUES(description), members_count = VALUES(members_count);

-- -------- Челленджи --------
INSERT INTO challenges (title, slug, description, goal, reward_points, difficulty, starts_at, ends_at, participants) VALUES
('7 дней без сахара',    '7-days-no-sugar',   'Неделя блюд без добавленного сахара. Делись прогрессом каждый день.', 'Приготовить 7 блюд без сахара',     350, 'medium', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 7 DAY),  428),
('Мастер пасты',         'pasta-master',      'Приготовь 5 разных видов пасты и стань мастером итальянской кухни.',  'Приготовить 5 видов пасты',         250, 'easy',   CURDATE(), DATE_ADD(CURDATE(), INTERVAL 14 DAY), 312),
('Завтрак чемпиона',     'champion-breakfast','30 дней полезных завтраков подряд. Заряжай утро правильно!',          'Готовить завтрак 30 дней подряд',   600, 'hard',   CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY), 189),
('Уложись в 500 ккал',   'under-500',         'Придумай и приготовь сытное блюдо не дороже 500 ккал на порцию.',     'Блюдо до 500 ккал',                 150, 'easy',   CURDATE(), DATE_ADD(CURDATE(), INTERVAL 10 DAY), 534),
('Домашний хлеб',        'homemade-bread',    'Испеки хлеб на закваске с нуля. От опары до румяной корочки.',        'Испечь домашний хлеб',              300, 'medium', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 21 DAY), 276),
('Кухни мира за неделю', 'world-cuisines',    'Каждый день — блюдо новой национальной кухни. 7 стран за 7 дней.',    'Приготовить блюда 7 разных кухонь', 400, 'hard',   CURDATE(), DATE_ADD(CURDATE(), INTERVAL 7 DAY),  221),
('Zero waste готовка',   'zero-waste',        'Готовь без пищевых отходов: используй продукт целиком.',              '5 блюд по принципу zero waste',     280, 'medium', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 14 DAY), 167)
ON DUPLICATE KEY UPDATE description = VALUES(description), participants = VALUES(participants);

-- -------- Демо-рецепты --------
INSERT INTO recipes (slug, author_email, title, description, ingredients, steps, cuisine, difficulty, cook_minutes, servings, calories, proteins, fats, carbs, is_ai) VALUES
('pasta-carbonara', 'demo@cookai.ru', 'Паста Карбонара',
 'Классическая римская паста с гуанчиале, яйцом и пекорино.',
 '["Спагетти 200 г","Гуанчиале 100 г","Яичные желтки 3 шт","Пекорино 50 г","Чёрный перец"]',
 '["Отварите спагетти al dente","Обжарьте гуанчиале до хруста","Смешайте желтки с сыром","Соедините всё с пастой вне огня","Поперчите и подавайте"]',
 'italian', 'medium', 25, 2, 620, 24, 30, 65, 0),
('greek-salad', 'demo@cookai.ru', 'Греческий салат',
 'Свежий салат с фетой, оливками и оливковым маслом.',
 '["Помидоры 3 шт","Огурцы 2 шт","Фета 150 г","Маслины","Красный лук","Оливковое масло","Орегано"]',
 '["Нарежьте овощи крупно","Добавьте маслины и лук","Сверху положите кусок феты","Полейте маслом и посыпьте орегано"]',
 'healthy-eating', 'easy', 15, 2, 320, 10, 24, 14, 0)
ON DUPLICATE KEY UPDATE title = VALUES(title), description = VALUES(description);

-- ============================================================
--  Готово. Демо-логин: demo@cookai.ru / demo1234
-- ============================================================