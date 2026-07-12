<?php
require_once __DIR__ . '/../config/config.php';
$id = (int)($_GET['id'] ?? 0);
$c  = db_one('SELECT * FROM communities WHERE id=?', [$id]);
if (!$c) { http_response_code(404); die('Сообщество не найдено'); }
$c['tags'] = json_field($c['tags']);
$pageTitle = $c['name'];
require_once __DIR__ . '/../includes/header.php';
?>
<section x-data="communityDetail(<?= $id ?>)" x-init="load()">
    <!-- Cover -->
    <div class="h-48 sm:h-64 bg-gradient-to-br from-amber-100 to-orange-100 relative">
        <?php if ($c['cover_image']): ?><img src="<?= e($c['cover_image']) ?>" class="w-full h-full object-cover"><?php endif; ?>
        <div class="absolute inset-0 bg-black/20"></div>
    </div>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 -mt-12 relative">
        <div class="bg-white rounded-3xl shadow-md p-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <div class="text-xs text-amber-600 font-semibold"><?= e($c['category'] ?? 'Общее') ?></div>
                    <h1 class="text-2xl font-extrabold text-gray-800"><?= e($c['name']) ?></h1>
                    <p class="text-gray-500 mt-1"><?= e($c['description'] ?? '') ?></p>
                    <div class="flex gap-4 mt-3 text-sm text-gray-400">
                        <span>👤 <?= (int)$c['members_count'] ?> участников</span>
                    </div>
                </div>
                <?php if (is_logged_in()): ?>
                    <button @click="join()" :disabled="joined"
                            class="shrink-0 px-5 py-2.5 rounded-xl bg-gradient-to-r from-amber-300 to-orange-300 text-amber-900 font-bold hover:shadow-md transition disabled:opacity-60">
                        <span x-show="!joined">+ Вступить</span>
                        <span x-show="joined" style="display:none">✓ Вы участник</span>
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <?php if (is_logged_in()): ?>
        <div class="bg-white rounded-2xl shadow-sm p-4 mt-5">
            <textarea x-model="newPost" rows="2" placeholder="Поделитесь чем-нибудь…"
                      class="w-full px-4 py-3 rounded-xl border border-gray-200 outline-none focus:border-amber-300 resize-none text-sm"></textarea>
            <button @click="post()" class="mt-2 px-4 py-2 rounded-xl bg-amber-100 text-amber-700 font-bold text-sm">Опубликовать</button>
        </div>
        <?php endif; ?>

        <div class="mt-6 space-y-4 pb-10">
            <template x-for="p in posts" :key="p.id">
                <div class="bg-white rounded-2xl shadow-sm p-5">
                    <div class="font-bold text-gray-700 mb-1" x-text="p.author_name"></div>
                    <div class="text-gray-600 text-sm whitespace-pre-wrap" x-text="p.content"></div>
                </div>
            </template>
            <p x-show="!posts.length" style="display:none" class="text-center text-gray-400 py-8">Постов пока нет</p>
        </div>
    </div>
</section>
<script src="<?= url('assets/js/social/community.js') ?>"></script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>