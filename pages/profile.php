<?php
require_once __DIR__ . '/../config/config.php';
require_login();
$u = current_user();
$stats = db_one('SELECT * FROM user_stats WHERE user_id=?', [$u['id']]) ?? [];
$myRecipes = db_all('SELECT * FROM recipes WHERE author_id=? ORDER BY created_at DESC LIMIT 12', [$u['id']]);
$pageTitle = 'Профиль';
require_once __DIR__ . '/../includes/header.php';
?>
<section class="max-w-5xl mx-auto px-4 sm:px-6 py-8">
    <div class="bg-white rounded-3xl shadow-md p-6 sm:p-8 flex flex-col sm:flex-row items-center gap-6" data-aos="fade-up">
        <img src="<?= e($u['avatar_url'] ?: 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=200') ?>" class="w-24 h-24 rounded-full object-cover ring-4 ring-amber-100" alt="Аватар">
        <div class="text-center sm:text-left flex-1">
            <h1 class="text-2xl font-extrabold text-gray-800"><?= e($u['name']) ?></h1>
            <p class="text-gray-400 text-sm"><?= e($u['email']) ?></p>
            <div class="flex gap-6 mt-4 justify-center sm:justify-start">
                <div class="text-center"><div class="text-xl font-extrabold text-amber-600"><?= (int)($stats['total_points'] ?? 0) ?></div><div class="text-xs text-gray-400">очков</div></div>
                <div class="text-center"><div class="text-xl font-extrabold text-emerald-600"><?= (int)($stats['recipes_created'] ?? 0) ?></div><div class="text-xs text-gray-400">рецептов</div></div>
                <div class="text-center"><div class="text-xl font-extrabold text-violet-600"><?= (int)($stats['level'] ?? 1) ?></div><div class="text-xs text-gray-400">уровень</div></div>
            </div>
        </div>
        <a href="<?= url('logout') ?>" class="px-4 py-2 rounded-xl bg-gray-100 text-gray-500 text-sm font-semibold hover:bg-gray-200">Выйти</a>
    </div>

    <h2 class="text-xl font-extrabold text-gray-800 mt-8 mb-4">Мои рецепты</h2>
    <?php if ($myRecipes): ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <?php foreach ($myRecipes as $recipe): include __DIR__ . '/../components/recipe-card.php'; endforeach; ?>
    </div>
    <?php else: ?>
        <div class="text-center py-10 text-gray-400 bg-white rounded-2xl">
            У вас пока нет рецептов. <a href="<?= url('create-recipe') ?>" class="text-amber-600 font-semibold">Создать первый →</a>
        </div>
    <?php endif; ?>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>