<x-oneduc.outil-tile
  tool-id="carrousel"
  title="Carrousel"
  icon-bg="bg-bleuone"
  :badge-count="$sessionsCarrouselRecentes->count()"
  :categories="['animation']"
  :modalites="['presentiel', 'distanciel']"
  :temporalite="['asynchrone']"
  :contexte="['libre']"
  cta-route="{{ route('formateur.carrousel.index') }}"
  cta-label="Gérer les carrousels"
  cta-bg="bg-bleuone hover:bg-bleuone-hover"
>
  <x-slot:icon>
    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6h16M4 6a2 2 0 00-2 2v8a2 2 0 002 2h16a2 2 0 002-2V8a2 2 0 00-2-2M4 6l4 4m12-4l-4 4m-4-2v8"/>
    </svg>
  </x-slot:icon>
  <x-slot:description>
    Présentez une succession de slides (texte et image) que chaque stagiaire parcourt librement sur son appareil.
  </x-slot:description>
  <x-slot:badges>
    <span class="rounded-full bg-green-100 px-2.5 py-0.5 font-semibold text-green-700">Présentiel</span>
    <span class="rounded-full bg-blue-100 px-2.5 py-0.5 font-semibold text-blue-700">Distanciel</span>
    <span class="rounded-full bg-purple-100 px-2.5 py-0.5 font-semibold text-purple-700">Individuel</span>
  </x-slot:badges>
  @if($sessionsCarrouselRecentes->isNotEmpty())
    <x-slot:body>
      <p class="mb-2 text-[10px] font-bold uppercase tracking-wider text-gray-400">Carrousels récents</p>
      <div class="space-y-2">
        @foreach($sessionsCarrouselRecentes as $session)
          <div class="flex items-center justify-between gap-2">
            <div class="min-w-0">
              <p class="truncate text-xs font-semibold text-gray-800">{{ $session->title }}</p>
              <p class="truncate text-[10px] text-gray-400">{{ $session->group_name }} · {{ $session->slides_count }} slide(s)</p>
            </div>
            <a href="{{ route('formateur.carrousel.show', $session->id) }}"
               class="shrink-0 rounded-[6px] bg-blue-100 px-2 py-1 text-[10px] font-bold text-blue-700 transition hover:bg-blue-200">
              Ouvrir
            </a>
          </div>
        @endforeach
      </div>
    </x-slot:body>
  @endif
</x-oneduc.outil-tile>
