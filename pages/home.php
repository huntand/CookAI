<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'Главная';

// Определяем текущий сезон
$month  = (int) date('n');
$season = match (true) {
    in_array($month, [12,1,2])  => 'Зима',
    in_array($month, [3,4,5])   => 'Весна',
    in_array($month, [6,7,8])   => 'Лето',
    default                     => 'Осень',
};

$trending = db_all('SELECT * FROM recipes ORDER BY likes_count DESC LIMIT 8');
$seasonal = db_all(
    'SELECT * FROM recipes WHERE JSON_CONTAINS(season, ?) ORDER BY created_at DESC LIMIT 8',
    [json_encode($season, JSON_UNESCAPED_UNICODE)]
);
if (!$seasonal) {
    $seasonal = db_all('SELECT * FROM recipes ORDER BY created_at DESC LIMIT 8');
}

require_once __DIR__ . '/../includes/header.php';
?>

<!-- HERO -->
<section class="hero-gradient">
    <div class="max-w-5xl mx-auto px-4 py-16 sm:py-24 text-center">
        <h1 data-aos="fade-up" class="text-4xl sm:text-6xl font-extrabold text-gray-800 leading-tight">
            Готовьте <span class="text-amber-500">умнее</span><br class="hidden sm:block">
            с помощью <span class="bg-gradient-to-r from-violet-500 to-purple-500 bg-clip-text text-transparent">AI</span>
        </h1>
        <p data-aos="fade-up" data-aos-delay="100" class="mt-5 text-lg text-gray-600 max-w-2xl mx-auto">
            Генерируйте рецепты, узнавайте что приготовить из имеющихся продуктов и получайте советы шефа — всё на русском.
        </p>

        <form action="<?= url('search') ?>" method="get" data-aos="fade-up" data-aos-delay="200"
              class="mt-8 max-w-xl mx-auto flex gap-2 bg-white rounded-2xl shadow-md p-2">
            <input type="text" name="q" placeholder="Найти рецепт, ингредиент или кухню…"
                   class="flex-1 px-4 py-3 rounded-xl outline-none text-gray-700">
            <button class="px-5 py-3 rounded-xl bg-gradient-to-r from-amber-300 to-orange-300 text-amber-900 font-bold hover:shadow-md transition">
                🔍 <?= t('search') ?>
            </button>
        </form>

        <div data-aos="fade-up" data-aos-delay="300" class="mt-6 flex flex-wrap justify-center gap-3">
            <a href="<?= url('ai-generator') ?>" class="px-4 py-2 rounded-xl bg-violet-100 text-violet-700 text-sm font-semibold hover:bg-violet-200 transition">✨ Сгенерировать рецепт</a>
            <a href="<?= url('what-to-cook') ?>" class="px-4 py-2 rounded-xl bg-emerald-100 text-emerald-700 text-sm font-semibold hover:bg-emerald-200 transition">🥕 Что приготовить?</a>
            <a href="<?= url('advisor') ?>" class="px-4 py-2 rounded-xl bg-amber-100 text-amber-700 text-sm font-semibold hover:bg-amber-200 transition">🧑‍🍳 Советник</a>
        </div>
    </div>
</section>

<!-- ТРЕНДЫ -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 py-12">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-800">🔥 <?= t('trending') ?></h2>
        <a href="<?= url('search') ?>" class="text-sm font-semibold text-amber-600 hover:underline">Все рецепты →</a>
    </div>
    <?php if ($trending): ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <?php foreach ($trending as $recipe): include __DIR__ . '/../components/recipe-card.php'; endforeach; ?>
        </div>
    <?php else: ?>
        <p class="text-gray-400"><?= t('no_recipes') ?></p>
    <?php endif; ?>
</section>

<!-- СЕЗОННЫЕ -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 py-12">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-800">🍂 <?= t('seasonal') ?>: <?= e($season) ?></h2>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <?php foreach ($seasonal as $recipe): include __DIR__ . '/../components/recipe-card.php'; endforeach; ?>
    </div>
</section>

<!-- CTA AI -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 py-12">
    <div data-aos="zoom-in" class="rounded-3xl bg-gradient-to-r from-violet-200 to-purple-200 p-8 sm:p-12 text-center">
        <h2 class="text-2xl sm:text-3xl font-extrabold text-violet-900">Не знаете, что приготовить?</h2>
        <p class="mt-3 text-violet-800/80 max-w-xl mx-auto">Введите продукты из холодильника — AI подберёт рецепт за секунды.</p>
        <a href="<?= url('what-to-cook') ?>" class="inline-block mt-6 px-6 py-3 rounded-xl bg-white text-violet-700 font-bold hover:shadow-lg transition">Попробовать →</a>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>