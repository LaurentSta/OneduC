@props(['submitUrl'])

<div {{ $attributes }} x-data="signaturePadComponent(@js($submitUrl))" x-init="init()">
    <canvas x-ref="canvas" class="w-full h-48 border-2 border-dashed border-gray-300 rounded-lg bg-white touch-none"></canvas>

    <div class="flex flex-wrap items-center gap-3 mt-3">
        <button type="button" @click="clear()" class="btn-oneduc-outline !py-2 !text-sm">Effacer</button>
        <button type="button" @click="submit()" :disabled="empty || submitting"
                class="btn-oneduc !py-2 !text-sm disabled:opacity-50 disabled:cursor-not-allowed">
            <span x-show="!submitting">Valider ma signature</span>
            <span x-show="submitting">Envoi…</span>
        </button>
    </div>

    <p x-show="error" x-text="error" class="text-xs text-red-600 mt-2"></p>
</div>

<script>
function signaturePadComponent(submitUrl) {
    return {
        submitUrl,
        empty: true,
        submitting: false,
        error: null,
        ctx: null,
        drawing: false,

        init() {
            const canvas = this.$refs.canvas;

            const resize = () => {
                const rect = canvas.getBoundingClientRect();
                canvas.width = rect.width;
                canvas.height = rect.height;
                this.ctx = canvas.getContext('2d');
                this.ctx.lineWidth = 2;
                this.ctx.lineCap = 'round';
                this.ctx.strokeStyle = '#172033';
            };
            resize();
            window.addEventListener('resize', resize);

            canvas.addEventListener('pointerdown', (e) => this.startStroke(e));
            canvas.addEventListener('pointermove', (e) => this.moveStroke(e));
            window.addEventListener('pointerup', () => { this.drawing = false; });
        },

        pos(e) {
            const rect = this.$refs.canvas.getBoundingClientRect();
            return { x: e.clientX - rect.left, y: e.clientY - rect.top };
        },

        startStroke(e) {
            this.drawing = true;
            this.empty = false;
            const { x, y } = this.pos(e);
            this.ctx.beginPath();
            this.ctx.moveTo(x, y);
        },

        moveStroke(e) {
            if (!this.drawing) return;
            const { x, y } = this.pos(e);
            this.ctx.lineTo(x, y);
            this.ctx.stroke();
        },

        clear() {
            const canvas = this.$refs.canvas;
            this.ctx.clearRect(0, 0, canvas.width, canvas.height);
            this.empty = true;
            this.error = null;
        },

        async submit() {
            if (this.empty || this.submitting) return;
            this.submitting = true;
            this.error = null;
            try {
                const res = await fetch(this.submitUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ signature: this.$refs.canvas.toDataURL('image/png') }),
                });
                if (res.ok) {
                    window.location.reload();
                    return;
                }
                const data = await res.json().catch(() => ({}));
                this.error = data.message || data.errors?.signature?.[0] || 'Une erreur est survenue.';
            } catch (e) {
                this.error = 'Une erreur réseau est survenue.';
            } finally {
                this.submitting = false;
            }
        },
    };
}
</script>
