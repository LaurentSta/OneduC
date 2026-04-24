@extends('formateur.dashboard')

@section('formateur')
<div class="w-full px-6 lg:px-8">

  <header class="bg-white rounded-[20px] shadow-md px-8 pt-5 pb-6 my-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
      <div>
        <nav class="text-sm font-varela text-gray-500 mb-2">
          <ol class="inline-flex items-center space-x-1">
            <li>
              <a href="{{ route('formateur.outils.index') }}" class="text-orangeone hover:underline">Outils numériques</a>
            </li>
            <li><span class="mx-2 text-gray-400">/</span></li>
            <li class="text-gray-400">Page collaborative</li>
          </ol>
        </nav>
        <p class="font-raleway text-2xl text-bleuone">Page collaborative (HedgeDoc)</p>
        <p class="text-sm text-gray-500 mt-1">
          Créez et animez des documents Markdown collaboratifs en temps réel, comme Digipage.
        </p>
      </div>
    </div>
  </header>

  @if(blank($baseUrl))
    <div class="rounded-[16px] border border-amber-200 bg-amber-50 px-6 py-5 text-amber-800 mb-6">
      <p class="font-bold text-sm mb-2">Configuration requise</p>
      <p class="text-sm">Ajoutez l'URL de votre instance HedgeDoc dans votre fichier d'environnement :</p>
      <pre class="mt-3 rounded-lg bg-white border border-amber-100 p-3 text-xs text-gray-700">HEDGEDOC_BASE_URL=https://pad.votre-domaine.fr
HEDGEDOC_NEW_PATH=/new</pre>
      <p class="text-xs mt-2 text-amber-700">Puis rechargez la configuration Laravel si nécessaire.</p>
    </div>
  @endif

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <div class="lg:col-span-1">
      <div class="bg-white rounded-[20px] shadow-md p-6 sticky top-6">
        <p class="font-varela text-base font-bold text-bleuone mb-3">Actions rapides</p>
        <div class="space-y-3">
          @if(!blank($newUrl))
            <a href="{{ $newUrl }}" target="_blank" rel="noopener"
               class="w-full inline-flex items-center justify-center gap-2 rounded-[10px] bg-cyan-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-cyan-700 transition">
              Créer une nouvelle page
            </a>
          @endif
          @if(!blank($baseUrl))
            <a href="{{ $baseUrl }}" target="_blank" rel="noopener"
               class="w-full inline-flex items-center justify-center gap-2 rounded-[10px] border border-gray-300 bg-white px-4 py-2.5 text-sm font-bold text-gray-700 hover:bg-gray-50 transition">
              Ouvrir HedgeDoc
            </a>
          @endif
        </div>
      </div>
    </div>

    <div class="lg:col-span-2">
      <div class="bg-white rounded-[20px] shadow-md p-6 space-y-4">
        <p class="text-sm font-bold text-bleuone">Workflow conseillé</p>
        <ol class="list-decimal ml-5 text-sm text-gray-700 space-y-2">
          <li>Cliquez sur <strong>Créer une nouvelle page</strong>.</li>
          <li>Récupérez immédiatement l'URL d'édition (barre d'adresse).</li>
          <li>Partagez l'URL aux stagiaires pour l'écriture collaborative.</li>
          <li>Utilisez le lien publié/lecture seule pour la diffusion finale.</li>
        </ol>
      </div>
    </div>
  </div>
</div>
@endsection
