<?php
/**
 * POST /api/save_recipe.php — сохранение (в т.ч. AI-сгенерированного) рецепта.
 */
declare(strict_types=1);
require_once __DIR__ . '/../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['error' => 'Только POST'], 405);
}
if (!is_logged_in()) {
    json_response(['error' => 'Требуется вход (401)'], 401);
}

$in = json_input();
$title = trim((string)($in['title'] ?? ''));
if ($title === '') {
    json_response(['error' => 'Не указано название'], 400);
}

$allowedCuisine = ['Русская','Итальянская','Японская','Мексиканская','Индийская','Тайская',
    'Французская','Грузинская','Средиземноморская','Американская','Китайская','Корейская','Другая'];
$allowedDiff = ['Легко','Средне','Сложно','Мастер-класс'];

$cuisine    = in_array($in['cuisine'] ?? '', $allowedCuisine, true) ? $in['cuisine'] : 'Другая';
$difficulty = in_array($in['difficulty'] ?? '', $allowedDiff, true) ? $in['difficulty'] : 'Легко';

$je = fn($v) => json_encode(array_values((array)($v ?? [])), JSON_UNESCAPED_UNICODE);

try {
    $id = db_insert(
        'INSERT INTO recipes
         (title, description, cuisine, diet_type, difficulty, prep_time, cook_time, servings,
          calories, proteins, fats, carbs, ingredients, steps, image_url, tags, season,
          is_ai_generated, author_id)
         VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
        [
            mb_substr($title, 0, 255),
            trim((string)($in['description'] ?? '')),
            $cuisine,
            $je($in['diet_type'] ?? []),
            $difficulty,
            max(0, (int)($in['prep_time'] ?? 0)),
            max(0, (int)($in['cook_time'] ?? 0)),
            max(1, (int)($in['servings'] ?? 1)),
            (int)($in['calories'] ?? 0),
            (int)($in['proteins'] ?? 0),
            (int)($in['fats'] ?? 0),
            (int)($in['carbs'] ?? 0),
            $je($in['ingredients'] ?? []),
            $je($in['steps'] ?? []),
            trim((string)($in['image_url'] ?? '')) ?: null,
            $je($in['tags'] ?? []),
            $je($in['season'] ?? []),
            !empty($in['is_ai_generated']) ? 1 : 0,
            (int) current_user()['id'],
        ]
    );

    db_exec('UPDATE user_stats SET recipes_created = recipes_created + 1, total_points = total_points + 10 WHERE user_id = ?',
        [(int) current_user()['id']]);

    json_response(['ok' => true, 'id' => $id]);
} catch (Throwable $ex) {
    json_response(['error' => APP_DEBUG ? $ex->getMessage() : 'Ошибка сохранения'], 500);
}