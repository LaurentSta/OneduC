@extends('stagiaire.master')

@section('content')
@php
    $creneauxLabels = [
        'matin' => 'Matin',
        'apres_midi' => 'Après-midi',
        'journee' => 'Journée complète',
        'soiree' => 'Soirée',
    ];
@endphp

<div class="mx-auto max-w-[900px] px-8 py-8 space-y-6">
    <header class="rounded-[24px] bg-white px-8 py-6 shadow-md">
        <p class="font-raleway text-2xl text-bleuone">Émargement</p>
        <p class="text-sm text-gray-500 mt-1">Groupe {{ $group->name }}</p>
    </header>

    @if (session('success'))
        <div class="rounded-lg bg-vertone/10 text-vertone px-4 py-3 text-sm font-semibold">
            {{ session('success') }}
        </div>
    @endif

    @if (! $seance)
        <div class="rounded-[24px] bg-white px-8 py-10 shadow-md text-center">
            <p class="text-gray-500">Aucune séance d'émargement n'est ouverte actuellement pour votre groupe.</p>
        </div>
    @elseif (! $presence)
        <div class="rounded-[24px] bg-white px-8 py-10 shadow-md text-center">
            <p class="text-gray-500">Vous n'êtes pas inscrit·e à cette séance. Contactez votre formateur si vous pensez que c'est une erreur.</p>
        </div>
    @elseif ($presence->statut === 'present')
        <div class="rounded-[24px] bg-white px-8 py-10 shadow-md text-center">
            <p class="text-vertone font-bold text-lg mb-2">Présence signée ✓</p>
            <p class="text-gray-500 text-sm">
                Séance du {{ $seance->date->format('d/m/Y') }} — {{ $creneauxLabels[$seance->creneau] ?? $seance->creneau }},
                signée le {{ $presence->updated_at->format('d/m/Y à H:i') }}.
            </p>
        </div>
    @else
        <div class="rounded-[24px] bg-white px-8 py-6 shadow-md">
            <p class="font-bold text-bleuone mb-1">
                Séance du {{ $seance->date->format('d/m/Y') }} — {{ $creneauxLabels[$seance->creneau] ?? $seance->creneau }}
            </p>
            <p class="text-sm text-gray-500 mb-4">Signez ci-dessous pour confirmer votre présence.</p>

            <x-oneduc.signature-pad :submit-url="route('stagiaire.emargement.signer', $group->id)" />
        </div>
    @endif
</div>
@endsection
