<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Oneduc - Jeu de mémoire</title>
  @vite(['resources/css/app.css'])
</head>
<body class="flex min-h-screen items-center justify-center bg-slate-100 p-4 font-lisible">
  <main class="w-full max-w-md rounded-[20px] bg-white p-6 shadow-md">
    <h1 class="font-raleway text-2xl text-bleuone">Rejoindre un jeu de mémoire</h1>
    <p class="mt-2 text-sm text-gray-600">Saisissez le code communiqué par votre formateur.</p>
    <form method="POST" action="{{ route('memoire.resolve') }}" class="mt-6 space-y-4">
      @csrf
      <label for="memoire-code" class="block text-sm font-semibold text-gray-700">Code de la partie</label>
      <input id="memoire-code" name="code" value="{{ old('code') }}" maxlength="12" required autofocus class="w-full rounded-[8px] border-gray-300 text-center font-mono text-xl uppercase tracking-widest focus:border-bleuone focus:ring-bleuone/20" placeholder="AB12CD">
      @if($errors->memoireJoin->has('code'))
        <p class="text-sm text-red-600">{{ $errors->memoireJoin->first('code') }}</p>
      @endif
      <button type="submit" class="btn-oneduc-blue w-full justify-center">Rejoindre</button>
    </form>
  </main>
</body>
</html>
