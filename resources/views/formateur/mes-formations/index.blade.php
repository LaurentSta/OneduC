@extends('formateur.dashboard')

@section('formateur')

<div class="max-w-[1285px] mx-auto px-8">

  @include('formateur.mes-formations._tabs')

  <div class="flex justify-end mb-4">
    <a href="{{ route('formateur.mes-formations.create') }}"
       class="inline-flex items-center gap-2 px-5 py-3 rounded-[10px] bg-[#E94D2A] text-white font-medium hover:bg-[#cf4121] transition">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
      </svg>
      Créer un parcours
    </a>
  </div>

  @if (session('success'))
    <div class="mb-4 px-4 py-3 rounded-[10px] bg-green-50 text-green-800 border border-green-200 text-sm">
      {{ session('success') }}
    </div>
  @endif

  @if ($parcours->isEmpty())
    <div class="bg-white rounded-[20px] shadow-md px-8 py-16 text-center">
      <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
      </svg>
      <p class="text-gray-500 mb-6">Vous n'avez pas encore créé de formation.</p>
      <a href="{{ route('formateur.mes-formations.create') }}"
         class="inline-flex items-center gap-2 px-5 py-3 rounded-[10px] bg-[#E94D2A] text-white font-medium hover:bg-[#cf4121] transition">
        Créer ma première formation
      </a>
    </div>
  @else
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
      @foreach ($parcours as $item)
        <div class="bg-white rounded-[20px] shadow-md flex flex-col overflow-hidden">
          <div class="px-6 pt-5 pb-4 flex-1">
            <h2 class="text-base font-semibold text-gray-900 mb-1 line-clamp-2">{{ $item->title }}</h2>
            @if ($item->description)
              <p class="text-sm text-gray-500 line-clamp-3 mb-3">{{ $item->description }}</p>
            @endif
            <div class="flex items-center gap-2 text-xs text-gray-400">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
              </svg>
              {{ $item->items_count }} étape{{ $item->items_count > 1 ? 's' : '' }}
            </div>
          </div>
          <div class="border-t border-gray-100 px-6 py-3 flex justify-between items-center bg-gray-50">
            <span class="text-xs text-gray-400">{{ $item->created_at->diffForHumans() }}</span>
            <div class="flex gap-2">
              <a href="{{ route('formateur.mes-formations.show', $item) }}"
                 class="text-xs px-3 py-1.5 rounded-lg bg-white border border-gray-200 text-gray-700 hover:border-[#E94D2A] hover:text-[#E94D2A] transition">
                Voir
              </a>
              <a href="{{ route('formateur.mes-formations.edit', $item) }}"
                 class="text-xs px-3 py-1.5 rounded-lg bg-[#E94D2A] text-white hover:bg-[#cf4121] transition">
                Modifier
              </a>
            </div>
          </div>
        </div>
      @endforeach
    </div>

    <div class="mt-8">
      {{ $parcours->links() }}
    </div>
  @endif

</div>

@endsection
