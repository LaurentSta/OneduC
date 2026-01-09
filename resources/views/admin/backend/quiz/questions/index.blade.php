@extends('admin.admin_dashboard')

@section('admin')
<div class="max-w-[1248px] mx-auto px-4">
  <div class="bg-white rounded-[20px] shadow-md p-8 my-10 w-full">

    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
      <div>
        <h1 class="text-xl font-raleway text-bleuone font-semibold">Quiz – Banque de questions</h1>
        <p class="text-sm text-gray-600 mt-1">
          Leçon : <span class="font-semibold text-gray-900">{{ $lecture->lecture_title }}</span>
        </p>
      </div>

      <div class="flex items-center gap-2">
        <a href="{{ route('admin.quiz.questions.create', ['lecture' => $lecture->id]) }}"
           class="inline-flex items-center px-4 py-2 bg-orangeone text-white text-sm font-semibold rounded-lg hover:opacity-90 transition">
          Ajouter une question
        </a>

        <a href="{{ route('admin.lectures.edit', ['id' => $lecture->id]) }}"
           class="inline-flex items-center px-4 py-2 bg-bleuone text-white text-sm font-semibold rounded-lg hover:opacity-90 transition">
          Retour à la leçon
        </a>
      </div>
    </div>

    @if(session('success'))
      <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-800 rounded">
        {{ session('success') }}
      </div>
    @endif

    @if($errors->any())
      <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-800 rounded">
        <ul class="list-disc list-inside text-sm">
          @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead>
          <tr class="text-left border-b">
            <th class="py-3 pr-4">Question</th>
            <th class="py-3 pr-4">Type</th>
            <th class="py-3 pr-4">Options</th>
            <th class="py-3 pr-4">Active</th>
            <th class="py-3 pr-4 text-right">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($questions as $q)
            <tr class="border-b align-top">
              <td class="py-3 pr-4">
                <div class="font-medium text-gray-900">
                  {{ \Illuminate\Support\Str::limit($q->question_text, 120) }}
                </div>
                <div class="text-xs text-gray-500 mt-1">
                  ID: {{ $q->id }}
                </div>
              </td>
              <td class="py-3 pr-4">
                @php
                  $label = match($q->type) {
                    'single' => 'Choix unique',
                    'multiple' => 'Choix multiple',
                    'boolean' => 'Vrai / Faux',
                    default => $q->type
                  };
                @endphp
                <span class="inline-flex px-2 py-1 rounded bg-gray-100 text-gray-800">
                  {{ $label }}
                </span>
              </td>
              <td class="py-3 pr-4">
                {{ $q->options_count }}
              </td>
              <td class="py-3 pr-4">
                {!! $q->is_active ? '<span class="text-green-700 font-semibold">Oui</span>' : '<span class="text-gray-500">Non</span>' !!}
              </td>
              <td class="py-3 pr-0 text-right whitespace-nowrap">
                <a href="{{ route('admin.quiz.questions.edit', ['lecture' => $lecture->id, 'question' => $q->id]) }}"
                   class="inline-flex items-center px-3 py-2 bg-bleuone text-white text-xs font-semibold rounded-lg hover:opacity-90 transition">
                  Modifier
                </a>

                <form action="{{ route('admin.quiz.questions.destroy', ['lecture' => $lecture->id, 'question' => $q->id]) }}"
                      method="POST"
                      class="inline-block"
                      onsubmit="return confirm('Supprimer cette question ?');">
                  @csrf
                  @method('DELETE')
                  <button type="submit"
                          class="inline-flex items-center px-3 py-2 bg-red-600 text-white text-xs font-semibold rounded-lg hover:opacity-90 transition">
                    Supprimer
                  </button>
                </form>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="py-8 text-center text-gray-600">
                Aucune question pour cette leçon. Clique sur “Ajouter une question”.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

  </div>
</div>
@endsection
