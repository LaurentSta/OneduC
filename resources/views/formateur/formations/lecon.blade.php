{{-- resources/views/formateur/formations/lecon.blade.php --}}

@extends('formateur.formations.master_lecon')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

@php
    $lecture   = $selectedLecture ?? ($lecture ?? null);
    $moduleId  = (int) ($module->id ?? 0);
    $lectureId = $lecture ? (int) $lecture->id : null;
    $sectionId = $lecture ? (int) $lecture->section_id : null;

    // Conserver le contexte (mode / group_id / include_hidden) dans la navigation
    $contextQuery = is_array($contextQuery ?? null) ? $contextQuery : [];
    $appendQuery = static function (string $url, array $query): string {
        if (empty($query)) {
            return $url;
        }
        return $url . (str_contains($url, '?') ? '&' : '?') . http_build_query($query);
    };

    // --- URL SCORM (robuste) ---
    $scormUrl = null;
    if ($lecture && !empty($lecture->scorm_path)) {
        $p = (string) $lecture->scorm_path;
        if (\Illuminate\Support\Str::startsWith($p, ['http://', 'https://'])) {
            $scormUrl = $p;
        } elseif (\Illuminate\Support\Str::startsWith($p, '/')) {
            $scormUrl = url($p);
        } else {
            $scormUrl = asset($p);
        }
    }

    // --- Navigation ---
    $finalUrl = $moduleId
        ? $appendQuery(route('formateur.formations.detail', ['module' => $moduleId]), $contextQuery)
        : route('formateur.dashboard');
    
    $nextUrl = '#';
    if (!empty($nextLecture) && isset($nextLecture['url'])) {
        $nextUrl = $appendQuery((string) $nextLecture['url'], $contextQuery);
    }

    // --- Quiz Start URL ---
    $quizStartUrl = null;
    if ($lecture && !empty($lecture->quiz_enabled) && $moduleId && $sectionId && $lectureId) {
        if (\Illuminate\Support\Facades\Route::has('formateur.quiz.start')) {
            $quizStartUrl = \Illuminate\Support\Facades\URL::signedRoute('formateur.quiz.start', array_merge([
                'module'  => $moduleId,
                'section' => $sectionId,
                'lecture' => $lectureId,
            ], $contextQuery));
        }
    }
@endphp

@if ($lectureId)
  <script>window.currentLectureId = {{ $lectureId }};</script>
@endif

{{-- Wrapper Principal avec Alpine pour gérer l'état Inspecteur --}}
<div x-data="{ 
    mode: 'formateur', // 'stagiaire' ou 'formateur'
    activeTab: 'quiz' 
}" class="flex flex-col h-[calc(100vh-64px)] bg-white overflow-hidden">

  {{-- BARRE D'OUTILS FORMATEUR (Cockpit) --}}
  <div class="bg-gray-800 text-white px-4 py-3 flex items-center justify-between shadow-md z-30 shrink-0 border-b border-gray-700">
      <div class="flex items-center gap-6">
          <div class="flex items-center gap-2">
            <span class="font-bold text-orangeone uppercase text-[10px] tracking-widest">Mode</span>
            
            {{-- Toggle Switch --}}
            <button @click="mode = (mode === 'formateur' ? 'stagiaire' : 'formateur')"
                    class="relative inline-flex items-center h-6 rounded-full w-11 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orangeone focus:ring-offset-gray-900"
                    :class="mode === 'stagiaire' ? 'bg-green-500' : 'bg-gray-600'">
                <span class="sr-only">Changer le mode de vue</span>
                <span class="inline-block w-4 h-4 transform bg-white rounded-full transition-transform duration-200 ease-in-out"
                      :class="mode === 'stagiaire' ? 'translate-x-6' : 'translate-x-1'"></span>
            </button>
            <span class="text-xs font-semibold ml-1" x-text="mode === 'formateur' ? 'Inspecteur (Vue Formateur)' : 'Réel (Vue Stagiaire)'"></span>
          </div>

          {{-- Onglets Inspecteur (visibles seulement en mode formateur) --}}
          <div class="flex gap-1 bg-gray-900/50 p-1 rounded-lg" x-show="mode === 'formateur'" x-cloak>
              <button @click="activeTab = 'quiz'" 
                      :class="activeTab === 'quiz' ? 'bg-gray-700 text-white shadow' : 'text-gray-400 hover:text-gray-200'"
                      class="px-3 py-1 text-xs font-bold rounded-md transition-all">
                  Quiz & Corrigés
              </button>
              <button @click="activeTab = 'infos'" 
                      :class="activeTab === 'infos' ? 'bg-gray-700 text-white shadow' : 'text-gray-400 hover:text-gray-200'"
                      class="px-3 py-1 text-xs font-bold rounded-md transition-all">
                  Fichiers & Infos
              </button>
          </div>
      </div>

      {{-- Bouton Jouer le Quiz (raccourci) --}}
      @if($quizStartUrl)
        <a href="{{ $quizStartUrl }}" 
           class="text-xs bg-orangeone hover:bg-orangeone-hover text-white px-3 py-1.5 rounded-lg font-bold transition-colors">
            Lancer le Quiz (Tester)
        </a>
      @endif
  </div>

  {{-- CORPS DE PAGE --}}
  <div class="flex flex-1 overflow-hidden relative">
      
      {{-- ZONE CONTENU (SCORM/VIDEO) --}}
      <main class="relative bg-gray-100 transition-all duration-300 ease-in-out flex flex-col"
            :class="mode === 'formateur' ? 'w-2/3 border-r border-gray-200' : 'w-full'">
          
          @if ($lecture && $scormUrl)
              <iframe
                title="Contenu de la leçon"
                src="{{ $scormUrl }}"
                frameborder="0"
                allowfullscreen
                class="w-full h-full block bg-white">
              </iframe>
          @else
              <div class="flex items-center justify-center h-full text-gray-500">
                  <div class="text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="mt-2">Aucun contenu SCORM/Vidéo défini pour cette leçon.</p>
                  </div>
              </div>
          @endif
      </main>

      {{-- ZONE INSPECTEUR (Visible seulement en mode formateur) --}}
      <aside x-show="mode === 'formateur'" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-cloak
             class="w-1/3 bg-white border-l border-gray-200 flex flex-col shadow-xl z-20 absolute right-0 top-0 bottom-0 lg:relative lg:translate-x-0">
          
          <div class="flex-1 overflow-y-auto custom-scrollbar p-6">
              {{-- ... début de la zone sidebar ... --}}

<div class="flex-1 overflow-y-auto custom-scrollbar p-6">

    {{-- Onglet QUIZ (Vue rayons X) --}}
    <div x-show="activeTab === 'quiz'">

        {{-- ======================================================== --}}
        {{-- 🎛️ NOUVEAU BLOC : PARAMÉTRAGE DU TIRAGE --}}
        {{-- ======================================================== --}}
        @php
            $totalInBank = isset($quizData) ? $quizData->count() : 0;
            $currentCount = $lecture->quiz_questions_per_attempt ?? 0;
            // Sécurité visuelle pour éviter division par zéro
            $percent = $totalInBank > 0 ? ($currentCount / $totalInBank) * 100 : 0;
        @endphp

        <div class="mb-6 bg-blue-50 rounded-xl p-4 border border-blue-100 shadow-sm">
            <h4 class="font-bold text-bleuone text-xs uppercase mb-3 flex items-center justify-between">
                <span>Paramètres du tirage</span>
                <span class="bg-white text-bleuone px-2 py-0.5 rounded text-[10px] border border-blue-100">
                    {{ $currentCount }} / {{ $totalInBank }} utilisés
                </span>
            </h4>

            <form action="{{ route('formateur.lecture.update_quiz_count', $lecture->id) }}" method="POST" class="space-y-3">
                @csrf
                
                <div>
                    <label for="q_count" class="block text-xs text-gray-500 mb-1">
                        Nombre de questions posées au stagiaire :
                    </label>
                    <div class="flex gap-2">
                        <input type="number" 
                               id="q_count" 
                               name="questions_count" 
                               value="{{ $currentCount }}" 
                               min="1" 
                               max="{{ $totalInBank }}"
                               class="w-full text-sm font-bold text-center border-gray-300 rounded-lg focus:ring-orangeone focus:border-orangeone"
                        >
                        <button type="submit" class="bg-bleuone hover:bg-bleuone/90 text-white px-3 py-2 rounded-lg text-xs font-bold transition-colors">
                            OK
                        </button>
                    </div>
                </div>

                {{-- Jauge visuelle --}}
                <div class="w-full bg-blue-200 rounded-full h-1.5 dark:bg-gray-700 mt-2 overflow-hidden">
                    <div class="bg-orangeone h-1.5 rounded-full transition-all duration-500" style="width: {{ $percent }}%"></div>
                </div>
                <p class="text-[10px] text-gray-400 italic text-center">
                    Le système tirera {{ $currentCount }} questions au hasard parmi les {{ $totalInBank }} disponibles.
                </p>
            </form>
        </div>
        {{-- ======================================================== --}}


        <div class="mb-6 pb-4 border-b border-gray-100">
            <h3 class="font-raleway text-bleuone font-bold text-lg flex items-center gap-2">
            {{-- ... La suite de ton code existant (Corrigé du Quiz) ... --}}
              {{-- Onglet QUIZ (Vue rayons X) --}}
              <div x-show="activeTab === 'quiz'">
                  <div class="mb-6 pb-4 border-b border-gray-100">
                    <h3 class="font-raleway text-bleuone font-bold text-lg flex items-center gap-2">
                        <svg class="w-5 h-5 text-orangeone" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        Corrigé du Quiz
                    </h3>
                    <p class="text-xs text-gray-500 mt-1">
                        Consultez les réponses sans avoir à lancer le quiz.
                    </p>
                  </div>
                  
                  @if(isset($quizData) && $quizData->count() > 0)
                      <div class="space-y-6">
                          @foreach($quizData as $index => $q)
                              <div class="bg-gray-50 rounded-xl p-4 border border-gray-200 hover:border-orangeone/30 transition-colors">
                                  <p class="font-bold text-gray-800 text-sm mb-3">
                                      <span class="text-orangeone mr-1">Q{{ $index + 1 }}.</span> {{ $q->question_text }}
                                  </p>
                                  <ul class="space-y-2 pl-1">
                                      @foreach($q->answers as $ans)
                                          <li class="text-xs flex items-start gap-2 p-2 rounded-lg {{ $ans->is_correct ? 'bg-green-50 border border-green-100 text-green-800' : 'text-gray-500' }}">
                                              <span class="mt-0.5 shrink-0">
                                                  @if($ans->is_correct) 
                                                    <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                                  @else 
                                                    <span class="inline-block w-4 h-4 border border-gray-300 rounded-full"></span>
                                                  @endif
                                              </span>
                                              <div class="flex-1">
                                                  <span class="{{ $ans->is_correct ? 'font-bold' : '' }}">{{ $ans->option_text }}</span>
                                                  @if($ans->feedback)
                                                      <div class="mt-1 text-[11px] text-gray-500 italic border-l-2 border-gray-300 pl-2">
                                                        💡 {{ $ans->feedback }}
                                                      </div>
                                                  @endif
                                              </div>
                                          </li>
                                      @endforeach
                                  </ul>
                              </div>
                          @endforeach
                      </div>
                  @else
                      <div class="text-center py-10 bg-gray-50 rounded-xl border border-dashed border-gray-300">
                        <p class="text-gray-500 text-sm font-medium">Aucun quiz configuré pour cette leçon.</p>
                        <p class="text-xs text-gray-400 mt-1">Activez l'option "Quiz" dans l'édition du module.</p>
                      </div>
                  @endif
              </div>

              {{-- Onglet INFOS --}}
              <div x-show="activeTab === 'infos'" style="display: none;">
                  <h3 class="font-raleway text-bleuone font-bold text-lg mb-4">Méta-données & Sources</h3>
                  
                  <div class="space-y-4">
                    {{-- Téléchargements --}}
                    <div class="bg-blue-50/50 p-4 rounded-xl border border-blue-100">
                        <h4 class="text-xs font-bold text-bleuone uppercase mb-3 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            Fichiers sources
                        </h4>
                        <ul class="text-sm space-y-3">
                             @if($lecture->video_url)
                                <li>
                                    <a href="{{ $lecture->video_url }}" target="_blank" class="text-gray-700 hover:text-orangeone hover:underline flex items-center gap-2 group">
                                        <span class="p-1 bg-white rounded border border-gray-200 group-hover:border-orangeone transition-colors">MP4</span>
                                        Télécharger la vidéo source
                                    </a>
                                </li>
                             @endif
                             @if($lecture->pdf_url)
                                <li>
                                    <a href="{{ $lecture->pdf_url }}" target="_blank" class="text-gray-700 hover:text-orangeone hover:underline flex items-center gap-2 group">
                                        <span class="p-1 bg-white rounded border border-gray-200 group-hover:border-orangeone transition-colors">PDF</span>
                                        Voir le support PDF
                                    </a>
                                </li>
                             @endif
                             @if(!$lecture->video_url && !$lecture->pdf_url)
                                <li class="text-xs text-gray-400 italic">Aucun fichier source direct disponible.</li>
                             @endif
                        </ul>
                    </div>

                    {{-- Infos Techniques --}}
                    <div class="bg-gray-50 p-4 rounded-xl border border-gray-200">
                        <h4 class="text-xs font-bold text-gray-500 uppercase mb-3">Détails Techniques</h4>
                        <dl class="space-y-2 text-xs">
                            <div class="flex justify-between">
                                <dt class="text-gray-500">ID Leçon</dt>
                                <dd class="font-mono text-gray-700">{{ $lecture->id }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Slides déclarées</dt>
                                <dd class="font-medium text-gray-700">{{ $lecture->slide_count ?? 0 }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Questions prévues</dt>
                                <dd class="font-medium text-gray-700">{{ $lecture->quiz_questions_per_attempt ?? 0 }}</dd>
                            </div>
                        </dl>
                    </div>
                  </div>
              </div>
          </div>
      </aside>
  </div>
</div>

{{-- Scripts SCORM / Navigation inchangés --}}
<script>
  const finalUrl = @json($finalUrl);

  window.SCORM_CONTEXT = {
    lecture_id: @json($lectureId),
    module_id: @json($moduleId),
    section_id: @json($sectionId),
    next_url: @json($nextUrl),
    // En mode formateur, le flux "Leçon suivante" ne doit jamais basculer sur le quiz.
    quiz_start_url: null,
    quiz_tester_url: @json($quizStartUrl),
    force_next_lesson: true,

    goToQuiz: function () {
      if (!this.quiz_tester_url) return;
      window.location.href = this.quiz_tester_url;
    },

    goToNextLesson: function () {
      if (this.next_url && this.next_url !== "#") {
        window.location.href = this.next_url;
        return;
      }
      window.location.href = finalUrl;
    }
  };
</script>
@endsection
