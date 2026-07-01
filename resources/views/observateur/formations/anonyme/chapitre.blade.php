@extends('observateur.formations.master_lecon')
@section('content')

@php
  use Illuminate\Support\Str;

  $q = array_merge(($contextQuery ?? []), ['anonymous' => 1]);
  $firstLecture = ($selectedSection->lectures ?? collect())->first();
  $rawQuestions = trim((string) ($selectedSection->section_html ?? ''));
  $isHtml = Str::contains($rawQuestions, ['<ul', '<ol', '<li', '<p', '<br', '</']);
  $lecturesWithObjectives = ($selectedSection->lectures ?? collect())
      ->filter(fn($lec) => ($lec->objectives ?? collect())->isNotEmpty());
@endphp

<main class="max-w-full mx-auto">
  <div class="bg-white rounded-[20px] shadow-md p-8 mb-6">
    <h1 class="text-2xl md:text-3xl font-raleway font-medium text-bleuone leading-tight mt-0 mb-2">
      {{ $selectedSection->section_title }}
    </h1>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div x-data="{ openItem: 1 }" class="space-y-3">
      @if($lecturesWithObjectives->isNotEmpty())
        <div class="border rounded-md">
          <button type="button" @click="openItem = openItem === 1 ? null : 1" class="w-full flex items-center justify-between px-4 py-3 text-left font-varela text-bleuone bg-gray-100 hover:bg-gray-200">
            <span>Objectifs</span>
          </button>
          <div x-show="openItem === 1" x-collapse.duration.400ms class="overflow-hidden p-4 bg-white font-lisible text-[17px] text-gray-800 leading-relaxed">
            <div class="space-y-4">
              @foreach($lecturesWithObjectives as $lec)
                <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                  <div class="font-semibold text-bleuone">{{ $lec->lecture_title }}</div>
                  <ul class="mt-2 list-disc pl-6 space-y-1 marker:text-orangeone">
                    @foreach($lec->objectives as $obj)
                      <li class="font-medium">{{ $obj->title }}</li>
                    @endforeach
                  </ul>
                </div>
              @endforeach
            </div>
          </div>
        </div>
      @endif

      <div class="border rounded-md">
        <button type="button" @click="openItem = openItem === 2 ? null : 2" class="w-full flex items-center justify-between px-4 py-3 text-left font-varela text-bleuone bg-gray-100 hover:bg-gray-200">
          <span>Questions pour commencer</span>
        </button>
        <div x-show="openItem === 2" x-collapse.duration.400ms class="overflow-hidden p-4 bg-white font-lisible text-[17px] text-gray-800 leading-relaxed">
          @if($rawQuestions !== '')
            @if($isHtml)
              {!! $rawQuestions !!}
            @else
              <ul class="list-disc pl-6 space-y-1 marker:text-orangeone">
                @foreach(collect(preg_split("/\r\n|\n|\r/", $rawQuestions))->map(fn($qq) => trim($qq))->filter()->values() as $qq)
                  <li>{!! preg_replace('/\s\?$/', '&nbsp;?', e($qq)) !!}</li>
                @endforeach
              </ul>
            @endif
          @else
            <p class="text-sm text-gray-600">Aucune question n’est renseignée pour le moment.</p>
          @endif
        </div>
      </div>
    </div>

    <div class="space-y-6">
      <div class="bg-white rounded-[16px] border border-gray-200 p-6 text-sm text-gray-600">
        Ce parcours est affiché en lecture seule pour l’observateur.
      </div>

      @if($firstLecture)
        <div class="mt-4">
          <a href="{{ route('observateur.formations.lecture', ['module' => $module->id, 'section' => $selectedSection->id, 'lecture' => $firstLecture->id] + $q) }}"
             class="btn-oneduc flex items-center justify-center gap-2">
            Commencer cette section
          </a>
        </div>
      @endif
    </div>
  </div>
</main>
@endsection
