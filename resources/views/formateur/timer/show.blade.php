@extends('formateur.dashboard')

@section('formateur')
@php
    $routesJson = json_encode($routes, JSON_UNESCAPED_SLASHES);
    $initialStatus = $timer->status;
    $initialRemaining = $timer->remaining_seconds;
    $initialDuration = $timer->duration_seconds;
    $initialLabel = $timer->label ?? '';

    $presets = [
        ['label' => '5 min',  'seconds' => 300],
        ['label' => '10 min', 'seconds' => 600],
        ['label' => '15 min', 'seconds' => 900],
        ['label' => '20 min', 'seconds' => 1200],
        ['label' => '25 min', 'seconds' => 1500],
        ['label' => '30 min', 'seconds' => 1800],
        ['label' => '45 min', 'seconds' => 2700],
        ['label' => '1 h',    'seconds' => 3600],
    ];
@endphp

<div
    class="w-full px-6 lg:px-8"
    x-data="timerApp({
        status: '{{ $initialStatus }}',
        remaining: {{ $initialRemaining }},
        duration: {{ $initialDuration }},
        label: @js($initialLabel),
        routes: {{ Js::from($routes) }}
    })"
    x-init="init()"
>
    {{-- En-tête --}}
    <header class="bg-white rounded-[20px] shadow-md px-8 pt-5 pb-6 my-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <nav class="text-sm font-varela text-gray-500 mb-2">
                    <ol class="inline-flex items-center space-x-1">
                        <li><a href="{{ route('formateur.outils.index') }}" class="text-orangeone hover:underline">Outils numériques</a></li>
                        <li><span class="mx-2 text-gray-400">/</span></li>
                        <li><a href="{{ route('formateur.groupes.edit', $group) }}" class="text-orangeone hover:underline">{{ $group->name }}</a></li>
                        <li><span class="mx-2 text-gray-400">/</span></li>
                        <li class="text-gray-400">Minuteur</li>
                    </ol>
                </nav>
                <p class="font-raleway text-2xl text-bleuone">Minuteur collaboratif</p>
                <p class="text-sm text-gray-500 mt-1">Lancez un compte à rebours visible en temps réel par tous les stagiaires du groupe <strong>{{ $group->name }}</strong>.</p>
            </div>
            <a href="{{ $stagiaire_url }}" target="_blank" rel="noopener"
               class="inline-flex items-center gap-2 rounded-[10px] border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                Vue stagiaire
            </a>
        </div>
    </header>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">

        {{-- Panneau de configuration --}}
        <div class="bg-white rounded-[20px] shadow-md p-6 space-y-5">
            <p class="font-varela text-base font-bold text-bleuone">Configuration</p>

            {{-- Durée --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Durée</label>
                <div class="flex flex-wrap gap-2 mb-3">
                    @foreach($presets as $preset)
                        <button type="button"
                            @click="setPreset({{ $preset['seconds'] }})"
                            :class="inputDuration == {{ $preset['seconds'] }} ? 'bg-bleuone text-white border-bleuone' : 'bg-white text-gray-700 border-gray-300 hover:border-bleuone hover:text-bleuone'"
                            class="rounded-[8px] border px-3 py-1.5 text-sm font-semibold transition">
                            {{ $preset['label'] }}
                        </button>
                    @endforeach
                </div>
                <div class="flex items-center gap-2">
                    <input type="number" x-model="inputMinutes" min="1" max="120"
                           class="w-20 rounded-[8px] border border-gray-300 px-3 py-2 text-sm focus:border-bleuone focus:outline-none focus:ring-0"
                           placeholder="min">
                    <span class="text-sm text-gray-500">minutes</span>
                </div>
            </div>

            {{-- Libellé --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Libellé <span class="font-normal text-gray-400">(optionnel)</span></label>
                <input type="text" x-model="inputLabel" maxlength="100"
                       placeholder="Ex : Pause café, Exercice 1, Évaluation..."
                       class="w-full rounded-[10px] border border-gray-300 px-4 py-2.5 text-sm focus:border-bleuone focus:outline-none focus:ring-0">
            </div>

            {{-- Bouton configurer --}}
            <button type="button" @click="configure()"
                    :disabled="isRunning"
                    :class="isRunning ? 'opacity-40 cursor-not-allowed' : 'hover:bg-bleuone/90'"
                    class="w-full rounded-[10px] bg-bleuone px-4 py-2.5 text-sm font-bold text-white transition">
                Appliquer la configuration
            </button>

            {{-- Lien stagiaires --}}
            <div class="rounded-[10px] border border-gray-200 bg-gray-50 px-4 py-3">
                <p class="text-xs font-semibold text-gray-500 mb-1">Lien à partager aux stagiaires</p>
                <div class="flex items-center gap-2">
                    <code class="flex-1 truncate text-xs text-gray-700">{{ $stagiaire_url }}</code>
                    <button type="button"
                        onclick="navigator.clipboard.writeText('{{ $stagiaire_url }}').then(() => this.textContent = 'Copié ✓').catch(() => {}); setTimeout(() => this.textContent = 'Copier', 2000)"
                        class="shrink-0 rounded-[6px] bg-bleuone/10 px-2.5 py-1 text-xs font-bold text-bleuone hover:bg-bleuone/20 transition">
                        Copier
                    </button>
                </div>
            </div>
        </div>

        {{-- Affichage du timer --}}
        <div class="bg-white rounded-[20px] shadow-md p-6 flex flex-col items-center justify-center gap-6">

            {{-- Badge statut --}}
            <div class="flex items-center gap-2">
                <span :class="{
                    'bg-green-100 text-green-700': isRunning,
                    'bg-amber-100 text-amber-700': isPaused,
                    'bg-red-100 text-red-700': isFinished,
                    'bg-gray-100 text-gray-500': isIdle
                }" class="rounded-full px-3 py-1 text-xs font-bold uppercase tracking-wider">
                    <span x-text="statusLabel"></span>
                </span>
            </div>

            {{-- Libellé actif --}}
            <p x-show="label" x-text="label" class="text-lg font-semibold text-bleuone text-center"></p>

            {{-- Cercle SVG + temps --}}
            <div class="relative flex items-center justify-center">
                <svg viewBox="0 0 220 220" class="w-56 h-56 -rotate-90">
                    <circle cx="110" cy="110" r="96" fill="none" stroke="#e2e8f0" stroke-width="14"/>
                    <circle cx="110" cy="110" r="96" fill="none"
                        :stroke="isFinished ? '#ef4444' : (isPaused ? '#f59e0b' : '#004461')"
                        stroke-width="14"
                        stroke-linecap="round"
                        :stroke-dasharray="`${2 * Math.PI * 96}`"
                        :stroke-dashoffset="`${2 * Math.PI * 96 * (1 - progress)}`"
                        class="transition-all duration-1000"/>
                </svg>
                <div class="absolute inset-0 flex flex-col items-center justify-center rotate-0">
                    <span x-text="displayTime" class="font-mono text-5xl font-bold text-bleuone tabular-nums"></span>
                    <span class="text-xs text-gray-400 mt-1" x-text="isFinished ? 'Terminé !' : ''"></span>
                </div>
            </div>

            {{-- Boutons de contrôle --}}
            <div class="flex items-center gap-3 flex-wrap justify-center">
                <button type="button" @click="isRunning ? pause() : start()"
                        x-show="!isFinished"
                        :class="isRunning
                            ? 'bg-amber-500 hover:bg-amber-600'
                            : 'bg-green-600 hover:bg-green-700'"
                        class="inline-flex items-center gap-2 rounded-[10px] px-5 py-2.5 text-sm font-bold text-white transition">
                    <span x-text="isRunning ? '⏸ Pause' : (isPaused ? '▶ Reprendre' : '▶ Démarrer')"></span>
                </button>

                <button type="button" @click="reset()"
                        x-show="!isIdle"
                        class="inline-flex items-center gap-2 rounded-[10px] border border-gray-300 bg-white px-5 py-2.5 text-sm font-bold text-gray-700 hover:bg-gray-50 transition">
                    ↺ Réinitialiser
                </button>
            </div>

        </div>
    </div>
</div>

<script>
function timerApp(config) {
    return {
        status: config.status,
        remaining: config.remaining,
        duration: config.duration,
        label: config.label,
        routes: config.routes,

        inputMinutes: Math.round(config.duration / 60),
        inputDuration: config.duration,
        inputLabel: config.label,

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
            return { running: 'En cours', paused: 'En pause', idle: 'En attente', finished: 'Terminé' }[this.status] ?? this.status;
        },

        setPreset(seconds) {
            this.inputDuration = seconds;
            this.inputMinutes = Math.round(seconds / 60);
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
                const res = await fetch(this.routes.status, {
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (!res.ok) return;
                this.applyData(await res.json());
            } catch {}
        },

        applyData(data) {
            this.status = data.status;
            this.remaining = data.remaining_seconds;
            this.duration = data.duration_seconds;
            this.label = data.label ?? '';
        },

        async post(url, body = {}) {
            try {
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify(body),
                });
                if (res.ok) this.applyData(await res.json());
            } catch {}
        },

        configure() {
            const secs = Math.max(30, Math.min(7200, (parseInt(this.inputMinutes) || 25) * 60));
            this.inputDuration = secs;
            this.post(this.routes.configure, { duration_seconds: secs, label: this.inputLabel });
        },
        start()  { this.post(this.routes.start); },
        pause()  { this.post(this.routes.pause); },
        reset()  { this.post(this.routes.reset); },
    };
}
</script>
@endsection
