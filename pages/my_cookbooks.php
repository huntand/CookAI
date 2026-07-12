<?php
require_once __DIR__ . '/../config/config.php';
require_login();
$uid = (int) current_user()['id'];
$saved = db_all(
    'SELECT r.* FROM saved_recipes s JOIN recipes r ON r.id = s.recipe_id WHERE s.user_id=? ORDER BY s.id DESC',
    [$uid]
);
$pageTitle = 'Мои книги';
require_once __DIR__ . '/../includes/header.php';
?>
<section class="max-w-7xl mx-auto px-4 sm:px-6 py-8">
    <h1 class="text-3xl font-extrabold text-gray-800 mb-6" data-aos="fade-up">📖 Сохранённые рецепты</h1>
    <?php if ($saved): ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <?php foreach ($saved as $recipe): include __DIR__ . '/../components/recipe-card.php'; endforeach; ?>
    </div>
    <?php else: ?>
        <div class="text-center py-16 text-gray-400 bg-white rounded-2xl">
            <div class="text-5xl mb-3">🔖</div>
            Нет сохранённых рецептов. <a href="<?= url('search') ?>" class="text-amber-600 font-semibold">Найти рецепты →</a>
        </div>
    <?php endif; ?>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>