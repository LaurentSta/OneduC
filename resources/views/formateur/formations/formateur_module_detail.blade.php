@extends('formateur.dashboard')

@section('formateur')

<style>[x-cloak]{display:none!important}</style>

{{-- EN-TÊTE PLEINE LARGEUR --}}
<header class="bg-white rounded-[20px] shadow-md px-6 sm:px-8 pt-4 pb-6 w-full mb-6">
  <div class="grid grid-cols-12 gap-6 items-center">
    {{-- Texte (9) --}}
    <div class="col-span-12 md:col-span-9">
      <p class="font-raleway text-titre text-bleuone leading-tight mb-2">{{ $module->module_name }}</p>

      <p class="font-varela text-sous-titre text-orangeone">
        {{ $module->module_title ?? 'Module de formation' }}
      </p>

      <p class="font-lisible">
        Proposé par
        <span class="font-semibold text-bleuone">
          {{ $module->formateur->name ?? 'Un formateur non défini' }}
        </span>
      </p>

      {{-- Fil d’Ariane --}}
      <nav class="text-sm font-varela text-gray-600 mt-2" aria-label="Fil d'Ariane">
        <ol class="inline-flex items-center space-x-1">
          <li><a href="{{ route('formateur.dashboard') }}" class="text-orangeone hover:underline">Accueil</a></li>
          <li class="text-gray-400">/</li>
          <li><a href="{{ route('formateur.formations.index') }}" class="text-orangeone hover:underline">Mes modules</a></li>
          <li class="text-gray-400">/</li>
          <li class="text-gray-700 font-medium">{{ $module->module_name }}</li>
        </ol>
      </nav>
    </div>
  </div>
</header>

{{-- CONTENU PLEINE LARGEUR --}}
<main class="w-full space-y-6">

  {{-- Colonne principale + aside --}}
  <section class="flex flex-col lg:flex-row gap-6">

    {{-- Onglets + contenu (2/3) --}}
    <div class="w-full lg:w-2/3 bg-white rounded-[20px] shadow-md p-6" x-data="{ active: 'presentation' }">
      {{-- Onglets --}}
      <div class="flex flex-wrap gap-2 border-b border-gray-200 mb-6" role="tablist" aria-label="Onglets module">
        <button @click="active='presentation'"
                class="px-4 py-2 text-sm font-semibold rounded-t-md transition"
                :class="active==='presentation' ? 'text-orangeone border-b-4 border-orangeone bg-orange-50' : 'text-gray-600 hover:text-gray-800 border-b-4 border-transparent'"
                role="tab" :aria-selected="active==='presentation'" aria-controls="panel-presentation" id="tab-presentation">
          Présentation
        </button>
        <button @click="active='objectifs'"
                class="px-4 py-2 text-sm font-semibold rounded-t-md transition"
                :class="active==='objectifs' ? 'text-orangeone border-b-4 border-orangeone bg-orange-50' : 'text-gray-600 hover:text-gray-800 border-b-4 border-transparent'"
                role="tab" :aria-selected="active==='objectifs'" aria-controls="panel-objectifs" id="tab-objectifs">
          Objectifs
        </button>
        <button @click="active='prerequis'"
                class="px-4 py-2 text-sm font-semibold rounded-t-md transition"
                :class="active==='prerequis' ? 'text-orangeone border-b-4 border-orangeone bg-orange-50' : 'text-gray-600 hover:text-gray-800 border-b-4 border-transparent'"
                role="tab" :aria-selected="active==='prerequis'" aria-controls="panel-prerequis" id="tab-prerequis">
          Prérequis
        </button>
      </div>

      {{-- Panneaux --}}
      <div class="mt-2 space-y-4">
        {{-- Présentation --}}
        <div x-show="active==='presentation'" x-cloak id="panel-presentation" role="tabpanel" :aria-labelledby="'tab-presentation'">
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

        <div x-show="active==='objectifs'" x-cloak>
          @if(!empty($module->objectifs) && is_array($module->objectifs))
            <ul class="list-none space-y-1 font-lisible text-base">
              @foreach($module->objectifs as $obj)
                @if(!empty(trim($obj)))
                  <li class="relative pl-5 text-gray-800">
                    <span class="absolute left-0 top-2 w-2 h-2 bg-orangeone rounded-full"></span>
                    {{ $obj }}
                  </li>
                @endif
              @endforeach
            </ul>
          @else
            <p class="italic text-gray-500">Aucun objectif spécifié pour ce module.</p>
          @endif
        </div>



        {{-- Prérequis --}}
        <div x-show="active==='prerequis'" x-cloak id="panel-prerequis" role="tabpanel" :aria-labelledby="'tab-prerequis'">
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

      {{-- Contenu du module --}}
      <div class="mt-8" x-data="{ open: -1 }">
        <h3 class="text-lg font-semibold mb-4">Contenu du module</h3>
        <div class="space-y-4">
          @foreach ($module->sections as $index => $section)
            <div class="border border-gray-200 rounded">
              {{-- En-tête section --}}
              <button
                @click="open === {{ $index }} ? open = -1 : open = {{ $index }}"
                class="w-full px-4 py-3 text-left font-medium text-gray-800 bg-gray-100 hover:bg-gray-200 flex justify-between items-center"
                :aria-expanded="open === {{ $index }}"
                aria-controls="sec-{{ $section->id }}"
                id="btn-sec-{{ $section->id }}">
                <div>
                  <div class="text-base font-semibold">{{ $section->section_title }}</div>
                  @if (isset($sectionProgress[$section->id]))
                    <div class="text-xs text-gray-600 mt-1">
                      {{ $sectionProgress[$section->id]['completed'] }} / {{ $sectionProgress[$section->id]['total'] }} réalisées
                    </div>
                  @endif
                </div>
                <svg :class="{'rotate-180': open === {{ $index }}}"
                     class="w-5 h-5 transform transition-transform duration-200"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </button>

              {{-- Barre de progression --}}
              @if (isset($sectionProgress[$section->id]) && $sectionProgress[$section->id]['total'] > 0)
                @php $percent = intval(($sectionProgress[$section->id]['completed'] / $sectionProgress[$section->id]['total']) * 100); @endphp
                <div class="w-full bg-gray-200 h-2"
                     role="progressbar"
                     aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100">
                  <div class="bg-orangeone h-2" style="width: {{ $percent }}%"></div>
                </div>
              @endif

              {{-- Leçons --}}
              <div x-show="open === {{ $index }}" x-cloak class="p-4 bg-white text-sm text-gray-700" id="sec-{{ $section->id }}">
                @forelse ($section->lectures as $lecture)
                  <div class="flex items-center justify-between mb-2">
                    <a href="{{ route('formateur.formations.lecture', ['module' => $module->id, 'section' => $section->id, 'lesson' => $lecture->id]) }}"
                       class="flex-1 pr-3 hover:underline font-medium text-gray-800">
                      {{ $lecture->lecture_title }}
                    </a>
                    @php $status = $lessonStatuses[$lecture->id] ?? null; @endphp
                    @if($status === 'completed')
                      <span class="text-green-600" aria-label="Terminé">✔</span>
                    @elseif($status === 'incomplete')
                      <span class="text-yellow-500" aria-label="En cours">⏳</span>
                    @else
                      <span class="text-gray-400" aria-label="Non commencé">–</span>
                    @endif
                  </div>
                @empty
                  <p class="italic text-gray-500">Aucune leçon dans cette section.</p>
                @endforelse
              </div>
            </div>
          @endforeach
        </div>
      </div>
    </div>

    {{-- Aside infos (1/3) --}}
    <aside class="w-full lg:w-1/3 space-y-6">
      <div class="bg-white p-6 rounded-[20px] shadow-md">
        @php
          $baseFolder = 'modules/scorm/02_videos/';
          $videoRelativePath = trim($module->module_video ?? '', '/');
          $videoSrc = $videoRelativePath ? url($baseFolder . $videoRelativePath) : null;
        @endphp

        {{-- Aperçu vidéo du module --}}
        @if($videoSrc)
          <div class="mb-6">
            <div class="relative w-full rounded-[16px] overflow-hidden shadow-md" style="padding-top:56.25%">
              <video class="absolute top-0 left-0 w-full h-full"
                     controls preload="metadata" playsinline
                     aria-label="Aperçu vidéo du module">
                <source src="{{ $videoSrc }}" type="video/mp4">
              </video>
            </div>
          </div>
        @else
          <img src="{{ asset('images/svg/Modules.svg') }}"
               alt="Illustration du module"
               class="block mx-auto max-w-[256px] h-auto mb-6">
        @endif

        {{-- Progression globale du module, si fournie --}}
        @isset($progression)
          <div class="mb-6">
            <p class="text-sm text-gray-600 font-medium">Progression du module : {{ $progression }}%</p>
            <div class="w-full bg-gray-200 rounded h-3 mt-1"
                 role="progressbar"
                 aria-valuenow="{{ $progression }}" aria-valuemin="0" aria-valuemax="100">
              <div class="bg-orangeone h-3 rounded" style="width: {{ $progression }}%"></div>
            </div>
          </div>
        @endisset

        <h3 class="text-lg font-semibold mb-4">Informations sur la formation</h3>
        <div class="space-y-3 text-sm text-gray-700">
          <div class="flex items-center justify-between"><span class="text-gray-600">Formateur :</span><span class="font-medium">{{ $module->formateur->name ?? 'Non défini' }}</span></div>
          <div class="flex items-center justify-between"><span class="text-gray-600">Mise à jour :</span><span class="font-medium">{{ optional($module->updated_at)->format('d/m/Y') }}</span></div>
          <div class="flex items-center justify-between"><span class="text-gray-600">Durée :</span><span class="font-medium">{{ $module->duree ?? 'Non précisée' }}</span></div>
          <div class="flex items-center justify-between"><span class="text-gray-600">Ressources :</span><span class="font-medium">{{ $module->resources ?? 'Non spécifié' }}</span></div>
          <div class="flex items-center justify-between"><span class="text-gray-600">Certificat :</span><span class="font-medium">{{ $module->certificat ? 'Oui' : 'Non' }}</span></div>
          <div class="flex items-center justify-between"><span class="text-gray-600">Niveau :</span><span class="font-medium">{{ $module->level ?? 'Tous niveaux' }}</span></div>
          <div class="flex items-center justify-between"><span class="text-gray-600">Langue :</span><span class="font-medium">Français</span></div>
        </div>

        @php $firstSection = $module->sections->first(); @endphp
        @if($firstSection)
          <a href="{{ route('formateur.formations.section', ['module' => $module->id, 'section' => $firstSection->id]) }}"
             class="block text-center bg-orangeone hover:bg-orange-600 text-white font-semibold mt-6 py-2 rounded transition">
            Tester la formation
          </a>
        @else
          <p class="text-sm text-gray-500 italic mt-6">Aucune section disponible dans ce module.</p>
        @endif
      </div>
    </aside>

  </section>

  {{-- Modal évaluation (optionnel) --}}
  @if($module->evaluation_id)
    <div id="evaluationModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center hidden" role="dialog" aria-modal="true" aria-labelledby="eval-title">
      <div class="bg-white p-6 rounded shadow-lg w-full max-w-md">
        <h4 id="eval-title" class="text-lg font-semibold mb-4 text-orangeone">Évaluation finale</h4>
        @auth
          <p class="text-sm text-gray-700 mb-4">
            Cette évaluation ne peut être réalisée <strong>qu’une seule fois</strong>.
          </p>
          <div class="flex justify-end gap-2">
            <a href="{{ route('evaluation.show', ['id' => $module->evaluation_id]) }}"
               class="bg-orangeone hover:bg-orange-600 text-white font-semibold px-4 py-2 rounded">
              Oui, je suis prêt
            </a>
            <button type="button" onclick="document.getElementById('evaluationModal').classList.add('hidden')"
                    class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold px-4 py-2 rounded">
              Annuler
            </button>
          </div>
        @else
          <p class="text-sm text-gray-700 mb-4">Connectez-vous pour accéder à l’évaluation.</p>
          <div class="flex justify-end">
            <a href="{{ route('login') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-4 py-2 rounded">
              Me connecter
            </a>
          </div>
        @endauth
      </div>
    </div>
  @endif

</main>





@endsection
