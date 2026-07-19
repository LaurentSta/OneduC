@extends('admin.admin_dashboard')

@section('title', 'Créer un modèle de parcours')

@section('admin')
    <div class="mx-auto w-full max-w-[1400px] space-y-5">
        <header class="border-b border-slate-200 pb-5">
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Catalogue Oneduc</p>
            <h1 class="mt-1 text-2xl font-semibold text-slate-950">Créer un modèle de parcours</h1>
            <p class="mt-1 text-sm text-slate-600">Le modèle restera en brouillon jusqu’à sa publication explicite.</p>
        </header>

        @include('admin.modeles-parcours._form', [
            'action' => route('admin.modeles-parcours.store'),
            'methode' => 'POST',
        ])
    </div>
@endsection
