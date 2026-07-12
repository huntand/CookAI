<?php
/**
 * POST /api/ai_recipe_chat.php
 * body: { recipe_id, message, history?:[{role,text}] }
 */
declare(strict_types=1);
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/yandex.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['error' => 'Только POST'], 405);
}

$in      = json_input();
$rid     = (int)($in['recipe_id'] ?? 0);
$message = trim((string)($in['message'] ?? ''));
$history = is_array($in['history'] ?? null) ? $in['history'] : [];

if ($message === '' || $rid <= 0) {
    json_response(['error' => 'Некорректный запрос'], 400);
}

$recipe = db_one('SELECT title, description, ingredients, steps FROM recipes WHERE id=?', [$rid]);
if (!$recipe) json_response(['error' => 'Рецепт не найден'], 404);

$ings  = json_field($recipe['ingredients']);
$steps = json_field($recipe['steps']);

$ctx  = 'Рецепт: ' . $recipe['title'] . '. ' . $recipe['description'] . "\nИнгредиенты: ";
$ctx .= implode(', ', array_map(fn($i) => ($i['name'] ?? '') . ' ' . ($i['amount'] ?? '') . ' ' . ($i['unit'] ?? ''), $ings));
$ctx .= "\nШаги: " . implode(' | ', array_map(fn($s) => $s['instruction'] ?? '', $steps));

$system = 'Ты — AI-помощник по конкретному рецепту в CookAI. Отвечай только на вопросы об этом рецепте: '
        . 'техника, замены, время, порции, подача. Кратко, на русском, с эмодзи. Контекст рецепта: '
        . mb_substr($ctx, 0, 2500);

$trimmed = array_slice($history, -6);
$trimmed[] = ['role' => 'user', 'text' => $message];

try {
    $reply = yandex_gpt_text($system, $trimmed, 0.6);
    json_response(['ok' => true, 'reply' => $reply ?: 'Не смог ответить, переформулируйте вопрос 🙂']);
} catch (Throwable $ex) {
    json_response(['error' => APP_DEBUG ? $ex->getMessage() : 'AI временно недоступен'], 500);
}