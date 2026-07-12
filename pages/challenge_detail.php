<?php
require_once __DIR__ . '/../config/config.php';
$id = (int)($_GET['id'] ?? 0);
$ch = db_one('SELECT * FROM challenges WHERE id=?', [$id]);
if (!$ch) { http_response_code(404); die('Челлендж не найден'); }
$pageTitle = $ch['title'];
require_once __DIR__ . '/../includes/header.php';
?>
<section class="max-w-3xl mx-auto px-4 sm:px-6 py-8" x-data="{ joined:false, joining:false }">
    <div class="h-48 rounded-3xl bg-gradient-to-br from-violet-100 to-purple-100 overflow-hidden mb-6" data-aos="fade-up">
        <?php if ($ch['cover_image']): ?><img src="<?= e($ch['cover_image']) ?>" class="w-full h-full object-cover"><?php endif; ?>
    </div>
    <div class="bg-white rounded-3xl shadow-md p-6 sm:p-8" data-aos="fade-up" data-aos-delay="100">
        <div class="flex flex-wrap gap-2 mb-3">
            <span class="px-3 py-1 rounded-full text-xs font-bold bg-violet-100 text-violet-700"><?= e($ch['difficulty'] ?? '') ?></span>
            <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-700">🎖 +<?= (int)$ch['reward_points'] ?> очков</span>
        </div>
        <h1 class="text-2xl font-extrabold text-gray-800"><?= e($ch['title']) ?></h1>
        <p class="text-gray-600 mt-2"><?= e($ch['description'] ?? '') ?></p>
        <?php if ($ch['requirements']): ?>
            <div class="mt-4 bg-gray-50 rounded-xl p-4">
                <h3 class="font-bold text-gray-700 text-sm mb-1">📋 Условия</h3>
                <p class="text-sm text-gray-600"><?= e($ch['requirements']) ?></p>
            </div>
        <?php endif; ?>
        <div class="flex items-center gap-4 mt-4 text-sm text-gray-400">
            <span>👤 <?= (int)$ch['participants_count'] ?> участников</span>
            <?php if ($ch['end_date']): ?><span>📅 до <?= date('d.m.Y', strtotime($ch['end_date'])) ?></span><?php endif; ?>
        </div>
        <?php if (is_logged_in()): ?>
        <button @click="joining=true; CookAPI.post('/api/challenges.php?join=<?= $id ?>',{}).then(()=>{joined=true;toast('Вы участвуете!','success')}).finally(()=>joining=false)"
                :disabled="joined || joining"
                class="mt-6 w-full py-3.5 rounded-xl bg-gradient-to-r from-violet-300 to-purple-300 text-violet-900 font-bold hover:shadow-md transition disabled:opacity-60">
            <span x-show="!joined">🚀 Участвовать</span>
            <span x-show="joined" style="display:none">✓ Вы участвуете</span>
        </button>
        <?php else: ?>
            <a href="<?= url('login') ?>" class="mt-6 block text-center py-3.5 rounded-xl bg-violet-100 text-violet-700 font-bold">Войдите, чтобы участвовать</a>
        <?php endif; ?>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>