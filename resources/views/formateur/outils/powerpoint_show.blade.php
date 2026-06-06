@extends('formateur.dashboard')

@section('formateur')
@php
  $status = (string) ($lecture->slides_status ?? 'none');
  $statusLabels = [
    'pending' => 'En attente',
    'processing' => 'Conversion en cours',
    'ready' => 'Présentation prête',
    'failed' => 'Conversion échouée',
    'none' => 'Non converti',
  ];
  $statusClasses = [
    'pending' => 'border-amber-200 bg-amber-50 text-amber-700',
    'processing' => 'border-blue-200 bg-blue-50 text-blue-700',
    'ready' => 'border-green-200 bg-green-50 text-green-700',
    'failed' => 'border-red-200 bg-red-50 text-red-700',
    'none' => 'border-gray-200 bg-gray-50 text-gray-600',
  ];
@endphp

<div
  class="w-full px-6 lg:px-8"
  x-data="{
    status: @js($status),
    poller: null,
    init() {
      if (['pending', 'processing'].includes(this.status)) {
        this.poller = window.setInterval(() => this.refreshStatus(), 3000);
      }
    },
    async refreshStatus() {
      try {
        const response = await fetch(@js(route('formateur.outils.powerpoint.status', $module)), {
          headers: { 'Accept': 'application/json' },
          credentials: 'same-origin'
        });
        const payload = await response.json();
        if (payload.status !== this.status || ['ready', 'failed'].includes(payload.status)) {
          window.clearInterval(this.poller);
          window.location.reload();
        }
      } catch (error) {
        console.error('Impossible de vérifier la conversion PowerPoint.', error);
      }
    }
  }"
>
  <header class="bg-white rounded-[20px] shadow-md px-8 pt-5 pb-6 my-6">
    <div class="flex flex-wrap items-start justify-between gap-5">
      <div class="min-w-0">
        <nav class="text-sm font-varela text-gray-500 mb-2">
          <ol class="inline-flex flex-wrap items-center space-x-1">
            <li><a href="{{ route('formateur.outils.index') }}" class="text-orangeone hover:underline">Outils numériques</a></li>
            <li><span class="mx-2 text-gray-400">/</span></li>
            <li><a href="{{ route('formateur.outils.powerpoint.index') }}" class="text-orangeone hover:underline">PowerPoint vers module</a></li>
            <li><span class="mx-2 text-gray-400">/</span></li>
            <li class="text-gray-400">{{ $module->module_title }}</li>
          </ol>
        </nav>
        <div class="flex flex-wrap items-center gap-3">
          <h1 class="font-raleway text-2xl text-bleuone">{{ $module->module_title }}</h1>
          <span class="rounded-full border px-3 py-1 text-xs font-bold {{ $statusClasses[$status] ?? $statusClasses['none'] }}">
            {{ $statusLabels[$status] ?? 'Inconnu' }}
          </span>
          <span class="rounded-full border border-gray-200 bg-gray-50 px-3 py-1 text-xs font-bold text-gray-600">
            {{ $module->status ? 'Publié' : 'Brouillon' }}
          </span>
        </div>
        <p class="mt-2 text-sm text-gray-500">
          {{ $module->category?->category_name }} · {{ $module->subCategory?->subcategory_name }}
          @if($lecture->slide_count)
            · {{ $lecture->slide_count }} diapositives
          @endif
        </p>
      </div>

      <div class="flex flex-wrap gap-2">
        <a
          href="{{ route('formateur.outils.powerpoint.index') }}"
          class="inline-flex items-center justify-center rounded-[10px] border border-gray-300 bg-white px-4 py-2.5 text-sm font-bold text-gray-700 transition hover:bg-gray-50"
        >
          Retour
        </a>
        @if($status === 'ready')
          <a
            href="{{ route('formateur.formations.detail', $module) }}"
            class="inline-flex items-center justify-center rounded-[10px] border border-violet-200 bg-violet-50 px-4 py-2.5 text-sm font-bold text-violet-700 transition hover:bg-violet-100"
          >
            Voir le module
          </a>
          <form method="POST" action="{{ route('formateur.outils.powerpoint.publish', $module) }}">
            @csrf
            <input type="hidden" name="published" value="{{ $module->status ? 0 : 1 }}">
            <button
              type="submit"
              class="inline-flex items-center justify-center rounded-[10px] px-4 py-2.5 text-sm font-bold text-white transition {{ $module->status ? 'bg-gray-600 hover:bg-gray-700' : 'bg-green-600 hover:bg-green-700' }}"
            >
              {{ $module->status ? 'Remettre en brouillon' : 'Publier le module' }}
            </button>
          </form>
        @endif
      </div>
    </div>
  </header>

  @if(session('success'))
    <div class="mb-5 rounded-[14px] border border-green-200 bg-green-50 px-5 py-4 text-sm text-green-700">
      {{ session('success') }}
    </div>
  @endif

  @if(session('error'))
    <div class="mb-5 rounded-[14px] border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700">
      {{ session('error') }}
    </div>
  @endif

  @if(in_array($status, ['pending', 'processing'], true))
    <section class="mb-8 rounded-[20px] bg-white p-8 text-center shadow-md">
      <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-blue-50 text-blue-600">
        <svg class="h-8 w-8 animate-spin" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3"></circle>
          <path class="opacity-75" fill="currentColor" d="M12 3a9 9 0 018.49 6H17a6 6 0 00-5-3V3z"></path>
        </svg>
      </div>
      <h2 class="mt-5 font-raleway text-xl font-semibold text-bleuone">Création des diapositives en cours</h2>
      <p class="mx-auto mt-2 max-w-xl text-sm leading-6 text-gray-500">
        Onéduc prépare votre présentation. Cette page se mettra automatiquement à jour dès que le lecteur sera prêt.
      </p>
    </section>
  @elseif($status === 'failed')
    <section class="mb-8 rounded-[20px] border border-red-200 bg-white p-7 shadow-md">
      <div class="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">
        <div>
          <h2 class="font-raleway text-xl font-semibold text-red-700">La conversion n’a pas abouti</h2>
          <p class="mt-2 max-w-3xl text-sm leading-6 text-gray-600">
            {{ $lecture->slides_error ?: 'Une erreur inconnue est survenue pendant la conversion.' }}
          </p>
        </div>
        <form method="POST" action="{{ route('formateur.outils.powerpoint.retry', $module) }}">
          @csrf
          <button type="submit" class="inline-flex shrink-0 items-center justify-center rounded-[10px] bg-red-600 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-red-700">
            Relancer la conversion
          </button>
        </form>
      </div>
    </section>
  @elseif($status === 'ready' && !empty($slides))
    <section
      class="mb-8 overflow-hidden rounded-[20px] bg-slate-950 shadow-xl"
      x-data="powerPointSlideViewer(@js($slides))"
      @keydown.right.window="next()"
      @keydown.left.window="previous()"
      @keydown.home.window="goTo(0)"
      @keydown.end.window="goTo(slides.length - 1)"
    >
      <div class="flex flex-wrap items-center justify-between gap-3 border-b border-white/10 px-5 py-4 text-white">
        <div>
          <p class="text-xs font-bold uppercase tracking-widest text-violet-300">Lecteur de présentation</p>
          <p class="mt-1 text-sm text-white/70">Utilisez les flèches du clavier ou les miniatures.</p>
        </div>
        <div class="flex items-center gap-2">
          <span class="rounded-full bg-white/10 px-3 py-1.5 text-xs font-bold">
            Diapositive <span x-text="current + 1"></span> / <span x-text="slides.length"></span>
          </span>
          <button
            type="button"
            @click="toggleFullscreen()"
            class="rounded-full bg-white/10 px-3 py-1.5 text-xs font-bold transition hover:bg-white/20"
          >
            Plein écran
          </button>
        </div>
      </div>

      <div x-ref="viewer" class="relative flex min-h-[55vh] items-center justify-center bg-black/30 p-3 sm:p-6">
        <img :src="slides[current]" :alt="'Diapositive ' + (current + 1)" class="max-h-[72vh] w-full object-contain shadow-2xl">

        <button
          type="button"
          @click="previous()"
          :disabled="current === 0"
          class="absolute left-4 top-1/2 flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full bg-black/60 text-2xl text-white transition hover:bg-black/80 disabled:cursor-not-allowed disabled:opacity-25"
          aria-label="Diapositive précédente"
        >‹</button>
        <button
          type="button"
          @click="next()"
          :disabled="current === slides.length - 1"
          class="absolute right-4 top-1/2 flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full bg-black/60 text-2xl text-white transition hover:bg-black/80 disabled:cursor-not-allowed disabled:opacity-25"
          aria-label="Diapositive suivante"
        >›</button>
      </div>

      <div class="border-t border-white/10 bg-slate-900 px-4 py-4">
        <div class="flex gap-3 overflow-x-auto pb-2">
          <template x-for="(slide, index) in slides" :key="slide">
            <button
              type="button"
              @click="goTo(index)"
              class="relative shrink-0 overflow-hidden rounded-lg border-2 transition"
              :class="current === index ? 'border-violet-400 ring-2 ring-violet-400/30' : 'border-white/10 opacity-60 hover:opacity-100'"
            >
              <img :src="slide" :alt="'Miniature ' + (index + 1)" class="h-20 w-32 object-cover">
              <span class="absolute bottom-1 right-1 rounded bg-black/70 px-1.5 py-0.5 text-[10px] font-bold text-white" x-text="index + 1"></span>
            </button>
          </template>
        </div>
      </div>
    </section>
  @else
    <section class="mb-8 rounded-[20px] border-2 border-dashed border-gray-200 bg-white px-6 py-12 text-center shadow-sm">
      <p class="font-varela text-base font-bold text-bleuone">Aucune diapositive disponible</p>
      <p class="mt-2 text-sm text-gray-500">Relancez la conversion ou créez un nouveau module depuis le fichier source.</p>
    </section>
  @endif
</div>

<script>
  window.powerPointSlideViewer = function (slides) {
    return {
      slides: Array.isArray(slides) ? slides : [],
      current: 0,
      goTo(index) {
        this.current = Math.max(0, Math.min(Number(index), this.slides.length - 1));
      },
      previous() {
        this.goTo(this.current - 1);
      },
      next() {
        this.goTo(this.current + 1);
      },
      async toggleFullscreen() {
        const target = this.$refs.viewer;
        if (!target || !document.fullscreenEnabled) return;

        if (document.fullscreenElement) {
          await document.exitFullscreen();
          return;
        }

        await target.requestFullscreen();
      }
    };
  };
</script>
@endsection
