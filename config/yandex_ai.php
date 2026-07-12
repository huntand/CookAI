<?php
/**
 * CookAI — настройки Yandex AI Studio
 */
declare(strict_types=1);

defined('YANDEX_API_KEY')   || define('YANDEX_API_KEY', 'AQVN27WMtneoptdWWqXZ4RyZzrIa5dUyS9zRwKYu');
defined('YANDEX_FOLDER_ID') || define('YANDEX_FOLDER_ID', 'b1g26lamin6oetbepvee');

defined('YANDEX_GPT_URL')    || define('YANDEX_GPT_URL',    'https://llm.api.cloud.yandex.net/foundationModels/v1/completion');
defined('YANDEX_ART_URL')    || define('YANDEX_ART_URL',    'https://llm.api.cloud.yandex.net:443/foundationModels/v1/imageGenerationAsync');
defined('YANDEX_ART_OP_URL') || define('YANDEX_ART_OP_URL', 'https://llm.api.cloud.yandex.net:443/operations/');

// --- Мультимодальная модель (анализ изображений) ---
defined('YANDEX_VISION_MODEL')   || define('YANDEX_VISION_MODEL', 'gpt://' . YANDEX_FOLDER_ID . '/yandex-gpt-vision/latest');
defined('YANDEX_COMPLETION_URL') || define('YANDEX_COMPLETION_URL', 'https://llm.api.cloud.yandex.net/foundationModels/v1/completion');

// Лимиты загрузки изображений
defined('MAX_IMAGE_SIZE')      || define('MAX_IMAGE_SIZE', 5 * 1024 * 1024);   // 5 МБ
defined('ALLOWED_IMAGE_TYPES') || define('ALLOWED_IMAGE_TYPES', 'image/jpeg,image/png,image/webp');

defined('YANDEX_GPT_MODEL') || define('YANDEX_GPT_MODEL', 'yandexgpt/latest');

// --- SMTP (опционально) ---
defined('SMTP_HOST')      || define('SMTP_HOST', '');
defined('SMTP_PORT')      || define('SMTP_PORT', 465);
defined('SMTP_USER')      || define('SMTP_USER', '');
defined('SMTP_PASS')      || define('SMTP_PASS', '');
defined('SMTP_FROM')      || define('SMTP_FROM', 'noreply@cookai.local');
defined('SMTP_FROM_NAME') || define('SMTP_FROM_NAME', 'CookAI');