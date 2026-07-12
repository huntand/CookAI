<?php
require_once __DIR__ . '/../config/config.php';
require_login();
$pageTitle = 'Чаты';
require_once __DIR__ . '/../includes/header.php';
?>
<section class="max-w-4xl mx-auto px-4 sm:px-6 py-8">
    <h1 class="text-3xl font-extrabold text-gray-800 mb-6" data-aos="fade-up">💬 Чаты</h1>
    <div class="bg-white rounded-3xl shadow-md p-8 text-center text-gray-400" data-aos="fade-up">
        <div class="text-5xl mb-3">💬</div>
        Личные сообщения появятся в следующем обновлении.<br>
        <a href="<?= url('friends') ?>" class="text-amber-600 font-semibold mt-2 inline-block">Добавить друзей →</a>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>