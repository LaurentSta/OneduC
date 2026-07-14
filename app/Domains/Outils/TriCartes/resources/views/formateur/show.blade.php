@extends('formateur.dashboard')

@section('formateur')
<div class="w-full px-6 lg:px-8">
  <header class="my-6 rounded-[20px] bg-white px-8 pb-6 pt-5 shadow-md">
    <x-oneduc.breadcrumb :items="[['label' => 'Outils numériques', 'url' => route('formateur.outils.index')], ['label' => 'Cartes à trier', 'url' => route('formateur.tri-cartes.index')], ['label' => $session->title]]" />
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
        <form method="POST" action="{{ route('formateur.tri-cartes.toggle', $session->id) }}">
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
    <section class="space-y-6 lg:col-span-1 lg:self-start">
      <div class="rounded-[20px] bg-white p-6 shadow-md">
        <h2 class="mb-4 font-varela text-base font-bold text-bleuone">Catégories</h2>
        <form method="POST" action="{{ route('formateur.tri-cartes.categories.store', $session->id) }}" class="mb-4 flex gap-2">
          @csrf
          <input name="label" maxlength="120" required class="w-full rounded-[8px] border-gray-300 text-sm focus:border-orangeone focus:ring-orangeone/20" placeholder="Nom de la catégorie">
          <button class="btn-oneduc shrink-0 !px-3" type="submit">Ajouter</button>
        </form>
        <div class="space-y-2">
          @forelse($categories as $categorie)
            <div class="flex items-center justify-between gap-2 rounded-[8px] bg-slate-50 px-3 py-2">
              <span class="truncate text-sm text-gray-700">{{ $categorie->label }}</span>
              <form method="POST" action="{{ route('formateur.tri-cartes.categories.destroy', [$session->id, $categorie->id]) }}" onsubmit="return confirm('Supprimer cette catégorie et les cartes associées ?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-xs font-semibold text-red-600 hover:underline">Supprimer</button>
              </form>
            </div>
          @empty
            <p class="text-xs text-gray-500">Ajoutez au moins deux catégories avant de créer des cartes.</p>
          @endforelse
        </div>
      </div>

      <div class="rounded-[20px] bg-white p-6 shadow-md">
        <h2 class="mb-4 font-varela text-base font-bold text-bleuone">Résultats en direct</h2>
        <div id="tri-cartes-resultats" class="space-y-2">
          <p class="text-xs text-gray-500">En attente de réponses...</p>
        </div>
      </div>
    </section>

    <section class="space-y-6 lg:col-span-2">
      <div class="rounded-[20px] bg-white p-6 shadow-md">
        <h2 class="mb-4 font-varela text-base font-bold text-bleuone">Ajouter une carte</h2>
        @if($categories->isEmpty())
          <p class="rounded-[8px] border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">Créez d'abord une catégorie.</p>
        @else
          <form method="POST" action="{{ route('formateur.tri-cartes.cartes.store', $session->id) }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
              <label class="mb-1 block text-xs font-semibold text-gray-700">Texte</label>
              <textarea name="text" rows="2" maxlength="1000" class="w-full rounded-[8px] border-gray-300 text-sm focus:border-orangeone focus:ring-orangeone/20" placeholder="Contenu de la carte"></textarea>
            </div>
            <div>
              <label class="mb-1 block text-xs font-semibold text-gray-700">Image (optionnelle)</label>
              <input name="image" type="file" accept="image/*" class="w-full text-sm">
            </div>
            <div>
              <label class="mb-1 block text-xs font-semibold text-gray-700">Catégorie correcte</label>
              <select name="correct_category_id" required class="w-full rounded-[8px] border-gray-300 text-sm focus:border-orangeone focus:ring-orangeone/20">
                @foreach($categories as $categorie)
                  <option value="{{ $categorie->id }}">{{ $categorie->label }}</option>
                @endforeach
              </select>
            </div>
            <button class="btn-oneduc w-full justify-center" type="submit">Ajouter la carte</button>
          </form>
        @endif
      </div>

      <div class="space-y-4">
        @forelse($cartes as $carte)
          @php($categorieCarte = $categories->firstWhere('id', $carte->correct_category_id))
          <article class="flex flex-col gap-4 rounded-[20px] bg-white p-5 shadow-md sm:flex-row sm:items-center sm:justify-between">
            <div class="flex min-w-0 items-center gap-4">
              @if($carte->image_path)
                <img src="{{ \Illuminate\Support\Facades\Storage::url($carte->image_path) }}" alt="" class="h-14 w-14 shrink-0 rounded-[8px] object-cover">
              @endif
              <div class="min-w-0">
                <p class="truncate text-sm text-gray-700">{{ $carte->text ?: '(sans texte)' }}</p>
                <span class="mt-1 inline-block rounded-full bg-orange-50 px-2 py-0.5 text-[10px] font-bold text-orangeone">{{ $categorieCarte->label ?? '—' }}</span>
              </div>
            </div>
            <div class="flex shrink-0 flex-wrap gap-2">
              <form method="POST" action="{{ route('formateur.tri-cartes.cartes.move', [$session->id, $carte->id]) }}">
                @csrf
                <input type="hidden" name="direction" value="up">
                <button type="submit" class="btn-oneduc-outline !px-3 !py-1.5" title="Monter">↑</button>
              </form>
              <form method="POST" action="{{ route('formateur.tri-cartes.cartes.move', [$session->id, $carte->id]) }}">
                @csrf
                <input type="hidden" name="direction" value="down">
                <button type="submit" class="btn-oneduc-outline !px-3 !py-1.5" title="Descendre">↓</button>
              </form>
              <form method="POST" action="{{ route('formateur.tri-cartes.cartes.destroy', [$session->id, $carte->id]) }}" onsubmit="return confirm('Supprimer cette carte ?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-oneduc-outline !px-3 !py-1.5">Supprimer</button>
              </form>
            </div>
          </article>
        @empty
          <div class="rounded-[20px] border-2 border-dashed border-gray-200 bg-white py-16 text-center text-sm text-gray-500">Aucune carte pour l'instant.</div>
        @endforelse
      </div>
    </section>
  </div>
</div>

<script>
  (function () {
    const conteneur = document.getElementById('tri-cartes-resultats');
    const url = @json(route('formateur.tri-cartes.state', $session->id));
    const vide = '<p class="text-xs text-gray-500">En attente de réponses...</p>';

    function ligne(resultat) {
      const div = document.createElement('div');
      div.className = 'flex items-center justify-between gap-2 rounded-[8px] bg-slate-50 px-3 py-2 text-sm';

      const nom = document.createElement('span');
      nom.className = 'truncate text-gray-700';
      nom.textContent = resultat.name;

      const score = document.createElement('span');
      score.className = 'font-bold text-orangeone';
      score.textContent = `${resultat.score}/${resultat.total}`;

      div.append(nom, score);
      return div;
    }

    function actualiser() {
      fetch(url, { headers: { Accept: 'application/json' } })
        .then((reponse) => reponse.json())
        .then((etat) => {
          if (!etat.results.length) {
            conteneur.innerHTML = vide;
            return;
          }

          conteneur.replaceChildren(...etat.results.map(ligne));
        })
        .catch(() => {});
    }

    actualiser();
    setInterval(actualiser, 3000);
  })();
</script>
@endsection
