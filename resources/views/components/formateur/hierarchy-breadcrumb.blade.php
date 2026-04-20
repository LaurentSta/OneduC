{{-- Fil d'ariane hiérarchique formateur : Module → Chapitre → Leçon → Activité --}}
@props([
    'group'    => null,  // ['label' => string, 'title' => string, 'url' => string|null]
    'module'   => null,  // ['label' => string, 'title' => string, 'url' => string|null]
    'chapter'  => null,  // ['label' => string, 'title' => string, 'url' => string|null]
    'lesson'   => null,  // ['label' => string, 'title' => string, 'url' => string|null]
    'activity' => null,  // ['label' => string, 'title' => string, 'url' => string|null]
])

@php
    $items = [];
    if ($group)    $items[] = array_merge(['type' => 'group'],    $group);
    if ($module)   $items[] = array_merge(['type' => 'module'],   $module);
    if ($chapter)  $items[] = array_merge(['type' => 'chapter'],  $chapter);
    if ($lesson)   $items[] = array_merge(['type' => 'lesson'],   $lesson);
    if ($activity) $items[] = array_merge(['type' => 'activity'], $activity);

    $badgeStyles = [
        'group'    => 'bg-violet-100 text-violet-700',
        'module'   => 'bg-orange-100 text-[#f97316]',
        'chapter'  => 'bg-blue-100 text-[#004461]',
        'lesson'   => 'bg-emerald-100 text-emerald-700',
        'activity' => 'bg-indigo-100 text-indigo-700',
    ];

    $dotStyles = [
        'group'    => 'bg-violet-400',
        'module'   => 'bg-[#f97316]',
        'chapter'  => 'bg-[#004461]',
        'lesson'   => 'bg-emerald-500',
        'activity' => 'bg-indigo-500',
    ];

    $lastIndex = count($items) - 1;
@endphp

@if(count($items) > 0)
<nav
    class="flex flex-wrap items-center gap-x-1.5 gap-y-2 rounded-xl border border-gray-100 bg-white px-4 py-2.5 shadow-sm"
    aria-label="Position dans la hiérarchie"
>
    @foreach($items as $i => $item)
        @php
            $isCurrent = ($i === $lastIndex);
            $badgeClass = $badgeStyles[$item['type']] ?? $badgeStyles['module'];
            $dotClass   = $dotStyles[$item['type']] ?? $dotStyles['module'];
            $hasUrl     = !$isCurrent && !empty($item['url']);
        @endphp

        @if($i > 0)
            <svg class="h-3.5 w-3.5 shrink-0 text-gray-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                <path d="M9 18l6-6-6-6" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        @endif

        <div class="flex min-w-0 items-center gap-2">
            {{-- Badge de niveau --}}
            <span class="shrink-0 rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider {{ $badgeClass }}">
                {{ $item['label'] }}
            </span>

            {{-- Titre : lien si parent, texte si courant --}}
            @if($hasUrl)
                <a
                    href="{{ $item['url'] }}"
                    class="min-w-0 truncate text-sm font-medium text-gray-500 hover:text-[#004461] hover:underline"
                    title="{{ $item['title'] }}"
                >{{ $item['title'] }}</a>
            @else
                <span
                    class="min-w-0 truncate text-sm font-semibold {{ $isCurrent ? 'text-gray-900' : 'text-gray-500' }}"
                    title="{{ $item['title'] }}"
                >{{ $item['title'] }}</span>
            @endif
        </div>
    @endforeach
</nav>
@endif
