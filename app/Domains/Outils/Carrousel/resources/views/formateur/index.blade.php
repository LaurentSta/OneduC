@extends('formateur.dashboard')

@section('formateur')
<div class="w-full px-6 lg:px-8">
  <header class="my-6 rounded-[20px] bg-white px-8 pb-6 pt-5 shadow-md">
    <x-oneduc.breadcrumb :items="[['label' => 'Outils numériques', 'url' => route('formateur.outils.index')], ['label' => 'Carrousel']]" />
    <h1 class="font-raleway text-2xl text-bleuone">Carrousel</h1>
    <p class="mt-1 text-sm text-gray-500">Créez une succession de slides (texte et image) que les stagiaires parcourent à leur rythme.</p>
  </header>

  <div class="mb-8 grid grid-cols-1 gap-6 lg:grid-cols-3">
    <section class="rounded-[20px] bg-white p-6 shadow-md lg:col-span-1 lg:self-start">
      <h2 class="mb-4 font-varela text-base font-bold text-bleuone">Nouveau carrousel</h2>

      @if($errors->any())
        <div class="mb-4 rounded-[8px] border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>
      @endif

      @if($groups->isEmpty())
        <p class="rounded-[8px] border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">Aucun groupe accessible.</p>
      @else
        <form method="POST" action="{{ route('formateur.carrousel.store') }}" class="space-y-4">
          @csrf
          <div>
            <label for="carrousel-group" class="mb-1 block text-xs font-semibold text-gray-700">Groupe</label>
            <select id="carrousel-group" name="group_id" required class="w-full rounded-[8px] border-gray-300 text-sm focus:border-orangeone focus:ring-orangeone/20">
              <option value="">Choisir un groupe</option>
              @foreach($groups as $group)
                <option value="{{ $group->id }}" @selected(old('group_id') == $group->id)>
                  {{ $group->name }} ({{ $group->students_count }} stagiaire{{ $group->students_count > 1 ? 's' : '' }})
                </option>
              @endforeach
            </select>
          </div>
          <div>
            <label for="carrousel-title" class="mb-1 block text-xs font-semibold text-gray-700">Titre</label>
            <input id="carrousel-title" name="title" value="{{ old('title') }}" maxlength="255" class="w-full rounded-[8px] border-gray-300 text-sm focus:border-orangeone focus:ring-orangeone/20" placeholder="Les grandes étapes du projet">
          </div>
          <button class="btn-oneduc w-full justify-center" type="submit">Créer le carrousel</button>
        </form>
      @endif
    </section>

    <section class="space-y-4 lg:col-span-2">
      @if(session('success'))
        <div class="rounded-[8px] border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
      @endif

      @forelse($sessions as $session)
        <article class="flex flex-col gap-4 rounded-[20px] bg-white p-5 shadow-md sm:flex-row sm:items-center sm:justify-between">
          <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2">
              <h3 class="truncate font-semibold text-bleuone">{{ $session->title }}</h3>
              <span class="rounded-full px-2 py-0.5 text-[10px] font-bold {{ $session->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                {{ $session->is_active ? 'Ouvert' : 'Fermé' }}
              </span>
            </div>
            <p class="mt-1 text-xs text-gray-500">{{ $session->group_name }} · code <span class="font-mono font-bold text-orangeone">{{ $session->access_code }}</span> · {{ $session->slides_count }} slide(s)</p>
          </div>
          <div class="flex shrink-0 gap-2">
            <a href="{{ route('formateur.carrousel.show', $session->id) }}" class="btn-oneduc !px-4 !py-2">Ouvrir</a>
            <form method="POST" action="{{ route('formateur.carrousel.destroy', $session->id) }}" onsubmit="return confirm('Supprimer définitivement ce carrousel ?')">
              @csrf
              @method('DELETE')
              <button type="submit" class="btn-oneduc-outline !px-4 !py-2">Supprimer</button>
            </form>
          </div>
        </article>
      @empty
        <div class="rounded-[20px] border-2 border-dashed border-gray-200 bg-white py-16 text-center text-sm text-gray-500">Aucun carrousel créé.</div>
      @endforelse
    </section>
  </div>
</div>
@endsection
