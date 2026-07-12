<?php
/**
 * CookAI — центральная конфигурация и bootstrap.
 * Подключается ПЕРВЫМ в каждом entry-point (pages/*, api/*, cron/*).
 */
declare(strict_types=1);

// ============================================================
//  РЕЖИМ И ОКРУЖЕНИЕ
// ============================================================
define('APP_NAME', 'CookAI');
define('APP_DEBUG', false);
define('APP_ENV', 'production');
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
define('MAINTENANCE_MODE', false);              // true — включить техработы (503)
define('MAINTENANCE_RETRY_AFTER', 3600);         // Retry-After в секундах
define('MAINTENANCE_ALLOWED_IPS', [
    // '203.0.113.10',                            // IP админов/разработчиков
]);
define('MAINTENANCE_MESSAGE', 'Мы обновляем кухню и скоро вернёмся с новыми возможностями!');

// --- Плановые техработы (мягкое предупреждение баннером) ---
define('MAINTENANCE_SCHEDULED_AT', null);        // напр. '2026-07-08 03:00' или null
define('MAINTENANCE_NOTICE_MINUTES', 30);        // за сколько минут показывать баннер
define('MAINTENANCE_DURATION_MINUTES', 15);      // ожидаемая длительность работ

// ============================================================
//  БАЗА ДАННЫХ
// ============================================================
define('DB_HOST', '188.127.239.143');
define('DB_PORT', '3306');
define('DB_NAME', 'exolyt-ai-975');
define('DB_USER', 'exolyt-ai-975');
define('DB_PASS', '01689075');

// ============================================================
//  БАЗОВЫЙ URL / ПУТИ
// ============================================================
define('SITE_URL', 'https://s1647298.smrtp.ru');
define('BASE_PATH', '');
define('ROOT_DIR', dirname(__DIR__));
define('UPLOADS_DIR', ROOT_DIR . '/uploads');

// ============================================================
//  СЕССИИ / БЕЗОПАСНОСТЬ
// ============================================================
define('SESSION_NAME', 'cookai_sess');
define('CRON_SECRET', 'ВСТАВЬТЕ_РЕЗУЛЬТАТ_bin2hex_random_bytes_32');

if (session_status() === PHP_SESSION_NONE) {
    $__sessDir = ROOT_DIR . '/logs/sessions';
    if (!is_dir($__sessDir)) @mkdir($__sessDir, 0755, true);
    if (is_dir($__sessDir) && is_writable($__sessDir)) {
        session_save_path($__sessDir);
    }
    session_name(SESSION_NAME);
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => (($_SERVER['HTTPS'] ?? '') === 'on')
                      || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https'),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// ============================================================
//  YANDEX AI
// ============================================================
define('YANDEX_API_KEY', 'AQVNxWarDG2MFTKKtfmBEkZ40ay_pjFLVT1Kq1yq');
define('YANDEX_FOLDER_ID', 'b1ge0sjhm88rvdjcgrvj');
define('YANDEX_COMPLETION_URL', 'https://llm.api.cloud.yandex.net/foundationModels/v1/completion');
define('YANDEX_GPT_MODEL', 'gpt://' . YANDEX_FOLDER_ID . '/yandexgpt/latest');
define('YANDEX_VISION_MODEL', 'gpt://' . YANDEX_FOLDER_ID . '/yandex-gpt-vision/latest');
define('YANDEX_ART_URL', 'https://llm.api.cloud.yandex.net/foundationModels/v1/imageGenerationAsync');
define('YANDEX_ART_MODEL', 'art://' . YANDEX_FOLDER_ID . '/yandex-art/latest');

define('MAX_IMAGE_SIZE', 5 * 1024 * 1024);
define('ALLOWED_IMAGE_TYPES', 'image/jpeg,image/png,image/webp');

// --- AI rate-limit (дневные лимиты по фичам) ---
define('AI_LIMIT_GUEST', 2);
define('AI_LIMIT_PRO', 40);
// Централизованная таблица лимитов: feature => [guest, pro]
define('AI_LIMITS', [
    'generate' => [AI_LIMIT_GUEST, AI_LIMIT_PRO],
    'calorie'  => [2, AI_LIMIT_PRO],
    'image'    => [1, 20],
]);

// ============================================================
//  ПЛАТЕЖИ (ЮKassa)
// ============================================================
define('YOOKASSA_SHOP_ID', 'ВАШ_SHOP_ID');
define('YOOKASSA_SECRET_KEY', 'ВАШ_СЕКРЕТНЫЙ_КЛЮЧ');
define('YOOKASSA_API_URL', 'https://api.yookassa.ru/v3/payments');
define('YOOKASSA_REFUND_URL', 'https://api.yookassa.ru/v3/refunds');
define('PAYMENT_LIVE_MODE', false);

define('PLAN_MONTHLY_PRICE', 299.00);
define('PLAN_YEARLY_PRICE', 2990.00);

define('MAX_RENEW_ATTEMPTS', 3);
define('RENEW_NOTIFY_DAYS', 3);
define('REFUND_WINDOW_DAYS', 14);

// ============================================================
//  ПОЧТА
// ============================================================
define('MAIL_FROM', 'no-reply@cookai.ru');
define('MAIL_FROM_NAME', 'CookAI');

// ============================================================
//  АВТОПОДКЛЮЧЕНИЕ СЛОЁВ
// ============================================================
require_once ROOT_DIR . '/includes/db.php';
require_once ROOT_DIR . '/includes/helpers.php';
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