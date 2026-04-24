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
            <li>
              <a href="{{ route('formateur.questions.index') }}" class="text-orangeone hover:underline">Mur de questions</a>
            </li>
            <li><span class="mx-2 text-gray-400">/</span></li>
            <li class="text-gray-400">{{ $wall->title }}</li>
          </ol>
        </nav>
        <p class="font-raleway text-2xl text-bleuone">{{ $wall->title }}</p>
        <p class="text-sm text-gray-500 mt-1">
          Groupe : <span class="font-semibold">{{ $wall->group?->name ?? '—' }}</span>
          · Code : <span class="font-mono font-semibold text-indigo-700">{{ $wall->access_code }}</span>
        </p>
      </div>

      <div class="flex flex-wrap gap-2">
        <a href="{{ $joinUrl }}" target="_blank"
           class="inline-flex items-center rounded-[10px] border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
          Ouvrir la vue stagiaire
        </a>

        <form method="POST" action="{{ route('formateur.questions.toggle', $wall) }}">
          @csrf
          <button type="submit"
                  class="inline-flex items-center rounded-[10px] px-4 py-2 text-sm font-bold text-white transition {{ $wall->is_active ? 'bg-red-500 hover:bg-red-600' : 'bg-green-600 hover:bg-green-700' }}">
            {{ $wall->is_active ? 'Fermer le mur' : 'Rouvrir le mur' }}
          </button>
        </form>
      </div>
    </div>
  </header>

  @if(session('success'))
    <div class="mb-4 rounded-[10px] bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
      {{ session('success') }}
    </div>
  @endif

  <div class="bg-white rounded-[20px] shadow-md p-5">
    <div class="flex items-center justify-between mb-4">
      <p class="text-sm font-bold text-bleuone">Questions du groupe</p>
      <p class="text-xs text-gray-500">Trie : ouvertes d'abord, puis votes desc.</p>
    </div>

    <div class="space-y-3">
      @forelse($questions as $question)
        <div class="rounded-[14px] border {{ $question->status === 'open' ? 'border-indigo-200 bg-indigo-50/40' : 'border-gray-200 bg-gray-50' }} p-4">
          <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
            <div class="min-w-0">
              <div class="flex flex-wrap items-center gap-2 mb-1">
                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold bg-white border border-gray-200 text-gray-600">
                  {{ $question->statusLabel() }}
                </span>
                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold bg-indigo-100 text-indigo-700">
                  {{ $question->votes_count }} vote{{ $question->votes_count > 1 ? 's' : '' }}
                </span>
                <span class="text-[10px] text-gray-400">{{ $question->created_at->diffForHumans() }}</span>
              </div>

              <p class="text-sm text-gray-800 leading-relaxed">{{ $question->body }}</p>

              <p class="text-[11px] text-gray-500 mt-2">
                @if($question->is_anonymous)
                  Auteur : Anonyme
                @elseif($question->user)
                  Auteur : {{ trim(($question->user->prenom ?? '') . ' ' . mb_substr((string) ($question->user->name ?? ''), 0, 1) . '.') }}
                @else
                  Auteur : Participant
                @endif
              </p>
            </div>

            <div class="grid grid-cols-2 gap-2 shrink-0">
              <form method="POST" action="{{ route('formateur.questions.status', ['wall' => $wall, 'question' => $question]) }}">
                @csrf
                <input type="hidden" name="status" value="treated">
                <button class="w-full rounded-[8px] bg-green-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-green-700 transition">Traitee</button>
              </form>

              <form method="POST" action="{{ route('formateur.questions.status', ['wall' => $wall, 'question' => $question]) }}">
                @csrf
                <input type="hidden" name="status" value="activity">
                <button class="w-full rounded-[8px] bg-orange-500 px-3 py-1.5 text-xs font-bold text-white hover:bg-orange-600 transition">Activite</button>
              </form>

              <form method="POST" action="{{ route('formateur.questions.status', ['wall' => $wall, 'question' => $question]) }}">
                @csrf
                <input type="hidden" name="status" value="later">
                <button class="w-full rounded-[8px] bg-slate-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-slate-700 transition">Plus tard</button>
              </form>

              <form method="POST" action="{{ route('formateur.questions.status', ['wall' => $wall, 'question' => $question]) }}">
                @csrf
                <input type="hidden" name="status" value="open">
                <button class="w-full rounded-[8px] bg-indigo-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-indigo-700 transition">Rouvrir</button>
              </form>
            </div>
          </div>
        </div>
      @empty
        <div class="flex flex-col items-center justify-center rounded-[16px] border-2 border-dashed border-gray-200 py-14 text-center">
          <p class="text-sm font-semibold text-gray-700">Aucune question pour le moment</p>
          <p class="text-xs text-gray-400 mt-1">Partagez le lien stagiaire pour lancer la collecte.</p>
        </div>
      @endforelse
    </div>
  </div>

</div>
@endsection
