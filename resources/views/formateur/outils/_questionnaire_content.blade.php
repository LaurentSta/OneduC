{{--
  Contenu d'un questionnaire (liste des questions + ajout manuel/IA).
  Réutilisé à la fois dans l'assistant de lancement (étape "Construire") et
  dans le panneau replié "Gérer mes questionnaires".

  Paramètres :
  - $questionnaire : QuizQuestionnaire
  - $wizardContext (bool, défaut false) : si true, les actions (ajouter,
    activer, générer via IA) renvoient l'utilisateur sur l'étape 3 de
    l'assistant plutôt que sur l'index simple.
--}}
@php $wizardContext = $wizardContext ?? false; @endphp

<div class="space-y-2">
  @forelse($questionnaire->questions as $question)
    @php
      $typeLabel = match($question->type) {
        'boolean' => 'Vrai/Faux',
        'single' => 'Choix unique',
        'multiple' => 'Choix multiples',
        'cloze' => 'Texte à trous',
        default => $question->type,
      };
    @endphp
    <div class="flex items-center justify-between gap-2 rounded-[8px] {{ $question->is_active ? 'bg-gray-50' : 'bg-amber-50' }} px-3 py-2">
      <div class="min-w-0">
        <span class="inline-flex items-center rounded-full bg-gray-200 px-2 py-0.5 text-[10px] font-bold text-gray-600 mr-2">{{ $typeLabel }}</span>
        @unless($question->is_active)
          <span class="inline-flex items-center rounded-full bg-amber-200 px-2 py-0.5 text-[10px] font-bold text-amber-800 mr-2">À valider</span>
        @endunless
        <span class="text-xs text-gray-700 truncate">{{ $question->question_text }}</span>
      </div>
      <div class="flex items-center gap-2 shrink-0">
        @unless($question->is_active)
          <form method="POST" action="{{ route('formateur.questionnaires.questions.toggle', [$questionnaire, $question]) }}">
            @csrf
            @if($wizardContext)
              <input type="hidden" name="wizard" value="1">
            @endif
            <button type="submit" class="text-[11px] font-semibold text-emerald-600 hover:underline">Activer</button>
          </form>
        @endunless
        <form method="POST" action="{{ route('formateur.questionnaires.questions.destroy', [$questionnaire, $question]) }}"
              onsubmit="return confirm('Supprimer cette question ?');">
          @csrf
          @method('DELETE')
          @if($wizardContext)
            <input type="hidden" name="wizard" value="1">
          @endif
          <button type="submit" class="text-gray-300 hover:text-red-500 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </form>
      </div>
    </div>
  @empty
    <p class="text-xs text-gray-400">Aucune question dans ce questionnaire.</p>
  @endforelse

  <div x-data="quizQuestionnaireQuestionForm()">
    <button type="button" @click="showForm = !showForm"
            class="mt-3 inline-flex w-full items-center justify-center gap-2 rounded-[10px] border-2 border-dashed px-4 py-3 text-sm font-bold transition"
            :class="showForm ? 'border-bleuone bg-bleuone/5 text-bleuone' : 'border-bleuone/30 text-bleuone hover:border-bleuone hover:bg-bleuone/5'">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 transition-transform" :class="showForm ? 'rotate-45' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
      </svg>
      <span x-text="showForm ? 'Fermer' : 'Ajouter une question'"></span>
    </button>

    <div x-show="showForm" x-cloak class="mt-3 rounded-[14px] border border-gray-200">
      <div class="flex border-b border-gray-200">
        <button type="button" @click="activeTab = 'manual'"
                :class="activeTab === 'manual' ? 'border-b-2 border-bleuone text-bleuone' : 'text-gray-400 hover:text-gray-600'"
                class="flex-1 px-4 py-2.5 text-xs font-bold uppercase tracking-wide transition">
          Ajouter une question
        </button>
        <button type="button" @click="activeTab = 'ia'"
                :class="activeTab === 'ia' ? 'border-b-2 border-bleuone text-bleuone' : 'text-gray-400 hover:text-gray-600'"
                class="flex-1 px-4 py-2.5 text-xs font-bold uppercase tracking-wide transition">
          Générer via IA
        </button>
      </div>

    <form x-show="activeTab === 'manual'" method="POST"
          action="{{ route('formateur.questionnaires.questions.store', $questionnaire) }}"
          class="space-y-6 p-5">
      @csrf
      @if($wizardContext)
        <input type="hidden" name="wizard" value="1">
      @endif

      <div>
        <label class="block text-sm font-bold text-gray-700 mb-2">Énoncé de la question</label>
        <textarea name="question_text" x-model="questionText" required rows="3" maxlength="1000"
                  placeholder="Ex : Quelle est la capitale de la France ?"
                  class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm focus:border-bleuone focus:outline-none focus:ring-2 focus:ring-bleuone/20 resize-none"></textarea>
      </div>

      <div>
        <label class="block text-sm font-bold text-gray-700 mb-3">Format de réponse</label>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
          <template x-for="(label, key) in types" :key="key">
            <button type="button" @click="type = key"
                    :class="type === key ? 'border-bleuone bg-bleuone/5 text-bleuone' : 'border-gray-200 bg-white text-gray-600 hover:border-gray-300'"
                    class="flex items-center justify-center p-4 border-2 rounded-xl transition text-center">
              <span class="block">
                <span class="block font-bold text-sm" x-text="label"></span>
                <span class="block text-[10px] uppercase tracking-widest mt-1 opacity-60" x-text="key"></span>
              </span>
            </button>
          </template>
        </div>
        <input type="hidden" name="type" :value="type">
      </div>

      <div x-show="type !== 'cloze'">
        <label class="block text-sm font-bold text-gray-700 mb-2">Points de la question</label>
        <input type="number" name="points" x-model="points" min="0" max="100"
               class="w-28 rounded-xl border border-gray-300 px-3 py-2 text-sm focus:border-bleuone focus:outline-none focus:ring-2 focus:ring-bleuone/20">
      </div>

      <hr class="border-gray-100">

      {{-- Choix unique / multiple / Vrai-Faux --}}
      <div x-show="type !== 'cloze'" class="space-y-4">
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-semibold text-gray-800">Propositions de réponses</h3>
          <button type="button" x-show="type !== 'boolean' && options.length < 6"
                  @click="options.push({ text: '', is_correct: false })"
                  class="text-xs font-semibold text-bleuone hover:underline">
            + Ajouter une option
          </button>
        </div>

        <template x-if="type === 'boolean'">
          <div class="space-y-3">
            <input type="hidden" name="options[0][text]" value="Vrai">
            <input type="hidden" name="options[1][text]" value="Faux">
            <input type="hidden" name="options[0][is_correct]" :value="booleanCorrect === 'vrai' ? 1 : 0">
            <input type="hidden" name="options[1][is_correct]" :value="booleanCorrect === 'faux' ? 1 : 0">
            <label class="flex items-center gap-3 rounded-xl border-2 px-4 py-3 cursor-pointer transition"
                   :class="booleanCorrect === 'vrai' ? 'border-bleuone bg-bleuone/5' : 'border-gray-200'">
              <input type="radio" x-model="booleanCorrect" value="vrai" class="h-4 w-4 accent-bleuone">
              <span class="text-sm font-medium text-gray-800">Vrai</span>
            </label>
            <label class="flex items-center gap-3 rounded-xl border-2 px-4 py-3 cursor-pointer transition"
                   :class="booleanCorrect === 'faux' ? 'border-bleuone bg-bleuone/5' : 'border-gray-200'">
              <input type="radio" x-model="booleanCorrect" value="faux" class="h-4 w-4 accent-bleuone">
              <span class="text-sm font-medium text-gray-800">Faux</span>
            </label>
          </div>
        </template>

        <template x-if="type !== 'boolean'">
          <div class="space-y-3">
            <template x-for="(opt, i) in options" :key="i">
              <div class="flex items-center gap-3 rounded-xl border-2 px-4 py-3 transition"
                   :class="(type === 'multiple' ? opt.is_correct : selectedIndex === i) ? 'border-bleuone bg-bleuone/5' : 'border-gray-200'">
                <input :type="type === 'multiple' ? 'checkbox' : 'radio'"
                       :checked="type === 'multiple' ? opt.is_correct : selectedIndex === i"
                       @change="type === 'multiple' ? (opt.is_correct = $event.target.checked) : (selectedIndex = i)"
                       class="h-4 w-4 shrink-0 accent-bleuone">
                <input type="text" :name="`options[${i}][text]`" x-model="opt.text" required maxlength="255"
                       :placeholder="`Option ${i + 1}`"
                       class="flex-1 border-0 bg-transparent p-0 text-sm text-gray-800 focus:outline-none focus:ring-0">
                <input type="hidden" :name="`options[${i}][is_correct]`"
                       :value="(type === 'multiple' ? opt.is_correct : selectedIndex === i) ? 1 : 0">
                <button type="button" x-show="options.length > 2" @click="options.splice(i, 1)"
                        class="shrink-0 text-gray-300 hover:text-red-500">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                  </svg>
                </button>
              </div>
            </template>
          </div>
        </template>

        <p class="text-xs text-gray-500">Choix unique / Vrai-Faux : une seule bonne réponse. Choix multiple : une ou plusieurs.</p>
      </div>

      {{-- Texte à trous --}}
      <div x-show="type === 'cloze'" class="space-y-3">
        <h3 class="text-lg font-semibold text-gray-800">Texte à trous</h3>
        <textarea name="cloze_raw_text" x-model="clozeRawText" @input="ensureBlanks()" rows="3"
                  placeholder="Ex : La capitale de la France est @{{capitale}}."
                  class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm focus:border-bleuone focus:outline-none focus:ring-2 focus:ring-bleuone/20 resize-none"></textarea>
        <p class="text-xs text-gray-500">Les trous sont détectés automatiquement à partir du texte. Utilisez <code>@{{cle}}</code> pour chaque trou.</p>
        <div class="space-y-2">
          <template x-for="key in detectedBlankKeys" :key="key">
            <div class="flex items-center gap-2">
              <span class="text-xs font-mono text-gray-500 shrink-0" x-text="key"></span>
              <input type="text" :name="`cloze_blanks[${key}][accepted_answers]`" required
                     placeholder="Réponses acceptées, séparées par une virgule"
                     class="flex-1 rounded-xl border border-gray-300 px-3 py-2 text-sm focus:border-bleuone focus:outline-none focus:ring-2 focus:ring-bleuone/20">
              <input type="number" :name="`cloze_blanks[${key}][points]`" value="1" min="0"
                     class="w-16 rounded-xl border border-gray-300 px-3 py-2 text-sm focus:border-bleuone focus:outline-none focus:ring-2 focus:ring-bleuone/20">
            </div>
          </template>
        </div>
      </div>

      <button type="submit" class="w-full rounded-xl bg-bleuone px-4 py-2.5 text-sm font-bold text-white hover:bg-bleuone-light transition">
        Ajouter la question
      </button>
    </form>

    <form x-show="activeTab === 'ia'" x-cloak method="POST"
          action="{{ route('formateur.questionnaires.questions.generate-ia', $questionnaire) }}"
          class="space-y-4 p-5">
      @csrf
      @if($wizardContext)
        <input type="hidden" name="wizard" value="1">
      @endif
      <div>
        <label class="block text-sm font-bold text-gray-700 mb-2">Sujet</label>
        <textarea name="topic" rows="2" required maxlength="500"
                  placeholder="Ex : la sécurité incendie, niveau débutant"
                  class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm focus:border-bleuone focus:outline-none focus:ring-2 focus:ring-bleuone/20 resize-none"></textarea>
      </div>
      <div>
        <label class="block text-sm font-bold text-gray-700 mb-2">Nombre de questions</label>
        <input type="number" name="count" value="5" min="1" max="15"
               class="w-24 rounded-xl border border-gray-300 px-3 py-2 text-sm focus:border-bleuone focus:outline-none focus:ring-2 focus:ring-bleuone/20">
      </div>
      <p class="text-xs text-gray-500">Les questions générées sont créées inactives : relisez-les et activez celles que vous souhaitez utiliser.</p>
      <button type="submit" class="w-full rounded-xl bg-bleuone px-4 py-2.5 text-sm font-bold text-white hover:bg-bleuone-light transition">
        Générer les questions
      </button>
    </form>
    </div>
  </div>
</div>
