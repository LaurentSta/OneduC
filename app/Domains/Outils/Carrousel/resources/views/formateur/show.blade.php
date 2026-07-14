@extends('formateur.dashboard')

@section('formateur')
<div class="w-full px-6 lg:px-8">
  <header class="my-6 rounded-[20px] bg-white px-8 pb-6 pt-5 shadow-md">
    <x-oneduc.breadcrumb :items="[['label' => 'Outils numériques', 'url' => route('formateur.outils.index')], ['label' => 'Carrousel', 'url' => route('formateur.carrousel.index')], ['label' => $session->title]]" />
    <div class="flex flex-wrap items-center justify-between gap-4">
      <div>
        <h1 class="font-raleway text-2xl text-bleuone">{{ $session->title }}</h1>
        <p class="mt-1 text-sm text-gray-500">{{ $session->group_name }}</p>
      </div>
      <div class="flex items-center gap-3">
        <div class="rounded-[10px] bg-slate-50 px-4 py-2 text-center">
          <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Code d'accès</p>
          <p class="font-mono text-lg font-bold text-orangeone">{{ $session->access_code }}</p>
        </div>
        <form method="POST" action="{{ route('formateur.carrousel.toggle', $session->id) }}">
          @csrf
          <button type="submit" class="btn-oneduc-outline !px-4 !py-2">{{ $session->is_active ? 'Fermer' : 'Rouvrir' }}</button>
        </form>
      </div>
    </div>
    <p class="mt-3 text-xs text-gray-500">Lien stagiaire : <a href="{{ $joinUrl }}" class="text-bleuone underline">{{ $joinUrl }}</a></p>
  </header>

  @if(session('success'))
    <div class="mb-4 rounded-[8px] border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
  @endif
  @if($errors->any())
    <div class="mb-4 rounded-[8px] border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>
  @endif

  <div class="mb-8 grid grid-cols-1 gap-6 lg:grid-cols-3">
    <section class="rounded-[20px] bg-white p-6 shadow-md lg:col-span-1 lg:self-start">
      <h2 class="mb-4 font-varela text-base font-bold text-bleuone">Ajouter une slide</h2>
      <form method="POST" action="{{ route('formateur.carrousel.slides.store', $session->id) }}" enctype="multipart/form-data" class="space-y-4">
        @csrf
        <div>
          <label for="slide-text" class="mb-1 block text-xs font-semibold text-gray-700">Texte</label>
          <textarea id="slide-text" name="text" rows="3" maxlength="2000" class="w-full rounded-[8px] border-gray-300 text-sm focus:border-orangeone focus:ring-orangeone/20" placeholder="Contenu de la slide"></textarea>
        </div>
        <div>
          <label for="slide-image" class="mb-1 block text-xs font-semibold text-gray-700">Image (optionnelle)</label>
          <input id="slide-image" name="image" type="file" accept="image/*" class="w-full text-sm">
        </div>
        <button class="btn-oneduc w-full justify-center" type="submit">Ajouter la slide</button>
      </form>
    </section>

    <section class="space-y-4 lg:col-span-2">
      @forelse($slides as $slide)
        <article class="flex flex-col gap-4 rounded-[20px] bg-white p-5 shadow-md sm:flex-row sm:items-start sm:justify-between">
          <div class="flex min-w-0 gap-4">
            @if($slide->image_path)
              <img src="{{ \Illuminate\Support\Facades\Storage::url($slide->image_path) }}" alt="" class="h-16 w-16 shrink-0 rounded-[8px] object-cover">
            @endif
            <p class="text-sm text-gray-700">{{ $slide->text ?: '(sans texte)' }}</p>
          </div>
          <div class="flex shrink-0 flex-wrap gap-2">
            <form method="POST" action="{{ route('formateur.carrousel.slides.move', [$session->id, $slide->id]) }}">
              @csrf
              <input type="hidden" name="direction" value="up">
              <button type="submit" class="btn-oneduc-outline !px-3 !py-1.5" title="Monter">↑</button>
            </form>
            <form method="POST" action="{{ route('formateur.carrousel.slides.move', [$session->id, $slide->id]) }}">
              @csrf
              <input type="hidden" name="direction" value="down">
              <button type="submit" class="btn-oneduc-outline !px-3 !py-1.5" title="Descendre">↓</button>
            </form>
            <form method="POST" action="{{ route('formateur.carrousel.slides.destroy', [$session->id, $slide->id]) }}" onsubmit="return confirm('Supprimer cette slide ?')">
              @csrf
              @method('DELETE')
              <button type="submit" class="btn-oneduc-outline !px-3 !py-1.5">Supprimer</button>
            </form>
          </div>
        </article>
      @empty
        <div class="rounded-[20px] border-2 border-dashed border-gray-200 bg-white py-16 text-center text-sm text-gray-500">Aucune slide pour l'instant.</div>
      @endforelse
    </section>
  </div>
</div>
@endsection
