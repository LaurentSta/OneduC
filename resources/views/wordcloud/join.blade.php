<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Oneduc - Nuage de mot</title>
  @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-slate-100 flex items-center justify-center p-4">
  <div class="w-full max-w-md bg-white rounded-2xl shadow-lg p-6">
    <h1 class="text-xl font-semibold text-[#004461]">Nuage de mot</h1>
    <p class="text-sm text-gray-600 mt-1">Entre le code donné par l'animateur.</p>

    <form method="POST" action="{{ route('wordcloud.resolve') }}" class="mt-6 space-y-4">
      @csrf
      <input type="text" name="code" value="{{ old('code') }}" maxlength="12" required class="w-full rounded-lg border-gray-300 uppercase" placeholder="Ex: AB12CD">
      @error('code')<p class="text-red-600 text-xs">{{ $message }}</p>@enderror
      <button class="w-full rounded-lg bg-[#004461] text-white py-2">Rejoindre</button>
    </form>
  </div>
</body>
</html>
