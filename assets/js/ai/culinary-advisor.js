/**
 * CookAI — чат кулинарного советника
 */
function culinaryAdvisor() {
    return {
        messages: [
            { role: 'assistant', text: 'Привет! Я ваш кулинарный AI-ассистент 🍳 Спросите меня о рецептах, заменах продуктов или советах по готовке.' }
        ],
        suggestions: [
            'Чем заменить яйца в выпечке?',
            'Что приготовить на ужин за 20 минут?',
            'Как сделать мясо мягким?',
            'Идеи для здорового завтрака'
        ],
        input: '',
        loading: false,

        async send() {
            const text = this.input.trim();
            if (!text || this.loading) return;
            this.messages.push({ role: 'user', text });
            this.input = '';
            this.loading = true;
            this.scrollDown();

            try {
                const history = this.messages
                    .filter(m => m.role !== 'assistant' || m !== this.messages[0])
                    .slice(-8)
                    .map(m => ({ role: m.role, text: m.text }));

                const res = await CookAPI.post('/api/ai_chat.php', {
                    message: text,
                    history: history.slice(0, -1)
                });
                this.messages.push({ role: 'assistant', text: res.reply });
            } catch (e) {
                this.messages.push({ role: 'assistant', text: '⚠️ ' + (e.message || 'Не удалось получить ответ. Попробуйте ещё раз.') });
            } finally {
                this.loading = false;
                this.scrollDown();
            }
        },

        scrollDown() {
            this.$nextTick(() => {
                const el = this.$refs.scroll;
                if (el) el.scrollTop = el.scrollHeight;
            });
        }
    };
}