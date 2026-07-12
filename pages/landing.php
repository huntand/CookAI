<?php
require_once __DIR__ . '/../config/config.php';
?>
<!DOCTYPE html>
<html lang="ru"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>CookAI — умная кулинарная платформа</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
<link rel="stylesheet" href="https://unpkg.com/aos@2.3.4/dist/aos.css">
<style>body{font-family:'Nunito',sans-serif}</style>
</head>
<body class="bg-white">
<section class="hero-gradient min-h-screen flex items-center">
    <div class="max-w-5xl mx-auto px-4 text-center py-20">
        <div class="text-6xl mb-4">🍳</div>
        <h1 class="text-5xl sm:text-7xl font-extrabold text-gray-800">Cook<span class="text-amber-500">AI</span></h1>
        <p class="mt-6 text-xl text-gray-600 max-w-2xl mx-auto">Кулинарная платформа нового поколения с искусственным интеллектом. Готовьте вкусно, умно и с удовольствием.</p>
        <div class="mt-8 flex flex-wrap gap-3 justify-center">
            <a href="<?= url('') ?>" class="px-8 py-4 rounded-xl bg-gradient-to-r from-amber-300 to-orange-300 text-amber-900 font-extrabold hover:shadow-lg transition">Начать бесплатно</a>
            <a href="<?= url('payment') ?>" class="px-8 py-4 rounded-xl bg-violet-100 text-violet-700 font-extrabold hover:bg-violet-200 transition">CookAI Pro ✨</a>
        </div>
    </div>
</section>
<section class="max-w-6xl mx-auto px-4 py-20 grid grid-cols-1 sm:grid-cols-3 gap-6">
    <?php
    $features = [
        ['✨','Генератор рецептов','AI создаёт полные рецепты с БЖУ по вашему запросу'],
        ['🥕','Что приготовить?','Подбор блюд из продуктов в холодильнике'],
        ['📷','Сканер калорий','Оценка КБЖУ блюда по описанию'],
        ['🧑‍🍳','Кулинарный советник','Чат-помощник по любым вопросам готовки'],
        ['🏆','Челленджи','Соревнуйтесь и получайте награды'],
        ['📖','AI-книга','Целые кулинарные книги за секунды'],
    ];
    foreach ($features as $f): ?>
        <div data-aos="fade-up" class="bg-white rounded-3xl shadow-md p-6 border border-amber-50">
            <div class="text-4xl mb-3"><?= $f[0] ?></div>
            <h3 class="font-extrabold text-gray-800 text-lg"><?= $f[1] ?></h3>
            <p class="text-gray-500 text-sm mt-1"><?= $f[2] ?></p>
        </div>
    <?php endforeach; ?>
</section>
<script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script><script>AOS.init({once:true});</script>
</body></html>