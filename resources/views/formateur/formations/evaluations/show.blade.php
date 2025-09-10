@extends('formateur.dashboard')
@section('formateur')
<div class="max-w-[1285px] mx-auto px-8">
  <div class="bg-white rounded-[20px] shadow-md p-6">
    <h1 class="text-xl font-semibold mb-4">{{ $evaluation->titre ?? 'Évaluation' }}</h1>
    @if(isset($evaluation) && $evaluation->scorm_path)
      <script>
        window.SCORM_CONTEXT = {
          user_id: {{ auth()->id() }},
          evaluation_id: {{ $evaluation->id }},
          post_url: '{{ url('/scorm/evaluation-progress') }}',
          goToFinEvaluation: function(){ history.back(); }
        };
      </script>
      <iframe
        title="Évaluation finale (aperçu formateur)"
        src="{{ asset('modules/scorm/01_evaluations/'.$evaluation->scorm_path.'/res/index.html') }}"
        frameborder="0" allowfullscreen class="w-full"
        style="height: 75vh; display: block;">
      </iframe>
      <script src="{{ asset('scorm_core/js/API_evaluation.js') }}"></script>
    @else
      <p class="text-gray-700">Aucune ressource SCORM configurée.</p>
    @endif
  </div>
</div>
@endsection
