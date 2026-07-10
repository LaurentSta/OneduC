<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Oneduc - Jeu du pendu</title>
  @vite(['resources/css/app.css'])
</head>
<body class="flex min-h-screen items-center justify-center bg-slate-100 p-4 font-lisible">
  <main class="w-full max-w-md rounded-[20px] bg-white p-6 shadow-md">
    <h1 class="font-raleway text-2xl text-bleuone">Rejoindre un jeu du pendu</h1>
    <p class="mt-2 text-sm text-gray-600">Saisissez le code communiqué par votre formateur.</p>
    <form method="POST" action="{{ route('pendu.resolve') }}" class="mt-6 space-y-4">
      @csrf
      <label for="pendu-code" class="block text-sm font-semibold text-gray-700">Code de la partie</label>
      <input id="pendu-code" name="code" value="{{ old('code') }}" maxlength="12" required autofocus class="w-full rounded-[8px] border-gray-300 text-center font-mono text-xl uppercase tracking-widest focus:border-orangeone focus:ring-orangeone/20" placeholder="AB12CD">
      @if($errors->penduJoin->has('code'))
        <p class="text-sm text-red-600">{{ $errors->penduJoin->first('code') }}</p>
      @endif
      <button type="submit" class="btn-oneduc w-full justify-center">Rejoindre</button>
    </form>
  </main>
</body>
</html>
