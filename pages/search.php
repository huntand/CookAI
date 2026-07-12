<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'Поиск рецептов';
require_once __DIR__ . '/../includes/header.php';
?>
<section class="max-w-7xl mx-auto px-4 sm:px-6 py-8" x-data="searchPage()" x-init="init()">
    <h1 class="text-3xl font-extrabold text-gray-800 mb-6" data-aos="fade-up">🔍 Поиск рецептов</h1>

    <div class="flex flex-col lg:flex-row gap-3 mb-6" data-aos="fade-up" data-aos-delay="100">
        <input type="text" x-model="q" @keydown.enter="search()" placeholder="Название, ингредиент…"
               class="flex-1 px-4 py-3 rounded-xl border border-gray-200 outline-none focus:border-amber-300">
        <select x-model="cuisine" @change="search()" class="px-4 py-3 rounded-xl border border-gray-200 outline-none">
            <option value="">Все кухни</option>
            <template x-for="c in cuisines" :key="c"><option x-text="c"></option></template>
        </select>
        <button @click="search()" class="px-6 py-3 rounded-xl bg-gradient-to-r from-amber-300 to-orange-300 text-amber-900 font-bold hover:shadow-md transition">Найти</button>
    </div>

    <div x-show="loading" style="display:none" class="text-center py-10 text-gray-400">Загрузка…</div>

    <div x-show="!loading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <template x-for="rec in results" :key="rec.id">
            <a :href="'/recipe/' + rec.id" class="recipe-card bg-white rounded-2xl shadow-md hover:shadow-lg overflow-hidden block">
                <div class="aspect-[4/3] overflow-hidden">
                    <img :src="rec.image_url || 'https://images.unsplash.com/photo-1495521821757-a1efb6729352?w=600'" class="w-full h-full object-cover">
                </div>
                <div class="p-4">
                    <div class="text-xs text-gray-400 mb-1"><span x-text="rec.cuisine"></span></div>
                    <h3 class="font-bold text-gray-800 clamp-2" x-text="rec.title"></h3>
                    <p class="text-sm text-gray-500 clamp-2 mt-1" x-text="rec.description"></p>
                </div>
            </a>
        </template>
    </div>
    <p x-show="!loading && !results.length" style="display:none" class="text-center text-gray-400 py-10">Рецепты не найдены</p>
</section>
<script src="<?= url('assets/js/search/search.js') ?>"></script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>