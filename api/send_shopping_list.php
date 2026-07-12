<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/mailer.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_response(['error' => 'Только POST'], 405);

$in    = json_input();
$email = trim((string)($in['email'] ?? ''));
$items = array_filter(array_map('trim', (array)($in['items'] ?? [])));

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) json_response(['error' => 'Некорректный email'], 400);
if (!$items) json_response(['error' => 'Список пуст'], 400);

$rows = implode('', array_map(fn($i) => '<li style="padding:6px 0;border-bottom:1px solid #f3f3f3;">🛒 ' . e($i) . '</li>', $items));
$html = '<div style="font-family:Arial,sans-serif;max-width:480px;margin:auto;">
    <h2 style="color:#d97706;">🍳 CookAI — Список покупок</h2>
    <ul style="list-style:none;padding:0;">' . $rows . '</ul>
    <p style="color:#999;font-size:12px;">Приятных покупок и вкусной готовки!</p></div>';

$ok = send_mail($email, 'CookAI — ваш список покупок', $html);
$ok ? json_response(['ok' => true]) : json_response(['error' => 'Не удалось отправить письмо'], 500);