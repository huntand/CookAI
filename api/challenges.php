<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/config.php';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $id = (int)($_GET['id'] ?? 0);
        if ($id > 0) {
            $ch = db_one('SELECT * FROM challenges WHERE id=?', [$id]);
            if (!$ch) json_response(['error' => 'Не найдено'], 404);
            json_response(['challenge' => $ch]);
        }
        $rows = db_all('SELECT * FROM challenges WHERE is_active=1 ORDER BY end_date ASC LIMIT 40');
        json_response(['challenges' => $rows]);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!is_logged_in()) json_response(['error' => 'Требуется вход (401)'], 401);
        $cid = (int)($_GET['join'] ?? 0);
        if ($cid <= 0) json_response(['error' => 'Не указан челлендж'], 400);
        $uid = (int) current_user()['id'];

        $exists = db_one('SELECT id FROM challenge_participants WHERE challenge_id=? AND user_id=?', [$cid, $uid]);
        if (!$exists) {
            db_insert('INSERT INTO challenge_participants (challenge_id, user_id) VALUES (?,?)', [$cid, $uid]);
            db_exec('UPDATE challenges SET participants_count = participants_count + 1 WHERE id=?', [$cid]);
        }
        json_response(['ok' => true]);
    }

    json_response(['error' => 'Метод не поддерживается'], 405);
} catch (Throwable $ex) {
    json_response(['error' => APP_DEBUG ? $ex->getMessage() : 'Ошибка сервера'], 500);
}