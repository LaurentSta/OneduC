@extends('admin.admin_dashboard')
@section('admin')

@php
  $stats = [
    ['label' => 'Catégories', 'value' => $categoryCount ?? 0, 'accent' => 'text-orange-600', 'bg' => 'bg-orange-100', 'icon' => 'folder'],
    ['label' => 'Sous-catégories', 'value' => $subCategoryCount ?? 0, 'accent' => 'text-cyan-600', 'bg' => 'bg-cyan-100', 'icon' => 'lines'],
    ['label' => 'Modules', 'value' => $moduleCount ?? 0, 'accent' => 'text-yellow-600', 'bg' => 'bg-yellow-100', 'icon' => 'blocks'],
    ['label' => 'Formateurs', 'value' => $formateurCount ?? 0, 'accent' => 'text-green-600', 'bg' => 'bg-green-100', 'icon' => 'user'],
    ['label' => 'Stagiaires', 'value' => $stagiaireCount ?? 0, 'accent' => 'text-red-600', 'bg' => 'bg-red-100', 'icon' => 'users'],
    ['label' => 'Groupes', 'value' => $groupCount ?? 0, 'accent' => 'text-fuchsia-600', 'bg' => 'bg-fuchsia-100', 'icon' => 'groups'],
    ['label' => 'Sections', 'value' => $sectionCount ?? 0, 'accent' => 'text-blue-600', 'bg' => 'bg-blue-100', 'icon' => 'rows'],
    ['label' => 'Leçons', 'value' => $lectureCount ?? 0, 'accent' => 'text-indigo-600', 'bg' => 'bg-indigo-100', 'icon' => 'plus'],
  ];
@endphp

<div class="mx-auto max-w-[1248px] px-3 py-6 sm:px-4 sm:py-8">
  <section class="mb-8 rounded-2xl border border-blue-100 bg-gradient-to-r from-white to-blue-50 px-5 py-6 sm:px-7">
    <p class="text-sm font-medium text-blue-700">Espace administrateur</p>
    <h1 class="mt-1 text-2xl font-semibold text-bleuone sm:text-3xl">Bonjour {{ Auth::user()->username }}</h1>
    <p class="mt-2 text-sm text-gray-600">Vue d'ensemble de la plateforme et des contenus suivis.</p>
  </section>

  <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
    @foreach ($stats as $stat)
      <article class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
        <div class="flex items-start justify-between gap-3">
          <div>
            <p class="text-sm font-medium text-gray-600">{{ $stat['label'] }}</p>
            <p class="mt-1 text-2xl font-bold {{ $stat['accent'] }}">{{ number_format((int) $stat['value'], 0, ',', ' ') }}</p>
          </div>
          <span class="flex h-10 w-10 items-center justify-center rounded-full {{ $stat['bg'] }} {{ $stat['accent'] }}">
            @if ($stat['icon'] === 'folder')
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 7h5l2 3h11v9H3V7z" />
              </svg>
            @elseif ($stat['icon'] === 'lines')
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
              </svg>
            @elseif ($stat['icon'] === 'blocks')
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 5h8v6H3V5Zm10 0h8v14h-8V5ZM3 13h8v6H3v-6Z" />
              </svg>
            @elseif ($stat['icon'] === 'user')
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19a7 7 0 1 0-6 0m6 0a8.5 8.5 0 0 1-6 0" />
              </svg>
            @elseif ($stat['icon'] === 'users')
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16 11a3 3 0 1 0 0-6 3 3 0 0 0 0 6ZM8 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm8 8a5 5 0 0 0-10 0m14 0a4 4 0 0 0-4-4" />
              </svg>
            @elseif ($stat['icon'] === 'groups')
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20v-1a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v1m18 0v-1a4 4 0 0 0-3-3.87M14 7a3 3 0 1 0-6 0 3 3 0 0 0 6 0Zm5 3a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z" />
              </svg>
            @elseif ($stat['icon'] === 'rows')
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16M4 17h16" />
              </svg>
            @else
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m7-7H5" />
              </svg>
            @endif
          </span>
        </div>
      </article>
    @endforeach
  </section>

  <section class="mt-10 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm sm:p-6">
    <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
      <h2 class="text-xl font-semibold text-gray-900">Cours suivis</h2>
      <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">Suivi des progressions</span>
    </div>
    <div class="overflow-x-auto">
      <table class="datatables-academy-course min-w-full text-left text-sm font-lisible">
        <thead class="border-b bg-gray-50 text-gray-700">
          <tr>
            <th class="px-2 py-3"></th>
            <th class="px-2 py-3"></th>
            <th class="px-2 py-3">Nom du cours</th>
            <th class="px-2 py-3">Durée</th>
            <th class="w-1/4 px-2 py-3">Progression</th>
            <th class="w-1/4 px-2 py-3">Statut</th>
          </tr>
        </thead>
      </table>
    </div>
  </section>
</div>

@endsection
