<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ $wordCloud->title }} - Nuage de mot</title>
  @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-slate-100 flex items-center justify-center p-4">
  <div class="w-full max-w-xl bg-white rounded-2xl shadow-lg p-6">
    <p class="text-xs uppercase tracking-wide text-gray-500">Code {{ $wordCloud->access_code }}</p>
    <h1 class="text-xl font-semibold text-[#004461] mt-1">{{ $wordCloud->title }}</h1>
    <p class="text-gray-700 mt-2">{{ $wordCloud->question }}</p>

    @if(session('success'))
      <div class="mt-4 rounded-lg border border-green-200 bg-green-50 px-3 py-2 text-sm text-green-800">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('wordcloud.submit', $wordCloud->access_code) }}" class="mt-6 space-y-4">
      @csrf
      <label class="block text-sm font-medium">Ta réponse (1 à 3 mots)</label>
      <input type="text" name="answer" value="{{ old('answer') }}" maxlength="150" required class="w-full rounded-lg border-gray-300" placeholder="Ex: inclusion, confiance, progression">
      @error('answer')<p class="text-red-600 text-xs">{{ $message }}</p>@enderror
      <button class="w-full rounded-lg bg-[#004461] text-white py-2" @disabled(!$wordCloud->is_active)>Envoyer</button>
    </form>

    @if(!$wordCloud->is_active)
      <p class="mt-3 text-sm text-amber-700">Cette session est actuellement fermée.</p>
    @endif
  </div>
</body>
</html>
