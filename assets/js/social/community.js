function communityDetail(id) {
    return {
        id, posts: [], newPost: '', joined: false,
        async load() {
            try {
                const res = await CookAPI.get('/api/communities.php?id=' + this.id);
                this.posts = res.posts || [];
            } catch (_) {}
        },
        async join() {
            try { await CookAPI.post('/api/communities.php?join=' + this.id, {}); this.joined = true; toast('Вы вступили!', 'success'); }
            catch (e) { toast(e.message, 'error'); }
        },
        async post() {
            if (!this.newPost.trim()) return;
            try {
                await CookAPI.post('/api/community_posts.php', { community_id: this.id, content: this.newPost });
                this.newPost = ''; CookAPI.clearCache(); await this.load(); toast('Опубликовано!', 'success');
            } catch (e) { toast(e.message, 'error'); }
        }
    };
}