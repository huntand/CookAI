<?php
// api/notifications.php
declare(strict_types=1);
require_once __DIR__ . '/../config/config.php';
if (!is_logged_in()) json_response(['error' => 'Требуется вход (401)'], 401);
$email = current_user()['email'];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $rows = db_all('SELECT * FROM notifications WHERE user_email=? ORDER BY created_at DESC LIMIT 20', [$email]);
    $unread = (int)(db_one('SELECT COUNT(*) c FROM notifications WHERE user_email=? AND is_read=0', [$email])['c'] ?? 0);
    json_response(['notifications' => $rows, 'unread' => $unread]);
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    db_exec('UPDATE notifications SET is_read=1 WHERE user_email=?', [$email]);
    json_response(['ok' => true]);
}
json_response(['error' => 'Метод не поддерживается'], 405);