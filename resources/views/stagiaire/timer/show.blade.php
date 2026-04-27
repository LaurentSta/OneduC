@extends('stagiaire.master')

@section('content')
<div
    class="mx-auto max-w-[700px] px-6 py-10"
    x-data="timerView({
        status: '{{ $timer->status }}',
        remaining: {{ $timer->remaining_seconds }},
        duration: {{ $timer->duration_seconds }},
        label: @js($timer->label ?? ''),
        routes: {{ Js::from($routes) }}
    })"
    x-init="init()"
>
    {{-- Groupe --}}
    <div class="mb-6 text-center">
        <p class="text-sm font-semibold text-gray-500">{{ $group->name }}</p>
        <p x-show="label" x-text="label" class="mt-1 text-xl font-bold text-bleuone"></p>
    </div>

    {{-- Grande horloge --}}
    <div class="bg-white rounded-[28px] shadow-md p-8 flex flex-col items-center gap-6">

        {{-- Statut --}}
        <div :class="{
            'bg-green-100 text-green-700': isRunning,
            'bg-amber-100 text-amber-700': isPaused,
            'bg-red-100 text-red-700': isFinished,
            'bg-gray-100 text-gray-500': isIdle
        }" class="rounded-full px-4 py-1.5 text-xs font-bold uppercase tracking-widest">
            <span x-text="statusLabel"></span>
        </div>

        {{-- Cercle SVG + temps --}}
        <div class="relative flex items-center justify-center">
            <svg viewBox="0 0 260 260" class="w-64 h-64 -rotate-90">
                <circle cx="130" cy="130" r="114" fill="none" stroke="#e2e8f0" stroke-width="14"/>
                <circle cx="130" cy="130" r="114" fill="none"
                    :stroke="isFinished ? '#ef4444' : (isPaused ? '#f59e0b' : '#004461')"
                    stroke-width="14"
                    stroke-linecap="round"
                    :stroke-dasharray="`${2 * Math.PI * 114}`"
                    :stroke-dashoffset="`${2 * Math.PI * 114 * (1 - progress)}`"
                    class="transition-all duration-1000"/>
            </svg>
            <div class="absolute inset-0 flex flex-col items-center justify-center">
                <span x-text="displayTime" class="font-mono text-6xl font-bold text-bleuone tabular-nums tracking-tight"></span>
                <span x-show="isFinished" class="mt-2 text-sm font-semibold text-red-500 animate-pulse">Temps écoulé !</span>
                <span x-show="isIdle" class="mt-2 text-xs text-gray-400">En attente du formateur</span>
            </div>
        </div>

        {{-- Indicateur de sync --}}
        <p class="text-xs text-gray-400 text-center">
            Synchronisé automatiquement toutes les 3 secondes
        </p>
    </div>

    {{-- Retour --}}
    <div class="mt-6 text-center">
        <a href="{{ route('stagiaire.dashboard') }}" class="text-sm text-gray-500 hover:text-bleuone transition">
            ← Retour au tableau de bord
        </a>
    </div>
</div>

<script>
function timerView(config) {
    return {
        status: config.status,
        remaining: config.remaining,
        duration: config.duration,
        label: config.label,

        get displayTime() {
            const r = Math.max(0, this.remaining);
            return `${String(Math.floor(r / 60)).padStart(2, '0')}:${String(r % 60).padStart(2, '0')}`;
        },
        get progress() {
            return this.duration > 0 ? Math.max(0, Math.min(1, this.remaining / this.duration)) : 0;
        },
        get isRunning()  { return this.status === 'running'; },
        get isPaused()   { return this.status === 'paused'; },
        get isIdle()     { return this.status === 'idle'; },
        get isFinished() { return this.status === 'finished'; },
        get statusLabel() {
            return { running: 'En cours', paused: 'En pause', idle: 'En attente', finished: 'Terminé !' }[this.status] ?? '';
        },

        init() {
            setInterval(() => {
                if (this.status === 'running' && this.remaining > 0) {
                    this.remaining--;
                    if (this.remaining === 0) this.status = 'finished';
                }
            }, 1000);

            setInterval(() => this.syncStatus(), 3000);
        },

        async syncStatus() {
            try {
                const res = await fetch(config.routes.status, {
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (!res.ok) return;
                const data = await res.json();
                this.status = data.status;
                this.remaining = data.remaining_seconds;
                this.duration = data.duration_seconds;
                this.label = data.label ?? '';
            } catch {}
        },
    };
}
</script>
@endsection
