/**
 * CookAI — список покупок (модальное окно, Alpine)
 * shoppingList(ingredients) — ingredients: [{name, amount, unit}]
 */
function shoppingList(initial) {
    return {
        open: false,
        items: (initial || []).map((i, idx) => ({
            id: idx,
            text: `${i.name} — ${i.amount} ${i.unit || ''}`.trim(),
            checked: false,
            editing: false
        })),
        newItem: '',
        email: '',
        sending: false,

        add() {
            const v = this.newItem.trim();
            if (!v) return;
            this.items.push({ id: Date.now(), text: v, checked: false, editing: false });
            this.newItem = '';
        },
        remove(id) { this.items = this.items.filter(i => i.id !== id); },
        startEdit(item) { item.editing = true; },
        saveEdit(item) { item.editing = false; if (!item.text.trim()) this.remove(item.id); },

        get remaining() { return this.items.filter(i => !i.checked).length; },

        copyList() {
            const text = this.items.map(i => (i.checked ? '✓ ' : '• ') + i.text).join('\n');
            navigator.clipboard.writeText(text).then(() => toast('Список скопирован', 'success'));
        },

        async sendEmail() {
            if (!this.email.trim()) { toast('Введите email', 'error'); return; }
            this.sending = true;
            try {
                await CookAPI.post('/api/send_shopping_list.php', {
                    email: this.email,
                    items: this.items.map(i => i.text)
                });
                toast('Список отправлен на почту!', 'success');
            } catch (e) {
                toast(e.message || 'Не удалось отправить', 'error');
            } finally {
                this.sending = false;
            }
        }
    };
}