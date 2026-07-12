<?php
/**
 * POST /api/ai_generate_recipe.php
 * body: { dish?, cuisine?, diet?, difficulty?, servings?, ingredients?, exclude? }
 */
declare(strict_types=1);
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/yandex.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['error' => 'Только POST'], 405);
}

$in = json_input();

$schema = 'Структура JSON: {
  "title": "string",
  "description": "string",
  "cuisine": "одно из: Русская,Итальянская,Японская,Мексиканская,Индийская,Тайская,Французская,Грузинская,Средиземноморская,Американская,Китайская,Корейская,Другая",
  "difficulty": "одно из: Легко,Средне,Сложно,Мастер-класс",
  "prep_time": число_минут,
  "cook_time": число_минут,
  "servings": число,
  "calories": число, "proteins": число, "fats": число, "carbs": число,
  "diet_type": ["string"],
  "tags": ["string"],
  "ingredients": [{"name":"string","amount":"string","unit":"string","is_optional":false}],
  "steps": [{"order":1,"instruction":"string","timer_minutes":0,"tip":"string"}]
}';

$parts = [];
if (!empty($in['dish']))        $parts[] = 'Блюдо: ' . $in['dish'];
if (!empty($in['cuisine']))     $parts[] = 'Кухня: ' . $in['cuisine'];
if (!empty($in['diet']))        $parts[] = 'Диета/тип питания: ' . $in['diet'];
if (!empty($in['difficulty']))  $parts[] = 'Сложность: ' . $in['difficulty'];
if (!empty($in['servings']))    $parts[] = 'Порций: ' . (int) $in['servings'];
if (!empty($in['ingredients'])) $parts[] = 'Использовать ингредиенты: ' . $in['ingredients'];
if (!empty($in['exclude']))     $parts[] = 'Исключить: ' . $in['exclude'];

if (!$parts) $parts[] = 'Придумай интересный рецепт на любой вкус.';

$prompt = 'Сгенерируй подробный кулинарный рецепт. ' . implode('. ', $parts)
        . '. Рассчитай примерную калорийность и БЖУ на порцию. Дай практичные советы в поле tip.';

try {
    $recipe = yandex_gpt_json($prompt, $schema);

    // --- Санитайзинг перед возвратом/записью ---
    $allowedCuisine = ['Русская','Итальянская','Японская','Мексиканская','Индийская','Тайская',
        'Французская','Грузинская','Средиземноморская','Американская','Китайская','Корейская','Другая'];
    $allowedDiff = ['Легко','Средне','Сложно','Мастер-класс'];

    $clean = [
        'title'       => mb_substr(trim((string)($recipe['title'] ?? 'Рецепт от AI')), 0, 255),
        'description' => trim((string)($recipe['description'] ?? '')),
        'cuisine'     => in_array($recipe['cuisine'] ?? '', $allowedCuisine, true) ? $recipe['cuisine'] : 'Другая',
        'difficulty'  => in_array($recipe['difficulty'] ?? '', $allowedDiff, true) ? $recipe['difficulty'] : 'Легко',
        'prep_time'   => max(0, (int)($recipe['prep_time'] ?? 0)),
        'cook_time'   => max(0, (int)($recipe['cook_time'] ?? 0)),
        'servings'    => max(1, (int)($recipe['servings'] ?? 2)),
        'calories'    => (int)($recipe['calories'] ?? 0),
        'proteins'    => (int)($recipe['proteins'] ?? 0),
        'fats'        => (int)($recipe['fats'] ?? 0),
        'carbs'       => (int)($recipe['carbs'] ?? 0),
        'diet_type'   => array_values(array_filter((array)($recipe['diet_type'] ?? []))),
        'tags'        => array_values(array_filter((array)($recipe['tags'] ?? []))),
        'ingredients' => array_values((array)($recipe['ingredients'] ?? [])),
        'steps'       => array_values((array)($recipe['steps'] ?? [])),
        'is_ai_generated' => true,
    ];

    json_response(['ok' => true, 'recipe' => $clean]);
} catch (Throwable $ex) {
    json_response(['error' => APP_DEBUG ? $ex->getMessage() : 'AI временно недоступен'], 500);
}