/**
 * CookAI — отзывы и рейтинг
 */
function reviews(recipeId) {
    return {
        recipeId,
        comments: [],
        avg: 0,
        count: 0,
        sending: false,
        form: { text: '', rating: 0 },

        stars(n) {
            n = Math.round(Number(n) || 0);
            return '★'.repeat(n) + '☆'.repeat(5 - n);
        },

        async load() {
            try {
                const res = await CookAPI.get('/api/comments.php?recipe_id=' + this.recipeId);
                this.comments = res.comments || [];
                this.avg = res.avg || 0;
                this.count = res.count || 0;
            } catch (_) {}
        },

        async submit() {
            if (!this.form.text.trim() && !this.form.rating) {
                toast('Напишите отзыв или поставьте оценку', 'error');
                return;
            }
            this.sending = true;
            try {
                await CookAPI.post('/api/comments.php', {
                    recipe_id: this.recipeId,
                    text: this.form.text,
                    rating: this.form.rating
                });
                toast('Спасибо за отзыв!', 'success');
                this.form = { text: '', rating: 0 };
                CookAPI.clearCache();
                await this.load();
            } catch (e) {
                toast(e.message || 'Ошибка отправки', 'error');
            } finally {
                this.sending = false;
            }
        }
    };
}