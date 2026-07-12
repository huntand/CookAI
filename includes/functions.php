<?php
/**
 * Общие вспомогательные функции
 */
declare(strict_types=1);

/** Экранирование для вывода в HTML */
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** JSON-ответ для API */
function json_response($data, int $code = 200): void
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/** Читает JSON-тело POST-запроса */
function json_input(): array
{
    $raw = file_get_contents('php://input');
    $data = json_decode($raw ?: '[]', true);
    return is_array($data) ? $data : [];
}

/** Абсолютный URL внутри приложения */
function url(string $path = ''): string
{
    return BASE_URL . '/' . ltrim($path, '/');
}

/** Безопасное декодирование JSON-поля из БД */
function json_field($value, $default = [])
{
    if (is_array($value)) return $value;
    if ($value === null || $value === '') return $default;
    $decoded = json_decode((string) $value, true);
    return is_array($decoded) ? $decoded : $default;
}

/** Локализация (статичные русские строки) */
function t(string $key): string
{
    static $dict = [
        'app_name'        => 'CookAI',
        'search'          => 'Поиск',
        'trending'        => 'В тренде',
        'seasonal'        => 'Сезонные рецепты',
        'ai_tools'        => 'AI-инструменты',
        'community'       => 'Сообщество',
        'personal'        => 'Личное',
        'login'           => 'Войти',
        'logout'          => 'Выйти',
        'register'        => 'Регистрация',
        'profile'         => 'Профиль',
        'save'            => 'Сохранить',
        'saved'           => 'Сохранено',
        'min'             => 'мин',
        'servings'        => 'порций',
        'view_recipe'     => 'Открыть рецепт',
        'no_recipes'      => 'Рецепты не найдены',
        'ai_generate'     => 'Сгенерировать рецепт',
        'what_to_cook'    => 'Что приготовить?',
        'advisor'         => 'Кулинарный советник',
    ];
    return $dict[$key] ?? $key;
}

/** Человекочитаемое время «X мин» */
function format_time(int $minutes): string
{
    if ($minutes <= 0) return '—';
    if ($minutes < 60) return $minutes . ' мин';
    $h = intdiv($minutes, 60);
    $m = $minutes % 60;
    return $m ? "{$h} ч {$m} мин" : "{$h} ч";
}

/** Цвет-акцент для типа сложности (Tailwind-классы) */
function difficulty_color(string $difficulty): string
{
    return match ($difficulty) {
        'Легко'         => 'bg-emerald-100 text-emerald-700',
        'Средне'        => 'bg-amber-100 text-amber-700',
        'Сложно'        => 'bg-rose-100 text-rose-700',
        'Мастер-класс'  => 'bg-violet-100 text-violet-700',
        default         => 'bg-gray-100 text-gray-600',
    };
}

/** CSRF-токен */
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}

function csrf_check(?string $token): bool
{
    return is_string($token)
        && !empty($_SESSION['csrf'])
        && hash_equals($_SESSION['csrf'], $token);
}