<header class="w-full bg-white shadow px-6 py-4">
  <div class="flex items-center gap-4 justify-between">
    <!-- Gauche : bouton + titre -->
    <div class="flex items-center gap-3">
      <button type="button"
              @click="$dispatch('toggle-sidebar')"
              aria-controls="module-sidebar-wrapper"
              class="inline-flex items-center gap-2 rounded-lg border border-gray-300 px-3 py-2 text-gray-700
                     hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-orangeone">
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
          <path d="M3 6h18M3 12h18M3 18h18" stroke-linecap="round"/>
        </svg>
        <span class="font-varela text-sm">Menu</span>
      </button>
      <!-- Logo -->
      <div class="flex items-center mb-4 md:mb-0">
        <a href="{{ route('index') }}">
          <img src="/frontend/assets/img/front-pages/branding/LogoOneducPositionG.svg"
               alt="Logo Onéduc"
               class="h-[60px] w-auto">
        </a>
      </div>
    </div>

    <!-- Centre : barre de progression globale -->
    <div class="hidden md:flex items-center gap-3 min-w-[320px]" aria-label="Progression globale du module">
      @php
        $totalQuestions = 0; $answeredQuestions = 0;
        $totalLectures  = 0; $doneLectures = 0;

        if (isset($module)) {
          foreach ($module->sections as $sec) {
            $totalLectures += $sec->lectures->count();
            foreach ($sec->lectures as $lecP) {
              // Comptage questions
              $q = (int)($lecP->question_count ?? 0);
              $totalQuestions += $q;
              $ans = (int)($lectureStats[$lecP->id]['answered']
                    ?? $lectureStats[$lecP->id]['answers']
                    ?? $lectureStats[$lecP->id]['answered_count']
                    ?? 0);
              $answeredQuestions += ($q > 0 ? min($ans, $q) : $ans);

              // Complétion leçon
              $st = $lectureStats[$lecP->id]['status'] ?? null;
              if (in_array($st, ['acquired','completed'])) $doneLectures++;
            }
          }
        }

        // Priorité aux questions si disponibles, sinon fallback leçons
        if ($totalQuestions > 0) {
          $percent = intval(($answeredQuestions / max($totalQuestions,1)) * 100);
          $label   = "Questions {$answeredQuestions}/{$totalQuestions} · {$percent}%";
        } else {
          $percent = $totalLectures > 0 ? intval(($doneLectures / $totalLectures) * 100) : 0;
          $label   = "Leçons {$doneLectures}/{$totalLectures} · {$percent}%";
        }
      @endphp

      <span class="text-xs font-varela text-gray-600">Progression</span>
      <div class="w-56 bg-gray-100 h-2 rounded-full"
           role="progressbar" aria-valuemin="0" aria-valuemax="100"
           aria-valuenow="{{ $percent }}">
        <div class="h-2 rounded-full bg-vertone" style="width: {{ $percent }}%"></div>
      </div>
      <span class="text-xs font-varela text-gray-700 whitespace-nowrap">{{ $label }}</span>
    </div>

    <!-- Droite : lien retour -->
    <a href="{{ route('stagiaire.dashboard') }}" class="text-sm text-orangeone hover:underline">
      Retour au tableau de bord
    </a>
  </div>
</header>
