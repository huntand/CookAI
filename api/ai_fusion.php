<?php
// api/ai_fusion.php — слияние двух кухонь
declare(strict_types=1);
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/yandex.php';
require_once __DIR__ . '/../includes/ai_guard.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_response(['error' => 'Только POST'], 405);
ai_guard('fusion', 3, 40);

$in = json_input();
$a = trim((string)($in['cuisine_a'] ?? ''));
$b = trim((string)($in['cuisine_b'] ?? ''));
if ($a === '' || $b === '') json_response(['error' => 'Выберите две кухни'], 400);

$schema = 'Структура JSON: {"title":"string","description":"string","concept":"объяснение фьюжна",
  "ingredients":[{"name":"string","amount":"string","unit":"string"}],
  "steps":[{"order":1,"instruction":"string","tip":"string"}]}';
$prompt = "Придумай оригинальное фьюжн-блюдо, объединяющее {$a} и {$b} кухни. Опиши концепцию слияния.";

try { json_response(['ok' => true, 'recipe' => yandex_gpt_json($prompt, $schema)]); }
catch (Throwable $ex) { json_response(['error' => APP_DEBUG ? $ex->getMessage() : 'AI недоступен'], 500); }