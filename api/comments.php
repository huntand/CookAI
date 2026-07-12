<?php
/**
 * GET  /api/comments.php?recipe_id=5 — список отзывов
 * POST /api/comments.php             — { recipe_id, text, rating, author_name?, photo_url? }
 */
declare(strict_types=1);
require_once __DIR__ . '/../config/config.php';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $rid = (int)($_GET['recipe_id'] ?? 0);
        $rows = db_all(
            'SELECT id, text, rating, author_name, photo_url, created_at
             FROM comments WHERE recipe_id=? ORDER BY created_at DESC',
            [$rid]
        );
        $avg = db_one('SELECT AVG(rating) a, COUNT(*) c FROM comments WHERE recipe_id=? AND rating>0', [$rid]);
        json_response([
            'comments' => $rows,
            'avg'      => round((float)($avg['a'] ?? 0), 1),
            'count'    => (int)($avg['c'] ?? 0),
        ]);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $in   = json_input();
        $rid  = (int)($in['recipe_id'] ?? 0);
        $text = trim((string)($in['text'] ?? ''));
        $rate = max(0, min(5, (int)($in['rating'] ?? 0)));
        if ($rid <= 0 || ($text === '' && $rate === 0)) {
            json_response(['error' => 'Заполните отзыв или поставьте оценку'], 400);
        }

        $author = current_user()['name'] ?? trim((string)($in['author_name'] ?? 'Гость'));
        $id = db_insert(
            'INSERT INTO comments (recipe_id, text, rating, author_name, photo_url) VALUES (?,?,?,?,?)',
            [$rid, $text, $rate, mb_substr($author, 0, 255), trim((string)($in['photo_url'] ?? '')) ?: null]
        );

        if (is_logged_in()) {
            db_exec('UPDATE user_stats SET reviews_written = reviews_written + 1, total_points = total_points + 5 WHERE user_id=?',
                [(int) current_user()['id']]);
        }
        json_response(['ok' => true, 'id' => $id]);
    }

    json_response(['error' => 'Метод не поддерживается'], 405);
} catch (Throwable $ex) {
    json_response(['error' => APP_DEBUG ? $ex->getMessage() : 'Ошибка сервера'], 500);
}