<x-oneduc.outil-tile
  tool-id="pendu"
  title="Jeu du pendu"
  icon-bg="bg-orangeone"
  :badge-count="$sessionsPenduRecentes->count()"
  :categories="['animation']"
  :modalites="['presentiel', 'distanciel']"
  :temporalite="['synchrone']"
  :contexte="['libre']"
  cta-route="{{ route('formateur.pendu.index') }}"
  cta-label="Gérer les pendus"
  cta-bg="bg-orangeone hover:bg-orangeone-hover"
>
  <x-slot:icon>
    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 21h14M7 21V3h9m0 0v4m0-4h3"/>
      <circle cx="16" cy="10" r="2" stroke-width="1.8"/>
      <path stroke-linecap="round" stroke-width="1.8" d="M16 12v4m0-2-2 2m2-2 2 2m-2 0-2 3m2-3 2 3"/>
    </svg>
  </x-slot:icon>
  <x-slot:description>
    Faites deviner un mot ou une expression au groupe. Chaque stagiaire propose une lettre et la partie se met à jour pour tous les participants.
  </x-slot:description>
  <x-slot:badges>
    <span class="rounded-full bg-green-100 px-2.5 py-0.5 font-semibold text-green-700">Présentiel</span>
    <span class="rounded-full bg-blue-100 px-2.5 py-0.5 font-semibold text-blue-700">Distanciel</span>
    <span class="rounded-full bg-orange-100 px-2.5 py-0.5 font-semibold text-orange-700">Collectif</span>
  </x-slot:badges>
  @if($sessionsPenduRecentes->isNotEmpty())
    <x-slot:body>
      <p class="mb-2 text-[10px] font-bold uppercase tracking-wider text-gray-400">Parties récentes</p>
      <div class="space-y-2">
        @foreach($sessionsPenduRecentes as $session)
          <div class="flex items-center justify-between gap-2">
            <div class="min-w-0">
              <p class="truncate text-xs font-semibold text-gray-800">{{ $session->title }}</p>
              <p class="truncate text-[10px] text-gray-400">{{ $session->group_name }} · {{ $session->guesses_count }} proposition(s)</p>
            </div>
            <a href="{{ route('formateur.pendu.show', $session->id) }}"
               class="shrink-0 rounded-[6px] bg-orange-100 px-2 py-1 text-[10px] font-bold text-orange-700 transition hover:bg-orange-200">
              Ouvrir
            </a>
          </div>
        @endforeach
      </div>
    </x-slot:body>
  @endif
</x-oneduc.outil-tile>
