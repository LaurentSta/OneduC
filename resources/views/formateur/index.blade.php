@extends('formateur.dashboard')

@section('formateur')

{{-- Wrapper unique pour en-tête + contenu, largeur et padding harmonisés --}}
<div class="max-w-[1285px] mx-auto px-8">

 
  {{-- 🧩 EN-TÊTE DE PAGE FORMATEUR --}}
<header class="bg-white rounded-[20px] shadow-md px-8 pt-4 pb-6 w-full mb-6">
  <div class="grid grid-cols-12 gap-6 items-center">

    {{-- Bloc texte (9 colonnes) --}}
    <div class="col-span-12 md:col-span-9">
      <p class="font-raleway text-titre text-bleuone leading-tight mb-4">
        Espace formateur
      </p>
      <p class="font-varela text-sous-titre text-orangeone leading-snug mb-3">
        Vision d'ensemble sur vos groupes, modules et stagiaires
      </p>
      <p class="font-lisible text-lg text-gray-800 leading-loose mb-6">
        Accédez rapidement à vos statistiques, à vos groupes et aux dernières activités des stagiaires.
      </p>

      {{-- 📍 Fil d’Ariane --}}
      <nav class="text-sm font-varela text-gray-600 mt-2" aria-label="Fil d'Ariane">
        <ol class="inline-flex items-center space-x-1">
          <li class="flex items-center">
            <a href="{{ route('formateur.dashboard') }}" 
               class="text-orangeone hover:underline flex items-center">
              <span class="sr-only">Accueil</span>
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" 
                   fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                      d="M3 9.75L12 3l9 6.75V19a2 2 0 01-2 2h-4a1 1 0 01-1-1v-5H10v5a1 1 0 01-1 1H5a2 2 0 01-2-2V9.75z"/>
              </svg>
            </a>
            <span class="mx-2 text-gray-400" aria-hidden="true">/</span>
          </li>
          <li class="text-gray-400">Accueil formateur</li>
        </ol>
      </nav>
    </div>

    {{-- Bloc image (3 colonnes) --}}
    <div class="col-span-12 md:col-span-3 flex justify-center md:justify-end">
      <img src="{{ asset('images/svg/TableauDeBord.svg') }}"
           alt="Illustration du tableau de bord formateur"
           class="max-w-[256px] h-auto">
    </div>

  </div>
</header>



  {{-- 📊 CONTENU PRINCIPAL --}}
  <main class="space-y-12">

    {{-- Statistiques globales --}}
    <section aria-labelledby="stats-title">
      <h2 id="stats-title" class="sr-only">Statistiques globales</h2>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <x-oneduc.card-stat title="Groupes créés" value="5" color="orangeone" />
        <x-oneduc.card-stat title="Modules utilisés" value="8 modules / 4 groupes" color="bleuone" />
        <x-oneduc.card-stat title="Total stagiaires" value="42" color="vertone" />
        <x-oneduc.card-stat title="Taux de complétion moyen" value="68%" color="orangeone" />
      </div>
    </section>

    {{-- Suivi par groupe --}}
    <section aria-labelledby="groupes-title">
      <h2 id="groupes-title" class="text-xl font-semibold text-bleuone mb-4">Suivi par groupe</h2>
      <div class="space-y-4">
        <article class="bg-white rounded-[20px] shadow-md p-6">
          <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
              <p class="text-lg font-semibold text-gray-800">Groupe A - Initiation numérique</p>
              <p class="text-sm text-gray-500">8 stagiaires | 3 modules</p>
            </div>
            <div class="text-left md:text-right">
              <p class="text-sm text-gray-600">Dernière activité : hier</p>
              <p class="text-sm text-vertone font-semibold">Progression moyenne : 75%</p>
            </div>
          </div>
        </article>
        {{-- ... autres groupes --}}
      </div>
    </section>

    {{-- Suivi par module --}}
    <section aria-labelledby="modules-title">
      <h2 id="modules-title" class="text-xl font-semibold text-bleuone mb-4">Suivi par module</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <article class="bg-white rounded-[20px] shadow-md p-6">
          <p class="font-semibold text-gray-800">Module Excel - Tableaux croisés</p>
          <p class="text-sm text-gray-500 mb-1">Utilisé par 3 groupes</p>
          <p class="text-sm text-orangeone">Taux de réussite moyen : 82%</p>
        </article>
        {{-- ... autres modules --}}
      </div>
    </section>

    {{-- Stagiaires actifs récemment --}}
    <section aria-labelledby="recent-stagiaires-title">
      <h2 id="recent-stagiaires-title" class="text-xl font-semibold text-bleuone mb-4">Stagiaires actifs récemment</h2>
      <div class="bg-white rounded-[20px] shadow-md">
        <div class="overflow-x-auto rounded-[20px]">
          <table class="min-w-full text-sm text-left text-gray-800">
            <thead class="bg-gray-100 uppercase text-xs text-gray-600">
              <tr>
                <th scope="col" class="px-6 py-3">Prénom</th>
                <th scope="col" class="px-6 py-3">Nom</th>
                <th scope="col" class="px-6 py-3">Groupe</th>
                <th scope="col" class="px-6 py-3">Progression</th>
                <th scope="col" class="px-6 py-3">Dernière activité</th>
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
              {{-- ... autres stagiaires --}}
            </tbody>
          </table>
        </div>
      </div>
    </section>

    {{-- Actions rapides --}}
    <section aria-labelledby="actions-rapides-title">
      <h2 id="actions-rapides-title" class="sr-only">Actions rapides</h2>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <a href="{{ route('formateur.groupes.create') }}" class="btn-oneduc text-center">Créer un groupe</a>
        <a href="{{ route('formateur.stagiaires.create') }}" class="btn-oneduc text-center">Ajouter un stagiaire</a>
        <a href="{{ route('frontend.modules.index') }}" class="btn-oneduc text-center">Consulter les modules</a>
      </div>
    </section>

  </main>
</div>
@endsection
