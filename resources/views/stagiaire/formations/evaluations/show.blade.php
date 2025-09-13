@extends('stagiaire.formations.evaluations.master_lecon_evaluation')
@section('title','Évaluation SCORM')

@section('content')
@php
  $folder = trim((string)($evaluation->scorm_path ?? ''), '/');                 // ex: Branchement_Evaluation_v1.2
  $rel    = $folder !== '' ? "modules/scorm/01_evaluations/{$folder}/res/index.html" : null;
  $abs    = $rel ? public_path($rel) : null;
  $exists = $abs ? file_exists($abs) : false;
@endphp

<main class="flex-1 bg-white">
  @if($rel && $exists)
    <script>
      window.SCORM_CONTEXT = {
        user_id: {{ auth()->id() }},
        evaluation_id: {{ $evaluation->id }},
        post_url: '{{ url('/scorm/evaluation-progress') }}',
        goToFinEvaluation: function(){ history.back(); }
      };
    </script>

    <iframe
      title="Évaluation finale"
      src="{{ asset($rel) }}"
      frameborder="0" allowfullscreen class="w-full"
      style="height: calc(100vh - 64px); display:block;">
    </iframe>
    <script>
      (function () {
        const FIN_URL = "{{ route('stagiaire.evaluations.fin', $evaluation->id) }}";
        const go = () => { window.location.href = FIN_URL; };

        // Contexte partagé pour l’iframe iSpring
        window.SCORM_CONTEXT = {
          user_id: {{ auth()->id() }},
          evaluation_id: {{ $evaluation->id }},
          post_url: '{{ url('/scorm/evaluation-progress') }}',
          goToFinEvaluation: go,           // nom que tu as dans iSpring
          // alias tolérés si jamais tu changes le nom dans iSpring
          finEvaluation: go,
          finirEvaluation: go,
          endEvaluation: go,
          goToEnd: go,
        };

        // Sécurité : expose aussi une fonction globale si l’iframe l’appelle sans SCORM_CONTEXT
        window.goToFinEvaluation = go;

        // Optionnel : support postMessage depuis l’iframe
        window.addEventListener('message', (e) => {
          if (!e || !e.data) return;
          if (e.data === 'goToFinEvaluation') go();
        });
      })();
    </script>

    <script src="{{ asset('scorm_core/js/API_evaluation.js') }}"></script>
  @else
    <div class="p-8 space-y-2">
      <p class="text-gray-700 font-medium">Aucune ressource SCORM configurée.</p>
      @if(config('app.debug'))
        <p class="text-xs text-gray-500">scorm_path: <code>{{ $evaluation->scorm_path ?? 'null' }}</code></p>
        <p class="text-xs text-gray-500">URL attendue: <code>/{{ $rel ?? '—' }}</code></p>
        <p class="text-xs text-gray-500">Fichier présent: <strong>{{ $exists ? 'oui' : 'non' }}</strong></p>
      @endif
    </div>
  @endif
</main>
@endsection
