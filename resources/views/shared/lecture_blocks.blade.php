{{-- resources/views/shared/lecture_blocks.blade.php --}}
@php $blocks = collect($blocks ?? []); @endphp

@foreach($blocks as $block)
  @switch($block['type'] ?? null)
    @case('text')
      <div class="prose prose-slate max-w-none mb-6">
        {!! $block['html'] ?? '' !!}
      </div>
      @break

    @case('image')
      <figure class="mb-6">
        <img src="{{ route('media.storage', ['path' => $block['path']]) }}"
             alt="{{ $block['caption'] ?? '' }}"
             class="w-full rounded-xl border border-gray-200">
        @if(!empty($block['caption']))
          <figcaption class="mt-2 text-center text-sm text-gray-500">{{ $block['caption'] }}</figcaption>
        @endif
      </figure>
      @break

    @case('list')
      @if(($block['style'] ?? 'bullet') === 'numbered')
        <ol class="mb-6 list-decimal pl-6 space-y-1 text-gray-700">
          @foreach($block['items'] ?? [] as $item)
            <li>{{ $item }}</li>
          @endforeach
        </ol>
      @else
        <ul class="mb-6 list-disc pl-6 space-y-1 text-gray-700">
          @foreach($block['items'] ?? [] as $item)
            <li>{{ $item }}</li>
          @endforeach
        </ul>
      @endif
      @break

    @case('quote')
      <blockquote class="mb-6 border-l-4 border-orangeone bg-orange-50/60 px-5 py-4 italic text-gray-700 rounded-r-xl">
        <p>&laquo;&nbsp;{{ $block['text'] ?? '' }}&nbsp;&raquo;</p>
        @if(!empty($block['source']))
          <cite class="mt-2 block not-italic text-sm font-semibold text-gray-500">&mdash; {{ $block['source'] }}</cite>
        @endif
      </blockquote>
      @break

    @case('divider')
      <hr class="my-8 border-gray-200">
      @break
  @endswitch
@endforeach
