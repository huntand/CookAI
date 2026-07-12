<?php
/**
 * Аутентификация, сессии, проверка подписки
 */
declare(strict_types=1);

function current_user(): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    static $cached = null;
    if ($cached !== null) return $cached;

    $cached = db_one(
        'SELECT id, email, name, avatar_url, created_at FROM users WHERE id = ?',
        [$_SESSION['user_id']]
    );
    return $cached;
}

function is_logged_in(): bool
{
    return current_user() !== null;
}

function require_login(): void
{
    if (!is_logged_in()) {
        header('Location: ' . url('login'));
        exit;
    }
}

function auth_register(string $email, string $password, string $name): array
{
    $email = mb_strtolower(trim($email));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['ok' => false, 'error' => 'Некорректный email'];
    }
    if (mb_strlen($password) < 6) {
        return ['ok' => false, 'error' => 'Пароль должен быть не короче 6 символов'];
    }
    if (db_one('SELECT id FROM users WHERE email = ?', [$email])) {
        return ['ok' => false, 'error' => 'Пользователь уже существует'];
    }

    $id = db_insert(
        'INSERT INTO users (email, password_hash, name) VALUES (?, ?, ?)',
        [$email, password_hash($password, PASSWORD_DEFAULT), $name ?: 'Кулинар']
    );
    db_exec('INSERT INTO user_stats (user_id) VALUES (?)', [$id]);
    $_SESSION['user_id'] = $id;
    return ['ok' => true, 'user_id' => $id];
}

function auth_login(string $email, string $password): array
{
    $email = mb_strtolower(trim($email));
    $user = db_one('SELECT id, password_hash FROM users WHERE email = ?', [$email]);
    if (!$user || !password_verify($password, $user['password_hash'])) {
        return ['ok' => false, 'error' => 'Неверный email или пароль'];
    }
    $_SESSION['user_id'] = (int) $user['id'];
    return ['ok' => true, 'user_id' => (int) $user['id']];
}

function auth_logout(): void
{
    $_SESSION = [];
    session_destroy();
}

/** Аналог SubscriptionGuard: проверяет активную подписку/триал */
function check_subscription(?string $email = null): bool
{
    $email ??= current_user()['email'] ?? null;
    if (!$email) return false;

    $sub = db_one(
        'SELECT status, trial_end_date, subscription_end_date
         FROM subscriptions WHERE user_email = ? ORDER BY id DESC LIMIT 1',
        [$email]
    );
    if (!$sub) return false;

    $today = date('Y-m-d');
    return match ($sub['status']) {
        'active' => $sub['subscription_end_date'] >= $today,
        'trial'  => $sub['trial_end_date'] >= $today,
        default  => false,
    };
}

/**
 * Проверка прав администратора.
 * ВАЖНО: admin_emails загружается из переменной окружения или конфига,
 * а НЕ захардкодируется в коде!
 */
function is_admin(): bool
{
    $u = current_user();
    if (!$u) return false;

    // Получаем список admin-email из переменной окружения или конфига
    $admin_emails = [];
    if (defined('ADMIN_EMAILS') && is_array(ADMIN_EMAILS)) {
        $admin_emails = ADMIN_EMAILS;
    } elseif (getenv('ADMIN_EMAILS')) {
        $admin_emails = array_map('trim', explode(',', getenv('ADMIN_EMAILS')));
    }

    return in_array(mb_strtolower($u['email']), array_map('mb_strtolower', $admin_emails), true);
}

/**
 * Проверка администратора с редиректом при отсутствии прав.
 */
function require_admin(): void
{
    require_login();
    if (!is_admin()) {
        http_response_code(403);
        die('Доступ запрещён');
    }
}
