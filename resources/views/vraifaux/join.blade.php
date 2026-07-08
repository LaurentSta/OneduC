<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Oneduc - Vrai ou Faux</title>
  @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-slate-100 flex items-center justify-center p-4">
  <div class="w-full max-w-md bg-white rounded-2xl shadow-lg p-6">
    <h1 class="text-xl font-semibold text-bleuone">Rejoindre un Vrai ou Faux</h1>
    <p class="text-sm text-gray-600 mt-1">Entrez le code donné par votre formateur.</p>

    <form method="POST" action="{{ route('vraifaux.resolve') }}" class="mt-6 space-y-4">
      @csrf
      <input type="text" name="code" value="{{ old('code') }}" maxlength="12" required
             class="w-full rounded-lg border-gray-300 uppercase tracking-wider focus:border-orangeone focus:ring-orange-200"
             placeholder="Ex : AB12CD">
      @error('code')
        <p class="text-red-600 text-xs">{{ $message }}</p>
      @enderror
      <button class="w-full rounded-lg bg-bleuone text-white py-2 font-bold hover:bg-bleuone-light transition">
        Rejoindre
      </button>
    </form>
  </div>
</body>
</html>
