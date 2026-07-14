@extends('admin.admin_dashboard')

@section('title', 'Modifier un utilisateur')

@section('admin')
    <div class="mx-auto w-full max-w-6xl space-y-5">
        <header class="flex flex-col gap-3 border-b border-slate-200 pb-5 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <a href="{{ route('admin.utilisateurs.index', ['role' => $utilisateur->role]) }}" class="mb-3 inline-flex items-center gap-1.5 text-sm font-semibold text-slate-500 hover:text-bleuone">
                    <i class="ti ti-arrow-left" aria-hidden="true"></i>
                    Retour aux utilisateurs
                </a>
                <h1 class="!mb-1 !text-2xl !font-semibold text-slate-950">{{ trim($utilisateur->prenom.' '.$utilisateur->name) }}</h1>
                <p class="text-sm text-slate-600">Mettez à jour les informations, l’accès et les rattachements de ce compte.</p>
            </div>
            <p class="text-xs text-slate-500">Compte créé le {{ $utilisateur->created_at?->format('d/m/Y') ?? '—' }}</p>
        </header>

        <form method="POST" action="{{ route('admin.utilisateurs.update', $utilisateur) }}" novalidate>
            @csrf
            @method('PUT')
            @include('admin.backend.utilisateurs.partials.formulaire')
        </form>
    </div>
@endsection
