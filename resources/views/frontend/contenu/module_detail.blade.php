@extends('frontend.master')
@section('home')

{{-- =======================
   EN-TÊTE AVEC IMAGE
======================= --}}
@if($module->header_image)
  <div class="relative w-full h-[300px] overflow-hidden">
    <img src="{{ asset('storage/' . $module->header_image) }}"
         alt="Image d'en-tête du module"
         class="absolute inset-0 w-full h-full object-cover">

    <div class="absolute inset-0 bg-black/50 flex items-center">
      <div class="w-full max-w-[1248px] mx-auto px-4">
        <h1 class="text-[48px] font-raleway text-white uppercase mb-6">
          {{ $module->module_name }}
        </h1>

        <nav class="text-sm text-gray-300 mb-6 space-x-2" aria-label="Fil d’Ariane">
          <a href="{{ url('/') }}" class="hover:text-white">Accueil</a> /
          <a href="{{ route('categories.all') }}" class="hover:text-white">Catégories</a> /
          <span class="text-white">{{ $module->module_title ?? 'Module' }}</span>
        </nav>

        <p class="text-sm text-gray-300">
          Formateur :
          <span class="font-medium text-white">{{ $module->formateur->name ?? 'Non défini' }}</span>
        </p>
      </div>
    </div>
  </div>
@endif

@php
  $lessonObjectives = collect($lessonObjectives ?? []);
@endphp

{{-- =======================
   BLOC INTRO + VIDÉO
======================= --}}
<section class="bg-white py-8">
  <div class="max-w-[1248px] mx-auto px-4">
    <div class="flex flex-col lg:flex-row gap-8 items-start">

      {{-- ONGLETES Présentation/Objectifs/Prérequis (aligné sur la vue stagiaire) --}}
      <div class="w-full lg:w-2/3 min-w-0 bg-white rounded-[20px] shadow-md p-6"
     x-data="{ active: 'presentation' }">

        {{-- Onglets --}}
        <div class="flex space-x-4 border-b border-gray-200 mb-6">
          <button @click="active = 'presentation'"
                  class="px-6 py-3 text-sm font-semibold rounded-t-md transition-all duration-200"
                  :class="active === 'presentation'
                      ? 'text-orangeone border-b-4 border-orangeone bg-orange-50'
                      : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100 border-b-4 border-transparent'">
            Présentation
          </button>
          <button @click="active = 'objectifs'"
                  class="px-6 py-3 text-sm font-semibold rounded-t-md transition-all duration-200"
                  :class="active === 'objectifs'
                      ? 'text-orangeone border-b-4 border-orangeone bg-orange-50'
                      : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100 border-b-4 border-transparent'">
            Objectifs
          </button>
          <button @click="active = 'prerequis'"
                  class="px-6 py-3 text-sm font-semibold rounded-t-md transition-all duration-200"
                  :class="active === 'prerequis'
                      ? 'text-orangeone border-b-4 border-orangeone bg-orange-50'
                      : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100 border-b-4 border-transparent'">
            Prérequis
          </button>
        </div>

        {{-- Contenus --}}
        <div class="mt-4 space-y-4">
          {{-- Présentation --}}
          <div x-show="active === 'presentation'" x-transition x-cloak>
            <p class="text-[17px] leading-relaxed font-lisible text-gray-800">
              {{ $module->description }}
            </p>
            <div class="flex flex-wrap items-center gap-2 mt-2">
              @if($module->bestseller)
                <span class="bg-green-600 text-white text-xs font-semibold px-3 py-1 rounded-full">Bestseller</span>
              @endif
              @if($module->vedette)
                <span class="bg-blue-600 text-white text-xs font-semibold px-3 py-1 rounded-full">À la Une</span>
              @endif
              @if($module->surevalue)
                <span class="bg-yellow-500 text-white text-xs font-semibold px-3 py-1 rounded-full">Valeur sûre</span>
              @endif
            </div>
          </div>

          {{-- Objectifs --}}
          <div x-show="active === 'objectifs'" x-transition x-cloak>
            <ul class="list-disc list-inside text-gray-800 text-base space-y-1 font-lisible">
              @forelse($lessonObjectives as $obj)
                <li>{{ $obj }}</li>
              @empty
                <li class="list-none italic text-gray-500">Aucun objectif de leçon n'est encore disponible.</li>
              @endforelse
            </ul>
          </div>

          {{-- Prérequis --}}
          <div x-show="active === 'prerequis'" x-transition x-cloak>
            @if(!empty($module->prerequi))
              <ul class="list-disc list-inside text-gray-800 text-base space-y-1 font-lisible">
                @foreach(explode("\n", $module->prerequi) as $item)
                  <li>{{ $item }}</li>
                @endforeach
              </ul>
            @else
              <p class="italic text-gray-500">Aucun prérequis spécifié pour ce module.</p>
            @endif
          </div>
        </div>

      </div>

      {{-- Vidéo (même logique que la vue stagiaire) --}}
      @php
        $baseFolder = 'modules/scorm/02_videos/';
        $videoRelativePath = trim($module->module_video ?? '', '/');
        $videoSrc = $videoRelativePath ? url($baseFolder . $videoRelativePath) : null;
      @endphp

      <div class="lg:w-1/3 w-full">
        <div class="rounded shadow overflow-hidden">
          @if($videoSrc)
            <div class="relative w-full" style="padding-top:56.25%;">
              <video class="absolute top-0 left-0 w-full h-full" controls preload="metadata" playsinline>
                <source src="{{ $videoSrc }}" type="video/mp4">
                Votre navigateur ne prend pas en charge la vidéo.
              </video>
            </div>
          @else
            <p class="text-sm text-gray-500 italic">Aucune vidéo fournie pour ce module.</p>
          @endif
        </div>
      </div>

    </div>
  </div>
</section>

{{-- =======================
   CONTENU + SIDEBAR
======================= --}}
<section class="py-10 bg-gray-50">
  <div class="max-w-[1248px] mx-auto px-4 flex flex-col lg:flex-row gap-8">

    {{-- Colonne gauche : contenu du module --}}
    <div class="w-full lg:w-2/3 space-y-6">
      <div class="bg-white p-6 rounded shadow">

        <section class="py-4 bg-white">
          <div class="max-w-[1248px] mx-auto px-0">
            <h3 class="text-lg font-semibold mb-4">Contenu du module</h3>

            <div class="space-y-4" x-data="{ active: 0 }">
              @foreach ($module->sections as $index => $section)
                <div class="border border-gray-200 rounded">
                  {{-- En-tête section --}}
                  <button
                    @click="active === {{ $index }} ? active = -1 : active = {{ $index }}"
                    class="w-full px-4 py-3 text-left font-medium text-gray-800 bg-gray-100 hover:bg-gray-200 flex justify-between items-center"
                    :aria-expanded="active === {{ $index }}">
                    <div>
                      <div class="text-base font-semibold">{{ $section->section_title }}</div>
                      @if (isset($sectionProgress[$section->id]))
                        <div class="text-xs text-gray-600 mt-1">
                          {{ $sectionProgress[$section->id]['completed'] ?? 0 }} / {{ $sectionProgress[$section->id]['total'] ?? 0 }} réalisées
                        </div>
                      @endif
                    </div>
                    <svg :class="{'rotate-180': active === {{ $index }}}"
                         class="w-5 h-5 transform transition-transform duration-200"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                  </button>

                  {{-- Barre de progression section --}}
                  @if (isset($sectionProgress[$section->id]) && ($sectionProgress[$section->id]['total'] ?? 0) > 0)
                    @php
                      $completed = (int) ($sectionProgress[$section->id]['completed'] ?? 0);
                      $total = (int) ($sectionProgress[$section->id]['total'] ?? 0);
                      $percent = $total > 0 ? intval($completed / $total * 100) : 0;
                    @endphp
                    <div class="w-full bg-gray-200 h-2">
                      <div class="bg-orangeone h-2" style="width: {{ $percent }}%"></div>
                    </div>
                  @endif

                  {{-- Leçons --}}
                  <div x-show="active === {{ $index }}" x-collapse class="p-4 bg-white text-sm text-gray-700">
                    @forelse ($section->lectures as $lecture)
                      <div class="flex items-center space-x-2 mb-2">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-5.197-3.027A1 1 0 008 9v6a1 1 0 001.555.832l5.197-3.027a1 1 0 000-1.664z"/>
                        </svg>

                        @auth
                          <a href="{{ route('stagiaire.module.lecture', [$module->id, $section->id, $lecture->id]) }}"
                             class="flex items-center justify-between px-2 py-1 hover:bg-gray-100 rounded text-sm font-medium text-gray-800"
                             aria-label="Ouvrir la leçon {{ $lecture->lecture_title }}">
                        @else
                          <a href="javascript:void(0)"
                            onclick="openAuthInfoModal()"
                            class="flex items-center justify-between px-2 py-1 hover:bg-gray-100 rounded text-sm font-medium text-gray-800"
                            aria-label="Veuillez vous connecter pour ouvrir la leçon {{ $lecture->lecture_title }}">

                        @endauth
                            <span>{{ $lecture->lecture_title }}</span>
                            <span class="ml-2">
                              @php $status = $lessonStatuses[$lecture->id] ?? null; @endphp
                              @if($status === 'completed')
                                <span class="text-green-600">✔️</span>
                              @elseif($status === 'incomplete')
                                <span class="text-yellow-500">⏳</span>
                              @else
                                <span class="text-gray-400">–</span>
                              @endif
                            </span>
                          </a>
                      </div>
                    @empty
                      <p class="italic text-gray-500">Aucune leçon dans cette section.</p>
                    @endforelse
                  </div>
                </div>
              @endforeach
            </div>

          </div>
        </section>

      </div>
    </div>

    {{-- Colonne droite : infos + CTA --}}
    <div class="w-full lg:w-1/3">
      <div class="sticky top-6 space-y-6">
        <div class="bg-white p-6 rounded shadow">
          {{-- Progression globale (si dispo pour un utilisateur connecté) --}}
          <div class="mb-6">
            <p class="text-sm text-gray-600 font-medium">Progression du module : {{ $progression ?? 0 }}%</p>
            <div class="w-full bg-gray-200 rounded h-3 mt-1" aria-hidden="true">
              <div class="bg-orangeone h-3 rounded" style="width: {{ $progression ?? 0 }}%"></div>
            </div>
          </div>

          <h3 class="text-lg font-semibold mb-4">Informations sur la formation</h3>
          <ul class="text-sm text-gray-700 space-y-2">
            <li class="flex justify-between"><span>Durée :</span><span>{{ $module->duree ?? 'Non précisée' }}</span></li>
            <li class="flex justify-between"><span>Ressources :</span><span>{{ $module->resources ?? 'Non spécifié' }}</span></li>
            <li class="flex justify-between"><span>Certificat :</span><span>{{ $module->certificat ? 'Oui' : 'Non' }}</span></li>
            <li class="flex justify-between"><span>Niveau :</span><span>{{ $module->level ?? 'Tous niveaux' }}</span></li>
          </ul>

{{-- =======================
   MODAL CONNEXION / INSCRIPTION
======================= --}}
<div id="authInfoModal"
     class="fixed inset-0 bg-black/60 z-[60] hidden"
     role="dialog" aria-modal="true" aria-labelledby="authInfoTitle">
  <div class="min-h-full w-full flex items-center justify-center p-4">
    <div class="relative bg-white w-full max-w-lg rounded-[20px] shadow-xl p-8">

      {{-- Bouton de fermeture (croix) --}}
      <button type="button"
              onclick="closeAuthInfoModal()"
              class="absolute top-4 right-4 p-2 rounded-full hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-orangeone"
              aria-label="Fermer le pop-up">
        <span class="sr-only">Fermer</span>
        ✕
      </button>

      {{-- Titre --}}
      <h4 id="authInfoTitle" class="text-2xl font-raleway font-semibold mb-6 text-bleuone text-center">
        Accéder à la formation
      </h4>

      {{-- Texte introductif --}}
      <p class="text-base text-gray-700 mb-4 text-center">
         Vous devez vous connecter pour poursuivre.
      </p>

      {{-- Bloc Formateurs --}}
      <div class="bg-bleuone/10 border border-bleuone rounded-lg p-4 mb-4">
        <p class="text-sm text-gray-800">
          <strong>Formateurs :</strong> veuillez vous connecter pour créer et gérer vos groupes.<br>
          Si vous n’avez pas encore de compte, inscrivez-vous.
        </p>
      </div>

      {{-- Bloc Stagiaires --}}
      <div class="bg-orangeone/10 border border-orangeone rounded-lg p-4 mb-8">
        <p class="text-sm text-gray-800">
          <strong>Stagiaires :</strong> vous pouvez vous connecter uniquement si votre formateur vous a déjà créé un compte.
        </p>
      </div>

      {{-- Boutons d’action --}}
      <div class="flex flex-col sm:flex-row sm:justify-center gap-3">
        <a href="{{ route('connexion') }}"
           class="btn-oneduc bg-white text-orangeone border-orangeone hover:bg-orangeone hover:text-white text-center"
           aria-label="Aller à la page de connexion">
          Me connecter
        </a>

        <a href="{{ route('formateur.inscription.form') }}"
           class="btn-oneduc-blue text-center"
           aria-label="Aller au formulaire d’inscription formateur">
          Je suis formateur • M’inscrire
        </a>
      </div>

    </div>
  </div>
</div>


<script>
  function openAuthInfoModal() {
    const el = document.getElementById('authInfoModal');
    if (el) el.classList.remove('hidden');
  }
  function closeAuthInfoModal() {
    const el = document.getElementById('authInfoModal');
    if (el) el.classList.add('hidden');
  }
  // Fermeture au clavier (Échap)
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeAuthInfoModal();
  });
</script>

          {{-- CTA principal --}}
          @php $firstSection = $module->sections->first(); @endphp
          @if($firstSection)
            @auth
              <a href="{{ route('stagiaire.module.section', [$module->id, $firstSection->id]) }}"
                 class="block text-center bg-indigo-600 hover:bg-indigo-700 text-white font-semibold mt-6 py-2 rounded"
                 aria-label="Ouvrir la première section">
                Démarrer la formation
              </a>
            @else
              <button type="button"
                    onclick="openAuthInfoModal()"
                    class="inline-block w-full text-center mt-6 px-4 py-2 text-base tracking-wide font-varela text-white bg-bleuone border-4 border-bleuone rounded-full transition duration-300 hover:bg-white hover:text-bleuone active:scale-95"
                    aria-label="Ouvrir la fenêtre d'information de connexion">
              Se connecter / S’inscrire
            </button>


        {{-- Lien direct d’inscription formateur en secours d’accessibilité --}}
        @php
          $routeFormateur = \Illuminate\Support\Facades\Route::has('formateur.register')
              ? route('formateur.register')
              : url('/formateur/register');
        @endphp
        
            @endauth
          @else
            <p class="text-sm text-gray-500 italic mt-6">Aucune section disponible dans ce module.</p>
          @endif

          @if($module->evaluation_id)
            <button type="button"
                    onclick="document.getElementById('evaluationModal').classList.remove('hidden')"
                    class="w-full mt-4 inline-flex items-center justify-center px-6 py-3 text-base font-varela font-semibold text-white bg-orangeone border-4 border-orangeone rounded-full transition duration-300 hover:bg-white hover:text-orangeone active:scale-95"
                    aria-label="Ouvrir la fenêtre de lancement de l’évaluation">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              Lancer l’évaluation
            </button>
          @endif


        </div>
      </div>
    </div>

  </div>
</section>

{{-- =======================
   MODAL ÉVALUATION
======================= --}}
@if($module->evaluation_id)
  <div id="evaluationModal"
       class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center hidden"
       role="dialog" aria-modal="true" aria-labelledby="evaluationTitle">
    <div class="relative bg-white w-full max-w-lg rounded-[20px] shadow-xl p-8">

      {{-- Bouton fermeture (croix) --}}
      <button type="button"
              onclick="document.getElementById('evaluationModal').classList.add('hidden')"
              class="absolute top-4 right-4 p-2 rounded-full hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-orangeone"
              aria-label="Fermer le pop-up">
        <span class="sr-only">Fermer</span>
        ✕
      </button>

      {{-- Titre --}}
      <h4 id="evaluationTitle" class="text-2xl font-raleway font-semibold mb-6 text-orangeone text-center">
        Évaluation finale
      </h4>

      @auth
        <p class="text-base text-gray-700 mb-6 text-center">
          Cette évaluation ne peut être réalisée <strong>qu’une seule fois</strong>.<br>
          Assurez-vous d’être prêt avant de commencer.
        </p>

        {{-- Boutons d’action --}}
        <div class="flex flex-col sm:flex-row sm:justify-center gap-3">
          <a href="{{ route('evaluation.show', ['id' => $module->evaluation_id]) }}"
             class="btn-oneduc"
             aria-label="Démarrer l’évaluation">
            Oui, je suis prêt
          </a>

          <button type="button"
                  onclick="document.getElementById('evaluationModal').classList.add('hidden')"
                  class="btn-oneduc bg-white text-orangeone border-orangeone hover:bg-orangeone hover:text-white"
                  aria-label="Annuler et fermer la fenêtre">
            Annuler
          </button>
        </div>
      @else
        <p class="text-base text-gray-700 mb-6 text-center">
          Vous devez être connecté pour accéder à l’évaluation de ce module.
        </p>

        <div class="flex justify-center">
          <a href="{{ route('connexion') }}"
             class="btn-oneduc-blue"
             aria-label="Aller à la page de connexion">
            Me connecter
          </a>
        </div>
      @endauth
    </div>
  </div>
@endif


@endsection
