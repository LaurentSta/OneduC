@extends('formateur.dashboard')

@section('formateur')
<div class="max-w-[1248px] mx-auto px-4 py-10">
  <div class="bg-white rounded-[20px] shadow-md p-8 w-full border border-gray-100">

    {{-- En-tête --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8 border-b pb-6">
      <div>
        <h1 class="text-2xl font-raleway text-bleuone font-bold">Nouvelle Question pédagogique</h1>
        <p class="text-sm text-gray-500 mt-1">
          Module : <span class="font-semibold text-gray-800">{{ $lecture->lecture_title }}</span>
        </p>
      </div>
      <a href="{{ route('formateur.modules.builder.quiz-questions.index', ['module' => $lecture->module_id, 'lecture' => $lecture->id]) }}"
         class="text-bleuone hover:underline text-sm font-medium">
        &larr; Retour à la banque de questions
      </a>
    </div>

    @include('formateur.modules-builder.quiz-questions._form', ['lecture' => $lecture])
  </div>
</div>
@endsection
