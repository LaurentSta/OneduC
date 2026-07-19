@extends('admin.admin_dashboard')

@section('title', 'Modifier un modèle de parcours')

@section('admin')
    <div class="mx-auto w-full max-w-[1400px] space-y-5">
        <header class="border-b border-slate-200 pb-5">
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Catalogue Oneduc · Brouillon</p>
            <h1 class="mt-1 text-2xl font-semibold text-slate-950">{{ $modele->titre }}</h1>
            <p class="mt-1 text-sm text-slate-600">Après publication, ce modèle deviendra immuable.</p>
        </header>

        @if (session('success'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
        @endif

        @include('admin.modeles-parcours._form', [
            'action' => route('admin.modeles-parcours.update', $modele),
            'methode' => 'PUT',
        ])
    </div>
@endsection
