<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'Что приготовить?';
require_once __DIR__ . '/../includes/header.php';
?>
<section class="max-w-4xl mx-auto px-4 sm:px-6 py-10" x-data="whatToCook()">
    <div data-aos="fade-up" class="text-center mb-8">
        <span class="inline-block px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 text-xs font-bold mb-3">🥕 Умный подбор</span>
        <h1 class="text-3xl sm:text-4xl font-extrabold text-gray-800">Что приготовить?</h1>
        <p class="mt-2 text-gray-500">Добавьте продукты из холодильника — AI предложит блюда.</p>
    </div>

    <!-- Ввод ингредиентов -->
    <div data-aos="fade-up" data-aos-delay="100" class="bg-white rounded-3xl shadow-md p-6 sm:p-8">
        <label class="block text-sm font-semibold text-gray-600 mb-2">Ваши продукты</label>
        <div class="flex gap-2 mb-3">
            <input type="text" x-model="ingredientInput" @keydown.enter.prevent="addIngredient()"
                   placeholder="Введите продукт и нажмите Enter"
                   class="flex-1 px-4 py-3 rounded-xl border border-gray-200 outline-none focus:border-emerald-300">
            <button @click="addIngredient()" class="px-4 py-3 rounded-xl bg-emerald-100 text-emerald-700 font-bold hover:bg-emerald-200 transition">+</button>
        </div>

        <div class="flex flex-wrap gap-2 mb-4" x-show="ingredients.length" style="display:none">
            <template x-for="(ing, i) in ingredients" :key="i">
                <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full bg-emerald-50 text-emerald-700 text-sm">
                    <span x-text="ing"></span>
                    <button @click="ingredients.splice(i,1)" class="text-emerald-400 hover:text-emerald-600">×</button>
                </span>
            </template>
        </div>

        <!-- Быстрое добавление -->
        <div class="flex flex-wrap gap-2 mb-5">
            <template x-for="p in popular" :key="p">
                <button @click="quickAdd(p)" class="px-3 py-1.5 rounded-full bg-gray-50 text-gray-600 text-xs hover:bg-emerald-50 hover:text-emerald-700 transition" x-text="'+ ' + p"></button>
            </template>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5">
            <div>
                <label class="block text-sm font-semibold text-gray-600 mb-1">Тип питания</label>
                <select x-model="diet" class="w-full px-4 py-3 rounded-xl border border-gray-200 outline-none focus:border-emerald-300">
                    <option value="">Любое</option>
                    <option>Вегетарианская</option>
                    <option>Веганская</option>
                    <option>Кето</option>
                    <option>Низкокалорийная</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-600 mb-1">Есть время (мин)</label>
                <input type="number" x-model="time" min="5" max="240" placeholder="30"
                       class="w-full px-4 py-3 rounded-xl border border-gray-200 outline-none focus:border-emerald-300">
            </div>
        </div>

        <button @click="find()" :disabled="loading || !ingredients.length"
                class="w-full py-3.5 rounded-xl bg-gradient-to-r from-emerald-300 to-teal-300 text-emerald-900 font-bold hover:shadow-md transition disabled:opacity-60">
            <span x-show="!loading">🍳 Подобрать блюда</span>
            <span x-show="loading" style="display:none">⏳ Думаем…</span>
        </button>
    </div>

    <div x-show="error" x-transition style="display:none" class="mt-6 px-4 py-3 rounded-xl bg-rose-100 text-rose-700 text-sm" x-text="error"></div>

    <!-- Результаты -->
    <div x-show="dishes.length" x-transition style="display:none" class="mt-8 space-y-4">
        <h2 class="text-xl font-extrabold text-gray-800">Вам подойдёт:</h2>
        <template x-for="(d, i) in dishes" :key="i">
            <div class="bg-white rounded-2xl shadow-md p-5">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h3 class="font-bold text-gray-800 text-lg" x-text="d.title"></h3>
                        <p class="text-sm text-gray-500 mt-1" x-text="d.description"></p>
                    </div>
                    <span class="shrink-0 px-2 py-1 rounded-lg text-xs font-bold bg-amber-50 text-amber-600" x-text="'⏱ ' + d.time + ' мин'"></span>
                </div>
                <div class="mt-3 flex flex-wrap gap-2">
                    <template x-for="ing in d.have" :key="ing">
                        <span class="px-2 py-0.5 rounded-md text-xs bg-emerald-50 text-emerald-700" x-text="'✓ ' + ing"></span>
                    </template>
                    <template x-for="ing in d.need" :key="ing">
                        <span class="px-2 py-0.5 rounded-md text-xs bg-rose-50 text-rose-500" x-text="'+ ' + ing"></span>
                    </template>
                </div>
                <button @click="generateFull(d.title)"
                        class="mt-4 text-sm font-semibold text-violet-600 hover:underline">✨ Сгенерировать полный рецепт →</button>
            </div>
        </template>
    </div>
</section>

<script src="<?= url('assets/js/ai/what-to-cook.js') ?>"></script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>