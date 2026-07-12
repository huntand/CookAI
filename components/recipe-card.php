<?php
/**
 * Переиспользуемая карточка рецепта.
 * Использование: $recipe — ассоц. массив строки recipes.
 *   include __DIR__ . '/../components/recipe-card.php';
 */
$tags = json_field($recipe['tags'] ?? null);
$total = (int)($recipe['prep_time'] ?? 0) + (int)($recipe['cook_time'] ?? 0);
?>
<article data-aos="fade-up"
    class="recipe-card group bg-white rounded-2xl shadow-md hover:shadow-lg overflow-hidden flex flex-col">
    <a href="<?= url('recipe/' . (int)$recipe['id']) ?>" class="block relative aspect-[4/3] overflow-hidden">
        <img src="<?= e($recipe['image_url'] ?: 'https://images.unsplash.com/photo-1495521821757-a1efb6729352?w=600') ?>"
             alt="<?= e($recipe['title']) ?>"
             loading="lazy"
             class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
        <?php if (!empty($recipe['is_ai_generated'])): ?>
            <span class="absolute top-3 left-3 px-2 py-1 rounded-lg text-xs font-bold bg-violet-100/90 text-violet-700 ai-pulse">✨ AI</span>
        <?php endif; ?>
        <span class="absolute top-3 right-3 px-2 py-1 rounded-lg text-xs font-bold <?= difficulty_color($recipe['difficulty'] ?? 'Легко') ?>">
            <?= e($recipe['difficulty'] ?? 'Легко') ?>
        </span>
    </a>
    <div class="p-4 flex flex-col flex-1">
        <div class="flex items-center gap-2 text-xs text-gray-400 mb-1">
            <span><?= e($recipe['cuisine'] ?? 'Другая') ?></span>
            <span>•</span>
            <span>⏱ <?= format_time($total) ?></span>
            <span>•</span>
            <span>🍽 <?= (int)($recipe['servings'] ?? 1) ?></span>
        </div>
        <h3 class="font-bold text-gray-800 leading-snug clamp-2 mb-1">
            <a href="<?= url('recipe/' . (int)$recipe['id']) ?>" class="hover:text-amber-600"><?= e($recipe['title']) ?></a>
        </h3>
        <p class="text-sm text-gray-500 clamp-2 mb-3 flex-1"><?= e($recipe['description'] ?? '') ?></p>

        <div class="flex items-center justify-between mt-auto">
            <div class="flex flex-wrap gap-1">
                <?php foreach (array_slice($tags, 0, 2) as $tag): ?>
                    <span class="px-2 py-0.5 rounded-md text-[11px] bg-amber-50 text-amber-600">#<?= e($tag) ?></span>
                <?php endforeach; ?>
            </div>
            <button onclick="saveRecipe(<?= (int)$recipe['id'] ?>, this)"
                    class="p-2 rounded-lg hover:bg-rose-50 text-gray-400 transition" title="Сохранить">
                <span class="text-lg">🤍</span>
            </button>
        </div>
    </div>
</article>