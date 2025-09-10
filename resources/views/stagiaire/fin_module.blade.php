@extends('stagiaire.master')

@section('content')
<div class="max-w-[1285px] mx-auto px-8">
  <header class="bg-white rounded-[20px] shadow-md px-8 pt-4 pb-6 w-full mb-6">
    <p class="font-raleway text-titre text-bleuone leading-tight mb-2">Félicitations</p>
    <p class="font-varela text-sous-titre text-orangeone">
      Module terminé : {{ $module->module_name }}
    </p>
  </header>

  <section class="bg-white rounded-[20px] shadow-md p-6 grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
    {{-- Colonne gauche : Résumé chiffres --}}
    <div>
      <h2 class="text-lg font-semibold text-bleuone mb-4">Résumé</h2>
      <ul class="space-y-2 text-gray-800">
        <li>Leçons : <strong>{{ $totalLectures }} sur {{ $totalLectures }}</strong></li>  
        <li>Sections : <strong>{{ $totalSections }} sur {{ $totalSections }}</strong></li>
        <li>Questions : <strong>{{ $questionsAnswered }} sur {{ $totalQuestionsPlanned }}</strong></li>
      </ul>

      <div class="mt-6">
        <a href="{{ route('stagiaire.modules') }}" 
           class="inline-block px-4 py-2 rounded-lg bg-bleuone text-white">
          Retour aux modules
        </a>
      </div>
    </div>

    {{-- Colonne droite : Image --}}
    <div class="flex justify-center">
      <img src="{{ asset('images/svg/Finish.svg') }}" 
           alt="Félicitations" 
           class="max-w-[300px] w-full h-auto">
    </div>
  </section>
</div>
@endsection
