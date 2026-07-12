<?php
require_once __DIR__ . '/../config/config.php';
http_response_code(403);
$pageTitle = '403 — Доступ запрещён';
require_once __DIR__ . '/../includes/header.php';

$logged = is_logged_in();
?>
<section class="min-h-[60vh] flex items-center justify-center px-4 py-16">
    <div class="text-center max-w-md" data-aos="fade-up">
        <div class="text-8xl mb-4 select-none">🔒</div>
        <div class="text-7xl font-extrabold bg-gradient-to-r from-rose-400 to-pink-400 bg-clip-text text-transparent">403</div>
        <h1 class="text-2xl font-extrabold text-gray-800 mt-4">Доступ запрещён</h1>
        <p class="text-gray-500 mt-2">
            <?php if ($logged): ?>
                У вас недостаточно прав для просмотра этой страницы.
            <?php else: ?>
                Эта страница доступна только авторизованным пользователям. Пожалуйста, войдите.
            <?php endif; ?>
        </p>
        <div class="flex flex-col sm:flex-row gap-3 justify-center mt-8">
            <?php if (!$logged): ?>
                <a href="<?= url('login') ?>" class="px-6 py-3 rounded-xl bg-gradient-to-r from-violet-300 to-purple-300 text-violet-900 font-bold hover:shadow-md transition">
                    🔑 Войти
                </a>
            <?php endif; ?>
            <a href="<?= url('') ?>" class="px-6 py-3 rounded-xl bg-white border border-gray-200 text-gray-600 font-bold hover:bg-gray-50 transition">
                🏠 На главную
            </a>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>