<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'Сообщества';
require_once __DIR__ . '/../includes/header.php';

$communities = db_all('SELECT * FROM communities ORDER BY members_count DESC');
?>
<section class="max-w-5xl mx-auto px-4 sm:px-6 py-10">
    <div class="flex items-center justify-between mb-6" data-aos="fade-up">
        <div>
            <h1 class="text-3xl font-extrabold text-gray-800">👥 Сообщества</h1>
            <p class="text-gray-500 mt-1">Присоединяйтесь к единомышленникам по интересам.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        <?php foreach ($communities as $i => $c): ?>
            <a href="<?= url('community/' . e($c['slug'])) ?>"
               class="bg-white rounded-3xl shadow-md p-6 hover:shadow-lg hover:-translate-y-1 transition-all"
               data-aos="fade-up" data-aos-delay="<?= ($i % 3) * 60 ?>">
                <div class="text-4xl mb-3"><?= e($c['cover_emoji'] ?: '🍽️') ?></div>
                <h3 class="font-extrabold text-gray-800 text-lg"><?= e($c['name']) ?></h3>
                <p class="text-gray-500 text-sm mt-1 line-clamp-2"><?= e($c['description']) ?></p>
                <div class="mt-4 flex items-center gap-2 text-xs text-gray-400">
                    <span class="font-bold text-amber-600"><?= number_format((int)$c['members_count'], 0, '', ' ') ?></span> участников
                </div>
            </a>
        <?php endforeach; ?>
    </div>

    <?php if (!$communities): ?>
        <div class="text-center py-16 text-gray-400" data-aos="fade-up">
            Сообществ пока нет. Загрузите демо-данные: <code>php cron/seed_demo.php</code>
        </div>
    <?php endif; ?>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>