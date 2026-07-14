<x-oneduc.outil-tile
  x-show="filtre === 'all' || filtre === 'animation'"
  tool-id="tri-cartes"
  title="Cartes à trier"
  icon-bg="bg-emerald-600"
  :badge-count="$sessionsTriCartesRecentes->count()"
  cta-route="{{ route('formateur.tri-cartes.index') }}"
  cta-label="Gérer les tris"
  cta-bg="bg-emerald-600 hover:bg-emerald-700"
>
  <x-slot:icon>
    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/>
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 13l2 2 4-4"/>
    </svg>
  </x-slot:icon>
  <x-slot:description>
    Définissez des catégories et des cartes (texte et image) : chaque stagiaire glisse-dépose les cartes et obtient un score corrigé automatiquement.
  </x-slot:description>
  <x-slot:badges>
    <span class="rounded-full bg-green-100 px-2.5 py-0.5 font-semibold text-green-700">Présentiel</span>
    <span class="rounded-full bg-blue-100 px-2.5 py-0.5 font-semibold text-blue-700">Distanciel</span>
    <span class="rounded-full bg-orange-100 px-2.5 py-0.5 font-semibold text-orange-700">Évaluation</span>
  </x-slot:badges>
  @if($sessionsTriCartesRecentes->isNotEmpty())
    <x-slot:body>
      <p class="mb-2 text-[10px] font-bold uppercase tracking-wider text-gray-400">Activités récentes</p>
      <div class="space-y-2">
        @foreach($sessionsTriCartesRecentes as $session)
          <div class="flex items-center justify-between gap-2">
            <div class="min-w-0">
              <p class="truncate text-xs font-semibold text-gray-800">{{ $session->title }}</p>
              <p class="truncate text-[10px] text-gray-400">{{ $session->group_name }} · {{ $session->attempts_count }} réponse(s)</p>
            </div>
            <a href="{{ route('formateur.tri-cartes.show', $session->id) }}"
               class="shrink-0 rounded-[6px] bg-emerald-100 px-2 py-1 text-[10px] font-bold text-emerald-700 transition hover:bg-emerald-200">
              Ouvrir
            </a>
          </div>
        @endforeach
      </div>
    </x-slot:body>
  @endif
</x-oneduc.outil-tile>
