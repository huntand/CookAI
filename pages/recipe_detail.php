<?php
require_once __DIR__ . '/../config/config.php';

$id = (int)($_GET['id'] ?? 0);
$recipe = db_one('SELECT * FROM recipes WHERE id=?', [$id]);
if (!$recipe) { http_response_code(404); die('Рецепт не найден'); }
db_exec('UPDATE recipes SET views_count = views_count + 1 WHERE id=?', [$id]);

$ings   = json_field($recipe['ingredients']);
$steps  = json_field($recipe['steps']);
$tags   = json_field($recipe['tags']);
$diet   = json_field($recipe['diet_type']);
$total  = (int)$recipe['prep_time'] + (int)$recipe['cook_time'];
$hasVideo = !empty($recipe['video_url']);
?>
<!DOCTYPE html>
<html lang="ru" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($recipe['title']) ?> — <?= APP_NAME ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
    <style>body{font-family:'Nunito',sans-serif}</style>
</head>
<body class="bg-orange-50/40 text-gray-800">

<!-- Мини-навбар -->
<header class="fixed top-0 inset-x-0 z-50 backdrop-blur-md bg-white/70 shadow-sm">
    <div class="max-w-5xl mx-auto px-4 h-14 flex items-center justify-between">
        <a href="<?= url('') ?>" class="flex items-center gap-2 font-extrabold text-amber-700">🍳 CookAI</a>
        <div class="flex items-center gap-2">
            <button onclick="navigator.share ? navigator.share({title:document.title,url:location.href}) : navigator.clipboard.writeText(location.href).then(()=>toast('Ссылка скопирована','success'))"
                    class="p-2 rounded-lg hover:bg-amber-50" title="Поделиться">🔗</button>
            <button onclick="window.print()" class="p-2 rounded-lg hover:bg-amber-50" title="Печать">🖨</button>
            <button onclick="saveRecipe(<?= $id ?>, this)" class="p-2 rounded-lg hover:bg-rose-50" title="Сохранить">🤍</button>
        </div>
    </div>
</header>

<!-- HERO -->
<section class="relative h-[55vh] min-h-[360px] w-full overflow-hidden">
    <img src="<?= e($recipe['image_url'] ?: 'https://images.unsplash.com/photo-1495521821757-a1efb6729352?w=1200') ?>"
         class="absolute inset-0 w-full h-full object-cover" alt="<?= e($recipe['title']) ?>">
    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>
    <div class="absolute bottom-0 inset-x-0 p-6 sm:p-10 max-w-5xl mx-auto">
        <div class="flex flex-wrap gap-2 mb-3">
            <span class="px-3 py-1 rounded-full text-xs font-bold bg-white/90 text-gray-700"><?= e($recipe['cuisine']) ?></span>
            <span class="px-3 py-1 rounded-full text-xs font-bold <?= difficulty_color($recipe['difficulty']) ?>"><?= e($recipe['difficulty']) ?></span>
            <?php if ($recipe['is_ai_generated']): ?><span class="px-3 py-1 rounded-full text-xs font-bold bg-violet-200 text-violet-800">✨ AI</span><?php endif; ?>
        </div>
        <h1 class="text-3xl sm:text-5xl font-extrabold text-white leading-tight"><?= e($recipe['title']) ?></h1>
        <div class="flex flex-wrap gap-4 mt-4 text-white/90 text-sm font-semibold">
            <span>⏱ <?= format_time($total) ?></span>
            <span>🍽 <?= (int)$recipe['servings'] ?> <?= t('servings') ?></span>
            <span>🔥 <?= (int)$recipe['calories'] ?> ккал</span>
            <span>❤️ <?= (int)$recipe['likes_count'] ?></span>
        </div>
    </div>
</section>

<main class="max-w-5xl mx-auto px-4 sm:px-6 py-8"
      x-data="{ tab:'ingredients' }">

    <p class="text-gray-600 text-lg mb-6"><?= e($recipe['description']) ?></p>

    <!-- Табы -->
    <div class="flex gap-1 overflow-x-auto no-scrollbar border-b border-gray-200 mb-6">
        <?php
        $tabs = ['ingredients'=>'🧺 Ингредиенты','steps'=>'👩‍🍳 Шаги'];
        if ($hasVideo) $tabs['video'] = '🎬 Видео';
        $tabs['nutrition'] = '📊 Питание';
        $tabs['reviews']   = '⭐ Отзывы';
        foreach ($tabs as $key => $label): ?>
            <button @click="tab='<?= $key ?>'"
                    :class="tab==='<?= $key ?>' ? 'border-amber-400 text-amber-700' : 'border-transparent text-gray-400'"
                    class="px-4 py-3 border-b-2 font-semibold text-sm whitespace-nowrap transition"><?= $label ?></button>
        <?php endforeach; ?>
    </div>
    <!-- ВКЛАДКА: ИНГРЕДИЕНТЫ -->
    <div x-show="tab==='ingredients'" x-transition
         x-data="shoppingList(<?= e(json_encode($ings, JSON_UNESCAPED_UNICODE)) ?>)">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-extrabold text-gray-800">Ингредиенты</h2>
            <button @click="open=true"
                    class="px-4 py-2 rounded-xl bg-emerald-100 text-emerald-700 text-sm font-bold hover:bg-emerald-200 transition">
                🛒 Список покупок
            </button>
        </div>

        <ul class="bg-white rounded-2xl shadow-md divide-y divide-gray-50">
            <?php foreach ($ings as $ing): ?>
                <li class="flex items-center gap-3 px-5 py-3.5">
                    <span class="text-emerald-400">•</span>
                    <span class="text-gray-700"><?= e($ing['name'] ?? '') ?>
                        <?php if (!empty($ing['is_optional'])): ?>
                            <span class="text-xs text-gray-400">(по желанию)</span>
                        <?php endif; ?>
                    </span>
                    <span class="ml-auto font-semibold text-gray-500"><?= e(($ing['amount'] ?? '') . ' ' . ($ing['unit'] ?? '')) ?></span>
                </li>
            <?php endforeach; ?>
        </ul>

        <!-- Замена ингредиентов (AI) -->
        <div class="mt-6 bg-violet-50 rounded-2xl p-5" x-data="ingredientSubstitute('<?= e($recipe['title']) ?>')">
            <h3 class="font-bold text-violet-800 mb-3">✨ Не хватает ингредиента?</h3>
            <div class="flex flex-col sm:flex-row gap-2">
                <input type="text" x-model="ingredient" placeholder="Например: сливки"
                       class="flex-1 px-4 py-2.5 rounded-xl border border-violet-200 outline-none focus:border-violet-400 bg-white">
                <button @click="find()" :disabled="loading"
                        class="px-5 py-2.5 rounded-xl bg-violet-300 text-violet-900 font-bold hover:shadow-md transition disabled:opacity-60">
                    <span x-show="!loading">Найти замену</span>
                    <span x-show="loading" style="display:none">Ищем…</span>
                </button>
            </div>
            <p x-show="error" style="display:none" class="text-rose-500 text-sm mt-2" x-text="error"></p>
            <div x-show="results.length" style="display:none" class="mt-4 space-y-2">
                <template x-for="(s, i) in results" :key="i">
                    <div class="bg-white rounded-xl p-3 text-sm">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-gray-800" x-text="s.name"></span>
                            <span class="text-xs text-violet-600 font-semibold" x-text="s.ratio"></span>
                        </div>
                        <p class="text-gray-500 text-xs mt-1" x-text="s.note"></p>
                    </div>
                </template>
            </div>
        </div>

        <!-- МОДАЛ: список покупок -->
        <div x-show="open" x-transition style="display:none"
             class="fixed inset-0 z-[90] flex items-end sm:items-center justify-center bg-black/40 p-4"
             @click.self="open=false">
            <div class="bg-white rounded-3xl shadow-xl w-full max-w-md max-h-[85vh] flex flex-col">
                <div class="flex items-center justify-between p-5 border-b border-gray-100">
                    <h3 class="font-extrabold text-gray-800">🛒 Список покупок</h3>
                    <button @click="open=false" class="p-1 text-gray-400 hover:text-gray-600 text-xl">×</button>
                </div>

                <div class="flex-1 overflow-y-auto p-5 space-y-1">
                    <template x-for="item in items" :key="item.id">
                        <div class="flex items-center gap-2 py-1 group">
                            <input type="checkbox" x-model="item.checked" class="w-5 h-5 rounded accent-emerald-500">
                            <span x-show="!item.editing" @dblclick="startEdit(item)"
                                  :class="item.checked ? 'line-through text-gray-400' : 'text-gray-700'"
                                  class="flex-1 text-sm cursor-pointer" x-text="item.text"></span>
                            <input x-show="item.editing" style="display:none" x-model="item.text"
                                   @keydown.enter="saveEdit(item)" @blur="saveEdit(item)"
                                   class="flex-1 text-sm px-2 py-1 rounded border border-emerald-300 outline-none">
                            <button @click="remove(item.id)" class="text-gray-300 hover:text-rose-400 opacity-0 group-hover:opacity-100 transition">×</button>
                        </div>
                    </template>
                    <p x-show="!items.length" style="display:none" class="text-gray-400 text-sm text-center py-4">Список пуст</p>
                </div>

                <div class="p-5 border-t border-gray-100 space-y-3">
                    <div class="flex gap-2">
                        <input type="text" x-model="newItem" @keydown.enter="add()" placeholder="Добавить продукт…"
                               class="flex-1 px-3 py-2 rounded-xl border border-gray-200 outline-none text-sm focus:border-emerald-300">
                        <button @click="add()" class="px-4 py-2 rounded-xl bg-emerald-100 text-emerald-700 font-bold">+</button>
                    </div>
                    <div class="flex gap-2">
                        <input type="email" x-model="email" placeholder="Email для отправки"
                               class="flex-1 px-3 py-2 rounded-xl border border-gray-200 outline-none text-sm focus:border-amber-300">
                        <button @click="sendEmail()" :disabled="sending"
                                class="px-4 py-2 rounded-xl bg-amber-100 text-amber-700 font-bold text-sm disabled:opacity-60">✉️</button>
                    </div>
                    <button @click="copyList()" class="w-full py-2 rounded-xl bg-gray-100 text-gray-600 text-sm font-semibold hover:bg-gray-200 transition">
                        📋 Скопировать список
                    </button>
                    <p class="text-xs text-gray-400 text-center">Осталось купить: <span x-text="remaining"></span></p>
                </div>
            </div>
        </div>
    </div>

    <!-- ВКЛАДКА: ШАГИ -->
    <div x-show="tab==='steps'" x-transition style="display:none">
        <h2 class="text-xl font-extrabold text-gray-800 mb-4">Приготовление</h2>
        <div class="space-y-4">
            <?php foreach ($steps as $i => $step): ?>
                <div class="bg-white rounded-2xl shadow-md p-5 flex gap-4"
                     <?= (!empty($step['timer_minutes'])) ? 'x-data="stepTimer(' . (int)$step['timer_minutes'] . ')"' : '' ?>>
                    <span class="shrink-0 w-9 h-9 rounded-full bg-amber-100 text-amber-700 font-extrabold flex items-center justify-center"><?= $i + 1 ?></span>
                    <div class="flex-1">
                        <p class="text-gray-700"><?= e($step['instruction'] ?? '') ?></p>
                        <?php if (!empty($step['tip'])): ?>
                            <p class="text-sm text-amber-600 mt-2 bg-amber-50 rounded-lg px-3 py-2">💡 <?= e($step['tip']) ?></p>
                        <?php endif; ?>

                        <?php if (!empty($step['timer_minutes'])): ?>
                            <div class="mt-3 flex items-center gap-3">
                                <div class="flex-1 h-2 bg-gray-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-gradient-to-r from-amber-300 to-orange-300 transition-all" :style="`width:${progress}%`"></div>
                                </div>
                                <span class="font-mono font-bold text-gray-700 tabular-nums" x-text="display"></span>
                                <button @click="toggle()" class="px-3 py-1.5 rounded-lg bg-amber-100 text-amber-700 text-sm font-bold hover:bg-amber-200 transition">
                                    <span x-text="running ? '⏸' : '▶'"></span>
                                </button>
                                <button @click="reset()" class="px-3 py-1.5 rounded-lg bg-gray-100 text-gray-500 text-sm hover:bg-gray-200 transition">↺</button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- ВКЛАДКА: ВИДЕО -->
    <?php if ($hasVideo): ?>
    <div x-show="tab==='video'" x-transition style="display:none">
        <div class="aspect-video rounded-2xl overflow-hidden shadow-md bg-black">
            <iframe src="<?= e($recipe['video_url']) ?>" class="w-full h-full" frameborder="0" allowfullscreen></iframe>
        </div>
    </div>
    <?php endif; ?>

    <!-- ВКЛАДКА: ПИТАНИЕ -->
    <div x-show="tab==='nutrition'" x-transition style="display:none">
        <h2 class="text-xl font-extrabold text-gray-800 mb-4">Пищевая ценность на порцию</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-center">
            <div class="bg-white rounded-2xl shadow-md p-6">
                <canvas id="nutritionChart" height="240"></canvas>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div class="bg-amber-50 rounded-xl p-4 text-center">
                    <div class="text-3xl font-extrabold text-amber-600"><?= (int)$recipe['calories'] ?></div>
                    <div class="text-sm text-gray-500">ккал</div>
                </div>
                <div class="bg-emerald-50 rounded-xl p-4 text-center">
                    <div class="text-3xl font-extrabold text-emerald-600"><?= (int)$recipe['proteins'] ?> г</div>
                    <div class="text-sm text-gray-500">белки</div>
                </div>
                <div class="bg-rose-50 rounded-xl p-4 text-center">
                    <div class="text-3xl font-extrabold text-rose-500"><?= (int)$recipe['fats'] ?> г</div>
                    <div class="text-sm text-gray-500">жиры</div>
                </div>
                <div class="bg-violet-50 rounded-xl p-4 text-center">
                    <div class="text-3xl font-extrabold text-violet-600"><?= (int)$recipe['carbs'] ?> г</div>
                    <div class="text-sm text-gray-500">углеводы</div>
                </div>
            </div>
        </div>
    </div>

    <!-- ВКЛАДКА: ОТЗЫВЫ -->
    <div x-show="tab==='reviews'" x-transition style="display:none"
         x-data="reviews(<?= $id ?>)" x-init="load()">
        <div class="flex items-center gap-4 mb-6">
            <div class="text-4xl font-extrabold text-amber-500" x-text="avg || '—'"></div>
            <div>
                <div class="text-amber-400 text-lg" x-text="stars(avg)"></div>
                <div class="text-sm text-gray-400"><span x-text="count"></span> отзывов</div>
            </div>
        </div>

        <!-- Форма отзыва -->
        <div class="bg-white rounded-2xl shadow-md p-5 mb-6">
            <h3 class="font-bold text-gray-800 mb-3">Оставить отзыв</h3>
            <div class="flex gap-1 mb-3 text-2xl">
                <template x-for="s in 5" :key="s">
                    <button @click="form.rating=s" class="transition"
                            :class="s <= form.rating ? 'text-amber-400' : 'text-gray-200'">★</button>
                </template>
            </div>
            <textarea x-model="form.text" rows="3" placeholder="Поделитесь впечатлениями…"
                      class="w-full px-4 py-3 rounded-xl border border-gray-200 outline-none focus:border-amber-300 text-sm resize-none"></textarea>
            <button @click="submit()" :disabled="sending"
                    class="mt-3 px-5 py-2.5 rounded-xl bg-gradient-to-r from-amber-300 to-orange-300 text-amber-900 font-bold hover:shadow-md transition disabled:opacity-60">
                Отправить
            </button>
        </div>

        <!-- Список отзывов -->
        <div class="space-y-3">
            <template x-for="c in comments" :key="c.id">
                <div class="bg-white rounded-2xl shadow-sm p-4">
                    <div class="flex items-center justify-between mb-1">
                        <span class="font-bold text-gray-700" x-text="c.author_name"></span>
                        <span class="text-amber-400" x-text="stars(c.rating)"></span>
                    </div>
                    <p class="text-gray-600 text-sm" x-text="c.text"></p>
                </div>
            </template>
            <p x-show="!comments.length" style="display:none" class="text-gray-400 text-center py-6">Пока нет отзывов. Будьте первым!</p>
        </div>
    </div>
</main>

<!-- AI-АССИСТЕНТ ПО РЕЦЕПТУ (плавающая кнопка) -->
<div x-data="recipeAssistant(<?= $id ?>)" class="fixed bottom-5 right-5 z-[80]">
    <button @click="open=!open" x-show="!open"
            class="w-14 h-14 rounded-full bg-gradient-to-br from-violet-300 to-purple-300 shadow-lg flex items-center justify-center text-2xl hover:scale-105 transition ai-pulse">🧑‍🍳</button>

    <div x-show="open" x-transition style="display:none"
         class="w-[90vw] max-w-sm bg-white rounded-3xl shadow-2xl flex flex-col h-[70vh]">
        <div class="flex items-center justify-between p-4 border-b border-gray-100 bg-gradient-to-r from-violet-100 to-purple-100 rounded-t-3xl">
            <span class="font-extrabold text-violet-800">✨ AI-помощник по рецепту</span>
            <button @click="open=false" class="text-violet-400 hover:text-violet-600 text-xl">×</button>
        </div>
        <div class="flex-1 overflow-y-auto p-4 space-y-3" x-ref="scroll">
            <template x-for="(m, i) in messages" :key="i">
                <div :class="m.role==='user' ? 'flex justify-end' : 'flex justify-start'">
                    <div :class="m.role==='user' ? 'bg-violet-100 text-violet-900' : 'bg-gray-50 text-gray-700'"
                         class="max-w-[85%] px-3 py-2 rounded-2xl text-sm whitespace-pre-wrap" x-text="m.text"></div>
                </div>
            </template>
            <div x-show="loading" style="display:none" class="text-gray-400 text-sm">Печатает… ⏳</div>
        </div>
        <div class="p-3 border-t border-gray-100 flex gap-2">
            <input type="text" x-model="input" @keydown.enter="send()" placeholder="Спросите о рецепте…"
                   class="flex-1 px-3 py-2 rounded-xl border border-gray-200 outline-none text-sm focus:border-violet-300">
            <button @click="send()" :disabled="loading" class="px-4 py-2 rounded-xl bg-violet-200 text-violet-800 font-bold">➤</button>
        </div>
    </div>
</div>

<script src="<?= url('assets/js/api-client.js') ?>"></script>
<script src="<?= url('assets/js/app.js') ?>"></script>
<script src="<?= url('assets/js/recipe/step-timer.js') ?>"></script>
<script src="<?= url('assets/js/recipe/shopping-list.js') ?>"></script>
<script src="<?= url('assets/js/ai/nutrition-chart.js') ?>"></script>
<script src="<?= url('assets/js/ai/ingredient-substitute.js') ?>"></script>
<script src="<?= url('assets/js/ai/recipe-assistant.js') ?>"></script>
<script src="<?= url('assets/js/recipe/reviews.js') ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    initNutritionChart('nutritionChart', {
        proteins: <?= (int)$recipe['proteins'] ?>,
        fats: <?= (int)$recipe['fats'] ?>,
        carbs: <?= (int)$recipe['carbs'] ?>
    });
});
</script>
</body>
</html>