<?php
/**
 * CookAI — общие хелперы: JSON, URL, экранирование, CSRF, флеши.
 */
declare(strict_types=1);

// ==========================================================
//  ВЫВОД / ВВОД
// ==========================================================

/** Отдаёт JSON-ответ и завершает выполнение */
function json_response(array $data, int $code = 200): void
{
    if (!headers_sent()) {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
    }
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/** Читает и декодирует JSON-тело запроса */
function json_input(): array
{
    $raw  = file_get_contents('php://input');
    $data = json_decode($raw ?: '[]', true);
    return is_array($data) ? $data : [];
}

/**
 * Безопасное декодирование JSON-поля из БД.
 * Принимает строку JSON или уже готовый массив; при пустом/некорректном
 * значении возвращает $default (по умолчанию — пустой массив).
 * Используется в шаблонах (например, components/recipe-card.php) для полей
 * ingredients / steps / season и т.п.
 *
 * @param mixed $value   Строка JSON, массив или null
 * @param mixed $default Значение по умолчанию, если декодировать нечего
 * @return mixed
 */
function json_field($value, $default = [])
{
    if (is_array($value)) {
        return $value;
    }
    if ($value === null || $value === '') {
        return $default;
    }
    $decoded = json_decode((string) $value, true);
    return is_array($decoded) ? $decoded : $default;
}

// ==========================================================
//  HTML / URL
// ==========================================================

/** Экранирование для HTML-вывода */
function e(?string $s): string
{
    return htmlspecialchars((string) $s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Абсолютный URL внутри сайта с учётом BASE_PATH */
function url(string $path = ''): string
{
    $base = defined('BASE_PATH') ? rtrim(BASE_PATH, '/') : '';
    return $base . '/' . ltrim($path, '/');
}

/** URL к статике (assets/) */
function asset(string $path): string
{
    return url('assets/' . ltrim($path, '/'));
}

/** Редирект внутри сайта или на внешний URL */
function redirect(string $path): void
{
    if (!headers_sent()) {
        $target = preg_match('~^https?://~', $path) ? $path : url($path);
        header('Location: ' . $target);
    }
    exit;
}

// ==========================================================
//  I18N
// ==========================================================

/** Перевод строки (заглушка с поддержкой :param) */
function t(string $key, array $params = []): string
{
    return $params ? strtr($key, $params) : $key;
}

// ==========================================================
//  ФОРМЫ / ФЛЕШИ
// ==========================================================

/** Возвращает старое значение поля формы (после редиректа) */
function old(string $key, string $default = ''): string
{
    return e($_SESSION['_old'][$key] ?? $default);
}

/** Флеш-сообщение: set при $msg !== null, get+clear при null */
function flash(string $key, ?string $msg = null): ?string
{
    if ($msg === null) {
        $v = $_SESSION['_flash'][$key] ?? null;
        unset($_SESSION['_flash'][$key]);
        return $v;
    }
    $_SESSION['_flash'][$key] = $msg;
    return null;
}

// ==========================================================
//  CSRF  (сессия стартует централизованно в config.php)
// ==========================================================

/** Возвращает CSRF-токен текущей сессии (создаёт при отсутствии) */
function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

/**
 * Скрытое поле с CSRF-токеном для форм.
 * Дублирует токен под двумя именами ('_csrf' и 'csrf') для совместимости
 * с формами, использующими любое из имён.
 */
function csrf_field(): string
{
    $t = e(csrf_token());
    return '<input type="hidden" name="_csrf" value="' . $t . '">'
         . '<input type="hidden" name="csrf" value="' . $t . '">';
}

/**
 * Проверка CSRF-токена.
 * Источники (по приоритету): явный аргумент → заголовок X-CSRF-Token →
 * POST-поле (_csrf | csrf) → JSON-тело (_csrf | csrf).
 * Поддержка обоих имён поля обеспечивает совместимость форм и AJAX (CookAPI).
 *
 * @param string|null $token Токен, переданный явно (например, csrf_check($_POST['csrf']))
 * @return bool
 */
function csrf_check(?string $token = null): bool
{
    // 1. Явно переданный аргумент (формы вида csrf_check($_POST['csrf']))
    if ($token === null || $token === '') {
        // 2. Заголовок (AJAX через CookAPI)
        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    }

    // 3. POST-поле: поддержка обоих имён
    if (!$token) {
        $token = $_POST['_csrf'] ?? ($_POST['csrf'] ?? '');
    }

    // 4. JSON-тело: поддержка обоих имён
    if (!$token) {
        $data  = json_input();
        $token = $data['_csrf'] ?? ($data['csrf'] ?? '');
    }

    return !empty($_SESSION['_csrf'])
        && is_string($token) && $token !== ''
        && hash_equals($_SESSION['_csrf'], $token);
}