<?php
// pages/friends.php
require_once __DIR__ . '/../config/config.php';
require_login();
$pageTitle = 'Друзья';
require_once __DIR__ . '/../includes/header.php';
?>
<section class="max-w-2xl mx-auto px-4 sm:px-6 py-8" x-data="friendsPage()" x-init="load()">
    <h1 class="text-3xl font-extrabold text-gray-800 mb-6" data-aos="fade-up">🤝 Друзья</h1>

    <div class="bg-white rounded-2xl shadow-md p-4 mb-6 flex gap-2" data-aos="fade-up" data-aos-delay="100">
        <input type="email" x-model="email" placeholder="Email друга…" class="flex-1 px-4 py-2.5 rounded-xl border border-gray-200 outline-none focus:border-amber-300 text-sm">
        <button @click="add()" class="px-5 py-2.5 rounded-xl bg-amber-100 text-amber-700 font-bold text-sm">+ Добавить</button>
    </div>

    <div class="space-y-2">
        <template x-for="f in list" :key="f.id">
            <div class="bg-white rounded-2xl shadow-sm p-4 flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-amber-100 flex items-center justify-center">👤</div>
                <div class="flex-1">
                    <div class="font-bold text-gray-700 text-sm" x-text="f.friend_name || f.friend_email"></div>
                    <div class="text-xs" :class="f.status==='accepted' ? 'text-emerald-500' : 'text-amber-500'" x-text="f.status==='accepted' ? 'В друзьях' : 'Ожидает'"></div>
                </div>
            </div>
        </template>
        <p x-show="!list.length" style="display:none" class="text-center text-gray-400 py-8">Пока нет друзей</p>
    </div>
</section>
<script src="<?= url('assets/js/social/friends.js') ?>"></script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>