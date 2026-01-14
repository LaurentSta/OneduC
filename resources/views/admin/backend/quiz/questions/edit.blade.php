@extends('admin.admin_dashboard')

@section('admin')
<div class="max-w-[1248px] mx-auto px-4 py-10" x-data="quizEditManager()">
  <div class="bg-white rounded-[20px] shadow-md p-8 w-full border border-gray-100">

    {{-- En-tête --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8 border-b pb-6">
      <div>
        <h1 class="text-2xl font-raleway text-bleuone font-bold">Modifier une question</h1>
        <p class="text-sm text-gray-500 mt-1">
          Leçon : <span class="font-semibold text-gray-800">{{ $lecture->lecture_title }}</span>
        </p>
      </div>
      <a href="{{ route('admin.quiz.questions.index', ['lecture' => $lecture->id]) }}"
         class="text-bleuone hover:underline text-sm font-medium">
        &larr; Retour à la banque de questions
      </a>
    </div>

    @if($errors->any())
      <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg">
        <ul class="list-disc list-inside text-sm">
          @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form method="POST"
          action="{{ route('admin.quiz.questions.update', ['lecture' => $lecture->id, 'question' => $question->id]) }}"
          enctype="multipart/form-data"
          class="space-y-8">
      @csrf
      @method('PUT')

      {{-- Énoncé --}}
      <div class="space-y-4">
        <div>
          <label class="block text-sm font-bold text-gray-700 mb-2" for="question_text">Énoncé de la question</label>
          <textarea name="question_text" id="question_text" rows="3" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-orangeone focus:border-orangeone shadow-sm"
                    placeholder="Ex: Quelle est la capitale de la France ?">{{ old('question_text', $question->question_text) }}</textarea>
        </div>

        {{-- Type --}}
        <div>
          <span class="block text-sm font-bold text-gray-700 mb-3">Format de réponse</span>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            @php
              $currentType = old('type', $question->type);
            @endphp

            <button type="button" @click="changeType('boolean')"
                    :class="currentType === 'boolean' ? 'border-bleuone bg-blue-50 text-bleuone' : 'border-gray-200 bg-white text-gray-600'"
                    class="flex items-center justify-center p-4 border-2 rounded-xl transition-all duration-200">
              <div class="text-center">
                <span class="block font-bold text-sm">Vrai / Faux</span>
                <span class="block text-[10px] uppercase tracking-widest mt-1 opacity-60">boolean</span>
              </div>
            </button>

            <button type="button" @click="changeType('single')"
                    :class="currentType === 'single' ? 'border-bleuone bg-blue-50 text-bleuone' : 'border-gray-200 bg-white text-gray-600'"
                    class="flex items-center justify-center p-4 border-2 rounded-xl transition-all duration-200">
              <div class="text-center">
                <span class="block font-bold text-sm">Choix Unique</span>
                <span class="block text-[10px] uppercase tracking-widest mt-1 opacity-60">single</span>
              </div>
            </button>

            <button type="button" @click="changeType('multiple')"
                    :class="currentType === 'multiple' ? 'border-bleuone bg-blue-50 text-bleuone' : 'border-gray-200 bg-white text-gray-600'"
                    class="flex items-center justify-center p-4 border-2 rounded-xl transition-all duration-200">
              <div class="text-center">
                <span class="block font-bold text-sm">Choix Multiple</span>
                <span class="block text-[10px] uppercase tracking-widest mt-1 opacity-60">multiple</span>
              </div>
            </button>
          </div>

          <input type="hidden" name="type" x-model="currentType">
        </div>
      </div>

      <hr class="border-gray-100">

      {{-- Propositions --}}
      <div class="space-y-4">
        <div class="flex items-center justify-between">
          <h2 class="text-lg font-semibold text-gray-800">Propositions de réponses</h2>

          <button type="button" @click="addOption()" x-show="currentType !== 'boolean'"
                  class="text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-2 rounded-lg font-medium transition">
            + Ajouter un choix
          </button>
        </div>

        <div class="space-y-3">
          <template x-for="(option, index) in options" :key="index">
            <div class="flex items-center gap-4 p-4 rounded-xl border border-gray-200 bg-white shadow-sm">
              <div class="flex items-center justify-center">
                <input :type="currentType === 'multiple' ? 'checkbox' : 'radio'"
                       :name="`options[${index}][is_correct]`"
                       value="1"
                       x-model="option.is_correct"
                       class="h-6 w-6 text-orangeone border-gray-300 focus:ring-orangeone rounded-full">
              </div>

              <div class="flex-1">
                <input type="text" :name="`options[${index}][text]`" x-model="option.text" required
                       class="w-full border-0 border-b border-transparent focus:border-orangeone focus:ring-0 text-sm p-0 pb-1"
                       :placeholder="'Option ' + (index + 1)">
              </div>

              <button type="button" @click="removeOption(index)" x-show="canRemove()"
                      class="text-red-400 hover:text-red-600 p-1">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
              </button>
            </div>
          </template>
        </div>

        <p class="text-xs text-gray-500">
          Choix unique : une seule bonne réponse. Choix multiple : une ou plusieurs. Vrai/Faux : 2 options fixes.
        </p>
      </div>

      {{-- Médias optionnels (pliable) --}}
      <details class="bg-gray-50 rounded-2xl p-6 border border-gray-100">
        <summary class="cursor-pointer text-sm font-bold text-gray-700 uppercase tracking-wider">
          Médias & Accessibilité (optionnel)
        </summary>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-6">
          <div class="space-y-3">
            <label class="block text-xs font-bold text-gray-500 italic">Image</label>
            <input type="file" name="image" accept="image/*" class="text-sm w-full">
            <input type="text" name="image_alt"
                   value="{{ old('image_alt', $question->image_alt) }}"
                   placeholder="Texte alternatif (obligatoire si image)"
                   class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:ring-orangeone focus:border-orangeone">
          </div>

          <div class="space-y-3">
            <label class="block text-xs font-bold text-gray-500 italic">Audio</label>
            <input type="file" name="audio" accept="audio/*" class="text-sm w-full">
            <textarea name="audio_transcript" rows="2"
                      placeholder="Transcription"
                      class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:ring-orangeone focus:border-orangeone">{{ old('audio_transcript', $question->audio_transcript) }}</textarea>
          </div>
        </div>
      </details>

      {{-- Validation --}}
      <div class="flex items-center justify-between pt-6 border-t">
        <label class="inline-flex items-center cursor-pointer">
          <input type="hidden" name="is_active" value="0">
          <input type="checkbox" name="is_active" value="1"
                 {{ old('is_active', $question->is_active) ? 'checked' : '' }}
                 class="sr-only peer">
          <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer
                      peer-checked:after:translate-x-full peer-checked:after:border-white
                      after:content-[''] after:absolute after:top-[2px] after:left-[2px]
                      after:bg-white after:border-gray-300 after:border after:rounded-full
                      after:h-5 after:w-5 after:transition-all peer-checked:bg-orangeone"></div>
          <span class="ml-3 text-sm font-medium text-gray-700">Rendre la question active</span>
        </label>

        <button type="submit"
                class="bg-orangeone text-white px-10 py-3 rounded-xl font-bold shadow-lg hover:opacity-95 transition">
          Enregistrer les modifications
        </button>
      </div>
    </form>
  </div>
</div>

<script>
function quizEditManager() {
  return {
    currentType: @json(old('type', $question->type)),
    options: @json(
      old('options',
        $question->options
          ->sortBy('position')
          ->values()
          ->map(fn($o) => ['text' => $o->option_text, 'is_correct' => (bool)$o->is_correct])
      )
    ),

    changeType(type) {
      this.currentType = type;

      if (type === 'boolean') {
        // force 2 options
        this.options = [
          { text: 'Vrai', is_correct: true },
          { text: 'Faux', is_correct: false }
        ];
        return;
      }

      // si on bascule depuis V/F vers autre type
      if (this.options.length < 2 || this.options[0]?.text === 'Vrai') {
        this.options = [
          { text: '', is_correct: true },
          { text: '', is_correct: false },
          { text: '', is_correct: false },
          { text: '', is_correct: false }
        ];
      }

      // en "single", on impose une seule bonne réponse côté interface
      if (type === 'single') {
        let firstTrue = this.options.findIndex(o => o.is_correct);
        if (firstTrue === -1) firstTrue = 0;
        this.options = this.options.map((o, i) => ({ ...o, is_correct: i === firstTrue }));
      }
    },

    addOption() {
      if (this.currentType === 'boolean') return;
      this.options.push({ text: '', is_correct: false });
    },

    removeOption(index) {
      if (this.currentType === 'boolean') return;
      if (this.options.length <= 2) return;
      this.options.splice(index, 1);

      if (this.currentType === 'single') {
        // toujours 1 true
        if (!this.options.some(o => o.is_correct)) this.options[0].is_correct = true;
        // pas plus d'un true
        let seen = false;
        this.options.forEach(o => {
          if (o.is_correct) {
            if (!seen) seen = true;
            else o.is_correct = false;
          }
        });
      }
    },

    canRemove() {
      return this.currentType !== 'boolean' && this.options.length > 2;
    }
  }
}
</script>
@endsection
