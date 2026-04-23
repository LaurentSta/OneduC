@extends('formateur.dashboard')

@section('formateur')
<div class="w-full px-6 lg:px-8">

  {{-- En-tête --}}
  <header class="bg-white rounded-[20px] shadow-md px-8 pt-5 pb-6 my-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
      <div>
        <nav class="text-sm font-varela text-gray-500 mb-2">
          <ol class="inline-flex items-center space-x-1">
            <li>
              <a href="{{ route('formateur.outils.index') }}" class="text-orangeone hover:underline">Outils numériques</a>
            </li>
            <li><span class="mx-2 text-gray-400">/</span></li>
            <li class="text-gray-400">Roue aléatoire</li>
          </ol>
        </nav>
        <p class="font-raleway text-2xl text-bleuone">Roue aléatoire</p>
        <p class="text-sm text-gray-500 mt-1">
          Désignez un stagiaire au hasard. Les stagiaires voient la roue tourner en direct sur leur écran.
        </p>
      </div>
    </div>
  </header>

  @if(session('error'))
    <div class="mb-4 rounded-[10px] bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
      {{ session('error') }}
    </div>
  @endif

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">

    {{-- ── Formulaire création ────────────────────────────────────── --}}
    <div class="lg:col-span-1">
      <div class="bg-white rounded-[20px] shadow-md p-6 sticky top-6">
        <p class="font-varela text-base font-bold text-bleuone mb-4">Nouvelle roue</p>

        @if($groups->isEmpty())
          <div class="rounded-[10px] bg-amber-50 border border-amber-200 px-4 py-3 text-sm text-amber-700">
            Aucun groupe disponible. Créez un groupe et ajoutez-y des stagiaires pour utiliser cet outil.
          </div>
        @else
          <form method="POST" action="{{ route('formateur.roue.store') }}" class="space-y-4">
            @csrf

            <div>
              <label class="block text-xs font-semibold text-gray-600 mb-1">Groupe</label>
              <select name="group_id" required
                      class="w-full rounded-[10px] border border-gray-300 px-3 py-2.5 text-sm focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-200">
                <option value="">Choisir un groupe…</option>
                @foreach($groups as $group)
                  <option value="{{ $group->id }}">
                    {{ $group->name }}
                    @if($group->students_count > 0)
                      ({{ $group->students_count }} stagiaire{{ $group->students_count > 1 ? 's' : '' }})
                    @endif
                  </option>
                @endforeach
              </select>
            </div>

            <button type="submit"
                    class="w-full inline-flex items-center justify-center gap-2 rounded-[10px] bg-violet-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-violet-700 transition">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
              </svg>
              Créer la roue
            </button>
          </form>

          <div class="mt-5 rounded-[10px] bg-violet-50 border border-violet-100 px-4 py-3">
            <p class="text-xs text-violet-700 font-semibold mb-1">Comment ça marche ?</p>
            <ol class="text-[11px] text-gray-600 space-y-1 list-decimal list-inside leading-relaxed">
              <li>Sélectionnez un groupe — les stagiaires inscrits remplissent la roue</li>
              <li>Cliquez "Tourner" — un stagiaire est désigné aléatoirement</li>
              <li>Les stagiaires voient la roue tourner via le code ou QR code</li>
              <li>Répétez jusqu'à ce que tout le monde soit passé</li>
            </ol>
          </div>
        @endif
      </div>
    </div>

    {{-- ── Sessions récentes ────────────────────────────────────────── --}}
    <div class="lg:col-span-2 space-y-4">

      @forelse($sessions as $session)
        @php
          $picksCount = count($session->picks ?? []);
          $total      = count($session->entries ?? []);
          $exhausted  = $picksCount >= $total && $total > 0;
        @endphp

        <div class="bg-white rounded-[20px] shadow-md p-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
          <div class="flex items-start gap-3 min-w-0">
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-violet-100 text-violet-700">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
              </svg>
            </div>
            <div class="min-w-0">
              <div class="flex flex-wrap items-center gap-2 mb-0.5">
                <p class="text-sm font-bold text-gray-900">
                  {{ $session->group?->name ?? 'Groupe supprimé' }}
                </p>
                @if($exhausted)
                  <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold bg-gray-100 text-gray-500">
                    Épuisée
                  </span>
                @else
                  <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold bg-violet-100 text-violet-700">
                    {{ $total - $picksCount }} restant{{ ($total - $picksCount) > 1 ? 's' : '' }}
                  </span>
                @endif
              </div>
              <p class="text-xs text-gray-500">
                Code : <span class="font-mono font-semibold text-violet-700">{{ $session->access_code }}</span>
                · {{ $picksCount }} / {{ $total }} tirages
              </p>
              <p class="text-[10px] text-gray-400 mt-0.5">{{ $session->created_at->diffForHumans() }}</p>
            </div>
          </div>
          <div class="flex gap-2 shrink-0">
            <a href="{{ route('formateur.roue.show', $session) }}"
               class="inline-flex items-center gap-1.5 rounded-[8px] bg-violet-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-violet-700 transition">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
              </svg>
              Ouvrir
            </a>
          </div>
        </div>

      @empty
        <div class="flex flex-col items-center justify-center rounded-[20px] border-2 border-dashed border-gray-200 bg-white py-16 text-center">
          <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-violet-100 text-violet-600">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
          </div>
          <p class="text-sm font-semibold text-gray-700">Aucune roue créée</p>
          <p class="text-xs text-gray-400 mt-1">Sélectionnez un groupe pour lancer votre première roue.</p>
        </div>
      @endforelse

    </div>
  </div>

</div>
@endsection
