/**
 * CookAI — Alpine-компонент генератора рецептов
 */
function aiGenerator() {
    return {
        cuisines: ['Русская','Итальянская','Японская','Мексиканская','Индийская','Тайская',
            'Французская','Грузинская','Средиземноморская','Американская','Китайская','Корейская'],
        form: { dish:'', cuisine:'', diet:'', difficulty:'', servings:2, exclude:'', ingredients:'' },
        recipe: null,
        loading: false,
        saving: false,
        error: '',

        init() {
            const params = new URLSearchParams(location.search);
            const dish = params.get('dish');
            if (dish) {
                this.form.dish = dish;
                this.generate();
            }
        },

        // ... остальные методы (generate, saveGenerated) без изменений

        async generate() {
            this.error = '';
            if (!this.form.dish.trim() && !this.form.cuisine) {
                this.error = 'Опишите блюдо или выберите кухню.';
                return;
            }
            this.loading = true;
            this.recipe = null;
            try {
                const res = await CookAPI.post('/api/ai_generate_recipe.php', this.form);
                this.recipe = res.recipe;
                this.$nextTick(() => window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' }));
            } catch (e) {
                this.error = e.message || 'Не удалось сгенерировать рецепт.';
            } finally {
                this.loading = false;
            }
        },

        async saveGenerated() {
            if (!this.recipe) return;
            this.saving = true;
            try {
                const res = await CookAPI.post('/api/save_recipe.php', this.recipe);
                toast('Рецепт сохранён!', 'success');
                if (res.id) setTimeout(() => location.href = '/recipe/' + res.id, 800);
            } catch (e) {
                toast(e.message.includes('401') ? 'Войдите, чтобы сохранять' : 'Ошибка сохранения', 'error');
            } finally {
                this.saving = false;
            }
        }
    };
}