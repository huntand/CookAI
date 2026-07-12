<?php
// pages/ai_cookbook_generator.php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'AI-книга рецептов';
require_once __DIR__ . '/../includes/header.php';
?>
<section class="max-w-3xl mx-auto px-4 sm:px-6 py-10" x-data="aiCookbook()">
    <div class="text-center mb-8" data-aos="fade-up">
        <span class="inline-block px-3 py-1 rounded-full bg-violet-100 text-violet-700 text-xs font-bold mb-3">📖 AI</span>
        <h1 class="text-3xl font-extrabold text-gray-800">Генератор книги рецептов</h1>
        <p class="mt-2 text-gray-500">Задайте тему — AI составит целую книгу.</p>
    </div>
    <div class="bg-white rounded-3xl shadow-md p-6 sm:p-8 flex flex-col sm:flex-row gap-3" data-aos="fade-up" data-aos-delay="100">
        <input type="text" x-model="theme" placeholder="Например: Быстрые ужины из курицы"
               class="flex-1 px-4 py-3 rounded-xl border border-gray-200 outline-none focus:border-violet-300">
        <select x-model="count" class="px-4 py-3 rounded-xl border border-gray-200 outline-none">
            <option value="3">3 рецепта</option><option value="5" selected>5 рецептов</option><option value="7">7 рецептов</option>
        </select>
        <button @click="generate()" :disabled="loading"
                class="px-6 py-3 rounded-xl bg-gradient-to-r from-violet-300 to-purple-300 text-violet-900 font-bold disabled:opacity-60">
            <span x-show="!loading">✨ Создать</span><span x-show="loading" style="display:none">⏳…</span>
        </button>
    </div>
    <p x-show="error" style="display:none" class="text-rose-500 text-sm mt-3" x-text="error"></p>

    <div x-show="book" x-transition style="display:none" class="mt-8 bg-white rounded-3xl shadow-md p-6 sm:p-8" data-aos="fade-up">
        <h2 class="text-2xl font-extrabold text-gray-800" x-text="book?.title"></h2>
        <p class="text-gray-500 mt-2 italic" x-text="book?.intro"></p>
        <div class="mt-6 space-y-3">
            <template x-for="(r, i) in book?.recipes" :key="i">
                <div class="flex gap-3 p-4 rounded-2xl bg-violet-50">
                    <span class="shrink-0 w-8 h-8 rounded-full bg-violet-200 text-violet-800 font-bold flex items-center justify-center" x-text="i+1"></span>
                    <div class="flex-1">
                        <div class="flex items-center justify-between">
                            <h3 class="font-bold text-gray-800" x-text="r.title"></h3>
                            <span class="text-xs text-gray-400" x-text="'⏱ ' + r.time + ' мин'"></span>
                        </div>
                        <p class="text-sm text-gray-500 mt-1" x-text="r.description"></p>
                        <button @click="location.href='/ai-generator?dish='+encodeURIComponent(r.title)"
                                class="text-xs text-violet-600 font-semibold mt-2 hover:underline">Сгенерировать полный рецепт →</button>
                    </div>
                </div>
            </template>
        </div>
    </div>
</section>
<script>
function aiCookbook() {
    return {
        theme: '', count: 5, book: null, loading: false, error: '',
        async generate() {
            if (!this.theme.trim()) { this.error = 'Укажите тему'; return; }
            this.error = ''; this.loading = true; this.book = null;
            try { this.book = (await CookAPI.post('/api/ai_cookbook.php', { theme: this.theme, count: this.count })).book; }
            catch (e) { this.error = e.message; }
            finally { this.loading = false; }
        }
    };
}
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>