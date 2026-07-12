<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_response(['error' => 'Только POST'], 405);
if (!is_logged_in()) json_response(['error' => 'Требуется вход (401)'], 401);

$in  = json_input();
$cid = (int)($in['community_id'] ?? 0);
$content = trim((string)($in['content'] ?? ''));
if ($cid <= 0 || $content === '') json_response(['error' => 'Заполните пост'], 400);

$id = db_insert(
    'INSERT INTO community_posts (community_id, author_name, title, content, type, image_url) VALUES (?,?,?,?,?,?)',
    [$cid, current_user()['name'], mb_substr((string)($in['title'] ?? ''),0,255), $content,
     trim((string)($in['type'] ?? 'text')), trim((string)($in['image_url'] ?? '')) ?: null]
);
json_response(['ok' => true, 'id' => $id]);