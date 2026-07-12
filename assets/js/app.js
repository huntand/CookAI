/**
 * CookAI — общая клиентская логика (Alpine-хелперы, тосты)
 */

// Глобальный тост
window.toast = function (message, type = 'info') {
    const colors = {
        info: 'bg-amber-100 text-amber-800 border-amber-200',
        success: 'bg-emerald-100 text-emerald-800 border-emerald-200',
        error: 'bg-rose-100 text-rose-800 border-rose-200',
    };
    const el = document.createElement('div');
    el.className = `fixed bottom-5 right-5 z-[100] px-4 py-3 rounded-xl border shadow-lg text-sm font-semibold ${colors[type] || colors.info} transition-all`;
    el.style.opacity = '0';
    el.style.transform = 'translateY(10px)';
    el.textContent = message;
    document.body.appendChild(el);
    requestAnimationFrame(() => { el.style.opacity = '1'; el.style.transform = 'translateY(0)'; });
    setTimeout(() => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(10px)';
        setTimeout(() => el.remove(), 300);
    }, 3000);
};

// Сохранение рецепта (кнопка на карточке / странице)
window.saveRecipe = async function (recipeId, btn) {
    try {
        await CookAPI.post('/api/saved_recipes.php', { recipe_id: recipeId });
        if (btn) { btn.classList.add('text-rose-500'); btn.dataset.saved = '1'; }
        toast('Рецепт сохранён', 'success');
    } catch (e) {
        toast(e.message.includes('401') ? 'Войдите, чтобы сохранять' : 'Не удалось сохранить', 'error');
    }
};