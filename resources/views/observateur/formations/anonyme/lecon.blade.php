@extends('observateur.formations.master_lecon')
@section('content')

@php
  use Illuminate\Support\Facades\Storage;

  $lecture = $selectedLecture ?? null;
  $contentType = (string) ($lecture->content_type ?? 'scorm');
  $isSlidesSelected = $contentType === 'slides';
  $isScormSelected = ! $isSlidesSelected;
  $q = array_merge(($contextQuery ?? []), ['anonymous' => 1]);
  $appendQuery = static function (string $url, array $query): string {
      if (empty($query)) {
          return $url;
      }
      return $url . (str_contains($url, '?') ? '&' : '?') . http_build_query($query);
  };
  $nextUrl = '#';
  $finalUrl = route('observateur.groupes.index');

  if (!empty($nextLecture['url'])) {
      $nextUrl = $appendQuery((string) $nextLecture['url'], $q);
  }

  $scormSrc = $lecture?->scorm_asset_url;
  $isSlidesMode = $lecture
      && $isSlidesSelected
      && ($lecture->slides_status ?? null) === 'ready'
      && !empty($lecture->slides_path);

  $slideImages = [];
  if ($isSlidesMode) {
      $slideImages = collect(Storage::disk('public')->files($lecture->slides_path))
          ->filter(fn (string $file) => (bool) preg_match('/^slide[-_]\\d+\\.jpg$/i', basename($file)))
          ->sortBy(function (string $file): int {
              if (preg_match('/(\\d+)\\.jpg$/i', basename($file), $matches)) {
                  return (int) $matches[1];
              }
              return PHP_INT_MAX;
          })
          ->values()
          ->map(fn (string $file) => route('media.storage', ['path' => $file], false))
          ->all();
  }
@endphp

<main class="flex-1 bg-white">
  @if ($lecture)
    <script>
      window.SCORM_CONTEXT = {
        lecture_id: @json($lecture->id ?? null),
        module_id: @json($module->id ?? null),
        section_id: @json($lecture->section_id ?? null),
        next_url: @json($nextUrl),
        final_url: @json($finalUrl),
        is_already_done: false,
        anonymous: true,
        read_only: true,
        force_next_lesson: true,
        quiz_start_url: null,
        quiz_tester_url: null,
        goToNextLesson: function () {
          if (this.next_url && this.next_url !== '#') {
            window.location.href = this.next_url;
            return;
          }
          window.location.href = this.final_url;
        }
      };
    </script>

    @if ($isSlidesMode && !empty($slideImages))
      <div class="relative w-full bg-gray-100" style="height: calc(100vh - var(--app-header-h, 86px));">
        <div
          x-data="{
            current: 1,
            total: {{ count($slideImages) }},
            slides: @js($slideImages),
            get currentSrc() { return this.slides[this.current - 1] ?? null; }
          }"
          class="h-full flex flex-col"
        >
          <div class="relative flex-1 p-4 md:p-6">
            <div class="absolute top-6 right-6 z-10 px-3 py-1 rounded-full bg-black/70 text-white text-xs font-semibold">
              Slide <span x-text="current"></span> / <span x-text="total"></span>
            </div>
            <div class="h-full w-full flex items-center justify-center rounded-xl border border-gray-200 bg-white overflow-hidden">
              <img :src="currentSrc" alt="Slide de cours" class="max-h-full max-w-full object-contain">
            </div>
          </div>

          <div class="border-t border-gray-200 bg-white px-4 py-3 flex items-center justify-between gap-3">
            <div class="flex items-center gap-2">
              <button type="button" @click="current = Math.max(1, current - 1)" class="px-3 py-2 text-xs font-bold uppercase border border-gray-300 rounded hover:bg-gray-50">
                Precedent
              </button>
              <button type="button" @click="current = Math.min(total, current + 1)" class="px-3 py-2 text-xs font-bold uppercase border border-gray-300 rounded hover:bg-gray-50">
                Suivant
              </button>
            </div>
            <a href="{{ $nextUrl !== '#' ? $nextUrl : $finalUrl }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-orangeone text-white rounded-full text-xs font-bold uppercase hover:bg-orangeone-hover transition">
              Leçon suivante
            </a>
          </div>
        </div>
      </div>
    @elseif ($lecture && $isScormSelected && $scormSrc)
      <div class="relative w-full bg-gray-100" style="height: calc(100vh - var(--app-header-h, 86px));">
        <iframe title="{{ $lecture->lecture_title }}" src="{{ $scormSrc }}" frameborder="0" allowfullscreen class="w-full h-full block"></iframe>
      </div>
    @else
      <div class="flex items-center justify-center h-full min-h-[60vh]">
        <div class="text-center p-8 bg-white rounded-2xl shadow-sm border border-gray-200">
          <h3 class="text-lg font-bold text-bleuone">Contenu non disponible</h3>
          <p class="text-gray-500 text-sm">Aucun contenu prêt n’est disponible pour cette leçon.</p>
        </div>
      </div>
    @endif
  @endif
</main>
@endsection
