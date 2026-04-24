<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ $wall->title }} - Mur de questions</title>
  @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-slate-100 p-4">
  <div class="mx-auto w-full max-w-3xl space-y-4">

    <div class="rounded-2xl bg-white shadow-lg p-6">
      <p class="text-xs uppercase tracking-wide text-gray-400">Code · {{ $wall->access_code }}</p>
      <h1 class="text-xl font-bold text-indigo-700 mt-1">{{ $wall->title }}</h1>
      <p class="text-sm text-gray-500 mt-1">Posez une question, en anonyme ou non. Votez pour prioriser les blocages.</p>

      @if(!$wall->is_active)
        <div class="mt-3 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-700">
          Ce mur est actuellement ferme. Vous pouvez lire les questions existantes.
        </div>
      @endif

      @if(session('success'))
        <div class="mt-3 rounded-lg border border-green-200 bg-green-50 px-3 py-2 text-sm text-green-700">
          {{ session('success') }}
        </div>
      @endif

      @if($errors->any())
        <div class="mt-3 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
          {{ $errors->first() }}
        </div>
      @endif
    </div>

    <div class="rounded-2xl bg-white shadow-lg p-6">
      <p class="text-sm font-bold text-gray-800 mb-3">Poser une question</p>

      @auth
        <form method="POST" action="{{ route('questions.submit', $wall->access_code) }}" class="space-y-3">
          @csrf
          <textarea name="body" rows="3" maxlength="1000" required
                    placeholder="Ex : Pouvez-vous reprendre la difference entre X et Y ?"
                    class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">{{ old('body') }}</textarea>

          <label class="inline-flex items-center gap-2 text-sm text-gray-600">
            <input type="checkbox" name="is_anonymous" value="1" {{ old('is_anonymous') ? 'checked' : '' }}>
            Publier en anonyme
          </label>

          <div>
            <button type="submit" {{ !$wall->is_active ? 'disabled' : '' }}
                    class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-indigo-700 transition disabled:opacity-50">
              Envoyer la question
            </button>
          </div>
        </form>
      @else
        <p class="text-sm text-gray-600">Connectez-vous pour poser une question et voter.</p>
      @endauth
    </div>

    <div class="rounded-2xl bg-white shadow-lg p-6">
      <div class="flex items-center justify-between mb-4">
        <p class="text-sm font-bold text-gray-800">Questions du groupe</p>
        <p class="text-xs text-gray-500">Tri : ouvertes puis votes</p>
      </div>

      <div class="space-y-3">
        @forelse($questions as $question)
          <div class="rounded-xl border {{ $question->status === 'open' ? 'border-indigo-200 bg-indigo-50/40' : 'border-gray-200 bg-gray-50' }} p-4">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
              <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2 mb-1">
                  <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold bg-white border border-gray-200 text-gray-600">
                    {{ $question->statusLabel() }}
                  </span>
                  <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold bg-indigo-100 text-indigo-700">
                    {{ $question->votes_count }} vote{{ $question->votes_count > 1 ? 's' : '' }}
                  </span>
                </div>
                <p class="text-sm text-gray-800 leading-relaxed">{{ $question->body }}</p>
                <p class="text-[11px] text-gray-500 mt-1">
                  @if($question->is_anonymous)
                    Anonyme
                  @elseif($question->user)
                    {{ trim(($question->user->prenom ?? '') . ' ' . mb_substr((string) ($question->user->name ?? ''), 0, 1) . '.') }}
                  @else
                    Participant
                  @endif
                </p>
              </div>

              @auth
                <form method="POST" action="{{ route('questions.vote.toggle', ['code' => $wall->access_code, 'question' => $question]) }}">
                  @csrf
                  <button type="submit" {{ !$wall->is_active ? 'disabled' : '' }}
                          class="inline-flex items-center gap-1.5 rounded-[8px] px-3 py-1.5 text-xs font-bold transition disabled:opacity-50 {{ $question->voted_by_me ? 'bg-indigo-600 text-white hover:bg-indigo-700' : 'border border-gray-300 bg-white text-gray-600 hover:bg-gray-50' }}">
                    {{ $question->voted_by_me ? 'Retirer mon vote' : 'Voter' }}
                  </button>
                </form>
              @endauth
            </div>
          </div>
        @empty
          <div class="text-center py-8 text-sm text-gray-500">Aucune question pour le moment.</div>
        @endforelse
      </div>
    </div>

  </div>
</body>
</html>
