<?php
require_once __DIR__ . '/../config/config.php';
require_login();
$pageTitle = 'Создать рецепт';
require_once __DIR__ . '/../includes/header.php';
?>
<section class="max-w-3xl mx-auto px-4 sm:px-6 py-10" x-data="createRecipe()">
    <h1 class="text-3xl font-extrabold text-gray-800 mb-6" data-aos="fade-up">Создать рецепт</h1>

    <div class="bg-white rounded-3xl shadow-md p-6 sm:p-8 space-y-5" data-aos="fade-up" data-aos-delay="100">
        <div>
            <label class="block text-sm font-semibold text-gray-600 mb-1">Название *</label>
            <input type="text" x-model="r.title" class="w-full px-4 py-3 rounded-xl border border-gray-200 outline-none focus:border-amber-300">
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-600 mb-1">Описание</label>
            <textarea x-model="r.description" rows="2" class="w-full px-4 py-3 rounded-xl border border-gray-200 outline-none focus:border-amber-300 resize-none"></textarea>
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-600 mb-1">Фото (URL)</label>
            <input type="url" x-model="r.image_url" placeholder="https://images.unsplash.com/…" class="w-full px-4 py-3 rounded-xl border border-gray-200 outline-none focus:border-amber-300">
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div><label class="text-sm text-gray-600">Кухня</label>
                <select x-model="r.cuisine" class="w-full px-3 py-2.5 rounded-xl border border-gray-200 outline-none text-sm">
                    <template x-for="c in cuisines" :key="c"><option x-text="c"></option></template>
                </select></div>
            <div><label class="text-sm text-gray-600">Сложность</label>
                <select x-model="r.difficulty" class="w-full px-3 py-2.5 rounded-xl border border-gray-200 outline-none text-sm">
                    <option>Легко</option><option>Средне</option><option>Сложно</option><option>Мастер-класс</option>
                </select></div>
            <div><label class="text-sm text-gray-600">Подготовка (мин)</label>
                <input type="number" x-model="r.prep_time" class="w-full px-3 py-2.5 rounded-xl border border-gray-200 outline-none text-sm"></div>
            <div><label class="text-sm text-gray-600">Готовка (мин)</label>
                <input type="number" x-model="r.cook_time" class="w-full px-3 py-2.5 rounded-xl border border-gray-200 outline-none text-sm"></div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div><label class="text-sm text-gray-600">Порций</label>
                <input type="number" x-model="r.servings" class="w-full px-3 py-2.5 rounded-xl border border-gray-200 outline-none text-sm"></div>
            <div><label class="text-sm text-gray-600">Ккал</label>
                <input type="number" x-model="r.calories" class="w-full px-3 py-2.5 rounded-xl border border-gray-200 outline-none text-sm"></div>
            <div><label class="text-sm text-gray-600">Белки</label>
                <input type="number" x-model="r.proteins" class="w-full px-3 py-2.5 rounded-xl border border-gray-200 outline-none text-sm"></div>
            <div><label class="text-sm text-gray-600">Жиры/Угл</label>
                <div class="flex gap-1">
                    <input type="number" x-model="r.fats" placeholder="Ж" class="w-1/2 px-2 py-2.5 rounded-xl border border-gray-200 outline-none text-sm">
                    <input type="number" x-model="r.carbs" placeholder="У" class="w-1/2 px-2 py-2.5 rounded-xl border border-gray-200 outline-none text-sm">
                </div></div>
        </div>

        <!-- Ингредиенты -->
        <div>
            <label class="block text-sm font-semibold text-gray-600 mb-2">Ингредиенты</label>
            <template x-for="(ing, i) in r.ingredients" :key="i">
                <div class="flex gap-2 mb-2">
                    <input x-model="ing.name" placeholder="Название" class="flex-1 px-3 py-2 rounded-lg border border-gray-200 outline-none text-sm">
                    <input x-model="ing.amount" placeholder="Кол-во" class="w-24 px-3 py-2 rounded-lg border border-gray-200 outline-none text-sm">
                    <input x-model="ing.unit" placeholder="Ед." class="w-20 px-3 py-2 rounded-lg border border-gray-200 outline-none text-sm">
                    <button @click="r.ingredients.splice(i,1)" class="px-2 text-rose-300 hover:text-rose-500">×</button>
                </div>
            </template>
            <button @click="r.ingredients.push({name:'',amount:'',unit:''})" class="text-sm text-amber-600 font-semibold">+ Добавить ингредиент</button>
        </div>

        <!-- Шаги -->
        <div>
            <label class="block text-sm font-semibold text-gray-600 mb-2">Шаги</label>
            <template x-for="(st, i) in r.steps" :key="i">
                <div class="flex gap-2 mb-2 items-start">
                    <span class="mt-2 text-gray-400 font-bold" x-text="(i+1)+'.'"></span>
                    <textarea x-model="st.instruction" rows="2" placeholder="Что делать" class="flex-1 px-3 py-2 rounded-lg border border-gray-200 outline-none text-sm resize-none"></textarea>
                    <input type="number" x-model="st.timer_minutes" placeholder="⏱" class="w-16 px-2 py-2 rounded-lg border border-gray-200 outline-none text-sm">
                    <button @click="r.steps.splice(i,1)" class="px-2 text-rose-300 hover:text-rose-500 mt-2">×</button>
                </div>
            </template>
            <button @click="r.steps.push({order:r.steps.length+1,instruction:'',timer_minutes:0,tip:''})" class="text-sm text-amber-600 font-semibold">+ Добавить шаг</button>
        </div>

        <button @click="save()" :disabled="saving"
                class="w-full py-3.5 rounded-xl bg-gradient-to-r from-amber-300 to-orange-300 text-amber-900 font-bold hover:shadow-md transition disabled:opacity-60">
            <span x-show="!saving">💾 Опубликовать рецепт</span>
            <span x-show="saving" style="display:none">Сохраняем…</span>
        </button>
    </div>
</section>
<script src="<?= url('assets/js/recipe/create-recipe.js') ?>"></script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>