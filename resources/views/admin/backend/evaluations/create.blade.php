@extends('admin.admin_dashboard')
@section('admin')

<div class="max-w-[768px] mx-auto px-4">
    <div class="bg-white rounded-[20px] shadow-md p-8 my-10 w-full">

        <h2 class="text-2xl font-semibold text-gray-800 mb-4">Ajouter une évaluation finale</h2>

        <form method="POST" action="{{ route('admin.evaluations.store') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <!-- Titre de l'évaluation -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1" for="titre">Titre de l’évaluation</label>
                <input type="text" name="titre" id="titre"
                       class="w-full rounded-md border-gray-300 shadow-sm focus:ring-orangeone focus:border-orangeone"
                       required>
            </div>

            <!-- Fichier SCORM -->
           <!-- Fichier SCORM (chemin manuel) -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1" for="scorm_path">Dossier SCORM</label>
                <input type="text" name="scorm_path" id="scorm_path"
                    placeholder="ex: 01_branchement_v1.3"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:ring-orangeone focus:border-orangeone"
                    required>
                <p class="text-sm text-gray-500 mt-1">
                    Indique simplement le nom du dossier contenant le SCORM, placé dans <code>public/modules/evaluations/scorm/</code>.
                </p>
            </div>


            <!-- Boutons -->
            <div class="flex justify-end gap-4">
                <a href="{{ route('admin.evaluations.index') }}"
                   class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded hover:bg-gray-100">
                    Annuler
                </a>
                <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-orangeone text-white text-sm font-medium rounded hover:bg-orange-600">
                    <i class="ti ti-check mr-2"></i> Enregistrer
                </button>
            </div>

        </form>

    </div>
</div>

@endsection
