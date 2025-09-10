@extends('stagiaire.formations.evaluations.master_lecon_evaluation')

@section('title', 'Évaluation SCORM')

@section('content')
@php
  /** Variables attendues :
   * - $module (Module) pour le sidebar
   * - $evaluation (Evaluation) contenant ->id et ->scorm_path
   */
@endphp

<meta name="csrf-token" content="{{ csrf_token() }}">

<main class="flex-1 bg-white">
  @if(isset($evaluation) && $evaluation->scorm_path)
    {{-- Contexte SCORM ÉVALUATION --}}
    <script>
      window.SCORM_CONTEXT = {
        user_id: {{ auth()->id() }},
        evaluation_id: {{ $evaluation->id }},
        post_url: '{{ url('/scorm/evaluation-progress') }}',

        // appelé par le bouton du paquet :
        // javascript:window.parent.SCORM_CONTEXT?.goToFinEvaluation?.();
        goToFinEvaluation: function () {
          // Redirige vers la fin du module si disponible, sinon retour module
          @if(isset($module))
            window.location.href = "{{ route('stagiaire.module.fin', ['module'=>$module->id]) }}";
          @else
            window.location.href = "{{ url()->previous() }}";
          @endif
        }
      };
    </script>

    <iframe
      title="Évaluation finale"
      src="{{ asset('modules/scorm/01_evaluations/' . $evaluation->scorm_path . '/res/index.html') }}"
      frameborder="0"
      allowfullscreen
      class="w-full"
      style="height: calc(100vh - 64px); display: block;">
    </iframe>

    {{-- Charge l'API front d'évaluation si le paquet ne l'inclut pas déjà --}}
    <script src="{{ asset('scorm_core/js/API_evaluation.js') }}"></script>
  @else
    <div class="p-8">
      <p class="text-gray-700">Aucune ressource SCORM d’évaluation n’est configurée.</p>
    </div>
  @endif
</main>
@endsection
