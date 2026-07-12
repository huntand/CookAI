/**
 * CookAI — таймер для шагов рецепта (Alpine-компонент)
 */
function stepTimer(minutes) {
    return {
        total: (Number(minutes) || 0) * 60,
        left: (Number(minutes) || 0) * 60,
        running: false,
        interval: null,

        get display() {
            const m = Math.floor(this.left / 60);
            const s = this.left % 60;
            return `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
        },
        get progress() {
            return this.total ? ((this.total - this.left) / this.total) * 100 : 0;
        },

        toggle() {
            if (this.running) { this.pause(); return; }
            if (this.left === 0) this.left = this.total;
            this.running = true;
            this.interval = setInterval(() => {
                this.left--;
                if (this.left <= 0) {
                    this.left = 0;
                    this.finish();
                }
            }, 1000);
        },
        pause() {
            this.running = false;
            clearInterval(this.interval);
        },
        reset() {
            this.pause();
            this.left = this.total;
        },
        finish() {
            this.pause();
            try {
                const a = new AudioContext();
                const o = a.createOscillator();
                o.connect(a.destination);
                o.frequency.value = 880;
                o.start(); setTimeout(() => o.stop(), 500);
            } catch (_) {}
            if (window.toast) toast('⏰ Время вышло!', 'success');
        }
    };
}