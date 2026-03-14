@extends('stagiaire.formations.master_lecon_evaluation')

@section('title', 'Rejoindre une session presentielle')

@section('content')
<div class="max-w-[860px] mx-auto px-6 py-10">
    <div class="rounded-[24px] border border-slate-200 bg-white shadow-sm overflow-hidden">
        <div class="bg-bleuone px-8 py-8 text-white">
            <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-white/70">Session presentielle</p>
            <h1 class="mt-3 text-3xl font-bold">{{ $lecture->lecture_title ?? 'Lecon' }}</h1>
            <p class="mt-3 text-sm text-white/80">
                Vous allez rejoindre la session live du formateur avec votre compte stagiaire.
            </p>
        </div>

        <div class="p-8">
            <div class="grid gap-4 rounded-[20px] border border-slate-200 bg-slate-50 p-6 md:grid-cols-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Code</p>
                    <p class="mt-2 text-2xl font-bold tracking-[0.26em] text-slate-900">{{ $session->access_code }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Lecon</p>
                    <p class="mt-2 text-base font-semibold text-slate-900">{{ $lecture->lecture_title }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Etat</p>
                    <p class="mt-2 text-base font-semibold text-slate-900">
                        {{ $session->isClosed() ? 'Terminee' : ($session->isWaiting() ? 'En attente de demarrage' : 'En cours') }}
                    </p>
                </div>
            </div>

            @if ($session->isClosed())
                <div class="mt-6 rounded-2xl border border-amber-200 bg-amber-50 px-6 py-5 text-amber-800">
                    Cette session est deja terminee. Demandez au formateur de relancer une nouvelle session si besoin.
                </div>
            @else
                <div class="mt-6 rounded-2xl border border-slate-200 bg-white px-6 py-6">
                    <h2 class="text-xl font-bold text-slate-900">Pret a rejoindre ?</h2>
                    <p class="mt-2 text-sm leading-6 text-slate-600">
                        Une fois dans la session, les questions apparaitront au rythme du formateur. Vos reponses seront enregistrees sur votre compte.
                    </p>

                    <form method="POST" action="{{ route('stagiaire.live-quiz.join', ['session' => $session->id]) }}" class="mt-6">
                        @csrf
                        <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-orangeone px-6 py-3 text-sm font-bold text-white hover:opacity-90 transition">
                            Rejoindre la session
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
