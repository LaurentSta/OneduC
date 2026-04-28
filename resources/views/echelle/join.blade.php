<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Rejoindre une échelle — Oneduc</title>
  @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-slate-100 flex items-center justify-center p-4">

  <div class="w-full max-w-sm">
    <div class="rounded-2xl bg-white shadow-lg p-8">
      <div class="text-center mb-6">
        <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-bleuone/10 mb-3">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-bleuone" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M3 6h18M3 12h18M3 18h18"/>
          </svg>
        </div>
        <h1 class="text-xl font-bold text-bleuone font-raleway">Échelle de positionnement</h1>
        <p class="text-sm text-gray-500 mt-1">Entrez le code fourni par votre formateur</p>
      </div>

      @if($errors->any())
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
          {{ $errors->first() }}
        </div>
      @endif

      <form method="POST" action="{{ route('echelle.resolve') }}" class="space-y-4">
        @csrf
        <input type="text" name="code" required maxlength="12" autofocus autocomplete="off"
               placeholder="Ex. : AB3K7Z"
               class="w-full rounded-xl border border-gray-200 px-4 py-3 text-center text-xl font-mono font-bold uppercase tracking-widest focus:border-bleuone focus:outline-none focus:ring-2 focus:ring-bleuone/20">
        <button type="submit"
                class="w-full rounded-xl bg-bleuone px-5 py-3 text-sm font-bold text-white hover:bg-bleuone-light transition">
          Rejoindre
        </button>
      </form>
    </div>
  </div>

</body>
</html>
