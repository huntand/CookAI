<?php
/**
 * GET    /api/saved_recipes.php            — список сохранённых рецептов пользователя
 * POST   /api/saved_recipes.php            — сохранить { recipe_id, notes?, is_favorite? }
 * DELETE /api/saved_recipes.php?recipe_id= — удалить из сохранённых
 */
declare(strict_types=1);
require_once __DIR__ . '/../config/config.php';

if (!is_logged_in()) {
    json_response(['error' => 'Требуется вход (401)'], 401);
}
$uid = (int) current_user()['id'];

try {
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'GET') {
        $rows = db_all(
            'SELECT r.id,r.title,r.description,r.cuisine,r.difficulty,r.prep_time,r.cook_time,
                    r.servings,r.image_url,r.tags,r.season,r.likes_count,r.is_ai_generated,
                    s.notes,s.is_favorite
             FROM saved_recipes s JOIN recipes r ON r.id = s.recipe_id
             WHERE s.user_id = ? ORDER BY s.id DESC',
            [$uid]
        );
        foreach ($rows as &$r) { $r['tags'] = json_field($r['tags']); }
        json_response(['recipes' => $rows]);
    }

    if ($method === 'POST') {
        $in  = json_input();
        $rid = (int)($in['recipe_id'] ?? 0);
        if ($rid <= 0) json_response(['error' => 'Не указан рецепт'], 400);

        $exists = db_one('SELECT id FROM saved_recipes WHERE user_id=? AND recipe_id=?', [$uid, $rid]);
        if ($exists) {
            db_exec('DELETE FROM saved_recipes WHERE id=?', [(int)$exists['id']]);
            json_response(['ok' => true, 'saved' => false]);
        }
        db_insert(
            'INSERT INTO saved_recipes (user_id, recipe_id, notes, is_favorite) VALUES (?,?,?,?)',
            [$uid, $rid, trim((string)($in['notes'] ?? '')), !empty($in['is_favorite']) ? 1 : 0]
        );
        json_response(['ok' => true, 'saved' => true]);
    }

    if ($method === 'DELETE') {
        $rid = (int)($_GET['recipe_id'] ?? 0);
        db_exec('DELETE FROM saved_recipes WHERE user_id=? AND recipe_id=?', [$uid, $rid]);
        json_response(['ok' => true]);
    }

    json_response(['error' => 'Метод не поддерживается'], 405);
} catch (Throwable $ex) {
    json_response(['error' => APP_DEBUG ? $ex->getMessage() : 'Ошибка сервера'], 500);
}