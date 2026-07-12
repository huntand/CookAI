/**
 * CookAI — «Что приготовить?» по ингредиентам
 */
function whatToCook() {
    return {
        ingredientInput: '',
        ingredients: [],
        popular: ['Яйца','Курица','Картофель','Помидоры','Сыр','Рис','Макароны','Лук','Морковь','Молоко'],
        diet: '',
        time: '',
        dishes: [],
        loading: false,
        error: '',

        addIngredient() {
            const v = this.ingredientInput.trim();
            if (v && !this.ingredients.includes(v)) this.ingredients.push(v);
            this.ingredientInput = '';
        },
        quickAdd(p) {
            if (!this.ingredients.includes(p)) this.ingredients.push(p);
        },

        async find() {
            if (!this.ingredients.length) return;
            this.error = '';
            this.loading = true;
            this.dishes = [];
            try {
                const res = await CookAPI.post('/api/ai_what_to_cook.php', {
                    ingredients: this.ingredients,
                    diet: this.diet,
                    time: this.time
                });
                this.dishes = res.dishes || [];
                if (!this.dishes.length) this.error = 'Не нашлось подходящих блюд. Добавьте больше продуктов.';
                this.$nextTick(() => window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' }));
            } catch (e) {
                this.error = e.message || 'Ошибка подбора.';
            } finally {
                this.loading = false;
            }
        },

        generateFull(title) {
            // Передаём блюдо в генератор через URL
            location.href = '/ai-generator?dish=' + encodeURIComponent(title);
        }
    };
}