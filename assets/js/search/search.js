function searchPage() {
    return {
        cuisines: ['Русская','Итальянская','Японская','Мексиканская','Индийская','Тайская',
            'Французская','Грузинская','Средиземноморская','Американская','Китайская','Корейская'],
        q: '', cuisine: '', results: [], loading: false,

        init() {
            const params = new URLSearchParams(location.search);
            this.q = params.get('q') || '';
            this.search();
        },
        async search() {
            this.loading = true;
            try {
                const url = `/api/recipes.php?limit=40&q=${encodeURIComponent(this.q)}&cuisine=${encodeURIComponent(this.cuisine)}`;
                const res = await CookAPI.get(url, { cache: true, ttl: 30000 });
                this.results = res.recipes || [];
            } catch (e) {
                toast('Ошибка поиска', 'error');
            } finally {
                this.loading = false;
            }
        }
    };
}