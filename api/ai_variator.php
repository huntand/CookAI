<?php
// api/ai_variator.php — вариации рецепта
declare(strict_types=1);
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/yandex.php';
require_once __DIR__ . '/../includes/ai_guard.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_response(['error' => 'Только POST'], 405);
ai_guard('variator', 3, 40);

$in   = json_input();
$dish = trim((string)($in['dish'] ?? ''));
$mode = trim((string)($in['mode'] ?? 'здоровая версия')); // веган / острая / бюджетная и т.д.
if ($dish === '') json_response(['error' => 'Укажите блюдо'], 400);

$schema = 'Структура JSON: {"title":"string","description":"string","changes":["что изменено"],
  "ingredients":[{"name":"string","amount":"string","unit":"string"}],
  "steps":[{"order":1,"instruction":"string","tip":"string"}]}';
$prompt = "Создай вариацию блюда «{$dish}» в стиле: {$mode}. Укажи, что именно изменено относительно оригинала.";

try { json_response(['ok' => true, 'recipe' => yandex_gpt_json($prompt, $schema)]); }
catch (Throwable $ex) { json_response(['error' => APP_DEBUG ? $ex->getMessage() : 'AI недоступен'], 500); }