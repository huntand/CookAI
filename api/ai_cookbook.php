<?php
// api/ai_cookbook.php
declare(strict_types=1);
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/yandex.php';
require_once __DIR__ . '/../includes/ai_guard.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_response(['error' => 'Только POST'], 405);
ai_guard('cookbook', 1, 20);

$in    = json_input();
$theme = trim((string)($in['theme'] ?? ''));
$count = max(3, min(7, (int)($in['count'] ?? 5)));
if ($theme === '') json_response(['error' => 'Укажите тему книги'], 400);

$schema = 'Структура JSON: {"title":"название книги","intro":"вступление",
  "recipes":[{"title":"string","description":"string","time":число,"difficulty":"string"}]}';

$prompt = "Составь план кулинарной книги на тему «{$theme}» из {$count} рецептов. "
        . 'Дай название книги, короткое вступление и список рецептов с кратким описанием.';

try {
    $book = yandex_gpt_json($prompt, $schema);
    json_response(['ok' => true, 'book' => [
        'title'   => trim((string)($book['title'] ?? ('Книга: ' . $theme))),
        'intro'   => trim((string)($book['intro'] ?? '')),
        'recipes' => array_values((array)($book['recipes'] ?? [])),
    ]]);
} catch (Throwable $ex) {
    json_response(['error' => APP_DEBUG ? $ex->getMessage() : 'AI временно недоступен'], 500);
}