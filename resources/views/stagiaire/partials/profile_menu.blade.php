@php
  $profileMenuItems = [
    [
      'label' => 'Profil',
      'route' => 'stagiaire.profile',
      'active' => request()->routeIs('stagiaire.profile'),
      'icon' => 'profile',
    ],
    [
      'label' => 'Préférences',
      'route' => 'stagiaire.parametre',
      'active' => request()->routeIs('stagiaire.parametre'),
      'icon' => 'settings',
    ],
    [
      'label' => 'Sécurité',
      'route' => 'stagiaire.securite.show',
      'active' => request()->routeIs('stagiaire.securite') || request()->routeIs('stagiaire.securite.show'),
      'icon' => 'security',
    ],
  ];
@endphp

<aside class="bg-white rounded-[20px] shadow-md p-6 h-fit" aria-label="Navigation Mon Espace">
  <h3 class="text-lg font-semibold text-[#004461] mb-4">Mon Espace</h3>
  <ul class="space-y-3">
    @foreach ($profileMenuItems as $item)
      @php
        $isActive = $item['active'];
      @endphp
      <li>
        <a
          href="{{ route($item['route']) }}"
          class="group flex items-center gap-3 rounded-[18px] border px-4 py-3 transition {{ $isActive ? 'border-orangeone/20 bg-orangeone/10 text-orangeone shadow-sm' : 'border-slate-200 bg-white text-slate-600 hover:border-bleuone/20 hover:bg-bleuone/5 hover:text-bleuone' }}"
          @if($isActive) aria-current="page" @endif
        >
          <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full transition {{ $isActive ? 'bg-orangeone text-white' : 'bg-bleuone/10 text-bleuone group-hover:bg-bleuone group-hover:text-white' }}">
            @if($item['icon'] === 'profile')
              <x-icons.profile-iconify class="h-5 w-5" />
            @elseif($item['icon'] === 'settings')
              <x-icons.settings-iconify class="h-5 w-5" />
            @else
              <x-icons.security-iconify class="h-5 w-5" />
            @endif
          </span>

          <span class="font-varela text-sm {{ $isActive ? 'font-semibold' : 'font-medium' }}">
            {{ $item['label'] }}
          </span>
        </a>
      </li>
    @endforeach
  </ul>
</aside>
