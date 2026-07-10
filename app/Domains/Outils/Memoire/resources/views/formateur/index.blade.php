@extends('formateur.dashboard')

@section('formateur')
<div class="w-full px-6 lg:px-8">
  <header class="my-6 rounded-[20px] bg-white px-8 pb-6 pt-5 shadow-md">
    <x-oneduc.breadcrumb :items="[['label' => 'Outils numériques', 'url' => route('formateur.outils.index')], ['label' => 'Jeu de mémoire']]" />
    <h1 class="font-raleway text-2xl text-bleuone">Jeu de mémoire</h1>
    <p class="mt-1 text-sm text-gray-500">Préparez des paires à associer et partagez le code avec le groupe.</p>
  </header>

  <div class="mb-8 grid grid-cols-1 gap-6 lg:grid-cols-3">
    <section class="rounded-[20px] bg-white p-6 shadow-md lg:col-span-1 lg:self-start"
             x-data='{ pairs: [{a:"",b:""},{a:"",b:""},{a:"",b:""}], add(){ if(this.pairs.length<10)this.pairs.push({a:"",b:""}) }, remove(i){ if(this.pairs.length>3)this.pairs.splice(i,1) } }'>
      <h2 class="mb-4 font-varela text-base font-bold text-bleuone">Nouvelle partie</h2>

      @if($errors->any())
        <div class="mb-4 rounded-[8px] border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>
      @endif

      @if($groups->isEmpty())
        <p class="rounded-[8px] border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">Aucun groupe accessible.</p>
      @else
        <form method="POST" action="{{ route('formateur.memoire.store') }}" class="space-y-4">
          @csrf
          <div>
            <label for="memoire-group" class="mb-1 block text-xs font-semibold text-gray-700">Groupe</label>
            <select id="memoire-group" name="group_id" required class="w-full rounded-[8px] border-gray-300 text-sm focus:border-bleuone focus:ring-bleuone/20">
              <option value="">Choisir un groupe</option>
              @foreach($groups as $group)
                <option value="{{ $group->id }}" @selected(old('group_id') == $group->id)>
                  {{ $group->name }} ({{ $group->students_count }} stagiaire{{ $group->students_count > 1 ? 's' : '' }})
                </option>
              @endforeach
            </select>
          </div>
          <div>
            <label for="memoire-title" class="mb-1 block text-xs font-semibold text-gray-700">Titre (optionnel)</label>
            <input id="memoire-title" name="title" value="{{ old('title') }}" maxlength="255" class="w-full rounded-[8px] border-gray-300 text-sm focus:border-bleuone focus:ring-bleuone/20" placeholder="Vocabulaire numérique">
          </div>
          <div class="space-y-3">
            <div class="flex items-center justify-between gap-2">
              <span class="text-xs font-semibold text-gray-700">Paires (3 minimum)</span>
              <button type="button" @click="add()" class="btn-oneduc-outline !px-3 !py-1.5 text-xs">Ajouter</button>
            </div>
            <template x-for="(pair, index) in pairs" :key="index">
              <div class="space-y-2 rounded-[8px] border border-gray-200 p-3">
                <div class="flex justify-between gap-2">
                  <span class="text-xs font-bold text-gray-500" x-text="'Paire ' + (index + 1)"></span>
                  <button type="button" x-show="pairs.length > 3" @click="remove(index)" class="text-xs font-semibold text-red-600">Supprimer</button>
                </div>
                <input :name="`pairs[${index}][a]`" x-model="pair.a" required maxlength="100" class="w-full rounded-[8px] border-gray-300 text-sm" placeholder="HTML">
                <input :name="`pairs[${index}][b]`" x-model="pair.b" required maxlength="100" class="w-full rounded-[8px] border-gray-300 text-sm" placeholder="Langage de balisage">
              </div>
            </template>
          </div>
          <button class="btn-oneduc-blue w-full justify-center" type="submit">Créer la partie</button>
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
            <p class="mt-1 text-xs text-gray-500">{{ $session->group_name }} · code <span class="font-mono font-bold text-bleuone">{{ $session->access_code }}</span> · {{ count($session->pairs) }} paires · {{ $session->attempts_count }} participation(s)</p>
          </div>
          <div class="flex shrink-0 gap-2">
            <a href="{{ route('formateur.memoire.show', $session->id) }}" class="btn-oneduc-blue !px-4 !py-2">Ouvrir</a>
            <form method="POST" action="{{ route('formateur.memoire.destroy', $session->id) }}" onsubmit="return confirm('Supprimer définitivement cette partie ?')">
              @csrf
              @method('DELETE')
              <button type="submit" class="btn-oneduc-outline !px-4 !py-2">Supprimer</button>
            </form>
          </div>
        </article>
      @empty
        <div class="rounded-[20px] border-2 border-dashed border-gray-200 bg-white py-16 text-center text-sm text-gray-500">Aucune partie créée.</div>
      @endforelse
    </section>
  </div>
</div>
@endsection
