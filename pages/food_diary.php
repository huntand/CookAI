<?php
require_once __DIR__ . '/../config/config.php';
require_login();
$pageTitle = 'Дневник питания';
require_once __DIR__ . '/../includes/header.php';
?>
<section class="max-w-3xl mx-auto px-4 sm:px-6 py-8" x-data="foodDiary()" x-init="load()">
    <div class="flex items-center justify-between flex-wrap gap-3 mb-6" data-aos="fade-up">
        <div>
            <h1 class="text-3xl font-extrabold text-gray-800">📔 Дневник питания</h1>
            <p class="text-gray-500 mt-1">Сумма КБЖУ по дням из сканера калорий.</p>
        </div>
        <a href="<?= url('scanner') ?>" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-emerald-300 to-teal-300 text-emerald-900 font-bold hover:shadow-md transition">
            + Сканировать блюдо
        </a>
    </div>

    <!-- Сегодня -->
    <div class="bg-gradient-to-r from-amber-100 to-orange-100 rounded-3xl shadow-md p-6 mb-6" data-aos="fade-up">
        <div class="text-sm font-bold text-amber-800 mb-3">Сегодня · <span x-text="todayLabel"></span></div>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div class="bg-white/70 rounded-xl p-3 text-center">
                <div class="text-2xl font-extrabold text-amber-600" x-text="today.calories || 0"></div>
                <div class="text-xs text-gray-500">ккал</div>
            </div>
            <div class="bg-white/70 rounded-xl p-3 text-center">
                <div class="text-2xl font-extrabold text-emerald-600" x-text="(today.proteins || 0) + ' г'"></div>
                <div class="text-xs text-gray-500">белки</div>
            </div>
            <div class="bg-white/70 rounded-xl p-3 text-center">
                <div class="text-2xl font-extrabold text-rose-500" x-text="(today.fats || 0) + ' г'"></div>
                <div class="text-xs text-gray-500">жиры</div>
            </div>
            <div class="bg-white/70 rounded-xl p-3 text-center">
                <div class="text-2xl font-extrabold text-violet-600" x-text="(today.carbs || 0) + ' г'"></div>
                <div class="text-xs text-gray-500">углеводы</div>
            </div>
        </div>
        <div class="text-xs text-amber-700/70 mt-2" x-text="(today.items || 0) + ' записей за сегодня'"></div>
    </div>

    <!-- Фильтр периода -->
    <div class="flex gap-2 mb-4" data-aos="fade-up">
        <template x-for="p in [7,14,30]" :key="p">
            <button @click="days=p; load()"
                    :class="days===p ? 'bg-violet-500 text-white' : 'bg-white text-gray-500'"
                    class="px-4 py-2 rounded-xl text-sm font-bold shadow-sm transition"
                    x-text="p + ' дней'"></button>
        </template>
    </div>

    <!-- Сводка по дням -->
    <template x-for="day in byDay" :key="day.d">
        <div class="bg-white rounded-3xl shadow-md mb-4 overflow-hidden" data-aos="fade-up">
            <!-- Заголовок дня + сумма -->
            <div class="flex items-center justify-between px-5 py-4 bg-gray-50 cursor-pointer" @click="toggle(day.d)">
                <div class="font-extrabold text-gray-800" x-text="fmtDay(day.d)"></div>
                <div class="flex items-center gap-4 text-sm">
                    <span class="font-extrabold text-amber-600" x-text="day.calories + ' ккал'"></span>
                    <span class="text-gray-400 text-xs" x-text="'Б' + day.proteins + ' · Ж' + day.fats + ' · У' + day.carbs"></span>
                    <span class="text-gray-300" x-text="open===day.d ? '▲' : '▼'"></span>
                </div>
            </div>
            <!-- Записи дня -->
            <div x-show="open===day.d" x-collapse style="display:none">
                <template x-for="scan in scansOfDay(day.d)" :key="scan.id">
                    <div class="flex items-center justify-between gap-3 px-5 py-3 border-t border-gray-50">
                        <div class="min-w-0">
                            <div class="font-bold text-gray-700 truncate" x-text="scan.dish"></div>
                            <div class="text-xs text-gray-400">
                                <span x-text="scan.portion"></span> ·
                                <span x-text="fmtTime(scan.created_at)"></span> ·
                                <span x-text="scan.source==='photo' ? '📷 фото' : '✍️ описание'"></span>
                            </div>
                        </div>
                        <div class="text-right shrink-0">
                            <div class="font-extrabold text-amber-600" x-text="scan.calories + ' ккал'"></div>
                            <button @click="del(scan.id)" class="text-xs text-rose-400 hover:underline">удалить</button>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </template>

    <div x-show="!byDay.length" style="display:none" class="text-center text-gray-400 py-16" data-aos="fade-up">
        Записей пока нет. <a href="<?= url('scanner') ?>" class="text-emerald-600 font-semibold hover:underline">Отсканируйте первое блюдо →</a>
    </div>
</section>

<script>
function foodDiary() {
    return {
        scans: [], byDay: [], today: {}, days: 14, open: null,
        todayLabel: new Date().toLocaleDateString('ru-RU', { day: 'numeric', month: 'long' }),

        async load() {
            try {
                const r = await CookAPI.get('/api/food_diary.php?days=' + this.days);
                this.scans = r.scans || [];
                this.byDay = r.by_day || [];
                this.today = r.today || {};
                if (this.byDay.length && this.open === null) this.open = this.byDay[0].d;
            } catch (e) { toast(e.message, 'error'); }
        },

        scansOfDay(d) {
            return this.scans.filter(s => (s.created_at || '').slice(0, 10) === d);
        },
        toggle(d) { this.open = this.open === d ? null : d; },

        fmtDay(d) {
            const date = new Date(d + 'T00:00:00');
            const today = new Date(); today.setHours(0,0,0,0);
            const diff = Math.round((today - date) / 86400000);
            if (diff === 0) return 'Сегодня';
            if (diff === 1) return 'Вчера';
            return date.toLocaleDateString('ru-RU', { weekday: 'short', day: 'numeric', month: 'long' });
        },
        fmtTime(dt) { return dt ? new Date(dt.replace(' ', 'T')).toLocaleTimeString('ru-RU', {hour:'2-digit', minute:'2-digit'}) : ''; },

        async del(id) {
            if (!confirm('Удалить запись из дневника?')) return;
            try {
                await CookAPI.post('/api/food_diary.php', { action: 'delete', id });
                CookAPI.clearCache(); await this.load();
                toast('Запись удалена', 'success');
            } catch (e) { toast(e.message, 'error'); }
        }
    };
}
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>