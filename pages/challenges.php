<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'Челленджи';
require_once __DIR__ . '/../includes/header.php';

$challenges = db_all('SELECT * FROM challenges WHERE ends_at >= CURDATE() ORDER BY ends_at ASC');

$diffMap = [
    'easy'   => ['Лёгкий',  'bg-emerald-100 text-emerald-700'],
    'medium' => ['Средний', 'bg-amber-100 text-amber-700'],
    'hard'   => ['Сложный', 'bg-rose-100 text-rose-600'],
];
?>
<section class="max-w-5xl mx-auto px-4 sm:px-6 py-10">
    <div class="mb-6" data-aos="fade-up">
        <h1 class="text-3xl font-extrabold text-gray-800">🏆 Челленджи</h1>
        <p class="text-gray-500 mt-1">Участвуйте, готовьте и зарабатывайте баллы.</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
        <?php foreach ($challenges as $i => $ch):
            $d = $diffMap[$ch['difficulty']] ?? $diffMap['easy'];
            $daysLeft = max(0, (int)ceil((strtotime($ch['ends_at']) - time()) / 86400));
        ?>
            <div class="bg-white rounded-3xl shadow-md p-6 hover:shadow-lg transition-all"
                 data-aos="fade-up" data-aos-delay="<?= ($i % 2) * 60 ?>">
                <div class="flex items-start justify-between gap-3">
                    <h3 class="font-extrabold text-gray-800 text-lg"><?= e($ch['title']) ?></h3>
                    <span class="shrink-0 px-2.5 py-1 rounded-lg text-xs font-bold <?= $d[1] ?>"><?= $d[0] ?></span>
                </div>
                <p class="text-gray-500 text-sm mt-2"><?= e($ch['description']) ?></p>

                <div class="mt-4 flex flex-wrap gap-3 text-xs">
                    <span class="inline-flex items-center gap-1 text-violet-600 font-bold">🎯 <?= e($ch['goal']) ?></span>
                    <span class="inline-flex items-center gap-1 text-amber-600 font-bold">⭐ <?= (int)$ch['reward_points'] ?> баллов</span>
                </div>

                <div class="mt-4 flex items-center justify-between">
                    <span class="text-xs text-gray-400">
                        <?= (int)$ch['participants'] ?> участников · осталось <?= $daysLeft ?> дн.
                    </span>
                    <button class="px-4 py-2 rounded-xl bg-gradient-to-r from-amber-300 to-orange-300 text-amber-900 font-bold text-sm hover:shadow-md transition">
                        Участвовать
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if (!$challenges): ?>
        <div class="text-center py-16 text-gray-400" data-aos="fade-up">
            Активных челленджей нет. Загрузите демо-данные: <code>php cron/seed_demo.php</code>
        </div>
    <?php endif; ?>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>