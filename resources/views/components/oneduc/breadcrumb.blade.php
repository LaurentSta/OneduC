@props(['items'])

<nav class="text-xs text-gray-500 mb-4" aria-label="breadcrumb">
    <ol class="list-none flex flex-wrap">
        @foreach($items as $i => $item)
            <li>
                @if(isset($item['url']))
                    <a href="{{ $item['url'] }}" class="text-orangeone hover:underline">{{ $item['label'] }}</a>
                @else
                    <span class="font-semibold">{{ $item['label'] }}</span>
                @endif
                @if($i < count($items) - 1)
                    <span class="mx-2">›</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
