@extends('stagiaire.master')

@section('content')
<div class="max-w-[700px] mx-auto px-6 py-8">

  <nav class="text-sm font-varela text-gray-500 mb-4">
    <ol class="inline-flex items-center space-x-1">
      <li>
        <a href="{{ route('stagiaire.dashboard') }}" class="text-orangeone hover:underline flex items-center">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9.75L12 3l9 6.75V19a2 2 0 01-2 2h-4a1 1 0 01-1-1v-5H10v5a1 1 0 01-1 1H5a2 2 0 01-2-2V9.75z"/>
          </svg>
        </a>
      </li>
      <li><span class="mx-2 text-gray-400">/</span></li>
      <li><a href="{{ route('stagiaire.modules') }}" class="text-orangeone hover:underline">Mes formations</a></li>
      <li><span class="mx-2 text-gray-400">/</span></li>
      <li class="text-gray-400">Activité</li>
    </ol>
  </nav>

  <div class="bg-white rounded-[20px] shadow-md px-8 py-12 text-center">
    <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-500">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
    </div>
    <p class="font-raleway text-xl text-bleuone">{{ $item->configuration['titre'] ?? 'Activité' }}</p>
    <p class="text-sm text-gray-500 mt-2">
      Cette activité n'a pas encore été lancée par votre formateur. Cette page se met à jour automatiquement.
    </p>
  </div>

</div>

<script>
  setTimeout(() => window.location.reload(), 5000);
</script>
@endsection
