<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'AI-генератор рецептов';
require_once __DIR__ . '/../includes/header.php';
?>
<section class="max-w-4xl mx-auto px-4 sm:px-6 py-10" x-data="aiGenerator()">
    <div data-aos="fade-up" class="text-center mb-8">
        <span class="inline-block px-3 py-1 rounded-full bg-violet-100 text-violet-700 text-xs font-bold mb-3">✨ YandexGPT</span>
        <h1 class="text-3xl sm:text-4xl font-extrabold text-gray-800">Генератор рецептов</h1>
        <p class="mt-2 text-gray-500">Опишите, что хотите приготовить — AI создаст полный рецепт с БЖУ.</p>
    </div>

    <!-- Форма -->
    <div data-aos="fade-up" data-aos-delay="100" class="bg-white rounded-3xl shadow-md p-6 sm:p-8">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
                <label class="block text-sm font-semibold text-gray-600 mb-1">Что приготовить?</label>
                <input type="text" x-model="form.dish" placeholder="Например: острый куриный суп с лапшой"
                       class="w-full px-4 py-3 rounded-xl border border-gray-200 outline-none focus:border-violet-300">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-600 mb-1">Кухня</label>
                <select x-model="form.cuisine" class="w-full px-4 py-3 rounded-xl border border-gray-200 outline-none focus:border-violet-300">
                    <option value="">Любая</option>
                    <template x-for="c in cuisines" :key="c">
                        <option :value="c" x-text="c"></option>
                    </template>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-600 mb-1">Тип питания</label>
                <select x-model="form.diet" class="w-full px-4 py-3 rounded-xl border border-gray-200 outline-none focus:border-violet-300">
                    <option value="">Обычное</option>
                    <option>Вегетарианская</option>
                    <option>Веганская</option>
                    <option>Кето</option>
                    <option>Без глютена</option>
                    <option>Низкокалорийная</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-600 mb-1">Сложность</label>
                <select x-model="form.difficulty" class="w-full px-4 py-3 rounded-xl border border-gray-200 outline-none focus:border-violet-300">
                    <option value="">Любая</option>
                    <option>Легко</option>
                    <option>Средне</option>
                    <option>Сложно</option>
                    <option>Мастер-класс</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-600 mb-1">Порций</label>
                <input type="number" x-model="form.servings" min="1" max="20" value="2"
                       class="w-full px-4 py-3 rounded-xl border border-gray-200 outline-none focus:border-violet-300">
            </div>
            <div class="sm:col-span-2">
                <label class="block text-sm font-semibold text-gray-600 mb-1">Исключить ингредиенты (необязательно)</label>
                <input type="text" x-model="form.exclude" placeholder="Например: грибы, кинза"
                       class="w-full px-4 py-3 rounded-xl border border-gray-200 outline-none focus:border-violet-300">
            </div>
        </div>

        <button @click="generate()" :disabled="loading"
                class="mt-6 w-full py-3.5 rounded-xl bg-gradient-to-r from-violet-300 to-purple-300 text-violet-900 font-bold hover:shadow-md transition disabled:opacity-60">
            <span x-show="!loading">✨ Сгенерировать рецепт</span>
            <span x-show="loading" style="display:none">⏳ Готовим рецепт…</span>
        </button>
    </div>

    <!-- Ошибка -->
    <div x-show="error" x-transition style="display:none"
         class="mt-6 px-4 py-3 rounded-xl bg-rose-100 text-rose-700 text-sm" x-text="error"></div>

    <!-- Результат -->
    <div x-show="recipe" x-transition style="display:none" class="mt-8 bg-white rounded-3xl shadow-md overflow-hidden">
        <div class="bg-gradient-to-r from-violet-100 to-purple-100 p-6">
            <div class="flex items-center gap-2 mb-2">
                <span class="px-2 py-1 rounded-lg text-xs font-bold bg-violet-200 text-violet-800">✨ AI</span>
                <span class="px-2 py-1 rounded-lg text-xs font-bold bg-white/70 text-gray-600" x-text="recipe?.cuisine"></span>
                <span class="px-2 py-1 rounded-lg text-xs font-bold bg-white/70 text-gray-600" x-text="recipe?.difficulty"></span>
            </div>
            <h2 class="text-2xl font-extrabold text-gray-800" x-text="recipe?.title"></h2>
            <p class="text-gray-600 mt-1" x-text="recipe?.description"></p>
            <div class="flex flex-wrap gap-4 mt-4 text-sm text-gray-600">
                <span>⏱ Подготовка: <b x-text="recipe?.prep_time"></b> мин</span>
                <span>🔥 Готовка: <b x-text="recipe?.cook_time"></b> мин</span>
                <span>🍽 Порций: <b x-text="recipe?.servings"></b></span>
            </div>
        </div>

        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Ингредиенты -->
            <div>
                <h3 class="font-bold text-gray-800 mb-3">🧺 Ингредиенты</h3>
                <ul class="space-y-2">
                    <template x-for="(ing, i) in recipe?.ingredients" :key="i">
                        <li class="flex items-center gap-2 text-sm text-gray-700 py-1.5 border-b border-gray-50">
                            <span class="text-violet-400">•</span>
                            <span x-text="ing.name"></span>
                            <span class="ml-auto text-gray-500" x-text="`${ing.amount} ${ing.unit || ''}`"></span>
                        </li>
                    </template>
                </ul>
            </div>
            <!-- КБЖУ -->
            <div>
                <h3 class="font-bold text-gray-800 mb-3">📊 На порцию</h3>
                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-amber-50 rounded-xl p-3 text-center">
                        <div class="text-2xl font-extrabold text-amber-600" x-text="recipe?.calories"></div>
                        <div class="text-xs text-gray-500">ккал</div>
                    </div>
                    <div class="bg-emerald-50 rounded-xl p-3 text-center">
                        <div class="text-2xl font-extrabold text-emerald-600" x-text="recipe?.proteins + ' г'"></div>
                        <div class="text-xs text-gray-500">белки</div>
                    </div>
                    <div class="bg-rose-50 rounded-xl p-3 text-center">
                        <div class="text-2xl font-extrabold text-rose-500" x-text="recipe?.fats + ' г'"></div>
                        <div class="text-xs text-gray-500">жиры</div>
                    </div>
                    <div class="bg-violet-50 rounded-xl p-3 text-center">
                        <div class="text-2xl font-extrabold text-violet-600" x-text="recipe?.carbs + ' г'"></div>
                        <div class="text-xs text-gray-500">углеводы</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Шаги -->
        <div class="px-6 pb-6">
            <h3 class="font-bold text-gray-800 mb-3">👩‍🍳 Приготовление</h3>
            <ol class="space-y-3">
                <template x-for="(step, i) in recipe?.steps" :key="i">
                    <li class="flex gap-3">
                        <span class="shrink-0 w-8 h-8 rounded-full bg-violet-100 text-violet-700 font-bold flex items-center justify-center text-sm" x-text="i + 1"></span>
                        <div>
                            <p class="text-gray-700 text-sm" x-text="step.instruction"></p>
                            <p x-show="step.tip" class="text-xs text-amber-600 mt-1" x-text="'💡 ' + step.tip"></p>
                            <p x-show="step.timer_minutes > 0" class="text-xs text-gray-400 mt-0.5" x-text="'⏱ ' + step.timer_minutes + ' мин'"></p>
                        </div>
                    </li>
                </template>
            </ol>

            <div class="flex flex-wrap gap-3 mt-6">
                <button @click="saveGenerated()" :disabled="saving"
                        class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-amber-300 to-orange-300 text-amber-900 font-bold hover:shadow-md transition disabled:opacity-60">
                    <span x-show="!saving">💾 Сохранить рецепт</span>
                    <span x-show="saving" style="display:none">Сохраняем…</span>
                </button>
                <button @click="generate()" class="px-5 py-2.5 rounded-xl bg-violet-100 text-violet-700 font-bold hover:bg-violet-200 transition">
                    🔄 Другой вариант
                </button>
            </div>
        </div>
    </div>
</section>

<script src="<?= url('assets/js/ai/ai_generator.js') ?>"></script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>