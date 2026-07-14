@extends('admin.admin_dashboard')

@section('title', 'Créer un utilisateur')

@section('admin')
    <div class="mx-auto w-full max-w-6xl space-y-5">
        <header class="border-b border-slate-200 pb-5">
            <a href="{{ route('admin.utilisateurs.index', ['role' => $roleSelectionne]) }}" class="mb-3 inline-flex items-center gap-1.5 text-sm font-semibold text-slate-500 hover:text-bleuone">
                <i class="ti ti-arrow-left" aria-hidden="true"></i>
                Retour aux utilisateurs
            </a>
            <h1 class="!mb-1 !text-2xl !font-semibold text-slate-950">Créer un compte</h1>
            <p class="text-sm text-slate-600">Ajoutez un formateur ou un stagiaire directement depuis l’administration.</p>
        </header>

        <form method="POST" action="{{ route('admin.utilisateurs.store') }}" novalidate>
            @csrf
            @include('admin.backend.utilisateurs.partials.formulaire')
        </form>
    </div>
@endsection
