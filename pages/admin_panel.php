<?php
// pages/admin_panel.php
require_once __DIR__ . '/../config/config.php';
require_admin();

/** Безопасный подсчёт — не падает, если таблицы нет */
function admin_count(string $sql): int {
    try {
        $row = db_one($sql);
        return (int) ($row['c'] ?? 0);
    } catch (Throwable $e) {
        return 0;
    }
}

$stats = [
    'users'    => admin_count('SELECT COUNT(*) c FROM users'),
    'recipes'  => admin_count('SELECT COUNT(*) c FROM recipes'),
    'comments' => admin_count('SELECT COUNT(*) c FROM comments'),
    'ai'       => admin_count('SELECT COUNT(*) c FROM recipes WHERE is_ai_generated=1'),
];

try {
    $latest = db_all('SELECT id, title, cuisine, created_at FROM recipes ORDER BY created_at DESC LIMIT 15');
} catch (Throwable $e) {
    $latest = [];
}

$pageTitle = 'Админка';
require_once __DIR__ . '/../includes/header.php';
?>
<section class="max-w-5xl mx-auto px-4 sm:px-6 py-8">
    <h1 class="text-3xl font-extrabold text-gray-800 mb-6">⚙️ Панель управления</h1>
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">
        <?php foreach (['users'=>'Пользователи','recipes'=>'Рецепты','comments'=>'Отзывы','ai'=>'AI-рецепты'] as $k=>$label): ?>
            <div class="bg-white rounded-2xl shadow-md p-5 text-center">
                <div class="text-3xl font-extrabold text-amber-600"><?= (int)$stats[$k] ?></div>
                <div class="text-sm text-gray-400"><?= $label ?></div>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="bg-white rounded-3xl shadow-md overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 font-extrabold text-gray-800">Последние рецепты</div>
        <?php foreach ($latest as $r): ?>
            <div class="flex items-center justify-between px-5 py-3 border-b border-gray-50 text-sm">
                <a href="<?= url('recipe/'.(int)$r['id']) ?>" class="font-semibold text-gray-700 hover:text-amber-600"><?= e($r['title']) ?></a>
                <span class="text-gray-400"><?= e($r['cuisine']) ?> · <?= date('d.m.Y', strtotime($r['created_at'])) ?></span>
            </div>
        <?php endforeach; ?>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>