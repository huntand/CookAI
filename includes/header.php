<?php
require_once __DIR__ . '/../config/config.php';
$__user = current_user();
?>
<!DOCTYPE html>
<html lang="ru" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="base-path" content="<?= e(BASE_PATH) ?>">
    <?php if (function_exists('csrf_token')): ?>
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <?php endif; ?>
    <title><?= isset($pageTitle) ? e($pageTitle) . ' — ' : '' ?><?= APP_NAME ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: { extend: { fontFamily: { sans: ['Nunito','system-ui','sans-serif'] } } }
        };
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/aos@2.3.4/dist/aos.css">
    <link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-orange-50/40 text-gray-800 font-sans antialiased">

<?php
require_once __DIR__ . '/maintenance.php';
$maintNotice = cookai_maintenance_notice();
?>
<?php if ($maintNotice): ?>
<div x-data="maintenanceBanner(<?= (int)$maintNotice['minutes_left'] ?>, '<?= e($maintNotice['starts_full']) ?>')"
     x-init="init()" x-show="visible" x-cloak
     class="sticky top-0 z-50 bg-gradient-to-r from-amber-400 to-orange-400 text-amber-950 shadow-md">
    <div class="max-w-6xl mx-auto px-4 py-2.5 flex items-center gap-3 text-sm font-semibold">
        <span class="text-lg shrink-0">🔧</span>
        <div class="flex-1 min-w-0">
            Плановые техработы в <?= e($maintNotice['starts_at']) ?>
            (≈<?= (int)$maintNotice['duration'] ?> мин) ·
            начнутся через <span x-text="label"></span>. Сохраните изменения.
        </div>
        <button @click="dismiss()" class="shrink-0 w-7 h-7 rounded-full hover:bg-amber-500/30 transition flex items-center justify-center" aria-label="Скрыть">✕</button>
    </div>
</div>
<script>
function maintenanceBanner(minutesLeft, startsFull) {
    return {
        visible: true,
        seconds: minutesLeft * 60,
        label: '',
        startsFull,
        timer: null,
        init() {
            if (sessionStorage.getItem('maint_dismissed') === this.startsFull) {
                this.visible = false; return;
            }
            this.render();
            this.timer = setInterval(() => {
                this.seconds -= 1;
                if (this.seconds <= 0) { this.visible = false; clearInterval(this.timer); return; }
                this.render();
            }, 1000);
        },
        render() {
            const m = Math.floor(this.seconds / 60);
            const s = this.seconds % 60;
            this.label = m > 0 ? `${m} мин ${String(s).padStart(2,'0')} с` : `${s} с`;
        },
        dismiss() {
            this.visible = false;
            sessionStorage.setItem('maint_dismissed', this.startsFull);
        }
    };
}
</script>
<?php endif; ?>

<header
    x-data="{ open:false, scrolled:false, menu:null }"
    @scroll.window="scrolled = window.scrollY > 20"
    :class="scrolled ? 'backdrop-blur-md bg-white/70 shadow-sm' : 'bg-transparent'"
    class="fixed top-0 inset-x-0 z-50 transition-all duration-300">
    <nav class="max-w-7xl mx-auto px-4 sm:px-6 flex items-center justify-between h-16">

        <a href="<?= url('') ?>" class="flex items-center gap-2 shrink-0">
            <span class="w-9 h-9 rounded-xl bg-gradient-to-br from-amber-200 to-orange-200 flex items-center justify-center text-lg">🍳</span>
            <span class="font-extrabold text-xl text-amber-700">CookAI</span>
        </a>

        <!-- Desktop -->
        <div class="hidden lg:flex items-center gap-1">
            <div class="relative" @mouseenter="menu='ai'" @mouseleave="menu=null">
                <button class="px-3 py-2 rounded-lg text-sm font-semibold text-gray-600 hover:text-violet-600 hover:bg-violet-50 transition">
                    Инструменты ▾
                </button>
                <div x-show="menu==='ai'" x-transition class="absolute left-0 mt-1 w-56 bg-white rounded-2xl shadow-lg border border-gray-100 p-2" style="display:none">
                    <a href="<?= url('ai-generator') ?>" class="block px-3 py-2 rounded-lg text-sm hover:bg-violet-50">✨ Генератор рецептов</a>
                    <a href="<?= url('what-to-cook') ?>" class="block px-3 py-2 rounded-lg text-sm hover:bg-violet-50">🥕 Что приготовить?</a>
                    <a href="<?= url('advisor') ?>" class="block px-3 py-2 rounded-lg text-sm hover:bg-violet-50">🧑‍🍳 Советник</a>
                    <a href="<?= url('calorie-scanner') ?>" class="block px-3 py-2 rounded-lg text-sm hover:bg-violet-50">📷 Сканер калорий</a>
                    <a href="<?= url('ai-cookbook') ?>" class="block px-3 py-2 rounded-lg text-sm hover:bg-violet-50">📖 AI-книга</a>
                </div>
            </div>
            <div class="relative" @mouseenter="menu='com'" @mouseleave="menu=null">
                <button class="px-3 py-2 rounded-lg text-sm font-semibold text-gray-600 hover:text-amber-600 hover:bg-amber-50 transition">
                    Сообщества ▾
                </button>
                <div x-show="menu==='com'" x-transition class="absolute left-0 mt-1 w-52 bg-white rounded-2xl shadow-lg border border-gray-100 p-2" style="display:none">
                    <a href="<?= url('communities') ?>" class="block px-3 py-2 rounded-lg text-sm hover:bg-amber-50">👥 Сообщества</a>
                    <a href="<?= url('challenges') ?>" class="block px-3 py-2 rounded-lg text-sm hover:bg-amber-50">🏆 Челленджи</a>
                    <a href="<?= url('leaderboard') ?>" class="block px-3 py-2 rounded-lg text-sm hover:bg-amber-50">📊 Лидеры</a>
                </div>
            </div>
            <a href="<?= url('inspiration') ?>" class="px-3 py-2 rounded-lg text-sm font-semibold text-gray-600 hover:text-rose-500 hover:bg-rose-50 transition">Вдохновение</a>
            <a href="<?= url('search') ?>" class="px-3 py-2 rounded-lg text-sm font-semibold text-gray-600 hover:text-amber-600 hover:bg-amber-50 transition">🔍 Поиск</a>
        </div>

        <!-- Right -->
        <div class="flex items-center gap-2">
            <?php if ($__user): ?>
<div x-data="notifBell()" x-init="poll()" class="relative">
    <button @click="toggle()" class="p-2 rounded-lg hover:bg-amber-50 relative">
        <span class="text-xl">🔔</span>
        <span x-show="unread>0" style="display:none" class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] px-1 rounded-full bg-rose-400 text-white text-[10px] font-bold flex items-center justify-center" x-text="unread"></span>
    </button>
    <div x-show="open" @click.outside="open=false" x-transition style="display:none"
         class="absolute right-0 mt-2 w-72 bg-white rounded-2xl shadow-lg border border-gray-100 max-h-80 overflow-y-auto p-2">
        <template x-for="n in items" :key="n.id">
            <a :href="n.link || '#'" class="block px-3 py-2 rounded-lg hover:bg-amber-50 text-sm">
                <div class="font-bold text-gray-700" x-text="n.title"></div>
                <div class="text-gray-500 text-xs" x-text="n.message"></div>
            </a>
        </template>
        <p x-show="!items.length" style="display:none" class="text-center text-gray-400 text-sm py-4">Нет уведомлений</p>
    </div>
</div>
<script>
function notifBell() {
    return {
        open: false, items: [], unread: 0,
        async load() { try { const r = await CookAPI.get('/api/notifications.php'); this.items = r.notifications; this.unread = r.unread; } catch (_) {} },
        toggle() { this.open = !this.open; if (this.open && this.unread) { CookAPI.post('/api/notifications.php', {}); this.unread = 0; } },
        poll() { this.load(); setInterval(() => this.load(), 30000); }
    };
}
</script>
<?php endif; ?>
            <?php if ($__user): ?>
                <a href="<?= url('create-recipe') ?>" class="hidden sm:inline-flex px-3 py-2 rounded-xl text-sm font-semibold bg-gradient-to-r from-amber-200 to-orange-200 text-amber-800 hover:shadow-md transition">+ Рецепт</a>
                <a href="<?= url('profile') ?>" class="flex items-center gap-2">
                    <img src="<?= e($__user['avatar_url'] ?: 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=80') ?>" class="w-9 h-9 rounded-full object-cover ring-2 ring-amber-200" alt="Профиль">
                </a>
            <?php else: ?>
                <a href="<?= url('login') ?>" class="px-4 py-2 rounded-xl text-sm font-semibold text-amber-700 hover:bg-amber-50 transition">Войти</a>
                <a href="<?= url('register') ?>" class="hidden sm:inline-flex px-4 py-2 rounded-xl text-sm font-semibold bg-gradient-to-r from-amber-200 to-orange-200 text-amber-800 hover:shadow-md transition">Регистрация</a>
            <?php endif; ?>

            <!-- Mobile burger -->
            <button @click="open=!open" class="lg:hidden p-2 rounded-lg hover:bg-amber-50">
                <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" class="text-gray-700"><path x-show="!open" d="M4 6h16M4 12h16M4 18h16"/><path x-show="open" d="M6 6l12 12M6 18L18 6"/></svg>
            </button>
        </div>
    </nav>

    <!-- Mobile menu -->
    <div x-show="open" x-transition @click.outside="open=false" class="lg:hidden bg-white border-t border-gray-100 px-4 py-3 space-y-1 shadow-lg" style="display:none">
        <a href="<?= url('ai-generator') ?>" class="block px-3 py-2 rounded-lg hover:bg-violet-50">✨ Генератор рецептов</a>
        <a href="<?= url('what-to-cook') ?>" class="block px-3 py-2 rounded-lg hover:bg-violet-50">🥕 Что приготовить?</a>
        <a href="<?= url('advisor') ?>" class="block px-3 py-2 rounded-lg hover:bg-violet-50">🧑‍🍳 Советник</a>
        <a href="<?= url('communities') ?>" class="block px-3 py-2 rounded-lg hover:bg-amber-50">👥 Сообщества</a>
        <a href="<?= url('challenges') ?>" class="block px-3 py-2 rounded-lg hover:bg-amber-50">🏆 Челленджи</a>
        <a href="<?= url('search') ?>" class="block px-3 py-2 rounded-lg hover:bg-amber-50">🔍 Поиск</a>
    </div>
</header>

<main class="pt-16 min-h-screen">