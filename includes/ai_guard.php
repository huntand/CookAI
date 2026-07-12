<?php
/**
 * CookAI — rate-limit для AI-функций (таблица ai_usage).
 * Гость/бесплатный: дневной лимит. Pro: расширенный лимит.
 * При превышении → 429 (JSON для API, HTML pages/429.php для страниц).
 */
declare(strict_types=1);
require_once __DIR__ . '/../config/config.php';

/** Возвращает [guestLimit, proLimit] для фичи из AI_LIMITS */
function ai_limits_for(string $feature): array
{
    $map = defined('AI_LIMITS') ? AI_LIMITS : [];
    return $map[$feature] ?? [AI_LIMIT_GUEST, AI_LIMIT_PRO];
}

/**
 * Проверяет и инкрементирует счётчик использования AI-функции.
 * Лимиты берутся из AI_LIMITS по имени фичи.
 */
function ai_guard(string $feature): void
{
    [$guestLimit, $proLimit] = ai_limits_for($feature);

    $isPro = function_exists('check_subscription') && check_subscription();
    $limit = $isPro ? $proLimit : $guestLimit;

    $id = (function_exists('is_logged_in') && is_logged_in())
        ? current_user()['email']
        : cookai_client_ip();

    $today = date('Y-m-d');

    $row = db_one(
        'SELECT used_count FROM ai_usage WHERE identifier=? AND feature=? AND used_date=?',
        [$id, $feature, $today]
    );
    $used = $row ? (int) $row['used_count'] : 0;

    if ($used >= $limit) {
        ai_guard_reject($isPro);
    }

    db_exec(
        'INSERT INTO ai_usage (identifier, feature, used_date, used_count)
         VALUES (?,?,?,1)
         ON DUPLICATE KEY UPDATE used_count = used_count + 1',
        [$id, $feature, $today]
    );
}

/**
 * Текущее состояние лимита БЕЗ инкремента (для UI-счётчика).
 * @return array{used:int, limit:int, remaining:int, is_pro:bool, reset:string, feature:string}
 */
function ai_usage_status(string $feature): array
{
    [$guestLimit, $proLimit] = ai_limits_for($feature);

    $isPro = function_exists('check_subscription') && check_subscription();
    $limit = $isPro ? $proLimit : $guestLimit;

    $id = (function_exists('is_logged_in') && is_logged_in())
        ? current_user()['email']
        : cookai_client_ip();

    $row = db_one(
        'SELECT used_count FROM ai_usage WHERE identifier=? AND feature=? AND used_date=?',
        [$id, $feature, date('Y-m-d')]
    );
    $used = $row ? (int) $row['used_count'] : 0;

    return [
        'feature'   => $feature,
        'used'      => $used,
        'limit'     => $limit,
        'remaining' => max(0, $limit - $used),
        'is_pro'    => $isPro,
        'reset'     => 'завтра в 00:00',
    ];
}

/** Отдаёт 429 в подходящем формате */
function ai_guard_reject(bool $isPro): void
{
    $retryAfter = strtotime('tomorrow') - time();
    $resetLabel = 'завтра в 00:00';

    $isApi = str_contains($_SERVER['REQUEST_URI'] ?? '', '/api/')
          || str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');

    if ($isApi) {
        if (!headers_sent()) {
            http_response_code(429);
            header('Content-Type: application/json; charset=utf-8');
            header('Retry-After: ' . $retryAfter);
        }
        echo json_encode([
            'error'       => $isPro
                ? 'Достигнут дневной лимит запросов. Попробуйте позже.'
                : 'Лимит бесплатных AI-запросов исчерпан. Оформите CookAI Pro для безлимита.',
            'limit'       => true,
            'is_pro'      => $isPro,
            'retry_after' => $retryAfter,
            'reset'       => $resetLabel,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $GLOBALS['__retry_after'] = $retryAfter;
    $GLOBALS['__limit_reset'] = $resetLabel;
    require ROOT_DIR . '/pages/429.php';
    exit;
}