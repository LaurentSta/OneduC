<x-oneduc.outil-tile
  tool-id="memoire"
  title="Jeu de mémoire"
  icon-bg="bg-bleuone"
  :badge-count="$sessionsMemoireRecentes->count()"
  :categories="['animation']"
  :modalites="['presentiel', 'distanciel']"
  :temporalite="['asynchrone']"
  :contexte="['libre']"
  cta-route="{{ route('formateur.memoire.index') }}"
  cta-label="Gérer les jeux de mémoire"
  cta-bg="bg-bleuone hover:bg-bleuone-light"
>
  <x-slot:icon>
    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18M3 9v10a2 2 0 002 2h4m10-12v10a2 2 0 01-2 2h-4"/>
    </svg>
  </x-slot:icon>
  <x-slot:description>
    Créez des paires terme-définition ou image-concept. Les stagiaires retournent les cartes et leur classement remonte en direct.
  </x-slot:description>
  <x-slot:badges>
    <span class="rounded-full bg-green-100 px-2.5 py-0.5 font-semibold text-green-700">Présentiel</span>
    <span class="rounded-full bg-blue-100 px-2.5 py-0.5 font-semibold text-blue-700">Distanciel</span>
    <span class="rounded-full bg-gray-100 px-2.5 py-0.5 font-semibold text-gray-600">Autonomie</span>
  </x-slot:badges>
  @if($sessionsMemoireRecentes->isNotEmpty())
    <x-slot:body>
      <p class="mb-2 text-[10px] font-bold uppercase tracking-wider text-gray-400">Parties récentes</p>
      <div class="space-y-2">
        @foreach($sessionsMemoireRecentes as $session)
          <div class="flex items-center justify-between gap-2">
            <div class="min-w-0">
              <p class="truncate text-xs font-semibold text-gray-800">{{ $session->title }}</p>
              <p class="truncate text-[10px] text-gray-400">{{ $session->group_name }} · {{ $session->attempts_count }} participation(s)</p>
            </div>
            <a href="{{ route('formateur.memoire.show', $session->id) }}"
               class="shrink-0 rounded-[6px] bg-blue-100 px-2 py-1 text-[10px] font-bold text-bleuone transition hover:bg-blue-200">
              Ouvrir
            </a>
          </div>
        @endforeach
      </div>
    </x-slot:body>
  @endif
</x-oneduc.outil-tile>
