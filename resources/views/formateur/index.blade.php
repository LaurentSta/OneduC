<!-- resources/views/formateur/dashboard/index.blade.php -->
@extends('formateur.dashboard')

@section('formateur')

<div class="max-w-[1285px] mx-auto px-4 py-10">
    {{-- En-tête du dashboard --}}
    <div class="bg-white rounded-[20px] shadow-md px-8 py-6 mb-10">
        <h1 class="text-titre font-raleway text-bleuone mb-2">Bienvenue sur votre espace formateur</h1>
        <p class="text-sous-titre font-varela text-orangeone">Vision d'ensemble sur vos groupes, modules et stagiaires</p>
    </div>

    {{-- Statistiques globales --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
        <x-oneduc.card-stat title="Groupes créés" value="5" color="orangeone" />
        <x-oneduc.card-stat title="Modules utilisés" value="8 modules / 4 groupes" color="bleuone" />
        <x-oneduc.card-stat title="Total stagiaires" value="42" color="vertone" />
        <x-oneduc.card-stat title="Taux de complétion moyen" value="68%" color="orangeone" />
    </div>

    {{-- Suivi par groupe --}}
    <div class="mb-12">
        <h2 class="text-xl font-semibold text-bleuone mb-4">Suivi par groupe</h2>
        <div class="space-y-4">
            <div class="bg-white rounded-xl shadow p-4">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-lg font-semibold text-gray-800">Groupe A - Initiation numérique</p>
                        <p class="text-sm text-gray-500">8 stagiaires | 3 modules</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-600">Dernière activité : hier</p>
                        <p class="text-sm text-vertone font-semibold">Progression moyenne : 75%</p>
                    </div>
                </div>
            </div>
            <!-- Ajouter d'autres groupes ici -->
        </div>
    </div>

    {{-- Suivi par module --}}
    <div class="mb-12">
        <h2 class="text-xl font-semibold text-bleuone mb-4">Suivi par module</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white rounded-xl shadow p-4">
                <p class="font-semibold text-gray-800">Module Excel - Tableaux croisés</p>
                <p class="text-sm text-gray-500 mb-1">Utilisé par 3 groupes</p>
                <p class="text-sm text-orangeone">Taux de réussite moyen : 82%</p>
            </div>
            <!-- Ajouter d'autres modules ici -->
        </div>
    </div>

    {{-- Bloc stagiaires récents --}}
    <div class="mb-12">
        <h2 class="text-xl font-semibold text-bleuone mb-4">Stagiaires actifs récemment</h2>
        <table class="min-w-full text-sm text-left text-gray-800 bg-white rounded-xl shadow overflow-hidden">
            <thead class="bg-gray-100 uppercase text-xs text-gray-600">
                <tr>
                    <th class="px-6 py-3">Prénom</th>
                    <th class="px-6 py-3">Nom</th>
                    <th class="px-6 py-3">Groupe</th>
                    <th class="px-6 py-3">Progression</th>
                    <th class="px-6 py-3">Dernière activité</th>
                </tr>
            </thead>
            <tbody>
                <tr class="border-t hover:bg-gray-50">
                    <td class="px-6 py-4">Lucie</td>
                    <td class="px-6 py-4">Moreau</td>
                    <td class="px-6 py-4">Groupe A</td>
                    <td class="px-6 py-4 text-vertone font-semibold">81%</td>
                    <td class="px-6 py-4 text-sm text-gray-500">Aujourd’hui</td>
                </tr>
                <!-- Autres stagiaires -->
            </tbody>
        </table>
    </div>

    {{-- Actions rapides --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <a href="{{ route('formateur.groupes.create') }}" class="btn-oneduc text-center">➕ Créer un groupe</a>
        <a href="{{ route('formateur.stagiaires.create') }}" class="btn-oneduc text-center">👤 Ajouter un stagiaire</a>
        <a href="{{ route('frontend.modules.index') }}" class="btn-oneduc text-center">📚 Consulter les modules</a>
    </div>
</div>

@endsection