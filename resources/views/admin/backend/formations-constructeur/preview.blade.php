@extends('admin.admin_dashboard')

@section('title', 'Aperçu — '.($module->module_title ?? $module->module_name))

@section('admin')
@php
  $sections = collect($module->sections ?? []);
  $section = $section ?? $sections->first(fn ($item) => collect($item->lectures ?? [])->isNotEmpty()) ?? $sections->first();
  $lecture = $lecture ?? ($section ? collect($section->lectures ?? [])->first() : null);
  $blocsApercu = collect($initialBlocks ?? $lecture?->content_blocks ?? [])->filter(fn ($block) => is_array($block));
@endphp

<div class="mx-auto w-full max-w-[1500px]">
  <header class="my-4 rounded-[20px] border border-gray-100 bg-white px-5 py-4 shadow-sm sm:px-7">
    <div class="flex flex-wrap items-start justify-between gap-4">
      <div class="min-w-0">
        <nav aria-label="Fil d'Ariane" class="text-sm text-gray-500">
          <a href="{{ route('admin.formations.constructeur.index') }}" class="font-semibold text-orangeone hover:underline">Catalogue Oneduc</a>
          <span class="mx-2 text-gray-300">/</span>
          <a href="{{ route('admin.formations.constructeur.edit', $module) }}" class="font-semibold text-orangeone hover:underline">{{ $module->module_title }}</a>
          <span class="mx-2 text-gray-300">/</span>
          <span>Aperçu</span>
        </nav>
        <h1 class="mt-2 truncate font-raleway text-2xl font-bold text-bleuone">{{ $module->module_title }}</h1>
        <p class="mt-1 text-sm text-gray-600">Aperçu pédagogique avant publication, sans progression ni résultat stagiaire.</p>
      </div>
      <a href="{{ route('admin.formations.constructeur.edit', $module) }}" class="btn-oneduc-outline !px-4 !py-2 !text-sm">
        <i class="ti ti-arrow-left" aria-hidden="true"></i>
        Retour au constructeur
      </a>
    </div>
  </header>

  @if(!$section || !$lecture)
    <div class="rounded-[20px] border-2 border-dashed border-gray-200 bg-white px-6 py-16 text-center shadow-sm">
      <i class="ti ti-book-off text-4xl text-gray-300" aria-hidden="true"></i>
      <h2 class="mt-3 font-varela text-lg font-bold text-bleuone">Aucune leçon à prévisualiser</h2>
      <p class="mt-1 text-sm text-gray-500">Ajoutez une leçon au brouillon pour afficher son rendu.</p>
    </div>
  @else
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-[280px_minmax(0,1fr)]">
      <aside class="rounded-[20px] border border-gray-100 bg-white p-4 shadow-sm lg:sticky lg:top-20 lg:self-start" aria-label="Plan de la formation">
        <p class="px-2 pb-3 font-varela text-sm font-bold uppercase tracking-wide text-bleuone">Programme</p>
        <div class="space-y-4">
          @foreach($sections as $sectionPlan)
            <section>
              <h2 class="px-2 text-xs font-bold uppercase tracking-wide text-gray-500">{{ $sectionPlan->section_title }}</h2>
              <ul class="mt-2 space-y-1">
                @foreach($sectionPlan->lectures as $lecturePlan)
                  @php($active = (int) $lecturePlan->id === (int) $lecture->id)
                  <li>
                    <a href="{{ route('admin.formations.constructeur.preview', ['module' => $module, 'section' => $sectionPlan->id, 'lecture' => $lecturePlan->id]) }}"
                       @class([
                         'flex items-center justify-between gap-2 rounded-[10px] px-3 py-2 text-sm font-semibold transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-orangeone',
                         'bg-bleuone text-white' => $active,
                         'text-gray-700 hover:bg-gray-50 hover:text-bleuone' => !$active,
                       ])
                       @if($active) aria-current="page" @endif>
                      <span class="truncate">{{ $lecturePlan->lecture_title }}</span>
                      @if(in_array($lecturePlan->content_type, ['scorm', 'slides'], true))
                        <span class="text-[9px] font-bold uppercase {{ $active ? 'text-white/80' : 'text-amber-700' }}">{{ $lecturePlan->content_type }}</span>
                      @endif
                    </a>
                  </li>
                @endforeach
              </ul>
            </section>
          @endforeach
        </div>
      </aside>

      <main class="min-w-0 rounded-[20px] border border-gray-100 bg-white px-5 py-7 shadow-sm sm:px-9">
        <p class="text-xs font-bold uppercase tracking-widest text-orangeone">{{ $section->section_title }}</p>
        <h2 class="mt-2 font-raleway text-3xl font-bold text-bleuone">{{ $lecture->lecture_title }}</h2>
        <hr class="my-6 w-24 border-orangeone">

        @if(in_array($lecture->content_type, ['scorm', 'slides'], true))
          <div class="rounded-[16px] border border-amber-200 bg-amber-50 p-6">
            <span class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-xs font-bold uppercase text-amber-800">{{ $lecture->content_type }}</span>
            <h3 class="mt-3 font-varela text-lg font-bold text-bleuone">Contenu historique conservé dans son format d'origine</h3>
            <p class="mt-2 text-sm leading-relaxed text-gray-700">La ressource s'ouvre dans son lecteur spécialisé afin de préserver son fonctionnement et ses données.</p>
            @php($routeLegacy = $lecture->content_type === 'slides' ? 'lecture.slides' : 'lecture.scorm')
            @if(Route::has($routeLegacy))
              <a href="{{ route($routeLegacy, ['id' => $lecture->id]) }}" target="_blank" rel="noopener"
                 class="btn-oneduc mt-5 !px-4 !py-2 !text-sm">
                Ouvrir l'aperçu {{ strtoupper($lecture->content_type) }}
              </a>
            @endif
          </div>
        @else
          <div class="font-lisible">
            @forelse($blocsApercu as $block)
              @include('shared.lecture_block_single', ['block' => $block, 'lecture' => $lecture])
            @empty
              <p class="rounded-[12px] bg-gray-50 px-4 py-8 text-center text-sm text-gray-500">Cette leçon ne contient encore aucun bloc.</p>
            @endforelse
          </div>
        @endif
      </main>
    </div>
  @endif
</div>
@endsection
