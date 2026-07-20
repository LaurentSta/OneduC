@php
  $documentsAccessibilite = [
      'declaration' => ['label' => 'Déclaration d’accessibilité', 'route' => 'accessibilite'],
      'schema' => ['label' => 'Schéma pluriannuel 2026-2028', 'route' => 'accessibilite.schema'],
      'plan' => ['label' => 'Plan d’action 2026', 'route' => 'accessibilite.plan-2026'],
  ];
@endphp

<nav aria-label="Documents relatifs à l’accessibilité" class="rounded-[20px] border border-slate-200 bg-white p-4">
  <ul class="flex flex-col gap-2 sm:flex-row sm:flex-wrap">
    @foreach($documentsAccessibilite as $cle => $document)
      <li>
        <a
          href="{{ route($document['route']) }}"
          @if($actif === $cle) aria-current="page" @endif
          class="inline-flex rounded-lg px-4 py-3 font-varela text-sm font-semibold underline-offset-4 focus:outline-none focus:ring-2 focus:ring-bleuone focus:ring-offset-2 {{ $actif === $cle ? 'bg-bleuone text-white' : 'text-bleuone underline hover:bg-slate-100' }}"
        >
          {{ $document['label'] }}
        </a>
      </li>
    @endforeach
  </ul>
</nav>
