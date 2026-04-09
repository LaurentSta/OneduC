@php
  $messages = collect($messages ?? [])
    ->filter(fn ($message) => filled($message))
    ->unique()
    ->values();

  $clientBoxId = $clientBoxId ?? null;
@endphp

@if($messages->isNotEmpty())
  <div class="mb-6 rounded-[18px] border border-red-200 bg-red-50 px-5 py-4 text-red-800 shadow-sm" role="alert" aria-live="polite">
    <div class="flex items-start gap-3">
      <span class="mt-0.5 inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-red-100 text-red-700">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v4m0 4h.01M10.29 3.86l-7.5 13A1 1 0 003.66 18h16.68a1 1 0 00.87-1.5l-7.5-13a1 1 0 00-1.74 0z" />
        </svg>
      </span>
      <div class="min-w-0">
        <p class="text-sm font-bold">Certaines informations doivent être corrigées avant de continuer.</p>
        <ul class="mt-2 list-disc space-y-1 pl-5 text-sm">
          @foreach($messages as $message)
            <li>{{ $message }}</li>
          @endforeach
        </ul>
      </div>
    </div>
  </div>
@endif

@if($clientBoxId)
  <div id="{{ $clientBoxId }}" class="mb-6 hidden rounded-[18px] border border-red-200 bg-red-50 px-5 py-4 text-red-800 shadow-sm" role="alert" aria-live="polite">
    <div class="flex items-start gap-3">
      <span class="mt-0.5 inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-red-100 text-red-700">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v4m0 4h.01M10.29 3.86l-7.5 13A1 1 0 003.66 18h16.68a1 1 0 00.87-1.5l-7.5-13a1 1 0 00-1.74 0z" />
        </svg>
      </span>
      <div class="min-w-0">
        <p class="text-sm font-bold">Certaines informations doivent être corrigées avant de continuer.</p>
        <ul class="mt-2 list-disc space-y-1 pl-5 text-sm" data-role="messages"></ul>
      </div>
    </div>
  </div>
@endif
