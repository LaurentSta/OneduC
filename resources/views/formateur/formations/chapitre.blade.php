@extends('formateur.formations.master_lecon')

@section('content')
<main class="max-w-full mx-auto">

  {{-- En-tête --}}
  <header class="bg-white rounded-[20px] shadow-soft border border-gray-100 p-8 mb-6">
    <h1 class="text-titre font-raleway text-bleuone">
      {{ $selectedSection->section_title }}
    </h1>
  </header>

  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    {{-- Colonne gauche : accordéons --}}
    <section x-data="{ openItem: null }" class="space-y-4">

      @php
        use Illuminate\Support\Str;

        // 1) Objectifs issus des leçons
        $lecturesWithObjectives = ($selectedSection->lectures ?? collect())
          ->filter(fn($lec) => ($lec->objectives ?? collect())->isNotEmpty());

        // 2) Questions "de départ" saisies dans la section
        $rawQuestions = trim((string) ($selectedSection->section_html ?? ''));
        $isHtml = Str::contains($rawQuestions, ['<ul', '<ol', '<li', '<p', '<br', '</']);
        $lectures = $selectedSection->lectures ?? collect();
      @endphp

      {{-- Accordéon 1 : Objectifs --}}
      <div class="bg-white rounded-[20px] shadow-soft border border-gray-100 overflow-hidden">
        <button
          type="button"
          @click="openItem = openItem === 1 ? null : 1"
          class="w-full flex items-center justify-between gap-4 px-6 py-4 text-left
                 bg-gray-50 hover:bg-gray-100 transition
                 font-varela text-bleuone"
          :aria-expanded="openItem === 1"
          aria-controls="panel-objectifs"
        >
          <div class="min-w-0">
            <div class="text-lg font-semibold">Objectifs</div>
            <div class="text-sm text-gray-600 font-lisible mt-1">
              Ce que l’apprenant doit viser dans les leçons de cette section
            </div>
          </div>

          <svg x-show="openItem !== 1" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
            <path fill-rule="evenodd" d="M6 8l4 4 4-4" clip-rule="evenodd"/>
          </svg>
          <svg x-show="openItem === 1" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0 transform rotate-180" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
            <path fill-rule="evenodd" d="M6 8l4 4 4-4" clip-rule="evenodd"/>
          </svg>
        </button>

        <div
          id="panel-objectifs"
          x-show="openItem === 1"
          x-collapse
          class="px-6 pb-6 pt-4 font-lisible text-[17px] text-gray-800 leading-relaxed"
        >
          @if($lecturesWithObjectives->isNotEmpty())
            <div class="space-y-4">
              @foreach($lecturesWithObjectives as $lec)
                <div class="rounded-[16px] border border-gray-200 bg-gray-50 p-4">
                  <div class="font-varela text-bleuone font-semibold">
                    {{ $lec->lecture_title }}
                  </div>

                  <ul class="mt-2 list-disc pl-6 space-y-1 marker:text-orangeone">
                    @foreach($lec->objectives as $obj)
                      <li>
                        <span class="font-medium">{{ $obj->title }}</span>
                        @if(!empty($obj->description))
                          <div class="text-sm text-gray-600 mt-1">{{ $obj->description }}</div>
                        @endif
                      </li>
                    @endforeach
                  </ul>
                </div>
              @endforeach
            </div>
          @elseif(!empty($selectedSection->objectif))
            {{-- Fallback : ancien champ "objectif" de section si aucune leçon n'a d'objectifs --}}
            <div class="
              [&_ul]:list-disc [&_ul]:pl-6 [&_ul]:space-y-1
              [&_ol]:list-decimal [&_ol]:pl-6 [&_ol]:space-y-1
              [&_li::marker]:text-orangeone
              [&_p]:my-2
            ">
              {!! $selectedSection->objectif !!}
            </div>
          @else
            <div class="text-sm text-gray-600">
              Aucun objectif n’est renseigné pour le moment.
            </div>
          @endif
        </div>
      </div>

      {{-- Accordéon 2 : Questions pour commencer --}}
      <div class="bg-white rounded-[20px] shadow-soft border border-gray-100 overflow-hidden">
        <button
          type="button"
          @click="openItem = openItem === 2 ? null : 2"
          class="w-full flex items-center justify-between gap-4 px-6 py-4 text-left
                 bg-gray-50 hover:bg-gray-100 transition
                 font-varela text-bleuone"
          :aria-expanded="openItem === 2"
          aria-controls="panel-questions"
        >
          <div class="min-w-0">
            <div class="text-lg font-semibold">Questions pour commencer</div>
            <div class="text-sm text-gray-600 font-lisible mt-1">
              Accroche et repères avant de lancer la section
            </div>
          </div>

          <svg x-show="openItem !== 2" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
            <path fill-rule="evenodd" d="M6 8l4 4 4-4" clip-rule="evenodd"/>
          </svg>
          <svg x-show="openItem === 2" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0 transform rotate-180" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
            <path fill-rule="evenodd" d="M6 8l4 4 4-4" clip-rule="evenodd"/>
          </svg>
        </button>

        <div
          id="panel-questions"
          x-show="openItem === 2"
          x-collapse
          class="px-6 pb-6 pt-4 font-lisible text-[17px] text-gray-800 leading-relaxed"
        >
          {{-- Questions saisies dans la section --}}
          @if($rawQuestions !== '')
            <div class="mb-5">
              <div class="text-sm font-varela text-orangeone mb-2">
                Questions de départ
              </div>

              @if($isHtml)
                <div class="
                  [&_ul]:list-disc [&_ul]:pl-6 [&_ul]:space-y-1
                  [&_ol]:list-decimal [&_ol]:pl-6 [&_ol]:space-y-1
                  [&_li::marker]:text-orangeone
                  [&_p]:my-2
                ">
                  {!! $rawQuestions !!}
                </div>
              @else
                @php
                  $lines = collect(preg_split("/\r\n|\n|\r/", $rawQuestions))
                    ->map(fn($q) => trim($q))
                    ->filter()
                    ->values();
                @endphp

                @if($lines->isNotEmpty())
                  <ul class="list-disc pl-6 space-y-1 marker:text-orangeone">
                    @foreach($lines as $q)
                      <li>{{ $q }}</li>
                    @endforeach
                  </ul>
                @endif
              @endif
            </div>
          @endif

          {{-- Repères par leçon (optionnel) --}}
          @if($lectures->isNotEmpty())
            <div class="mt-2">
              <div class="text-sm font-varela text-orangeone mb-2">
                Repères par leçon
              </div>

              <div class="space-y-3">
                @foreach($lectures as $lec)
                  @php
                    $quizCount = (int) ($lec->quiz_questions_count ?? 0);
                    $declaredCount = (int) ($lec->question_count ?? 0);
                    $total = $quizCount > 0 ? $quizCount : $declaredCount;

                    $badgeText = $quizCount > 0
                      ? "Quiz : {$quizCount}"
                      : ($declaredCount > 0 ? "Déclaré : {$declaredCount}" : "Non défini");
                  @endphp

                  <div class="rounded-[16px] border border-gray-200 bg-gray-50 p-4">
                    <div class="flex items-start justify-between gap-4">
                      <div class="min-w-0">
                        <div class="font-varela text-bleuone font-semibold truncate">
                          {{ $lec->lecture_title }}
                        </div>
                        <div class="text-sm text-gray-600 mt-1">
                          {{ $total }} question{{ $total > 1 ? 's' : '' }}
                        </div>
                      </div>

                      <span class="shrink-0 inline-flex items-center rounded-full px-3 py-1 text-sm
                                  bg-white border border-gray-200 text-gray-700">
                        {{ $badgeText }}
                      </span>
                    </div>
                  </div>
                @endforeach
              </div>
            </div>
          @endif

          @if($rawQuestions === '' && $lectures->isEmpty())
            <div class="text-sm text-gray-600">
              Aucune question n’est renseignée pour le moment.
            </div>
          @endif

        </div>
      </div>

    </section>

    {{-- Colonne droite : Vidéo + bouton --}}
    <aside class="space-y-6">
      @php
        $raw = (string) ($selectedSection->video_url ?? '');
        $videoSrc = \App\Support\LearningAssetPath::resolveSectionVideoUrl($raw);
        $firstLecture = $selectedSection->lectures->first();
      @endphp

      <div class="bg-white rounded-[20px] shadow-soft border border-gray-100 p-6">
        <div class="font-varela text-bleuone font-semibold mb-3">
          Support de la section
        </div>

        @if($videoSrc)
          <div class="relative w-full rounded-[16px] overflow-hidden border border-gray-200" style="padding-top:56.25%;" role="region" aria-label="Vidéo pédagogique">
            <video id="formation-video"
                  class="absolute top-0 left-0 w-full h-full"
                  controls preload="metadata" playsinline>
              <source src="{{ $videoSrc }}" type="video/mp4">
            </video>
          </div>
          <p class="mt-3 text-sm text-gray-600 font-lisible">
            Vidéo de présentation de la section.
          </p>
        @else
          <div class="flex items-center justify-center rounded-[16px] border border-gray-200 bg-gray-50 p-6">
            <img src="{{ asset('images/svg/Modules.svg') }}" alt="Aperçu indisponible" class="block max-w-[320px] h-auto">
          </div>
        @endif
      </div>

      @if($firstLecture)
        <div class="bg-white rounded-[20px] shadow-soft border border-gray-100 p-6">
          <a href="{{ route('formateur.formations.lecture', [
              'module'  => $module->id,
              'section' => $selectedSection->id,
              'lecture' => $firstLecture->id
          ]) }}"
          class="w-full inline-flex items-center justify-center rounded-[16px] px-5 py-3
                 bg-orangeone text-white font-varela font-semibold
                 hover:bg-orangeone-hover transition
                 focus:outline-none focus:ring-4 focus:ring-orange-200">
            Tester cette section
          </a>

          <p class="mt-3 text-sm text-gray-600 font-lisible">
            Lance la première leçon de la section.
          </p>
        </div>
      @endif
    </aside>

  </div>
</main>
@endsection
