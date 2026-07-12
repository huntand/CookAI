<?php
require_once __DIR__ . '/../config/config.php';
if (is_logged_in()) { header('Location: ' . url('')); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf'] ?? '')) {
        $error = 'Сессия истекла, попробуйте ещё раз.';
    } else {
        $res = auth_register($_POST['email'] ?? '', $_POST['password'] ?? '', $_POST['name'] ?? '');
        if ($res['ok']) { header('Location: ' . url('')); exit; }
        $error = $res['error'];
    }
}
$pageTitle = 'Регистрация';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="hero-gradient min-h-[80vh] flex items-center justify-center px-4 py-10">
    <div data-aos="fade-up" class="w-full max-w-md bg-white rounded-3xl shadow-lg p-8">
        <div class="text-center mb-6">
            <span class="text-4xl">✨</span>
            <h1 class="text-2xl font-extrabold text-amber-700 mt-2">Создать аккаунт</h1>
            <p class="text-sm text-gray-500">Присоединяйтесь к CookAI бесплатно</p>
        </div>

        <?php if ($error): ?>
            <div class="mb-4 px-4 py-3 rounded-xl bg-rose-100 text-rose-700 text-sm"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post" class="space-y-4">
            <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
            <div>
                <label class="block text-sm font-semibold text-gray-600 mb-1">Имя</label>
                <input type="text" name="name" required
                       class="w-full px-4 py-3 rounded-xl border border-gray-200 outline-none focus:border-amber-300"
                       placeholder="Как вас зовут?">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-600 mb-1">Email</label>
                <input type="email" name="email" required
                       class="w-full px-4 py-3 rounded-xl border border-gray-200 outline-none focus:border-amber-300">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-600 mb-1">Пароль</label>
                <input type="password" name="password" required minlength="6"
                       class="w-full px-4 py-3 rounded-xl border border-gray-200 outline-none focus:border-amber-300"
                       placeholder="Минимум 6 символов">
            </div>
            <button class="w-full py-3 rounded-xl bg-gradient-to-r from-amber-300 to-orange-300 text-amber-900 font-bold hover:shadow-md transition">
                Зарегистрироваться
            </button>
        </form>

        <p class="text-center text-sm text-gray-500 mt-5">
            Уже есть аккаунт?
            <a href="<?= url('login') ?>" class="text-amber-600 font-semibold hover:underline">Войти</a>
        </p>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>