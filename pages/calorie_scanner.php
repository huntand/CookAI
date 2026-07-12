<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'Сканер калорий';
$logged = is_logged_in();
require_once __DIR__ . '/../includes/header.php';
?>
<section class="max-w-2xl mx-auto px-4 sm:px-6 py-10"
         x-data="calorieScanner(<?= $logged ? 'true' : 'false' ?>)" x-init="init()">
    <div class="text-center mb-8" data-aos="fade-up">
        <span class="inline-block px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 text-xs font-bold mb-3">📷 AI Vision</span>
        <h1 class="text-3xl font-extrabold text-gray-800">Сканер калорий</h1>
        <p class="mt-2 text-gray-500">Сфотографируйте блюдо — AI распознает его и оценит КБЖУ.</p>

        <div x-show="ai.limit" x-cloak class="mt-4 inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-sm font-semibold"
             :class="ai.remaining > 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-600'">
            <span x-show="!ai.is_pro">
                Осталось <span class="font-extrabold" x-text="ai.remaining"></span> из <span x-text="ai.limit"></span> сегодня
            </span>
            <span x-show="ai.is_pro" style="display:none">
                CookAI Pro · <span x-text="ai.remaining"></span> из <span x-text="ai.limit"></span> сегодня
            </span>
        </div>
        <div x-show="ai.limit && ai.remaining === 0 && !ai.is_pro" x-cloak class="mt-2 text-xs text-gray-500">
            Лимит обновится <span x-text="ai.reset"></span> ·
            <a href="<?= url('payment') ?>" class="text-violet-600 font-semibold hover:underline">оформить безлимит →</a>
        </div>
    </div>

    <div class="bg-white rounded-3xl shadow-md p-6 sm:p-8" data-aos="fade-up" data-aos-delay="100">
        <label class="block border-2 border-dashed border-gray-200 rounded-2xl p-6 text-center cursor-pointer hover:border-emerald-300 transition mb-4">
            <input type="file" accept="image/jpeg,image/png,image/webp" class="hidden" @change="preview($event)">
            <template x-if="!image">
                <div class="text-gray-400">
                    <div class="text-4xl mb-2">📸</div>
                    Нажмите, чтобы загрузить или снять фото блюда
                    <div class="text-xs mt-1">JPEG, PNG, WebP · до 5 МБ</div>
                </div>
            </template>
            <template x-if="image">
                <img :src="image" class="mx-auto max-h-64 rounded-xl object-contain">
            </template>
        </label>

        <button x-show="image" style="display:none" @click="image=null; result=null" class="text-xs text-rose-400 mb-3 hover:underline">✕ Убрать фото</button>

        <input type="text" x-model="description"
               placeholder="Необязательно: уточните блюдо (например «двойная порция»)"
               class="w-full px-4 py-3 rounded-xl border border-gray-200 outline-none focus:border-emerald-300 mb-4">

        <button @click="scan()" :disabled="loading || (!image && !description.trim()) || (ai.limit && ai.remaining === 0)"
                class="w-full py-3.5 rounded-xl bg-gradient-to-r from-emerald-300 to-teal-300 text-emerald-900 font-bold hover:shadow-md transition disabled:opacity-60">
            <span x-show="!loading" x-text="image ? '🔍 Анализировать фото' : '🔍 Анализировать по описанию'"></span>
            <span x-show="loading" style="display:none">⏳ AI изучает блюдо…</span>
        </button>
        <p x-show="error" style="display:none" class="text-rose-500 text-sm mt-3" x-text="error"></p>
    </div>

    <!-- Результат -->
    <div x-show="result" x-transition style="display:none" class="mt-6 bg-white rounded-3xl shadow-md p-6" data-aos="fade-up">
        <div class="flex items-start justify-between gap-3">
            <div>
                <h2 class="text-xl font-extrabold text-gray-800" x-text="result?.dish"></h2>
                <p class="text-sm text-gray-400" x-text="result?.portion"></p>
            </div>
            <span class="shrink-0 px-2.5 py-1 rounded-lg text-xs font-bold"
                  :class="{
                    'bg-emerald-100 text-emerald-700': result?.confidence==='высокая',
                    'bg-amber-100 text-amber-700': result?.confidence==='средняя',
                    'bg-rose-100 text-rose-600': result?.confidence==='низкая'
                  }"
                  x-text="'точность: ' + result?.confidence"></span>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-4">
            <div class="bg-amber-50 rounded-xl p-3 text-center"><div class="text-2xl font-extrabold text-amber-600" x-text="result?.calories"></div><div class="text-xs text-gray-500">ккал</div></div>
            <div class="bg-emerald-50 rounded-xl p-3 text-center"><div class="text-2xl font-extrabold text-emerald-600" x-text="result?.proteins+' г'"></div><div class="text-xs text-gray-500">белки</div></div>
            <div class="bg-rose-50 rounded-xl p-3 text-center"><div class="text-2xl font-extrabold text-rose-500" x-text="result?.fats+' г'"></div><div class="text-xs text-gray-500">жиры</div></div>
            <div class="bg-violet-50 rounded-xl p-3 text-center"><div class="text-2xl font-extrabold text-violet-600" x-text="result?.carbs+' г'"></div><div class="text-xs text-gray-500">углеводы</div></div>
        </div>

        <div x-show="result?.ingredients?.length" style="display:none" class="mt-4">
            <div class="text-sm font-bold text-gray-700 mb-2">Распознанные ингредиенты:</div>
            <div class="flex flex-wrap gap-2">
                <template x-for="ing in result?.ingredients" :key="ing">
                    <span class="px-3 py-1 rounded-full bg-gray-100 text-gray-600 text-xs" x-text="ing"></span>
                </template>
            </div>
        </div>

        <p x-show="result?.note" style="display:none" class="text-sm text-gray-500 mt-4" x-text="'💡 ' + result?.note"></p>
        <p x-show="source==='text'" style="display:none" class="text-xs text-amber-500 mt-3">Анализ выполнен по описанию — точность может быть ниже.</p>

        <div class="mt-5 pt-4 border-t border-gray-100">
            <template x-if="!logged">
                <p class="text-sm text-gray-400">
                    <a href="<?= url('login') ?>" class="text-emerald-600 font-semibold hover:underline">Войдите</a>, чтобы сохранять блюда в дневник питания.
                </p>
            </template>
            <template x-if="logged && !saved">
                <button @click="saveToDiary()" :disabled="saving"
                        class="w-full py-3 rounded-xl bg-amber-100 text-amber-800 font-bold hover:bg-amber-200 transition disabled:opacity-60">
                    <span x-show="!saving">📔 Добавить в дневник питания</span>
                    <span x-show="saving" style="display:none">Сохраняем…</span>
                </button>
            </template>
            <template x-if="saved">
                <div class="text-center text-emerald-600 font-semibold">
                    ✓ Добавлено в дневник · <a href="<?= url('food-diary') ?>" class="underline">открыть дневник</a>
                </div>
            </template>
        </div>
    </div>
</section>
<script>
function calorieScanner(isLogged) {
    return {
        logged: isLogged,
        image: null, description: '', result: null, source: '',
        loading: false, error: '', saving: false, saved: false,
        ai: {},

        async init() { await this.loadStatus(); },

        async loadStatus() {
            try {
                const r = await CookAPI.get('/api/ai_status.php?feature=calorie');
                this.ai = r.ai || {};
            } catch (_) {}
        },

        preview(e) {
            const f = e.target.files[0];
            if (!f) return;
            if (f.size > 5 * 1024 * 1024) { this.error = 'Файл больше 5 МБ'; return; }
            this.error = '';
            const r = new FileReader();
            r.onload = ev => { this.image = ev.target.result; this.result = null; this.saved = false; };
            r.readAsDataURL(f);
        },

        async scan() {
            if (this.ai.limit && this.ai.remaining === 0) {
                toast('Лимит запросов исчерпан. Обновится ' + this.ai.reset, 'error');
                return;
            }
            this.error = ''; this.loading = true; this.result = null; this.saved = false;
            try {
                const payload = {};
                if (this.image) payload.image = this.image;
                if (this.description.trim()) payload.description = this.description.trim();
                const res = await CookAPI.post('/api/ai_calorie_scan.php', payload);
                this.result = res.result;
                this.source = res.source;
                if (res.ai) this.ai = res.ai;
            } catch (e) {
                this.error = e.message;
                if (e.code === 429) await this.loadStatus();
            } finally {
                this.loading = false;
            }
        },

        async saveToDiary() {
            if (!this.result) return;
            this.saving = true;
            try {
                await CookAPI.post('/api/diary_add.php', {
                    dish: this.result.dish, portion: this.result.portion,
                    calories: this.result.calories, proteins: this.result.proteins,
                    fats: this.result.fats, carbs: this.result.carbs,
                    source: this.source, confidence: this.result.confidence
                });
                this.saved = true;
                CookAPI.clearCache();
                toast('Добавлено в дневник', 'success');
            } catch (e) {
                toast(e.message, 'error');
            } finally {
                this.saving = false;
            }
        }
    };
}
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>