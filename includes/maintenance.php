<?php
/**
 * CookAI — режим обслуживания: гейт 503 + баннер плановых работ.
 */
declare(strict_types=1);

/** Определяет реальный IP клиента (с учётом прокси) */
function cookai_client_ip(): string
{
    foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $key) {
        if (!empty($_SERVER[$key])) {
            $ip = trim(explode(',', $_SERVER[$key])[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP)) return $ip;
        }
    }
    return '0.0.0.0';
}

/** Проверяет и при необходимости завершает запрос страницей 503 */
function cookai_maintenance_gate(): void
{
    if (!defined('MAINTENANCE_MODE') || MAINTENANCE_MODE !== true) {
        return;
    }

    $uri = $_SERVER['REQUEST_URI'] ?? '';

    // Cron и webhook пропускаем — иначе сорвутся автопродления/возвраты
    if (str_contains($uri, '/api/payment_webhook.php') || str_contains($uri, '/cron/')) {
        return;
    }

    // Белый список IP (админы/разработчики)
    $allowed = defined('MAINTENANCE_ALLOWED_IPS') ? (array) MAINTENANCE_ALLOWED_IPS : [];
    if (in_array(cookai_client_ip(), $allowed, true)) {
        return;
    }

    cookai_render_503();
}

/** Рендерит 503: JSON для API, HTML для страниц */
function cookai_render_503(): void
{
    $retry = defined('MAINTENANCE_RETRY_AFTER') ? (int) MAINTENANCE_RETRY_AFTER : 3600;

    if (!headers_sent()) {
        http_response_code(503);
        header('Retry-After: ' . $retry);
    }

    $isApi = str_contains($_SERVER['REQUEST_URI'] ?? '', '/api/')
          || str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');

    if ($isApi) {
        if (!headers_sent()) header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'error'       => defined('MAINTENANCE_MESSAGE') ? MAINTENANCE_MESSAGE : 'Технические работы',
            'maintenance' => true,
            'retry_after' => $retry,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    require ROOT_DIR . '/pages/503.php';
    exit;
}

/**
 * Данные баннера плановых техработ или null.
 * @return array{starts_at:string, starts_full:string, minutes_left:int, duration:int, message:string}|null
 */
function cookai_maintenance_notice(): ?array
{
    if (!defined('MAINTENANCE_SCHEDULED_AT') || empty(MAINTENANCE_SCHEDULED_AT)) {
        return null;
    }
    $startTs = strtotime((string) MAINTENANCE_SCHEDULED_AT);
    if ($startTs === false) return null;

    $now       = time();
    $noticeSec = (defined('MAINTENANCE_NOTICE_MINUTES') ? (int) MAINTENANCE_NOTICE_MINUTES : 30) * 60;
    $duration  = defined('MAINTENANCE_DURATION_MINUTES') ? (int) MAINTENANCE_DURATION_MINUTES : 15;

    // Показываем только в окне [start - N минут, start)
    if ($now < $startTs - $noticeSec || $now >= $startTs) {
        return null;
    }

    return [
        'starts_at'    => date('H:i', $startTs),
        'starts_full'  => date('d.m.Y H:i', $startTs),
        'minutes_left' => (int) ceil(($startTs - $now) / 60),
        'duration'     => $duration,
        'message'      => 'Плановые технические работы начнутся в ' . date('H:i', $startTs)
                        . ' (примерно ' . $duration . ' мин). Сохраните изменения заранее.',
    ];
}