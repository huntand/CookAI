function createRecipe() {
    return {
        cuisines: ['Русская','Итальянская','Японская','Мексиканская','Индийская','Тайская',
            'Французская','Грузинская','Средиземноморская','Американская','Китайская','Корейская','Другая'],
        r: {
            title:'', description:'', image_url:'', cuisine:'Русская', difficulty:'Легко',
            prep_time:0, cook_time:0, servings:2, calories:0, proteins:0, fats:0, carbs:0,
            ingredients: [{name:'',amount:'',unit:''}],
            steps: [{order:1,instruction:'',timer_minutes:0,tip:''}],
            tags: [], diet_type: [], season: []
        },
        saving: false,

        async save() {
            if (!this.r.title.trim()) { toast('Введите название', 'error'); return; }
            this.saving = true;
            try {
                const payload = { ...this.r };
                payload.ingredients = payload.ingredients.filter(i => i.name.trim());
                payload.steps = payload.steps.filter(s => s.instruction.trim());
                const res = await CookAPI.post('/api/save_recipe.php', payload);
                toast('Рецепт опубликован!', 'success');
                setTimeout(() => location.href = '/recipe/' + res.id, 700);
            } catch (e) {
                toast(e.message || 'Ошибка сохранения', 'error');
            } finally {
                this.saving = false;
            }
        }
    };
}