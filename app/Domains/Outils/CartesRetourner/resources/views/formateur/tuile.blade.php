<x-oneduc.outil-tile
  x-show="filtre === 'all' || filtre === 'animation'"
  tool-id="cartes-retourner"
  title="Cartes à retourner"
  icon-bg="bg-purple-600"
  :badge-count="$sessionsCartesRetournerRecentes->count()"
  cta-route="{{ route('formateur.cartes-retourner.index') }}"
  cta-label="Gérer les cartes"
  cta-bg="bg-purple-600 hover:bg-purple-700"
>
  <x-slot:icon>
    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 4h16v16H4V4z"/>
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 4v16m6-16v16" opacity="0.4"/>
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 9l3 3-3 3m10-6l-3 3 3 3"/>
    </svg>
  </x-slot:icon>
  <x-slot:description>
    Créez des cartes recto/verso (texte et image) : chaque stagiaire clique pour retourner la carte et découvrir la réponse.
  </x-slot:description>
  <x-slot:badges>
    <span class="rounded-full bg-green-100 px-2.5 py-0.5 font-semibold text-green-700">Présentiel</span>
    <span class="rounded-full bg-blue-100 px-2.5 py-0.5 font-semibold text-blue-700">Distanciel</span>
    <span class="rounded-full bg-purple-100 px-2.5 py-0.5 font-semibold text-purple-700">Individuel</span>
  </x-slot:badges>
  @if($sessionsCartesRetournerRecentes->isNotEmpty())
    <x-slot:body>
      <p class="mb-2 text-[10px] font-bold uppercase tracking-wider text-gray-400">Activités récentes</p>
      <div class="space-y-2">
        @foreach($sessionsCartesRetournerRecentes as $session)
          <div class="flex items-center justify-between gap-2">
            <div class="min-w-0">
              <p class="truncate text-xs font-semibold text-gray-800">{{ $session->title }}</p>
              <p class="truncate text-[10px] text-gray-400">{{ $session->group_name }} · {{ $session->cards_count }} carte(s)</p>
            </div>
            <a href="{{ route('formateur.cartes-retourner.show', $session->id) }}"
               class="shrink-0 rounded-[6px] bg-purple-100 px-2 py-1 text-[10px] font-bold text-purple-700 transition hover:bg-purple-200">
              Ouvrir
            </a>
          </div>
        @endforeach
      </div>
    </x-slot:body>
  @endif
</x-oneduc.outil-tile>
