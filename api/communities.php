<?php
/**
 * GET  /api/communities.php            — список
 * GET  /api/communities.php?id=5       — одно сообщество + посты
 * POST /api/communities.php            — { name, description, category, cover_image? }
 * POST /api/communities.php?join=5     — вступить
 */
declare(strict_types=1);
require_once __DIR__ . '/../config/config.php';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $id = (int)($_GET['id'] ?? 0);
        if ($id > 0) {
            $c = db_one('SELECT * FROM communities WHERE id=?', [$id]);
            if (!$c) json_response(['error' => 'Не найдено'], 404);
            $c['tags'] = json_field($c['tags']);
            $posts = db_all('SELECT * FROM community_posts WHERE community_id=? ORDER BY created_at DESC LIMIT 30', [$id]);
            json_response(['community' => $c, 'posts' => $posts]);
        }
        $rows = db_all('SELECT * FROM communities ORDER BY members_count DESC LIMIT 40');
        foreach ($rows as &$r) $r['tags'] = json_field($r['tags']);
        json_response(['communities' => $rows]);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!is_logged_in()) json_response(['error' => 'Требуется вход (401)'], 401);

        if (!empty($_GET['join'])) {
            $cid = (int) $_GET['join'];
            db_exec('UPDATE communities SET members_count = members_count + 1 WHERE id=?', [$cid]);
            db_exec('UPDATE user_stats SET communities_joined = communities_joined + 1, total_points = total_points + 3 WHERE user_id=?',
                [(int) current_user()['id']]);
            json_response(['ok' => true]);
        }

        $in = json_input();
        $name = trim((string)($in['name'] ?? ''));
        if ($name === '') json_response(['error' => 'Укажите название'], 400);
        $id = db_insert(
            'INSERT INTO communities (name, description, category, cover_image, tags, members_count) VALUES (?,?,?,?,?,1)',
            [mb_substr($name,0,255), trim((string)($in['description'] ?? '')),
             trim((string)($in['category'] ?? 'Общее')),
             trim((string)($in['cover_image'] ?? '')) ?: null,
             json_encode((array)($in['tags'] ?? []), JSON_UNESCAPED_UNICODE)]
        );
        json_response(['ok' => true, 'id' => $id]);
    }

    json_response(['error' => 'Метод не поддерживается'], 405);
} catch (Throwable $ex) {
    json_response(['error' => APP_DEBUG ? $ex->getMessage() : 'Ошибка сервера'], 500);
}