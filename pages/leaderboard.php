<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'Таблица лидеров';
$top = db_all(
    'SELECT s.total_points, s.level, s.recipes_created, u.name, u.avatar_url
     FROM user_stats s JOIN users u ON u.id = s.user_id
     ORDER BY s.total_points DESC LIMIT 50'
);
require_once __DIR__ . '/../includes/header.php';
?>
<section class="max-w-3xl mx-auto px-4 sm:px-6 py-8">
    <h1 class="text-3xl font-extrabold text-gray-800 mb-6" data-aos="fade-up">📊 Таблица лидеров</h1>

    <div class="bg-white rounded-3xl shadow-md overflow-hidden" data-aos="fade-up" data-aos-delay="100">
        <?php foreach ($top as $i => $u):
            $medal = ['🥇','🥈','🥉'][$i] ?? ('#' . ($i + 1)); ?>
            <div class="flex items-center gap-4 px-5 py-4 <?= $i < 3 ? 'bg-amber-50/50' : '' ?> border-b border-gray-50">
                <span class="w-8 text-center font-extrabold <?= $i < 3 ? 'text-xl' : 'text-gray-400' ?>"><?= $medal ?></span>
                <img src="<?= e($u['avatar_url'] ?: 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=80') ?>" class="w-10 h-10 rounded-full object-cover ring-2 ring-amber-100">
                <div class="flex-1">
                    <div class="font-bold text-gray-800"><?= e($u['name']) ?></div>
                    <div class="text-xs text-gray-400">Уровень <?= (int)$u['level'] ?> · <?= (int)$u['recipes_created'] ?> рецептов</div>
                </div>
                <div class="text-right">
                    <div class="font-extrabold text-amber-600"><?= (int)$u['total_points'] ?></div>
                    <div class="text-xs text-gray-400">очков</div>
                </div>
            </div>
        <?php endforeach; ?>
        <?php if (!$top): ?><div class="text-center py-16 text-gray-400">Рейтинг пуст</div><?php endif; ?>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>