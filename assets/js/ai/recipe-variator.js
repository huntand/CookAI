// assets/js/ai/recipe-variator.js
function recipeVariator() {
    return {
        dish: '', mode: 'здоровая версия', recipe: null, loading: false, error: '',
        modes: ['здоровая версия','веганская','острая','бюджетная','праздничная','детская'],
        async generate() {
            if (!this.dish.trim()) { this.error = 'Укажите блюдо'; return; }
            this.error = ''; this.loading = true; this.recipe = null;
            try { this.recipe = (await CookAPI.post('/api/ai_variator.php', { dish: this.dish, mode: this.mode })).recipe; }
            catch (e) { this.error = e.message; } finally { this.loading = false; }
        }
    };
}