@extends('formateur.dashboard')

@section('formateur')
@php
    $creneauxLabels = [
        'matin' => 'Matin',
        'apres_midi' => 'Après-midi',
        'journee' => 'Journée complète',
        'soiree' => 'Soirée',
    ];
    $statutsPresenceLabels = [
        'en_attente' => ['label' => 'En attente', 'class' => 'bg-gray-100 text-gray-700'],
        'present' => ['label' => 'Présent', 'class' => 'bg-vertone/10 text-vertone'],
        'absent' => ['label' => 'Absent', 'class' => 'bg-red-100 text-red-700'],
    ];
@endphp

<div class="w-full px-6 lg:px-8"
     x-data="emargementPilotage({ stateUrl: @js($stateUrl), statut: @js($seance->statut), corrigerUrlTemplate: @js($corrigerUrlTemplate) })"
     x-init="init()">

    <header class="bg-white rounded-[20px] shadow-md px-8 pt-5 pb-6 my-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <nav class="text-sm font-varela text-gray-500 mb-2">
                    <ol class="inline-flex items-center space-x-1">
                        <li><a href="{{ route('formateur.emargement.index', ['group_id' => $group->id]) }}" class="text-orangeone hover:underline">{{ $group->name }}</a></li>
                        <li><span class="mx-2 text-gray-400">/</span></li>
                        <li class="text-gray-400">Émargement</li>
                    </ol>
                </nav>
                <p class="font-raleway text-2xl text-bleuone">
                    Séance du {{ $seance->date->format('d/m/Y') }} — {{ $creneauxLabels[$seance->creneau] ?? $seance->creneau }}
                </p>
                @if ($seance->titre)
                    <p class="text-sm text-gray-500 mt-1">{{ $seance->titre }}</p>
                @endif
            </div>

            <div class="flex items-center gap-3">
                <span class="inline-flex items-center rounded-full px-3 py-1 text-sm font-semibold"
                      :class="{
                          'bg-gray-100 text-gray-700': statut === 'planifiee',
                          'bg-vertone/10 text-vertone': statut === 'ouverte',
                          'bg-bleuone/10 text-bleuone': statut === 'cloturee',
                      }"
                      x-text="{ planifiee: 'Planifiée', ouverte: 'Ouverte', cloturee: 'Clôturée' }[statut] ?? statut">
                </span>

                @if ($seance->statut !== 'ouverte')
                    <form method="POST" action="{{ route('formateur.groupes.emargement.ouvrir', [$group->id, $seance->id]) }}">
                        @csrf
                        <button type="submit" class="btn-oneduc !py-2 !text-sm">Ouvrir la séance</button>
                    </form>
                @endif

                @if ($seance->statut === 'ouverte')
                    <form method="POST" action="{{ route('formateur.groupes.emargement.fermer', [$group->id, $seance->id]) }}">
                        @csrf
                        <button type="submit" class="btn-oneduc-outline !py-2 !text-sm">Clôturer la séance</button>
                    </form>
                @endif

                <a href="{{ route('formateur.groupes.emargement.export-pdf', [$group->id, $seance->id]) }}"
                   class="btn-oneduc-blue !py-2 !text-sm">
                    Exporter en PDF
                </a>
            </div>
        </div>
    </header>

    @if (session('success'))
        <div class="mb-6 rounded-lg bg-vertone/10 text-vertone px-4 py-2 text-sm font-semibold">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-[20px] shadow-md overflow-hidden mb-8">
        <table class="w-full text-sm text-left">
            <thead class="bg-bleuone text-xs uppercase text-white font-bold">
                <tr>
                    <th class="px-6 py-3">Stagiaire</th>
                    <th class="px-6 py-3">Statut</th>
                    <th class="px-6 py-3">Signature</th>
                    <th class="px-6 py-3 text-right">Correction</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                <template x-for="presence in presences" :key="presence.id">
                    <tr>
                        <td class="px-6 py-3 font-medium text-gray-900" x-text="presence.nom"></td>
                        <td class="px-6 py-3">
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold"
                                  :class="{
                                      'bg-gray-100 text-gray-700': presence.statut === 'en_attente',
                                      'bg-vertone/10 text-vertone': presence.statut === 'present',
                                      'bg-red-100 text-red-700': presence.statut === 'absent',
                                  }"
                                  x-text="{ en_attente: 'En attente', present: 'Présent', absent: 'Absent' }[presence.statut] ?? presence.statut">
                            </span>
                        </td>
                        <td class="px-6 py-3">
                            <template x-if="presence.signature">
                                <img :src="presence.signature" alt="Signature" class="h-10 border border-gray-200 rounded bg-white">
                            </template>
                            <template x-if="!presence.signature && presence.statut === 'absent'">
                                <span class="text-xs text-gray-500" x-text="presence.motif_absence || 'Sans motif'"></span>
                            </template>
                            <template x-if="!presence.signature && presence.statut === 'en_attente'">
                                <span class="text-xs text-gray-400">—</span>
                            </template>
                        </td>
                        <td class="px-6 py-3 text-right whitespace-nowrap">
                            <button type="button" @click="marquerAbsent(presence.id)"
                                    class="text-xs font-bold text-red-600 hover:underline mr-3">
                                Marquer absent
                            </button>
                            <button type="button" @click="openSignerModal(presence.id)"
                                    class="text-xs font-bold text-bleuone hover:underline">
                                Signer à sa place
                            </button>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    {{-- Modale de signature déléguée (formateur signe à la place d'un stagiaire) --}}
    <div x-show="correctingPresenceId !== null" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4"
         @keydown.escape.window="closeSignerModal()">
        <div class="bg-white rounded-[16px] shadow-xl w-full max-w-md p-6" @click.outside="closeSignerModal()">
            <h3 class="font-bold text-bleuone mb-3">Signer à la place du stagiaire</h3>
            <canvas x-ref="correctingCanvas" class="w-full h-40 border-2 border-dashed border-gray-300 rounded-lg bg-white touch-none"></canvas>
            <p x-show="correctingError" x-text="correctingError" class="text-xs text-red-600 mt-2"></p>
            <div class="flex items-center justify-between gap-3 mt-4">
                <button type="button" @click="clearCorrectingCanvas()" class="btn-oneduc-outline !py-2 !text-sm">Effacer</button>
                <div class="flex gap-2">
                    <button type="button" @click="closeSignerModal()" class="text-sm text-gray-500 hover:text-gray-700 px-3">Annuler</button>
                    <button type="button" @click="submitCorrectionSignature()" :disabled="correctingEmpty || correctingSubmitting"
                            class="btn-oneduc !py-2 !text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                        <span x-show="!correctingSubmitting">Valider la signature</span>
                        <span x-show="correctingSubmitting">Envoi…</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function emargementPilotage(config) {
    return {
        statut: config.statut,
        stateUrl: config.stateUrl,
        corrigerUrlTemplate: config.corrigerUrlTemplate,
        presences: [],

        correctingPresenceId: null,
        correctingCtx: null,
        correctingDrawing: false,
        correctingEmpty: true,
        correctingSubmitting: false,
        correctingError: null,

        init() {
            this.syncState();
            setInterval(() => this.syncState(), 3000);
        },

        async syncState() {
            try {
                const res = await fetch(this.stateUrl, {
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (!res.ok) return;
                const data = await res.json();
                this.statut = data.statut;
                this.presences = data.presences;
            } catch {}
        },

        corrigerUrl(presenceId) {
            return this.corrigerUrlTemplate.replace('PRESENCE_ID', presenceId);
        },

        async postCorrection(presenceId, body) {
            const res = await fetch(this.corrigerUrl(presenceId), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify(body),
            });
            return res;
        },

        async marquerAbsent(presenceId) {
            const motif = window.prompt('Motif de l\'absence (optionnel) :', '') ?? '';
            const res = await this.postCorrection(presenceId, { statut: 'absent', motif_absence: motif || null });
            if (res.ok) this.syncState();
        },

        openSignerModal(presenceId) {
            this.correctingPresenceId = presenceId;
            this.correctingError = null;
            this.$nextTick(() => {
                const canvas = this.$refs.correctingCanvas;
                const rect = canvas.getBoundingClientRect();
                canvas.width = rect.width;
                canvas.height = rect.height;
                this.correctingCtx = canvas.getContext('2d');
                this.correctingCtx.lineWidth = 2;
                this.correctingCtx.lineCap = 'round';
                this.correctingCtx.strokeStyle = '#172033';
                this.correctingEmpty = true;

                canvas.onpointerdown = (e) => this.correctingStart(e);
                canvas.onpointermove = (e) => this.correctingMove(e);
                window.addEventListener('pointerup', () => { this.correctingDrawing = false; });
            });
        },

        closeSignerModal() {
            this.correctingPresenceId = null;
        },

        correctingPos(e) {
            const rect = this.$refs.correctingCanvas.getBoundingClientRect();
            return { x: e.clientX - rect.left, y: e.clientY - rect.top };
        },

        correctingStart(e) {
            this.correctingDrawing = true;
            this.correctingEmpty = false;
            const { x, y } = this.correctingPos(e);
            this.correctingCtx.beginPath();
            this.correctingCtx.moveTo(x, y);
        },

        correctingMove(e) {
            if (!this.correctingDrawing) return;
            const { x, y } = this.correctingPos(e);
            this.correctingCtx.lineTo(x, y);
            this.correctingCtx.stroke();
        },

        clearCorrectingCanvas() {
            const canvas = this.$refs.correctingCanvas;
            this.correctingCtx.clearRect(0, 0, canvas.width, canvas.height);
            this.correctingEmpty = true;
        },

        async submitCorrectionSignature() {
            if (this.correctingEmpty || this.correctingSubmitting) return;
            this.correctingSubmitting = true;
            this.correctingError = null;
            try {
                const signature = this.$refs.correctingCanvas.toDataURL('image/png');
                const res = await this.postCorrection(this.correctingPresenceId, { statut: 'present', signature });
                if (res.ok) {
                    this.closeSignerModal();
                    this.syncState();
                    return;
                }
                const data = await res.json().catch(() => ({}));
                this.correctingError = data.message || data.errors?.signature?.[0] || 'Une erreur est survenue.';
            } catch {
                this.correctingError = 'Une erreur réseau est survenue.';
            } finally {
                this.correctingSubmitting = false;
            }
        },
    };
}
</script>
@endsection
