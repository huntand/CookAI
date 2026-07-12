<?php
/**
 * JSON API: чтение рецептов
 *   GET /api/recipes.php                 — список (лимит 20)
 *   GET /api/recipes.php?id=5            — один рецепт
 *   GET /api/recipes.php?trending=1      — по лайкам
 *   GET /api/recipes.php?season=Осень    — сезонные
 *   GET /api/recipes.php?q=борщ          — поиск
 */
declare(strict_types=1);
require_once __DIR__ . '/../config/config.php';

try {
    $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

    if ($id > 0) {
        $recipe = db_one('SELECT * FROM recipes WHERE id = ?', [$id]);
        if (!$recipe) json_response(['error' => 'Рецепт не найден'], 404);
        db_exec('UPDATE recipes SET views_count = views_count + 1 WHERE id = ?', [$id]);
        foreach (['diet_type','ingredients','steps','tags','season'] as $f) {
            $recipe[$f] = json_field($recipe[$f]);
        }
        json_response(['recipe' => $recipe]);
    }

    $limit  = min(40, max(1, (int) ($_GET['limit'] ?? 20)));
    $params = [];
    $where  = '1=1';
    $order  = 'created_at DESC';

    if (!empty($_GET['trending'])) {
        $order = 'likes_count DESC';
    }
    if (!empty($_GET['season'])) {
        $where .= " AND JSON_CONTAINS(season, ?)";
        $params[] = json_encode($_GET['season'], JSON_UNESCAPED_UNICODE);
    }
    if (!empty($_GET['cuisine'])) {
        $where .= ' AND cuisine = ?';
        $params[] = $_GET['cuisine'];
    }
    if (!empty($_GET['q'])) {
        $where .= ' AND (title LIKE ? OR description LIKE ?)';
        $like = '%' . $_GET['q'] . '%';
        $params[] = $like; $params[] = $like;
    }

    $rows = db_all(
        "SELECT id,title,description,cuisine,difficulty,prep_time,cook_time,servings,
                image_url,tags,season,likes_count,views_count,is_ai_generated
         FROM recipes WHERE {$where} ORDER BY {$order} LIMIT {$limit}",
        $params
    );
    foreach ($rows as &$r) {
        $r['tags']   = json_field($r['tags']);
        $r['season'] = json_field($r['season']);
    }
    json_response(['recipes' => $rows, 'count' => count($rows)]);

} catch (Throwable $ex) {
    json_response(['error' => APP_DEBUG ? $ex->getMessage() : 'Ошибка сервера'], 500);
}