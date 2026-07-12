<?php
/**
 * CookAI — анализ изображений через YandexGPT Vision.
 */
declare(strict_types=1);
require_once __DIR__ . '/../config/yandex_ai.php';

/**
 * Отправляет изображение (base64 без префикса) + промпт, ожидает JSON-ответ.
 *
 * @param string $imageBase64 чистый base64 (без "data:image/...;base64,")
 * @param string $prompt      текстовая инструкция
 * @param string $schema      описание JSON-структуры ответа
 * @return array разобранный JSON
 * @throws RuntimeException
 */
function yandex_vision_json(string $imageBase64, string $prompt, string $schema): array
{
    $system = 'Ты — эксперт по питанию и распознаванию блюд по фотографии. '
            . 'Отвечай СТРОГО валидным JSON без markdown-разметки и пояснений. ' . $schema;

    $body = [
        'modelUri' => YANDEX_VISION_MODEL,
        'completionOptions' => [
            'stream'      => false,
            'temperature' => 0.2,
            'maxTokens'   => 1500,
        ],
        'messages' => [
            ['role' => 'system', 'text' => $system],
            [
                'role' => 'user',
                'text' => $prompt,
                // Изображение передаётся как вложение в поле image (base64)
                'image' => $imageBase64,
            ],
        ],
    ];

    $raw = yandex_vision_request($body);

    $text = $raw['result']['alternatives'][0]['message']['text'] ?? '';
    if ($text === '') throw new RuntimeException('Vision: пустой ответ модели');

    return yandex_extract_json($text);
}

/** Низкоуровневый запрос к completion endpoint */
function yandex_vision_request(array $body, int $retries = 2): array
{
    $lastErr = '';
    for ($attempt = 0; $attempt <= $retries; $attempt++) {
        $ch = curl_init(YANDEX_COMPLETION_URL);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_TIMEOUT        => 60,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Api-Key ' . YANDEX_API_KEY,
                'x-folder-id: ' . YANDEX_FOLDER_ID,
            ],
            CURLOPT_POSTFIELDS => json_encode($body, JSON_UNESCAPED_UNICODE),
        ]);
        $resp = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err  = curl_error($ch);
        curl_close($ch);

        if ($resp !== false && $code < 400) {
            $data = json_decode($resp, true);
            if (is_array($data)) return $data;
            $lastErr = 'Некорректный JSON от API';
        } else {
            $lastErr = 'HTTP ' . $code . ' ' . $err;
            // 429/5xx — имеет смысл повторить
            if ($code && $code < 500 && $code !== 429) break;
        }
        if ($attempt < $retries) usleep(700000 * ($attempt + 1));
    }
    throw new RuntimeException('Vision API: ' . $lastErr);
}

/** Извлекает JSON из текста ответа (на случай обёртки) */
function yandex_extract_json(string $text): array
{
    $text = trim($text);
    $text = preg_replace('/^```(?:json)?|```$/m', '', $text);
    $start = strpos($text, '{');
    $end   = strrpos($text, '}');
    if ($start === false || $end === false) {
        throw new RuntimeException('Vision: JSON не найден в ответе');
    }
    $json = substr($text, $start, $end - $start + 1);
    $data = json_decode($json, true);
    if (!is_array($data)) throw new RuntimeException('Vision: не удалось разобрать JSON');
    return $data;
}

/**
 * Валидирует и нормализует загруженное изображение из data-URL,
 * возвращает чистый base64.
 */
function normalize_image_data_url(string $dataUrl): string
{
    if (!preg_match('#^data:(image/[a-z+]+);base64,(.+)$#i', $dataUrl, $m)) {
        throw new RuntimeException('Некорректный формат изображения');
    }
    $mime   = strtolower($m[1]);
    $base64 = $m[2];

    if (!in_array($mime, explode(',', ALLOWED_IMAGE_TYPES), true)) {
        throw new RuntimeException('Поддерживаются только JPEG, PNG и WebP');
    }
    $bin = base64_decode($base64, true);
    if ($bin === false) throw new RuntimeException('Не удалось декодировать изображение');
    if (strlen($bin) > MAX_IMAGE_SIZE) {
        throw new RuntimeException('Изображение слишком большое (макс. ' . (MAX_IMAGE_SIZE / 1048576) . ' МБ)');
    }
    // Проверка, что это действительно изображение
    if (@getimagesizefromstring($bin) === false) {
        throw new RuntimeException('Файл не является изображением');
    }
    return base64_encode($bin);
}