<?php
// api/friends.php
declare(strict_types=1);
require_once __DIR__ . '/../config/config.php';
if (!is_logged_in()) json_response(['error' => 'Требуется вход (401)'], 401);
$uid = (int) current_user()['id'];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    json_response(['friends' => db_all('SELECT * FROM friends WHERE user_id=? ORDER BY id DESC', [$uid])]);
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $in = json_input();
    $email = mb_strtolower(trim((string)($in['email'] ?? '')));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) json_response(['error' => 'Некорректный email'], 400);
    $friend = db_one('SELECT name FROM users WHERE email=?', [$email]);
    $id = db_insert('INSERT INTO friends (user_id, friend_email, friend_name, status) VALUES (?,?,?,?)',
        [$uid, $email, $friend['name'] ?? null, 'pending']);
    json_response(['ok' => true, 'id' => $id]);
}
json_response(['error' => 'Метод не поддерживается'], 405);