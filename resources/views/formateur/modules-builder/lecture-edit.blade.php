{{-- Vue d'édition de leçon partagée par les espaces formateur et administrateur. --}}
@php
  $constructeurAdmin = (bool) ($constructeurAdmin ?? false);
  $layoutConstructeur = $layoutConstructeur ?? ($constructeurAdmin ? 'admin.admin_dashboard' : 'formateur.dashboard');
  $sectionConstructeur = $sectionConstructeur ?? ($constructeurAdmin ? 'admin' : 'formateur');
  $nomRoutesConstructeur = $nomRoutesConstructeur ?? ($constructeurAdmin
      ? 'admin.formations.constructeur'
      : 'formateur.modules.builder');
  $etatPublication = $module->publication_state ?? ($module->status ? 'published' : 'draft');
  $lectureSeule = $constructeurAdmin && in_array($etatPublication, ['published', 'archived'], true);
  $urlApercuLecon = $urlApercuLecon ?? (
      $constructeurAdmin
          ? route($nomRoutesConstructeur.'.preview', ['module' => $module, 'section' => $section->id, 'lecture' => $lecture->id])
          : route('formateur.formations.lecture', ['module' => $module->id, 'section' => $section->id, 'lecture' => $lecture->id])
  );
  $urlPanelLegacy = $constructeurAdmin && ! $lectureSeule && Route::has('admin.lectures.edit')
      ? route('admin.lectures.edit', $lecture->id)
      : null;
@endphp

@extends($layoutConstructeur)

@section($sectionConstructeur)
<div class="w-full px-6 lg:px-8">

  <div class="flex items-center gap-4 border-b border-gray-200 py-3">
    <nav aria-label="Fil d'ariane" class="min-w-0 flex-1 truncate text-sm font-varela text-gray-500">
      <a href="{{ route($nomRoutesConstructeur.'.index') }}" class="text-orangeone hover:underline">{{ $constructeurAdmin ? 'Catalogue Oneduc' : 'Mes créations' }}</a>
      <span class="mx-1 text-gray-400">/</span>
      <a href="{{ route($nomRoutesConstructeur.'.edit', $module) }}" class="text-orangeone hover:underline">{{ $module->module_title }}</a>
      <span class="mx-1 text-gray-400">/</span>
      <span class="text-gray-400">{{ $section->section_title }}</span>
      <span class="mx-1 text-gray-400">/</span>
      <span class="font-semibold text-bleuone">{{ $lecture->lecture_title }}</span>
    </nav>

    <div class="flex shrink-0 items-center gap-3">
      <a href="{{ route($nomRoutesConstructeur.'.edit', $module) }}" class="text-sm font-semibold text-gray-500 hover:text-orangeone">← Retour au plan de la formation</a>
      <a href="{{ $urlApercuLecon }}"
         target="_blank" rel="noopener"
         class="btn-oneduc-outline !px-4 !py-1.5 !text-xs">Aperçu</a>
    </div>
  </div>

  @if(session('success'))
    <div class="mb-6 rounded-[10px] bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
      {{ session('success') }}
    </div>
  @endif

  @if(session('error'))
    <div class="mb-6 rounded-[10px] bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
      {{ session('error') }}
    </div>
  @endif

  @if($lectureSeule)
    <div class="mb-6 flex items-start gap-3 rounded-[10px] border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-800" role="status">
      <i class="ti ti-lock mt-0.5" aria-hidden="true"></i>
      <p>
        Cette version {{ $etatPublication === 'archived' ? 'archivée' : 'publiée' }} est en lecture seule afin de préserver les progressions.
        Créez une nouvelle version depuis le plan de la formation pour la modifier.
      </p>
    </div>
  @endif

  <div class="bg-white rounded-[20px] shadow-md px-10 py-10 mb-8">
    @if(in_array($lecture->content_type, ['scorm', 'slides'], true))
      <div class="mb-4 flex items-center gap-2 rounded-[10px] bg-amber-50 border border-amber-200 px-3 py-2 text-xs text-amber-700">
        <span class="font-bold uppercase">{{ $lecture->content_type === 'slides' ? 'Slides' : 'SCORM' }}</span>
        <span>Contenu importé depuis le catalogue, non modifiable ici (seul le titre peut être renommé).</span>
      </div>
      @if($lectureSeule)
        <h1 class="font-raleway text-2xl font-bold text-bleuone">{{ $lecture->lecture_title }}</h1>
      @else
      <form method="POST" action="{{ route($nomRoutesConstructeur.'.lectures.update', $lecture) }}" class="flex gap-2">
        @csrf
        @method('PUT')
        <input type="text" name="lecture_title" value="{{ $lecture->lecture_title }}" required maxlength="255"
               class="flex-1 border-0 bg-transparent p-0 font-raleway text-2xl font-bold text-bleuone focus:outline-none focus:ring-0">
        <button type="submit" class="shrink-0 rounded-[8px] border border-gray-300 bg-white px-3 py-1.5 text-xs font-bold text-gray-600 hover:bg-gray-50">Renommer</button>
      </form>
      @endif
      @if($urlPanelLegacy)
        <div class="mt-5 rounded-[12px] border border-gray-200 bg-gray-50 p-4">
          <p class="text-sm font-semibold text-bleuone">Panneau spécialisé {{ strtoupper($lecture->content_type) }}</p>
          <p class="mt-1 text-xs text-gray-600">Le paquet historique reste dans son format d'origine afin d'éviter toute perte de contenu.</p>
          <a href="{{ $urlPanelLegacy }}" class="btn-oneduc-outline mt-3 !px-4 !py-2 !text-xs">
            Gérer le contenu {{ strtoupper($lecture->content_type) }}
          </a>
        </div>
      @endif
    @elseif($lectureSeule)
      <h1 class="font-raleway text-2xl font-bold text-bleuone">{{ $lecture->lecture_title }}</h1>
      <div class="mt-8">
        @forelse($initialBlocks ?? [] as $block)
          @include('shared.lecture_block_single', ['block' => $block, 'lecture' => $lecture])
        @empty
          <p class="rounded-[10px] bg-gray-50 px-4 py-6 text-center text-sm text-gray-500">Cette leçon ne contient encore aucun bloc.</p>
        @endforelse
      </div>
    @else
      <div data-block-editor
           data-lecture-id="{{ $lecture->id }}"
           data-update-url="{{ route($nomRoutesConstructeur.'.lectures.update', $lecture) }}"
           data-upload-url="{{ route($nomRoutesConstructeur.'.images.store', $module) }}"
           data-video-upload-url="{{ route($nomRoutesConstructeur.'.videos.store', $module) }}"
           data-audio-upload-url="{{ route($nomRoutesConstructeur.'.audios.store', $module) }}"
           data-audio-generate-url="{{ route($nomRoutesConstructeur.'.lectures.generate-audio', $lecture) }}"
           data-scorm-upload-url="{{ route($nomRoutesConstructeur.'.scorm.store', $module) }}"
           data-initial-title="{{ $lecture->lecture_title }}"
           data-initial-blocks="{{ json_encode($initialBlocks ?? []) }}"></div>
    @endif
  </div>
</div>
@endsection
