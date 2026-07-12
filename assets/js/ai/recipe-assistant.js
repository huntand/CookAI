/**
 * CookAI — AI-ассистент по конкретному рецепту
 */
function recipeAssistant(recipeId) {
    return {
        recipeId,
        open: false,
        input: '',
        loading: false,
        messages: [
            { role: 'assistant', text: 'Привет! Спросите меня об этом рецепте: замены, техника, время, порции 🍳' }
        ],

        async send() {
            const text = this.input.trim();
            if (!text || this.loading) return;
            this.messages.push({ role: 'user', text });
            this.input = '';
            this.loading = true;
            this.scrollDown();
            try {
                const history = this.messages.slice(1, -1).map(m => ({ role: m.role, text: m.text }));
                const res = await CookAPI.post('/api/ai_recipe_chat.php', {
                    recipe_id: this.recipeId, message: text, history
                });
                this.messages.push({ role: 'assistant', text: res.reply });
            } catch (e) {
                this.messages.push({ role: 'assistant', text: '⚠️ ' + (e.message || 'Ошибка ответа') });
            } finally {
                this.loading = false;
                this.scrollDown();
            }
        },
        scrollDown() {
            this.$nextTick(() => { const el = this.$refs.scroll; if (el) el.scrollTop = el.scrollHeight; });
        }
    };
}