<?php
/**
 * POST /api/ai_calorie_scan.php
 * body: { image?, description?, save?: bool }
 * Vision по фото + fallback на текст. Опционально сохраняет в дневник.
 */
declare(strict_types=1);
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/yandex.php';
require_once __DIR__ . '/../includes/yandex_vision.php';
require_once __DIR__ . '/../includes/ai_guard.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_response(['error' => 'Только POST'], 405);
ai_guard('calorie');

$in    = json_input();
$image = (string)($in['image'] ?? '');
$desc  = trim((string)($in['description'] ?? ''));
$save  = !empty($in['save']);

if ($image === '' && $desc === '') {
    json_response(['error' => 'Загрузите фото или опишите блюдо'], 400);
}

$schema = 'Структура JSON: {"dish":"string","portion":"string",
  "calories":число,"proteins":число,"fats":число,"carbs":число,
  "ingredients":["строки — распознанные ингредиенты"],
  "confidence":"низкая|средняя|высокая","note":"string"}';

try {
    if ($image !== '') {
        $base64 = normalize_image_data_url($image);
        $prompt = 'Определи блюдо на фотографии, перечисли видимые ингредиенты и оцени '
                . 'пищевую ценность на порцию (калории и БЖУ).'
                . ($desc !== '' ? " Дополнительный контекст: «{$desc}»." : '');
        $data = yandex_vision_json($base64, $prompt, $schema);
        $source = 'photo';
    } else {
        $prompt = "Оцени пищевую ценность блюда по описанию: «{$desc}». "
                . 'Дай калорийность и БЖУ на порцию, перечисли предполагаемые ингредиенты.';
        $data = yandex_gpt_json($prompt, $schema);
        $source = 'text';
    }

    $result = [
        'dish'        => trim((string)($data['dish'] ?? 'Блюдо')),
        'portion'     => trim((string)($data['portion'] ?? '1 порция')),
        'calories'    => max(0, (int)($data['calories'] ?? 0)),
        'proteins'    => max(0, (int)($data['proteins'] ?? 0)),
        'fats'        => max(0, (int)($data['fats'] ?? 0)),
        'carbs'       => max(0, (int)($data['carbs'] ?? 0)),
        'ingredients' => array_values(array_filter(array_map('strval', (array)($data['ingredients'] ?? [])))),
        'confidence'  => (string)($data['confidence'] ?? 'средняя'),
        'note'        => trim((string)($data['note'] ?? '')),
    ];

    $scanId = null;
    if ($save && is_logged_in()) {
        $scanId = db_insert(
            'INSERT INTO calorie_scans
             (user_email, dish, portion, calories, proteins, fats, carbs, source, confidence, created_at)
             VALUES (?,?,?,?,?,?,?,?,?, NOW())',
            [
                current_user()['email'], $result['dish'], $result['portion'],
                $result['calories'], $result['proteins'], $result['fats'], $result['carbs'],
                $source, $result['confidence'],
            ]
        );
    }

    json_response([
        'ok'      => true,
        'source'  => $source,
        'result'  => $result,
        'saved'   => $scanId !== null,
        'scan_id' => $scanId,
        'ai'      => ai_usage_status('calorie'),  // остаток после инкремента
    ]);
} catch (Throwable $ex) {
    json_response(['error' => APP_DEBUG ? $ex->getMessage() : 'Не удалось проанализировать. Попробуйте другое фото или описание.'], 500);
}