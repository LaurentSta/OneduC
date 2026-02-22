@extends('admin.admin_dashboard')
@section('admin')

<div class="max-w-[800px] mx-auto px-4">
    <div class="bg-white rounded-[20px] shadow-md p-8 my-10 w-full">
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">Modifier l'évaluation</h2>

        <form action="{{ route('admin.evaluations.update', $evaluation) }}" method="POST" class="space-y-6">
    @csrf
    @method('PUT')

            <!-- Titre -->
            <div>
                <label for="titre" class="block text-sm font-medium text-gray-700">Titre de l’évaluation</label>
                <input type="text" name="titre" id="titre"
                       value="{{ old('titre', $evaluation->titre) }}"
                       class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-orangeone focus:ring focus:ring-orangeone/50"
                       required>
                @error('titre')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Chemin SCORM -->
            <div>
                <label for="scorm_path" class="block text-sm font-medium text-gray-700">Dossier SCORM</label>
                <input type="text" name="scorm_path" id="scorm_path"
                       value="{{ old('scorm_path', $evaluation->scorm_path) }}"
                       placeholder="Ex : Branchement_Evaluation_v1.2"
                       class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-orangeone focus:ring focus:ring-orangeone/50"
                       required>
                <p class="text-sm text-gray-500 mt-1">
                    Le fichier sera chargé depuis :
                    <code>/modules/evaluations/scorm/<strong>scorm_path</strong>/res/index.html</code>
                </p>
                @error('scorm_path')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
@php
  $folder = trim(old('scorm_path',$evaluation->scorm_path));
  $rel = \App\Support\LearningAssetPath::resolveEvaluationIndexRelativePath($folder);
  $exists = $rel ? file_exists(public_path($rel)) : false;
@endphp
@if($rel)
  <p class="text-sm mt-2">
    Chemin visé: <code>/{{ $rel }}</code>
    @if($exists)
      <span class="ml-2 inline-flex items-center px-2 py-0.5 text-xs rounded bg-green-100 text-green-700">Fichier trouvé</span>
    @else
      <span class="ml-2 inline-flex items-center px-2 py-0.5 text-xs rounded bg-red-100 text-red-700">Fichier introuvable</span>
    @endif
  </p>
@endif

            <!-- Actions -->
            <div class="flex justify-end gap-3 pt-4 border-t">
                <a href="{{ route('admin.evaluations.index') }}"
                   class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 rounded hover:bg-gray-100">
                    Annuler
                </a>
                <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-orangeone text-white rounded hover:bg-orange-600">
                    Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>

@endsection
