<?php
/**
 * CookAI — загрузка демо-данных.
 * CLI: php cron/seed_demo.php
 * HTTP: /cron/seed_demo.php?token=CRON_SECRET
 */
declare(strict_types=1);
require_once __DIR__ . '/../config/config.php';

if (PHP_SAPI !== 'cli') {
    if (!hash_equals(CRON_SECRET, $_GET['token'] ?? '')) { http_response_code(403); exit('Forbidden'); }
    header('Content-Type: text/plain; charset=utf-8');
}

$log = fn(string $m) => print('[seed] ' . $m . PHP_EOL);
$sql = file_get_contents(__DIR__ . '/../database/seed_demo.sql');
if ($sql === false) { $log('Не найден seed_demo.sql'); exit(1); }

// Разбиваем по ; на верхнем уровне (простая стратегия — в сидах нет процедур)
$statements = array_filter(array_map('trim', preg_split('/;\s*[\r\n]/', $sql)));

$pdo = db(); // предполагается, что db() возвращает PDO из config
$ok = 0; $fail = 0;
foreach ($statements as $stmt) {
    if ($stmt === '' || str_starts_with($stmt, '--')) continue;
    try {
        $pdo->exec($stmt);
        $ok++;
    } catch (Throwable $ex) {
        $fail++;
        $log('Ошибка: ' . mb_substr($ex->getMessage(), 0, 120));
    }
}
$log("Готово. Успешно: {$ok}, ошибок: {$fail}");