@extends('stagiaire.master')

@section('content')
<div class="max-w-[1285px] mx-auto px-8">
  <header class="rounded-[20px] border border-gray-100 bg-white shadow-md px-6 py-6 md:px-8 md:py-7 w-full mb-6">
    <x-oneduc.breadcrumb :items="[['label' => 'Accueil', 'url' => route('stagiaire.dashboard')], ['label' => 'Évaluation terminée']]" />

    <h1 class="font-raleway text-2xl font-medium leading-tight text-bleuone md:text-3xl">
      Félicitations
    </h1>
    <p class="mt-0.5 font-varela text-base text-orangeone md:text-lg">
      Évaluation terminée : {{ $evaluation->titre ?? 'Évaluation finale' }}
    </p>
  </header>

  <section class="bg-white rounded-[20px] shadow-md p-6 grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
    {{-- Colonne gauche : chiffres clés --}}
    <div>
      <h2 class="text-lg font-semibold text-bleuone mb-4">Résumé</h2>
      <ul class="space-y-2 text-gray-800">
        @isset($lastScore)
          <li>Dernier score : <strong>{{ $lastScore }} / 100</strong></li>
        @endisset
        @isset($bestScore)
          <li>Meilleur score : <strong>{{ $bestScore }} / 100</strong></li>
        @endisset
        <li>Tentatives : <strong>{{ $attempts }}</strong></li>
        <li>Questions répondues : <strong>{{ $questionsAnswered }}</strong></li>
        <li>Temps total :
          <strong>
            @php
              $t = (int)($sessionTimeSeconds ?? 0);
              $h = intdiv($t, 3600);
              $m = intdiv($t % 3600, 60);
              $s = $t % 60;
            @endphp
            {{ sprintf('%02d:%02d:%02d', $h, $m, $s) }}
          </strong>
        </li>
      </ul>

      <div class="mt-6 flex flex-wrap gap-3">
        <a href="{{ route('stagiaire.modules') }}"
           class="inline-block px-4 py-2 rounded-lg bg-bleuone text-white">
          Retour aux formations
        </a>

        {{-- Refaire l’évaluation : renvoie vers la vue iFrame --}}
        <a href="{{ route('evaluation.show', $evaluation->id) }}"
           class="inline-block px-4 py-2 rounded-lg bg-gray-100 text-bleuone">
          Refaire l’évaluation
        </a>
      </div>
    </div>

    {{-- Colonne droite : illustration --}}
    <div class="flex justify-center">
      <img src="{{ asset('images/svg/Finish.svg') }}"
           alt="Fin d’évaluation"
           class="max-w-[300px] w-full h-auto">
    </div>
  </section>
</div>
@endsection
