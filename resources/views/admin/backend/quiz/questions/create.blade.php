@extends('admin.admin_dashboard')

@section('admin')
<div class="max-w-[1248px] mx-auto px-4 py-10" x-data="quizManager()">
  <div class="bg-white rounded-[20px] shadow-md p-8 w-full border border-gray-100">

    {{-- En-tête --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8 border-b pb-6">
      <div>
        <h1 class="text-2xl font-raleway text-bleuone font-bold">Nouvelle Question pédagogique</h1>
        <p class="text-sm text-gray-500 mt-1">
          Module : <span class="font-semibold text-gray-800">{{ $lecture->lecture_title }}</span>
        </p>
      </div>
      <a href="{{ route('admin.quiz.questions.index', ['lecture' => $lecture->id]) }}"
         class="text-bleuone hover:underline text-sm font-medium">
        &larr; Retour à la banque de questions
      </a>
    </div>

    {{-- IMPORTANT : afficher les erreurs, sinon on “ne voit rien” --}}
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
          action="{{ route('admin.quiz.questions.store', ['lecture' => $lecture->id]) }}"
          enctype="multipart/form-data"
          class="space-y-8">
      @csrf

      {{-- Section 1 : Énoncé --}}
      <div class="space-y-4">
        <div>
          <label class="block text-sm font-bold text-gray-700 mb-2" for="question_text">Énoncé de la question</label>
          <textarea name="question_text" id="question_text" rows="3" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-orangeone focus:border-orangeone shadow-sm"
                    placeholder="Ex: Quelle est la capitale de la France ?">{{ old('question_text') }}</textarea>
        </div>

        {{-- Sélecteur de Type --}}
        <div>
          <span class="block text-sm font-bold text-gray-700 mb-3">Format de réponse</span>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <template x-for="(label, key) in types" :key="key">
              <button type="button" @click="changeType(key)"
                      :class="currentType === key ? 'border-bleuone bg-blue-50 text-bleuone' : 'border-gray-200 bg-white text-gray-600'"
                      class="flex items-center justify-center p-4 border-2 rounded-xl transition-all duration-200">
                <div class="text-center">
                  <span class="block font-bold text-sm" x-text="label"></span>
                  <span class="block text-[10px] uppercase tracking-widest mt-1 opacity-60" x-text="key"></span>
                </div>
              </button>
            </template>
          </div>

          <input type="hidden" name="type" :value="currentType">
        </div>
      </div>

      <hr class="border-gray-100">

      {{-- Section 2 : Réponses --}}
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

              {{-- Correcteur --}}
              <div class="flex items-center justify-center">

                {{-- UI : checkbox en multiple, radio en single/boolean --}}
                <input
                  :type="currentType === 'multiple' ? 'checkbox' : 'radio'"
                  :name="currentType === 'multiple' ? `correct_${index}` : 'correct_choice_index'"
                  :value="index"
                  class="h-6 w-6 text-orangeone border-gray-300 focus:ring-orangeone rounded-full"
                  :checked="!!option.is_correct"
                  @change="markCorrect(index)"
                >

                {{-- IMPORTANT : on envoie TOUJOURS options[i][is_correct] (exigé par le contrôleur) --}}
                <input type="hidden" :name="`options[${index}][is_correct]`" value="0">
                <input type="hidden" :name="`options[${index}][is_correct]`" :value="option.is_correct ? 1 : 0">
              </div>

              {{-- Texte --}}
              <div class="flex-1">
                <input type="text"
                       :name="`options[${index}][text]`"
                       x-model="option.text"
                       required
                       class="w-full border-0 border-b border-transparent focus:border-orangeone focus:ring-0 text-sm p-0 pb-1"
                       :placeholder="'Option ' + (index + 1)">
              </div>

              {{-- Actions --}}
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
          Choix unique / Vrai-Faux : une seule bonne réponse. Choix multiple : une ou plusieurs.
        </p>
      </div>

      {{-- Section 3 : Médias (optionnel / repliable) --}}
      <div class="border border-gray-100 rounded-2xl p-6"
           x-data="{ openMedia: {{ ($errors->has('image') || $errors->has('image_alt') || $errors->has('audio') || $errors->has('audio_transcript')) ? 'true' : 'false' }} }">

        <div class="flex items-center justify-between">
          <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider">Médias (optionnel)</h3>

          <button type="button"
                  @click="openMedia = !openMedia"
                  class="text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-2 rounded-lg font-medium transition"
                  :aria-expanded="openMedia.toString()">
            <span x-text="openMedia ? 'Masquer' : 'Ajouter des médias'"></span>
          </button>
        </div>

        <div x-show="openMedia" x-transition x-cloak class="mt-6">
          <p class="text-xs text-gray-500 mb-5">
            Image et audio sont facultatifs. Si une image est ajoutée, une description alternative est obligatoire.
          </p>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="space-y-3">
              <label class="block text-xs font-bold text-gray-500 italic">Illustration (Image)</label>
              <div x-data="{ preview: null }" class="mt-3">
  <input type="file" name="image" accept="image/*"
         @change="preview = $event.target.files?.[0] ? URL.createObjectURL($event.target.files[0]) : null"
         class="text-sm w-full ...">

  <template x-if="preview">
    <img :src="preview" alt="" class="mt-3 max-h-40 rounded-lg border border-gray-200 shadow-sm">
  </template>
</div>

              <input type="file" name="image" accept="image/*"
                     class="text-sm w-full file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-bleuone file:text-white hover:file:opacity-90">

              <input type="text" name="image_alt" value="{{ old('image_alt') }}"
                     placeholder="Description alternative (obligatoire si image)"
                     class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:ring-orangeone focus:border-orangeone">
            </div>

            <div class="space-y-3">
              <label class="block text-xs font-bold text-gray-500 italic">Support Audio</label>
              <input type="file" name="audio" accept="audio/*"
                     class="text-sm w-full file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-gray-800 file:text-white hover:file:opacity-90">

              <textarea name="audio_transcript" rows="2"
                        placeholder="Transcription (recommandée)"
                        class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:ring-orangeone focus:border-orangeone">{{ old('audio_transcript') }}</textarea>
            </div>
          </div>
        </div>
      </div>

      {{-- Validation --}}
      <div class="flex items-center justify-between pt-6 border-t">
        <input type="hidden" name="is_active" value="0">

        <label class="inline-flex items-center cursor-pointer select-none">
          <input type="checkbox" name="is_active" value="1" {{ old('is_active', 1) ? 'checked' : '' }}
                 class="sr-only peer">

          <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full
                      peer-checked:bg-orangeone
                      after:content-[''] after:absolute after:top-[2px] after:left-[2px]
                      after:bg-white after:border after:border-gray-300 after:rounded-full
                      after:h-5 after:w-5 after:transition-all
                      peer-checked:after:translate-x-full peer-checked:after:border-white">
          </div>

          <span class="ml-3 text-sm font-medium text-gray-700">Rendre la question active</span>
        </label>

        <button type="submit"
                class="bg-orangeone text-white px-10 py-3 rounded-xl font-bold shadow-lg shadow-orange-900/20 hover:opacity-90 transition">
          Enregistrer la question
        </button>
      </div>

    </form>
  </div>
</div>

<script>
function quizManager() {
  return {
    currentType: @json(old('type', 'boolean')),
    types: {
      'boolean': 'Vrai / Faux',
      'single': 'Choix Unique',
      'multiple': 'Choix Multiple'
    },

    options: (function () {
      const oldOptions = @json(old('options'));
      if (Array.isArray(oldOptions) && oldOptions.length) {
        return oldOptions.map(o => ({
          text: o.text ?? '',
          is_correct: !!(o.is_correct && (o.is_correct === 1 || o.is_correct === '1' || o.is_correct === true))
        }));
      }
      // défaut
      return [
        { text: 'Vrai', is_correct: true },
        { text: 'Faux', is_correct: false }
      ];
    })(),

    changeType(type) {
      this.currentType = type;

      if (type === 'boolean') {
        this.options = [
          { text: 'Vrai', is_correct: true },
          { text: 'Faux', is_correct: false }
        ];
        return;
      }

      // si on vient de boolean, on recrée 4 options
      if (this.options.length < 2 || (this.options[0]?.text === 'Vrai' && this.options[1]?.text === 'Faux')) {
        this.options = [
          { text: '', is_correct: true },
          { text: '', is_correct: false },
          { text: '', is_correct: false },
          { text: '', is_correct: false }
        ];
      }

      // cohérence : en single, une seule bonne réponse
      if (type === 'single') {
        this.enforceSingleCorrect();
      }
    },

    markCorrect(index) {
      if (this.currentType === 'multiple') {
        // toggle
        this.options[index].is_correct = !this.options[index].is_correct;
        return;
      }
      // single/boolean : une seule
      this.options = this.options.map((o, i) => ({ ...o, is_correct: i === index }));
    },

    enforceSingleCorrect() {
      const first = this.options.findIndex(o => !!o.is_correct);
      const idx = first >= 0 ? first : 0;
      this.options = this.options.map((o, i) => ({ ...o, is_correct: i === idx }));
    },

    addOption() {
      if (this.currentType === 'boolean') return;
      this.options.push({ text: '', is_correct: false });
    },

    removeOption(index) {
      if (this.currentType === 'boolean') return;
      if (this.options.length <= 2) return;

      const wasCorrect = !!this.options[index].is_correct;
      this.options.splice(index, 1);

      if (this.currentType === 'single' && wasCorrect) {
        this.enforceSingleCorrect();
      }
    },

    canRemove() {
      return this.currentType !== 'boolean' && this.options.length > 2;
    },

    init() {
      if (this.currentType === 'single' || this.currentType === 'boolean') {
        this.enforceSingleCorrect();
      }
    }
  }
}
</script>
@endsection
