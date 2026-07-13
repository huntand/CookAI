<?php
/**
 * ДИАГНОСТИЧЕСКИЙ ФАЙЛ для отладки проблемы 500 ошибки
 * Удалить после исправления!
 */
header('Content-Type: text/plain; charset=utf-8');

echo "=== CookAI DIAGNOSTICS ===\n\n";

// 1. Проверяем существование .env
echo "1. .env файл:\n";
$env_file = dirname(__DIR__) . '/.env';
echo "   Path: {$env_file}\n";
echo "   Exists: " . (file_exists($env_file) ? 'YES' : 'NO') . "\n";
if (file_exists($env_file)) {
    echo "   Readable: " . (is_readable($env_file) ? 'YES' : 'NO') . "\n";
    echo "   Size: " . filesize($env_file) . " bytes\n";
}

echo "\n2. Переменные окружения:\n";
$critical_vars = ['DB_NAME', 'DB_USER', 'DB_PASS', 'DB_HOST', 'YANDEX_API_KEY'];
foreach ($critical_vars as $var) {
    $val = getenv($var);
    echo "   {$var}: " . ($val ? 'SET (' . strlen($val) . ' chars)' : 'NOT SET') . "\n";
}

echo "\n3. Проверка load_env_file функции:\n";
if (function_exists('load_env_file')) {
    echo "   Function exists: YES\n";
    echo "   Testing load_env_file()...\n";
    load_env_file();
    echo "   After loading:\n";
    foreach ($critical_vars as $var) {
        $val = getenv($var);
        echo "     {$var}: " . ($val ? 'SET' : 'NOT SET') . "\n";
    }
} else {
    echo "   Function exists: NO (load_env_file not found)\n";
}

echo "\n4. PHP Info:\n";
echo "   Version: " . phpversion() . "\n";
echo "   Display Errors: " . ini_get('display_errors') . "\n";
echo "   Error Reporting: " . error_reporting() . "\n";
echo "   Working Dir: " . getcwd() . "\n";

echo "\n5. Попытка подключения к БД:\n";
try {
    require_once dirname(__DIR__) . '/config/config.php';
    echo "   config.php loaded: YES\n";
    echo "   DB_HOST: " . (defined('DB_HOST') ? DB_HOST : 'NOT DEFINED') . "\n";
    echo "   DB_NAME: " . (defined('DB_NAME') ? DB_NAME : 'NOT DEFINED') . "\n";
    echo "   DB_USER: " . (defined('DB_USER') ? DB_USER : 'NOT DEFINED') . "\n";
} catch (Throwable $e) {
    echo "   ERROR: " . $e->getMessage() . "\n";
}

echo "\nEND DIAGNOSTICS\n";
