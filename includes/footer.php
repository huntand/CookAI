</main>

<footer class="mt-20 bg-white border-t border-amber-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-10 grid grid-cols-2 md:grid-cols-4 gap-8 text-sm">
        <div class="col-span-2 md:col-span-1">
            <div class="flex items-center gap-2 mb-3">
                <span class="w-8 h-8 rounded-lg bg-gradient-to-br from-amber-200 to-orange-200 flex items-center justify-center">🍳</span>
                <span class="font-extrabold text-lg text-amber-700">CookAI</span>
            </div>
            <p class="text-gray-500">Кулинарная платформа с AI. Готовьте с удовольствием.</p>
        </div>
        <div>
            <h4 class="font-bold text-gray-700 mb-3">AI-инструменты</h4>
            <ul class="space-y-2 text-gray-500">
                <li><a href="<?= url('ai-generator') ?>" class="hover:text-amber-600">Генератор</a></li>
                <li><a href="<?= url('what-to-cook') ?>" class="hover:text-amber-600">Что приготовить</a></li>
                <li><a href="<?= url('advisor') ?>" class="hover:text-amber-600">Советник</a></li>
            </ul>
        </div>
        <div>
            <h4 class="font-bold text-gray-700 mb-3">Сообщество</h4>
            <ul class="space-y-2 text-gray-500">
                <li><a href="<?= url('communities') ?>" class="hover:text-amber-600">Сообщества</a></li>
                <li><a href="<?= url('challenges') ?>" class="hover:text-amber-600">Челленджи</a></li>
                <li><a href="<?= url('leaderboard') ?>" class="hover:text-amber-600">Лидеры</a></li>
            </ul>
        </div>
        <div>
            <h4 class="font-bold text-gray-700 mb-3">Аккаунт</h4>
            <ul class="space-y-2 text-gray-500">
                <li><a href="<?= url('profile') ?>" class="hover:text-amber-600">Профиль</a></li>
                <li><a href="<?= url('my-cookbooks') ?>" class="hover:text-amber-600">Мои книги</a></li>
                <li><a href="<?= url('landing') ?>" class="hover:text-amber-600">О проекте</a></li>
            </ul>
        </div>
    </div>
    <div class="border-t border-amber-50 py-4 text-center text-xs text-gray-400">
        © <?= date('Y') ?> CookAI. Сделано с 🧡 на PHP + Yandex AI.
    </div>
</footer>

<script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
<script>AOS.init({ once:true, duration:600, easing:'ease-out' });</script>
<script src="<?= url('assets/js/api-client.js') ?>"></script>
<script src="<?= url('assets/js/app.js') ?>"></script>
</body>
</html>