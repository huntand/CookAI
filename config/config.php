<?php
/**
 * CookAI — центральная конфигурация и bootstrap.
 * Подключается ПЕРВЫМ в каждом entry-point (pages/*, api/*, cron/*).
 * 
 * ВАЖНО: Учётные данные загружаются из переменных окружения (.env),
 * а НЕ из исходного кода!
 */
declare(strict_types=1);

// ============================================================
//  РЕЖИМ И ОКРУЖЕНИЕ
// ============================================================
define('APP_NAME', 'CookAI');
define('APP_DEBUG', (bool)(getenv('APP_DEBUG') === 'true'));
define('APP_ENV', getenv('APP_ENV') ?: 'production');
define('APP_TIMEZONE', 'Europe/Moscow');

date_default_timezone_set(APP_TIMEZONE);
mb_internal_encoding('UTF-8');

if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', __DIR__ . '/../logs/php_errors.log');
}

// ============================================================
//  РЕЖИМ ОБСЛУЖИВАНИЯ (maintenance)
// ============================================================
define('MAINTENANCE_MODE', (bool)(getenv('MAINTENANCE_MODE') === 'true'));
define('MAINTENANCE_RETRY_AFTER', (int)(getenv('MAINTENANCE_RETRY_AFTER') ?: 3600));
define('MAINTENANCE_ALLOWED_IPS', array_filter(explode(',', getenv('MAINTENANCE_ALLOWED_IPS') ?: '')));
define('MAINTENANCE_MESSAGE', getenv('MAINTENANCE_MESSAGE') ?: 'Мы обновляем кухню и скоро вернёмся с новыми возможностями!');

// --- Плановые техработы (мягкое предупреждение баннером) ---
define('MAINTENANCE_SCHEDULED_AT', getenv('MAINTENANCE_SCHEDULED_AT') ?: null);
define('MAINTENANCE_NOTICE_MINUTES', (int)(getenv('MAINTENANCE_NOTICE_MINUTES') ?: 30));
define('MAINTENANCE_DURATION_MINUTES', (int)(getenv('MAINTENANCE_DURATION_MINUTES') ?: 15));

// ============================================================
//  БАЗА ДАННЫХ (из переменных окружения)
// ============================================================
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME'));
define('DB_USER', getenv('DB_USER'));
define('DB_PASS', getenv('DB_PASS'));

if (!DB_NAME || !DB_USER) {
    die('ERROR: Database credentials not configured. Set DB_NAME, DB_USER, DB_PASS in environment.');
}

// ============================================================
//  БАЗОВЫЙ URL / ПУТИ
// ============================================================
define('SITE_URL', getenv('SITE_URL') ?: 'http://localhost');
define('BASE_PATH', getenv('BASE_PATH') ?: '');
define('BASE_URL', rtrim(SITE_URL, '/') . BASE_PATH);
define('ROOT_DIR', dirname(__DIR__));
define('UPLOADS_DIR', ROOT_DIR . '/uploads');

// ============================================================
//  СЕССИИ / БЕЗОПАСНОСТЬ
// ============================================================
define('SESSION_NAME', 'cookai_sess');
define('CRON_SECRET', getenv('CRON_SECRET') ?: '');

if (!CRON_SECRET) {
    error_log('WARNING: CRON_SECRET not set. Cron endpoints will be vulnerable!');
}

if (session_status() === PHP_SESSION_NONE) {
    $__sessDir = ROOT_DIR . '/logs/sessions';
    if (!is_dir($__sessDir)) @mkdir($__sessDir, 0755, true);
    if (is_dir($__sessDir) && is_writable($__sessDir)) {
        session_save_path($__sessDir);
    }
    session_name(SESSION_NAME);
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => BASE_PATH ?: '/',
        'secure'   => (($_SERVER['HTTPS'] ?? '') === 'on')
                      || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https'),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// ============================================================
//  YANDEX AI (из переменных окружения)
// ============================================================
define('YANDEX_API_KEY', getenv('YANDEX_API_KEY') ?: '');
define('YANDEX_FOLDER_ID', getenv('YANDEX_FOLDER_ID') ?: '');
define('YANDEX_COMPLETION_URL', 'https://llm.api.cloud.yandex.net/foundationModels/v1/completion');
define('YANDEX_GPT_MODEL', 'gpt://' . YANDEX_FOLDER_ID . '/yandexgpt/latest');
define('YANDEX_VISION_MODEL', 'gpt://' . YANDEX_FOLDER_ID . '/yandex-gpt-vision/latest');
define('YANDEX_ART_URL', 'https://llm.api.cloud.yandex.net/foundationModels/v1/imageGenerationAsync');
define('YANDEX_ART_MODEL', 'art://' . YANDEX_FOLDER_ID . '/yandex-art/latest');

if (!YANDEX_API_KEY || !YANDEX_FOLDER_ID) {
    error_log('WARNING: Yandex AI credentials not configured. Set YANDEX_API_KEY, YANDEX_FOLDER_ID.');
}

define('MAX_IMAGE_SIZE', 5 * 1024 * 1024);
define('ALLOWED_IMAGE_TYPES', 'image/jpeg,image/png,image/webp');

// --- AI rate-limit (дневные лимиты по фичам) ---
define('AI_LIMIT_GUEST', 2);
define('AI_LIMIT_PRO', 40);
define('AI_LIMITS', [
    'generate' => [AI_LIMIT_GUEST, AI_LIMIT_PRO],
    'calorie'  => [2, AI_LIMIT_PRO],
    'image'    => [1, 20],
]);

// ============================================================
//  ПЛАТЕЖИ (ЮKassa) (из переменных окружения)
// ============================================================
define('YOOKASSA_SHOP_ID', getenv('YOOKASSA_SHOP_ID') ?: '');
define('YOOKASSA_SECRET_KEY', getenv('YOOKASSA_SECRET_KEY') ?: '');
define('YOOKASSA_API_URL', 'https://api.yookassa.ru/v3/payments');
define('YOOKASSA_REFUND_URL', 'https://api.yookassa.ru/v3/refunds');
define('PAYMENT_LIVE_MODE', (bool)(getenv('PAYMENT_LIVE_MODE') === 'true'));

define('PLAN_MONTHLY_PRICE', (float)(getenv('PLAN_MONTHLY_PRICE') ?: 299.00));
define('PLAN_YEARLY_PRICE', (float)(getenv('PLAN_YEARLY_PRICE') ?: 2990.00));

define('MAX_RENEW_ATTEMPTS', 3);
define('RENEW_NOTIFY_DAYS', 3);
define('REFUND_WINDOW_DAYS', 14);

if (PAYMENT_LIVE_MODE && (!YOOKASSA_SHOP_ID || !YOOKASSA_SECRET_KEY)) {
    error_log('ERROR: Payment live mode enabled but credentials not set!');
}

// ============================================================
//  ADMIN EMAILS (из переменных окружения)
// ============================================================
$__admin_emails = getenv('ADMIN_EMAILS') ?: '';
define('ADMIN_EMAILS', array_map('trim', array_filter(explode(',', $__admin_emails))));

// ============================================================
//  ПОЧТА
// ============================================================
define('MAIL_FROM', getenv('MAIL_FROM') ?: 'no-reply@cookai.ru');
define('MAIL_FROM_NAME', 'CookAI');

// ============================================================
//  АВТОПОДКЛЮЧЕНИЕ СЛОЁВ
// ============================================================
require_once ROOT_DIR . '/includes/db.php';
require_once ROOT_DIR . '/includes/functions.php';
require_once ROOT_DIR . '/includes/auth.php';

// ============================================================
//  ГЛОБАЛЬНЫЙ ОБРАБОТЧИК ОШИБОК → страница 500
// ============================================================
function cookai_render_500(?string $message = null): void
{
    $isApi = str_contains($_SERVER['REQUEST_URI'] ?? '', '/api/')
          || str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');

    if ($isApi) {
        if (!headers_sent()) {
            http_response_code(500);
            header('Content-Type: application/json; charset=utf-8');
        }
        echo json_encode([
            'error' => (APP_DEBUG && $message) ? $message : 'Внутренняя ошибка сервера',
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if (APP_DEBUG && $message) {
        $GLOBALS['__error_message'] = $message;
    }
    require ROOT_DIR . '/pages/500.php';
    exit;
}

set_exception_handler(function (Throwable $e): void {
    error_log('[CookAI 500] ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
    cookai_render_500($e->getMessage());
});

register_shutdown_function(function (): void {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        error_log('[CookAI fatal] ' . $err['message'] . ' @ ' . $err['file'] . ':' . $err['line']);
        cookai_render_500($err['message']);
    }
});

// ============================================================
//  ГЕЙТ РЕЖИМА ОБСЛУЖИВАНИЯ (после обработчиков ошибок)
// ============================================================
require_once ROOT_DIR . '/includes/maintenance.php';
cookai_maintenance_gate();