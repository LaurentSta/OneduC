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

          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
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

            <button type="button" @click="changeType('cloze')"
                    :class="currentType === 'cloze' ? 'border-bleuone bg-blue-50 text-bleuone' : 'border-gray-200 bg-white text-gray-600'"
                    class="flex items-center justify-center p-4 border-2 rounded-xl transition-all duration-200">
              <div class="text-center">
                <span class="block font-bold text-sm">Texte à trous</span>
                <span class="block text-[10px] uppercase tracking-widest mt-1 opacity-60">cloze</span>
              </div>
            </button>
          </div>

          <input type="hidden" name="type" x-model="currentType">
        </div>
      </div>

      <hr class="border-gray-100">

      {{-- Propositions --}}
      <div class="space-y-4" x-show="currentType !== 'cloze'">
        <div class="flex items-center justify-between">
          <h2 class="text-lg font-semibold text-gray-800">Propositions de réponses</h2>

          <button type="button" @click="addOption()" x-show="currentType !== 'boolean' && currentType !== 'cloze'"
                  class="text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-2 rounded-lg font-medium transition">
            + Ajouter un choix
          </button>
        </div>

        <div class="space-y-3">
          <template x-for="(option, index) in options" :key="index">
  <div class="flex items-center gap-4 p-4 rounded-xl border border-gray-200 bg-white shadow-sm">

    {{-- Sélecteur bonne réponse --}}
    <div class="flex items-center justify-center">
      {{-- En single/boolean : radio group unique --}}
      <template x-if="currentType !== 'multiple'">
        <input
          type="radio"
          name="correct_choice_index"
          :value="index"
          :checked="option.is_correct"
          @change="markCorrect(index)"
          class="h-6 w-6 text-orangeone border-gray-300 focus:ring-orangeone rounded-full"
        >
      </template>

      {{-- En multiple : checkbox toggle --}}
      <template x-if="currentType === 'multiple'">
        <input
          type="checkbox"
          :checked="option.is_correct"
          @change="toggleCorrect(index)"
          class="h-6 w-6 text-orangeone border-gray-300 focus:ring-orangeone rounded"
        >
      </template>

      {{-- Champs envoyés au serveur --}}
      <input type="hidden" :name="`options[${index}][text]`" :value="option.text">
      <input type="hidden" :name="`options[${index}][is_correct]`" :value="option.is_correct ? 1 : 0">
    </div>

    {{-- Texte option --}}
    <div class="flex-1">
      <input
        type="text"
        x-model="option.text"
        :disabled="currentType === 'boolean' || currentType === 'cloze'"
        :required="currentType !== 'boolean' && currentType !== 'cloze'"
        class="w-full border-0 border-b border-transparent focus:border-orangeone focus:ring-0 text-sm p-0 pb-1"
        :placeholder="'Option ' + (index + 1)"
      >
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

      {{-- Texte à trous (Cloze) --}}
      <div class="space-y-4" x-show="currentType === 'cloze'">
        <div>
          <h2 class="text-lg font-semibold text-gray-800">Texte à trous</h2>
          <p class="mt-1 text-xs text-gray-500">
            Délimiteur pris en charge: <code>{{ '{' }}{{ '{' }}blank_key{{ '}' }}{{ '}' }}</code>.
          </p>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-4">
          <label class="block text-sm font-bold text-gray-700 mb-2" for="cloze_raw_text">
            Texte brut à compléter
          </label>
          <textarea
            id="cloze_raw_text"
            name="cloze_raw_text"
            rows="4"
            x-model="clozeRawText"
            @input.debounce.250ms="refreshClozeBlanks()"
            :required="currentType === 'cloze'"
            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-orangeone focus:border-orangeone shadow-sm font-mono text-sm"
            placeholder="Ex: =RECHERCHEV({{ '{' }}{{ '{' }}search_val{{ '}' }}{{ '}' }}; {{ '{' }}{{ '{' }}matrix{{ '}' }}{{ '}' }}; {{ '{' }}{{ '{' }}col_index{{ '}' }}{{ '}' }}; FAUX)"
          ></textarea>
        </div>

        <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
          <h3 class="text-sm font-bold text-gray-700">Configuration des trous</h3>
          <p class="text-xs text-gray-500 mt-1">
            Réponses acceptées: virgules, point-virgules ou retours à la ligne.
          </p>

          <div class="mt-4 space-y-3" x-show="Object.keys(clozeBlanks).length > 0">
            <template x-for="(blank, blankKey) in clozeBlanks" :key="blankKey">
              <div class="grid grid-cols-1 md:grid-cols-[180px_1fr_140px] gap-3 items-center rounded-lg border border-gray-200 bg-white p-3">
                <div class="text-xs font-mono font-semibold text-bleuone" x-text="blankKey"></div>

                <div>
                  <label class="block text-[11px] font-semibold text-gray-500 mb-1">Réponses acceptées</label>
                  <input
                    type="text"
                    :name="`cloze_blanks[${blankKey}][accepted_answers]`"
                    x-model="blank.accepted_answers"
                    :required="currentType === 'cloze'"
                    class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:ring-orangeone focus:border-orangeone"
                    placeholder="valeur_cherchee, valeur cherchée"
                  >
                </div>

                <div>
                  <label class="block text-[11px] font-semibold text-gray-500 mb-1">Points</label>
                  <input
                    type="number"
                    min="0"
                    step="1"
                    :name="`cloze_blanks[${blankKey}][points]`"
                    x-model.number="blank.points"
                    :required="currentType === 'cloze'"
                    class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:ring-orangeone focus:border-orangeone"
                  >
                </div>
              </div>
            </template>
          </div>

          <div x-show="Object.keys(clozeBlanks).length === 0" class="mt-4 text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded-lg p-3">
            Aucun trou détecté. Ajoutez au moins un placeholder <code>{{ '{' }}{{ '{' }}id{{ '}' }}{{ '}' }}</code>.
          </div>
        </div>
      </div>

      {{-- Médias optionnels (pliable) --}}
<details class="bg-gray-50 rounded-2xl p-6 border border-gray-100">
    <summary class="cursor-pointer text-sm font-bold text-gray-700 uppercase tracking-wider">
        Médias & Accessibilité (optionnel)
    </summary>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-6">

        {{-- IMAGE --}}
        {{-- IMAGE --}}
      <div class="space-y-4" x-data="{ preview: null, removeImage: false }">
          <label class="block text-xs font-bold text-gray-500 italic">Image</label>

          {{-- Image existante --}}
          @if(!empty($question->image_path))
              <div>
                  <p class="text-xs font-semibold text-gray-600 mb-2">Image actuelle</p>
                  <img
                      src="{{ asset('storage/'.$question->image_path) }}"
                      alt="{{ $question->image_alt ?? '' }}"
                      class="max-h-44 rounded-lg border border-gray-200 shadow-sm"
                  >
              </div>

              
          @endif

          {{-- Upload nouvelle image --}}
          <input type="file"
                name="image"
                accept="image/*"
                class="text-sm w-full"
                @change="preview = URL.createObjectURL($event.target.files[0])">

          {{-- Aperçu nouvelle image --}}
          <template x-if="preview">
              <div>
                  <p class="text-xs font-semibold text-gray-600 mb-2">Nouvelle image</p>
                  <img :src="preview"
                      class="max-h-44 rounded-lg border border-gray-200 shadow-sm">
              </div>
          </template>

          {{-- Texte alternatif --}}
          <input type="text"
                name="image_alt"
                value="{{ old('image_alt', $question->image_alt) }}"
                placeholder="Texte alternatif (obligatoire si image)"
                class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:ring-orangeone focus:border-orangeone">

          <input type="hidden" name="remove_image" :value="removeImage ? 1 : 0">

          <button type="button"
                class="px-4 py-2 rounded-lg bg-red-600 text-white text-xs font-semibold hover:opacity-90"
                @click="if (confirm('Supprimer l’image de cette question ?')) { removeImage = true; $el.form?.requestSubmit(); }">
            Supprimer l’image
        </button>

      </div>


        {{-- AUDIO --}}
        <div class="space-y-4">
            <label class="block text-xs font-bold text-gray-500 italic">Audio</label>

            @if(!empty($question->audio_path))
                <div>
                    <p class="text-xs font-semibold text-gray-600 mb-2">Audio actuel</p>
                    <audio controls class="w-full">
                        <source src="{{ asset('storage/'.$question->audio_path) }}">
                        Votre navigateur ne supporte pas la lecture audio.
                    </audio>
                </div>
            @endif

            <input type="file" name="audio" accept="audio/*" class="text-sm w-full">

            <textarea name="audio_transcript"
                      rows="2"
                      placeholder="Transcription audio"
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
    clozeRawText: {{ \Illuminate\Support\Js::from(old('cloze_raw_text', data_get($question->payload, 'raw_text', ''))) }},
    clozeBlanks: (function () {
      const initialBlanks = {{ \Illuminate\Support\Js::from(old('cloze_blanks', data_get($question->payload, 'blanks', []))) }};
      return initialBlanks && typeof initialBlanks === 'object' ? initialBlanks : {};
    })(),

    parseClozeKeys(rawText) {
      const text = String(rawText || '');
      const regex = new RegExp('\\{\\{\\s*([A-Za-z0-9_]+)\\s*\\}\\}', 'g');
      const seen = new Set();
      const keys = [];
      let match = null;

      while ((match = regex.exec(text)) !== null) {
        const key = String(match[1] || '').trim();
        if (!key || seen.has(key)) continue;
        seen.add(key);
        keys.push(key);
      }

      return keys;
    },

    normalizeClozeBlanks(rawBlanks) {
      const out = {};
      const source = rawBlanks && typeof rawBlanks === 'object' ? rawBlanks : {};

      Object.entries(source).forEach(([rawKey, rawCfg]) => {
        const key = String(rawKey || '').trim();
        if (!key) return;

        let accepted = rawCfg?.accepted_answers ?? '';
        if (Array.isArray(accepted)) {
          accepted = accepted.join(', ');
        }

        let points = Number(rawCfg?.points ?? 1);
        if (!Number.isFinite(points) || points < 0) {
          points = 1;
        }

        out[key] = {
          accepted_answers: String(accepted ?? ''),
          points: Math.round(points),
        };
      });

      return out;
    },

    refreshClozeBlanks() {
      const keys = this.parseClozeKeys(this.clozeRawText);
      const current = this.normalizeClozeBlanks(this.clozeBlanks);
      const next = {};

      keys.forEach((key) => {
        next[key] = current[key] ?? { accepted_answers: '', points: 1 };
      });

      this.clozeBlanks = next;
    },

    init() {
      this.clozeBlanks = this.normalizeClozeBlanks(this.clozeBlanks);

      if (this.currentType === 'cloze') {
        this.refreshClozeBlanks();
      }

      // Sécurisation: si options vides, créer un état valide
      if (!Array.isArray(this.options) || this.options.length === 0) {
        this.changeType(this.currentType || 'single');
      } else {
        // Normalisation booleen: 2 options + 1 seule correcte
        if (this.currentType === 'boolean') {
          this.ensureBoolean();
        }
        if (this.currentType === 'single') {
          this.ensureSingle();
        }
      }
    },

    // ---------- Helpers de cohérence ----------
    ensureBoolean() {
      // Force 2 options libellées, mais conserve le choix si possible
      let vraiCorrect = !!(this.options[0]?.is_correct);
      let fauxCorrect = !!(this.options[1]?.is_correct);

      // Si aucune ou 2 bonnes réponses -> fallback: Vrai correct
      const correctCount = (vraiCorrect ? 1 : 0) + (fauxCorrect ? 1 : 0);
      if (correctCount !== 1) {
        vraiCorrect = true;
        fauxCorrect = false;
      }

      this.options = [
        { text: 'Vrai', is_correct: vraiCorrect },
        { text: 'Faux', is_correct: fauxCorrect },
      ];
    },

    ensureSingle() {
      let firstTrue = this.options.findIndex(o => !!o.is_correct);
      if (firstTrue === -1) firstTrue = 0;
      this.options = this.options.map((o, i) => ({ ...o, is_correct: i === firstTrue }));
    },

    // ---------- Actions UI ----------
    changeType(type) {
      this.currentType = type;

      if (type === 'cloze') {
        if (!this.clozeRawText) {
          const open = '{' + '{';
          const close = '}' + '}';
          this.clozeRawText = `=RECHERCHEV(${open}search_val${close}; ${open}matrix${close}; ${open}col_index${close}; FAUX)`;
        }
        this.refreshClozeBlanks();
        return;
      }

      if (type === 'boolean') {
        this.options = [
          { text: 'Vrai', is_correct: true },
          { text: 'Faux', is_correct: false },
        ];
        return;
      }

      // Si on bascule depuis V/F ou options insuffisantes
      if (this.options.length < 2 || this.options[0]?.text === 'Vrai') {
        this.options = [
          { text: '', is_correct: true },
          { text: '', is_correct: false },
          { text: '', is_correct: false },
          { text: '', is_correct: false },
        ];
      }

      if (type === 'single') {
        this.ensureSingle();
      }
    },

    markCorrect(index) {
      if (this.currentType === 'cloze') {
        return;
      }

      // single/boolean : une seule bonne réponse
      this.options = this.options.map((o, i) => ({ ...o, is_correct: i === index }));
      if (this.currentType === 'boolean') {
        // garder les libellés propres
        this.ensureBoolean();
      }
    },

    toggleCorrect(index) {
      // multiple : toggle
      this.options[index].is_correct = !this.options[index].is_correct;
    },

    addOption() {
      if (this.currentType === 'boolean' || this.currentType === 'cloze') return;
      this.options.push({ text: '', is_correct: false });
    },

    removeOption(index) {
      if (this.currentType === 'boolean' || this.currentType === 'cloze') return;
      if (this.options.length <= 2) return;

      this.options.splice(index, 1);

      if (this.currentType === 'single') {
        this.ensureSingle();
      }
    },

    canRemove() {
      return this.currentType !== 'boolean' && this.currentType !== 'cloze' && this.options.length > 2;
    },
  }
}
</script>

@endsection
