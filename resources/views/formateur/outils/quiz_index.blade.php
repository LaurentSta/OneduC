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
            <li class="text-gray-400">Quiz en direct</li>
          </ol>
        </nav>
        <p class="font-raleway text-2xl text-bleuone">Quiz en direct</p>
        <p class="text-sm text-gray-500 mt-1">
          Quatre étapes : groupe, questionnaire, construction, lancement.
        </p>
      </div>
    </div>
  </header>

  @if(session('success'))
    <div class="mb-4 rounded-[14px] bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-800">
      {{ session('success') }}
    </div>
  @endif

  @if(session('error'))
    <div class="mb-4 rounded-[14px] bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
      {{ session('error') }}
    </div>
  @endif

  {{-- ── Assistant de lancement (pas-à-pas) ──────────────────────── --}}
  <div class="bg-white rounded-[20px] shadow-md p-8 max-w-xl mx-auto mb-8"
       x-data="{
         step: {{ (int) session('wizard_step', 1) }},
         groups: {{ Js::from($groups->map(fn ($g) => ['id' => (string) $g->id, 'name' => $g->name])->values()) }},
         questionnaires: {{ Js::from($questionnaires->map(fn ($q) => [
             'id' => (string) $q->id,
             'title' => $q->title,
             'active' => $q->questions->where('is_active', true)->count(),
         ])->values()) }},
         groupId: '',
         groupLabel: 'Tous les groupes',
         questionnaireId: '{{ session('wizard_questionnaire_id', '') }}',
         questionnaireLabel: '{{ session('wizard_questionnaire_title', '') }}',
         questionnaireActive: {{ (int) session('wizard_questionnaire_active', 0) }},
         selectGroup(id, name) { this.groupId = id; this.groupLabel = name; },
         selectQuestionnaire(q) { this.questionnaireId = q.id; this.questionnaireLabel = q.title; this.questionnaireActive = q.active; },
         startNewQuestionnaire() { this.questionnaireId = ''; this.questionnaireLabel = ''; this.questionnaireActive = 0; this.step = 3; },
       }">

    @if($errors->any())
      <div class="mb-6 rounded-[10px] bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
        {{ $errors->first() }}
      </div>
    @endif

    {{-- Puces de progression --}}
    <div class="flex items-center justify-center gap-2 mb-8 text-xs font-semibold">
      <span class="flex items-center gap-1.5" :class="step >= 1 ? 'text-bleuone' : 'text-gray-400'">
        <span class="w-2 h-2 rounded-full" :class="step > 1 ? 'bg-emerald-500' : (step === 1 ? 'bg-bleuone ring-4 ring-bleuone/15' : 'bg-gray-300')"></span>
        Groupe
      </span>
      <span class="w-4 h-px bg-gray-200"></span>
      <span class="flex items-center gap-1.5" :class="step >= 2 ? 'text-bleuone' : 'text-gray-400'">
        <span class="w-2 h-2 rounded-full" :class="step > 2 ? 'bg-emerald-500' : (step === 2 ? 'bg-bleuone ring-4 ring-bleuone/15' : 'bg-gray-300')"></span>
        Questionnaire
      </span>
      <span class="w-4 h-px bg-gray-200"></span>
      <span class="flex items-center gap-1.5" :class="step >= 3 ? 'text-bleuone' : 'text-gray-400'">
        <span class="w-2 h-2 rounded-full" :class="step > 3 ? 'bg-emerald-500' : (step === 3 ? 'bg-bleuone ring-4 ring-bleuone/15' : 'bg-gray-300')"></span>
        Construction
      </span>
      <span class="w-4 h-px bg-gray-200"></span>
      <span class="flex items-center gap-1.5" :class="step >= 4 ? 'text-bleuone' : 'text-gray-400'">
        <span class="w-2 h-2 rounded-full" :class="step === 4 ? 'bg-bleuone ring-4 ring-bleuone/15' : 'bg-gray-300'"></span>
        Lancer
      </span>
    </div>

    {{-- Étape 1 : Groupe --}}
    <div x-show="step === 1" x-cloak>
      <h2 class="text-lg font-bold text-bleuone text-center mb-1">Quel groupe ?</h2>
      <p class="text-xs text-gray-500 text-center mb-5">Qui doit pouvoir rejoindre cette session ?</p>

      <div class="space-y-2">
        <button type="button" @click="selectGroup('', 'Tous les groupes')"
                :class="groupId === '' ? 'border-bleuone bg-bleuone/5' : 'border-gray-200 hover:border-gray-300'"
                class="w-full flex items-center justify-between rounded-xl border-2 px-4 py-3 text-left transition">
          <span class="text-sm font-semibold text-gray-800">Tous les groupes</span>
          <span class="text-xs text-gray-400">Tous vos stagiaires</span>
        </button>
        <template x-for="group in groups" :key="group.id">
          <button type="button" @click="selectGroup(group.id, group.name)"
                  :class="groupId === group.id ? 'border-bleuone bg-bleuone/5' : 'border-gray-200 hover:border-gray-300'"
                  class="w-full flex items-center justify-between rounded-xl border-2 px-4 py-3 text-left transition">
            <span class="text-sm font-semibold text-gray-800" x-text="group.name"></span>
          </button>
        </template>
      </div>

      <div class="flex justify-end mt-6">
        <button type="button" @click="step = 2"
                class="inline-flex items-center gap-1 rounded-[10px] bg-bleuone px-5 py-2.5 text-sm font-bold text-white hover:bg-bleuone-light transition">
          Continuer →
        </button>
      </div>
    </div>

    {{-- Étape 2 : Questionnaire --}}
    <div x-show="step === 2" x-cloak>
      <h2 class="text-lg font-bold text-bleuone text-center mb-1">Quel questionnaire ?</h2>
      <p class="text-xs text-gray-500 text-center mb-5">Pour le groupe : <strong x-text="groupLabel"></strong></p>

      <template x-if="questionnaires.length === 0">
        <p class="text-sm text-gray-400 text-center mb-4">Vous n'avez pas encore de questionnaire.</p>
      </template>

      <div class="space-y-2">
        <template x-for="q in questionnaires" :key="q.id">
          <button type="button" @click="selectQuestionnaire(q)"
                  :class="questionnaireId === q.id ? 'border-bleuone bg-bleuone/5' : 'border-gray-200 hover:border-gray-300'"
                  class="w-full flex items-center justify-between gap-3 rounded-xl border-2 px-4 py-3 text-left transition">
            <span class="text-sm font-semibold text-gray-800" x-text="q.title"></span>
            <span class="inline-flex items-center shrink-0 rounded-full px-2 py-0.5 text-[10px] font-bold"
                  :class="q.active > 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'"
                  x-text="q.active + ' active' + (q.active > 1 ? 's' : '')"></span>
          </button>
        </template>

        <button type="button" @click="startNewQuestionnaire()"
                class="w-full flex items-center justify-between rounded-xl border-2 border-dashed border-gray-300 px-4 py-3 text-left transition hover:border-bleuone">
          <span class="text-sm font-semibold text-bleuone">+ Nouveau questionnaire</span>
          <span class="text-xs text-gray-400">Nommer puis construire</span>
        </button>
      </div>

      <div class="flex items-center justify-between mt-6">
        <button type="button" @click="step = 1" class="text-sm font-semibold text-bleuone hover:underline">← Groupe</button>
        <button type="button" @click="step = 3"
                :disabled="questionnaireId === ''"
                :class="questionnaireId === '' ? 'bg-gray-300 cursor-not-allowed' : 'bg-bleuone hover:bg-bleuone-light'"
                class="rounded-[10px] px-5 py-2.5 text-sm font-bold text-white transition">
          Continuer →
        </button>
      </div>
    </div>

    {{-- Étape 3 : Construction --}}
    <div x-show="step === 3" x-cloak>
      <template x-if="questionnaireId === ''">
        <div>
          <h2 class="text-lg font-bold text-bleuone text-center mb-1">Nommez votre questionnaire</h2>
          <p class="text-xs text-gray-500 text-center mb-5">Vous pourrez ajouter les questions juste après.</p>

          <form method="POST" action="{{ route('formateur.questionnaires.store') }}" class="flex gap-2">
            @csrf
            <input type="text" name="title" required maxlength="255" placeholder="Titre du questionnaire"
                   class="flex-1 rounded-[10px] border border-gray-300 px-3 py-2.5 text-sm focus:border-bleuone focus:outline-none focus:ring-2 focus:ring-bleuone/20">
            <button type="submit" class="shrink-0 rounded-[10px] bg-bleuone px-5 py-2.5 text-sm font-bold text-white hover:bg-bleuone-light transition">
              Créer
            </button>
          </form>

          <div class="mt-6">
            <button type="button" @click="step = 2" class="text-sm font-semibold text-bleuone hover:underline">← Questionnaire</button>
          </div>
        </div>
      </template>

      <template x-if="questionnaireId !== ''">
        <div>
          <h2 class="text-lg font-bold text-bleuone text-center mb-1">Construire les questions</h2>
          <p class="text-xs text-gray-500 text-center mb-5"><strong x-text="questionnaireLabel"></strong></p>

          @foreach($questionnaires as $questionnaire)
            <div x-show="questionnaireId === '{{ $questionnaire->id }}'" x-cloak>
              @include('formateur.outils._questionnaire_content', ['questionnaire' => $questionnaire, 'wizardContext' => true])
            </div>
          @endforeach

          <template x-if="questionnaireActive === 0">
            <p class="mt-3 text-xs text-amber-600">Ajoutez au moins une question active pour pouvoir lancer.</p>
          </template>

          <div class="flex items-center justify-between mt-6">
            <button type="button" @click="step = 2" class="text-sm font-semibold text-bleuone hover:underline">← Questionnaire</button>
            <button type="button" @click="step = 4"
                    :disabled="questionnaireActive === 0"
                    :class="questionnaireActive === 0 ? 'bg-gray-300 cursor-not-allowed' : 'bg-bleuone hover:bg-bleuone-light'"
                    class="rounded-[10px] px-5 py-2.5 text-sm font-bold text-white transition">
              Continuer →
            </button>
          </div>
        </div>
      </template>
    </div>

    {{-- Étape 4 : Lancer --}}
    <div x-show="step === 4" x-cloak>
      <h2 class="text-lg font-bold text-bleuone text-center mb-1">Prêt à lancer</h2>
      <p class="text-xs text-gray-500 text-center mb-5">Vérifiez avant de démarrer la session.</p>

      <div class="rounded-xl border border-gray-200 divide-y divide-gray-100 mb-6">
        <div class="flex items-center justify-between px-4 py-3">
          <span class="text-xs font-semibold text-gray-500">Groupe</span>
          <span class="text-sm font-bold text-gray-800" x-text="groupLabel"></span>
        </div>
        <div class="flex items-center justify-between px-4 py-3">
          <span class="text-xs font-semibold text-gray-500">Questionnaire</span>
          <span class="text-sm font-bold text-gray-800" x-text="questionnaireLabel + ' (' + questionnaireActive + ' question' + (questionnaireActive > 1 ? 's' : '') + ' active' + (questionnaireActive > 1 ? 's' : '') + ')'"></span>
        </div>
      </div>

      <form method="POST" action="{{ route('formateur.group-quiz.launch') }}">
        @csrf
        <input type="hidden" name="group_id" :value="groupId">
        <input type="hidden" name="questionnaire_id" :value="questionnaireId">

        <div class="flex items-center justify-between">
          <button type="button" @click="step = 3" class="text-sm font-semibold text-bleuone hover:underline">← Construction</button>
          <button type="submit"
                  class="inline-flex items-center justify-center gap-2 rounded-[10px] bg-orangeone px-6 py-2.5 text-sm font-bold text-white hover:bg-orangeone-hover transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Lancer le quiz en direct
          </button>
        </div>
      </form>
    </div>
  </div>

  {{-- ── Sessions récentes ────────────────────────────────────────── --}}
  @if($sessions->isNotEmpty())
    <div class="space-y-3 mb-4">
      <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Sessions récentes</p>
      @foreach($sessions as $session)
        @php
          $statusLabel = match($session->status) {
            'waiting'          => 'En attente',
            'question_open'    => 'Question en cours',
            'answer_revealed'  => 'Correction affichée',
            'closed'           => 'Terminée',
            default            => $session->status,
          };
          $statusColor = match($session->status) {
            'waiting'          => 'bg-yellow-100 text-yellow-700',
            'question_open'    => 'bg-blue-100 text-blue-700',
            'answer_revealed'  => 'bg-purple-100 text-purple-700',
            'closed'           => 'bg-gray-100 text-gray-500',
            default            => 'bg-gray-100 text-gray-500',
          };
        @endphp
        <div class="bg-white rounded-[20px] shadow-md p-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
          <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2 mb-0.5">
              <p class="text-sm font-bold text-gray-900">
                {{ $session->group?->name ?? 'Tous les groupes' }}
              </p>
              <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold {{ $statusColor }}">
                {{ $statusLabel }}
              </span>
            </div>
            <p class="text-xs text-gray-500">
              Code : <span class="font-mono font-semibold text-bleuone">{{ $session->access_code }}</span>
              · {{ $session->created_at->diffForHumans() }}
            </p>
          </div>
          <a href="{{ route('formateur.group-quiz.show', $session) }}"
             class="inline-flex items-center gap-1.5 rounded-[8px] {{ $session->status !== 'closed' ? 'bg-bleuone text-white hover:bg-bleuone-light' : 'border border-gray-300 bg-white text-gray-600 hover:bg-gray-50' }} px-3 py-1.5 text-xs font-bold transition shrink-0">
            {{ $session->status !== 'closed' ? 'Reprendre' : 'Résultats' }}
          </a>
        </div>
      @endforeach
    </div>
  @endif

  {{-- ── Gestion des questionnaires (repliée) ────────────────────── --}}
  <details class="bg-white rounded-[20px] shadow-md mb-4">
    <summary class="cursor-pointer select-none px-6 py-4 font-varela text-base font-bold text-bleuone">
      Gérer mes questionnaires
    </summary>
    <div class="px-6 pb-6">
      <div class="space-y-3">
        @forelse($questionnaires as $questionnaire)
          <div x-data="{ open: false }" class="rounded-[14px] border border-gray-200">
            <div class="flex items-center gap-3 p-3">
              <button type="button" @click="open = !open" class="flex-1 flex items-center justify-between text-left">
                <span class="text-sm font-bold text-gray-800">{{ $questionnaire->title }}</span>
                <span class="text-[11px] text-gray-400 mr-2">{{ $questionnaire->questions->count() }} question(s)</span>
              </button>
              <form method="POST" action="{{ route('formateur.questionnaires.destroy', $questionnaire) }}"
                    onsubmit="return confirm('Supprimer ce questionnaire et ses questions ?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-gray-300 hover:text-red-500 transition">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                  </svg>
                </button>
              </form>
            </div>

            <div x-show="open" x-cloak class="border-t border-gray-100 p-3 space-y-2">
              @include('formateur.outils._questionnaire_content', ['questionnaire' => $questionnaire])
            </div>
          </div>
        @empty
          <p class="text-sm text-gray-400 text-center py-6">Aucun questionnaire créé pour l'instant.</p>
        @endforelse
      </div>
    </div>
  </details>

</div>

<script>
  function quizQuestionnaireQuestionForm() {
    return {
      showForm: false,
      activeTab: 'manual',
      type: 'single',
      types: {
        single: 'Choix unique',
        multiple: 'Choix multiple',
        boolean: 'Vrai / Faux',
        cloze: 'Texte à trous',
      },
      questionText: '',
      points: 1,
      options: [{ text: '', is_correct: false }, { text: '', is_correct: false }],
      selectedIndex: 0,
      booleanCorrect: 'vrai',
      clozeRawText: '',
      clozeBlanks: {},
      get detectedBlankKeys() {
        const regex = /\{\{\s*([A-Za-z0-9_]+)\s*\}\}/g;
        const keys = [];
        let match;
        while ((match = regex.exec(this.clozeRawText)) !== null) {
          if (!keys.includes(match[1])) keys.push(match[1]);
        }
        return keys;
      },
      ensureBlanks() {
        // no-op: detectedBlankKeys est recalculé automatiquement, cette
        // méthode existe pour être appelée depuis @input si besoin plus tard.
      },
    };
  }
</script>
@endsection
