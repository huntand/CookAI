<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'Вдохновение';
require_once __DIR__ . '/../includes/header.php';
?>
<section class="max-w-5xl mx-auto px-4 sm:px-6 py-10">
    <h1 class="text-3xl font-extrabold text-gray-800 mb-2" data-aos="fade-up">💡 Кулинарное вдохновение</h1>
    <p class="text-gray-500 mb-8" data-aos="fade-up" data-aos-delay="50">Экспериментируйте с AI-инструментами CookAI.</p>

    <!-- Fusion -->
    <div class="bg-white rounded-3xl shadow-md p-6 sm:p-8 mb-8" data-aos="fade-up" x-data="fusionGenerator()">
        <h2 class="text-xl font-extrabold text-gray-800 mb-1">🌏 Fusion-генератор</h2>
        <p class="text-sm text-gray-500 mb-4">Объедините две кухни в одно блюдо.</p>
        <div class="flex flex-col sm:flex-row gap-3 items-center">
            <select x-model="a" class="flex-1 px-4 py-3 rounded-xl border border-gray-200 outline-none"><template x-for="c in cuisines" :key="c"><option x-text="c"></option></template></select>
            <span class="text-2xl">✕</span>
            <select x-model="b" class="flex-1 px-4 py-3 rounded-xl border border-gray-200 outline-none"><template x-for="c in cuisines" :key="c"><option x-text="c"></option></template></select>
            <button @click="generate()" :disabled="loading" class="px-6 py-3 rounded-xl bg-gradient-to-r from-rose-300 to-pink-300 text-rose-900 font-bold disabled:opacity-60"><span x-show="!loading">Создать</span><span x-show="loading" style="display:none">…</span></button>
        </div>
        <div x-show="recipe" style="display:none" class="mt-5 p-5 rounded-2xl bg-rose-50">
            <h3 class="font-extrabold text-gray-800" x-text="recipe?.title"></h3>
            <p class="text-sm text-gray-600 mt-1" x-text="recipe?.description"></p>
            <p class="text-xs text-rose-600 mt-2" x-text="'💡 ' + recipe?.concept"></p>
        </div>
    </div>

    <!-- Variator -->
    <div class="bg-white rounded-3xl shadow-md p-6 sm:p-8" data-aos="fade-up" x-data="recipeVariator()">
        <h2 class="text-xl font-extrabold text-gray-800 mb-1">🔄 Вариатор рецептов</h2>
        <p class="text-sm text-gray-500 mb-4">Адаптируйте любимое блюдо под нужный стиль.</p>
        <div class="flex flex-col sm:flex-row gap-3">
            <input type="text" x-model="dish" placeholder="Название блюда" class="flex-1 px-4 py-3 rounded-xl border border-gray-200 outline-none">
            <select x-model="mode" class="px-4 py-3 rounded-xl border border-gray-200 outline-none"><template x-for="m in modes" :key="m"><option x-text="m"></option></template></select>
            <button @click="generate()" :disabled="loading" class="px-6 py-3 rounded-xl bg-gradient-to-r from-teal-300 to-emerald-300 text-emerald-900 font-bold disabled:opacity-60"><span x-show="!loading">Создать</span><span x-show="loading" style="display:none">…</span></button>
        </div>
        <div x-show="recipe" style="display:none" class="mt-5 p-5 rounded-2xl bg-emerald-50">
            <h3 class="font-extrabold text-gray-800" x-text="recipe?.title"></h3>
            <p class="text-sm text-gray-600 mt-1" x-text="recipe?.description"></p>
            <ul class="mt-2 text-xs text-emerald-700 space-y-1"><template x-for="ch in recipe?.changes" :key="ch"><li x-text="'✓ ' + ch"></li></template></ul>
        </div>
    </div>
</section>
<script src="<?= url('assets/js/ai/fusion-generator.js') ?>"></script>
<script src="<?= url('assets/js/ai/recipe-variator.js') ?>"></script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>