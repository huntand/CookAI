<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'Кулинарный советник';
require_once __DIR__ . '/../includes/header.php';
?>
<section class="max-w-3xl mx-auto px-4 sm:px-6 py-8" x-data="culinaryAdvisor()">
    <div data-aos="fade-up" class="text-center mb-6">
        <span class="text-4xl">🧑‍🍳</span>
        <h1 class="text-3xl font-extrabold text-gray-800 mt-2">Кулинарный советник</h1>
        <p class="text-gray-500 text-sm mt-1">Задайте любой вопрос о готовке — AI подскажет.</p>
    </div>

    <div class="bg-white rounded-3xl shadow-md flex flex-col h-[70vh]">
        <!-- Сообщения -->
        <div class="flex-1 overflow-y-auto p-4 sm:p-6 space-y-4" x-ref="scroll">
            <template x-for="(m, i) in messages" :key="i">
                <div :class="m.role === 'user' ? 'flex justify-end' : 'flex justify-start'">
                    <div :class="m.role === 'user'
                        ? 'bg-gradient-to-r from-amber-200 to-orange-200 text-amber-900'
                        : 'bg-gray-50 text-gray-700'"
                        class="max-w-[80%] px-4 py-2.5 rounded-2xl text-sm whitespace-pre-wrap"
                        x-text="m.text"></div>
                </div>
            </template>
            <div x-show="loading" style="display:none" class="flex justify-start">
                <div class="bg-gray-50 text-gray-400 px-4 py-2.5 rounded-2xl text-sm">Печатает… ⏳</div>
            </div>
        </div>

        <!-- Быстрые вопросы -->
        <div class="px-4 pb-2 flex flex-wrap gap-2" x-show="messages.length <= 1">
            <template x-for="q in suggestions" :key="q">
                <button @click="input=q; send()" class="px-3 py-1.5 rounded-full bg-amber-50 text-amber-700 text-xs hover:bg-amber-100 transition" x-text="q"></button>
            </template>
        </div>

        <!-- Ввод -->
        <div class="border-t border-gray-100 p-3 flex gap-2">
            <input type="text" x-model="input" @keydown.enter="send()" placeholder="Спросите что-нибудь…"
                   class="flex-1 px-4 py-3 rounded-xl border border-gray-200 outline-none focus:border-amber-300">
            <button @click="send()" :disabled="loading"
                    class="px-5 py-3 rounded-xl bg-gradient-to-r from-amber-300 to-orange-300 text-amber-900 font-bold hover:shadow-md transition disabled:opacity-60">
                ➤
            </button>
        </div>
    </div>
</section>

<script src="<?= url('assets/js/ai/culinary-advisor.js') ?>"></script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>