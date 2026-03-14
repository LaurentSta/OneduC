@extends('stagiaire.master')

@section('content')
<div class="max-w-[760px] mx-auto px-6 py-8">
    <div class="rounded-[24px] border border-slate-200 bg-white shadow-sm overflow-hidden">
        <div class="bg-bleuone px-8 py-8 text-white">
            <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-white/70">Session presentielle</p>
            <h1 class="mt-3 text-3xl font-bold">Rejoindre une session</h1>
            <p class="mt-3 text-sm text-white/80">
                Saisissez simplement le code donne par votre formateur, ou scannez le QR code si vous l'avez sous les yeux.
            </p>
        </div>

        <div class="p-8">
            <div class="rounded-[20px] border border-slate-200 bg-slate-50 p-6">
                <form method="POST" action="{{ route('stagiaire.live-quiz.lookup') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label for="live-quiz-code" class="block text-sm font-semibold text-slate-800">
                            Code de session
                        </label>
                        <input
                            id="live-quiz-code"
                            name="code"
                            type="text"
                            inputmode="text"
                            autocomplete="off"
                            autocapitalize="characters"
                            spellcheck="false"
                            value="{{ old('code') }}"
                            placeholder="Ex. A2BC9D"
                            class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-5 py-4 text-lg font-bold uppercase tracking-[0.2em] text-slate-900 placeholder:text-slate-300 focus:border-orangeone focus:outline-none focus:ring-2 focus:ring-orangeone/20"
                            required
                        >

                        @error('code')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <button type="submit"
                                class="inline-flex items-center justify-center rounded-xl bg-orangeone px-6 py-3 text-sm font-bold text-white hover:opacity-90 transition">
                            Rejoindre
                        </button>
                        <p class="text-sm text-slate-500">
                            Le lien QR fonctionne aussi si vous preferez scanner.
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const input = document.getElementById('live-quiz-code');
        if (!input) {
            return;
        }

        input.focus();
        input.addEventListener('input', function () {
            this.value = this.value.toUpperCase().replace(/\s+/g, '');
        });
    });
</script>
@endsection
