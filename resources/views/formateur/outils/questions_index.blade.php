@extends('formateur.dashboard')

@section('formateur')
<div class="w-full px-6 lg:px-8">

  <header class="bg-white rounded-[20px] shadow-md px-8 pt-5 pb-6 my-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
      <div>
        <nav class="text-sm font-varela text-gray-500 mb-2">
          <ol class="inline-flex items-center space-x-1">
            <li>
              <a href="{{ route('formateur.outils.index') }}" class="text-orangeone hover:underline">Outils numeriques</a>
            </li>
            <li><span class="mx-2 text-gray-400">/</span></li>
            <li class="text-gray-400">Mur de questions</li>
          </ol>
        </nav>
        <p class="font-raleway text-2xl text-bleuone">Mur de questions anonyme</p>
        <p class="text-sm text-gray-500 mt-1">Collectez les questions en direct, laissez le groupe voter, puis priorisez.</p>
      </div>
    </div>
  </header>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">

    <div class="lg:col-span-1">
      <div class="bg-white rounded-[20px] shadow-md p-6 sticky top-6">
        <p class="font-varela text-base font-bold text-bleuone mb-4">Nouveau mur</p>

        @if(session('success'))
          <div class="mb-4 rounded-[10px] bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
            {{ session('success') }}
          </div>
        @endif

        @if($errors->any())
          <div class="mb-4 rounded-[10px] bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
            {{ $errors->first() }}
          </div>
        @endif

        @if($groups->isEmpty())
          <div class="rounded-[10px] bg-amber-50 border border-amber-200 px-4 py-3 text-sm text-amber-700">
            Aucun groupe disponible. Creez un groupe et ajoutez-y des stagiaires pour utiliser cet outil.
          </div>
        @else
          <form method="POST" action="{{ route('formateur.questions.store') }}" class="space-y-4">
            @csrf

            <div>
              <label class="block text-xs font-semibold text-gray-600 mb-1">Groupe</label>
              <select name="group_id" required
                      class="w-full rounded-[10px] border border-gray-300 px-3 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                <option value="">Choisir un groupe...</option>
                @foreach($groups as $group)
                  <option value="{{ $group->id }}" {{ old('group_id') == $group->id ? 'selected' : '' }}>
                    {{ $group->name }} ({{ $group->students_count }} stagiaire{{ $group->students_count > 1 ? 's' : '' }})
                  </option>
                @endforeach
              </select>
            </div>

            <div>
              <label class="block text-xs font-semibold text-gray-600 mb-1">Titre (optionnel)</label>
              <input type="text" name="title" maxlength="255" value="{{ old('title') }}"
                     placeholder="Ex : Questions en cours - Matin"
                     class="w-full rounded-[10px] border border-gray-300 px-3 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
            </div>

            <button type="submit"
                    class="w-full inline-flex items-center justify-center gap-2 rounded-[10px] bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-indigo-700 transition">
              Creer le mur de questions
            </button>
          </form>
        @endif
      </div>
    </div>

    <div class="lg:col-span-2 space-y-4">
      @forelse($walls as $wall)
        <div class="bg-white rounded-[20px] shadow-md p-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
          <div class="flex items-start gap-3 min-w-0">
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl {{ $wall->is_active ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-500' }}">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h8m-8 4h5m-9 5h16a2 2 0 002-2V7a2 2 0 00-2-2h-3.586a1 1 0 01-.707-.293l-1.414-1.414A1 1 0 0013.586 3h-3.172a1 1 0 00-.707.293L8.293 4.707A1 1 0 017.586 5H4a2 2 0 00-2 2v10a2 2 0 002 2z"/>
              </svg>
            </div>
            <div class="min-w-0">
              <div class="flex flex-wrap items-center gap-2 mb-0.5">
                <p class="text-sm font-bold text-gray-900 truncate">{{ $wall->title }}</p>
                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold {{ $wall->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                  {{ $wall->is_active ? 'Ouvert' : 'Ferme' }}
                </span>
              </div>
              <p class="text-xs text-gray-500 truncate">
                Groupe : <span class="font-semibold">{{ $wall->group?->name ?? '—' }}</span>
                · Code : <span class="font-mono font-semibold text-indigo-700">{{ $wall->access_code }}</span>
              </p>
              <p class="text-[10px] text-gray-400 mt-0.5">{{ $wall->questions_count }} question{{ $wall->questions_count > 1 ? 's' : '' }} · {{ $wall->created_at->diffForHumans() }}</p>
            </div>
          </div>
          <div class="flex gap-2 shrink-0">
            <a href="{{ route('formateur.questions.show', $wall) }}"
               class="inline-flex items-center gap-1.5 rounded-[8px] bg-indigo-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-indigo-700 transition">
              Ouvrir
            </a>
          </div>
        </div>
      @empty
        <div class="flex flex-col items-center justify-center rounded-[20px] border-2 border-dashed border-gray-200 bg-white py-16 text-center">
          <p class="text-sm font-semibold text-gray-700">Aucun mur cree</p>
          <p class="text-xs text-gray-400 mt-1">Utilisez le formulaire pour lancer votre premier mur de questions.</p>
        </div>
      @endforelse
    </div>

  </div>
</div>
@endsection
