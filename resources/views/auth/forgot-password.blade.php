<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mot de passe oublié - Onéduc</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @vite('resources/css/app.css')
</head>
<body class="bg-gray-100 font-sans antialiased">
    <div class="min-h-screen flex flex-col justify-center items-center px-6">
        
        <div class="w-full max-w-md bg-white rounded-[20px] shadow-xl p-8 border-t-8 border-orangeone">
            <div class="text-center mb-8">
                <a href="{{ route('index') }}">
                    <img src="{{ asset('frontend/assets/img/front-pages/branding/LogoOneducPositionG.svg') }}" alt="Logo Onéduc" class="h-16 mx-auto mb-4">
                </a>
                <h2 class="text-2xl font-bold text-bleuone">Mot de passe oublié ?</h2>
                <p class="text-sm text-gray-500 mt-2">
                    Pas de souci. Indiquez-nous votre adresse email et nous vous enverrons un lien de réinitialisation.
                </p>
            </div>

            @if (session('status'))
                <div class="mb-4 font-medium text-sm text-green-600 bg-green-50 p-3 rounded-lg border border-green-200">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
                @csrf
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Votre Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                           class="w-full border border-gray-300 rounded-lg py-3 px-4 focus:ring-2 focus:ring-orangeone outline-none transition">
                    @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <button type="submit" class="w-full bg-bleuone hover:bg-[#00334a] text-white font-bold py-3 rounded-lg shadow-md transition transform hover:-translate-y-1">
                    Envoyer le lien de réinitialisation
                </button>
            </form>

            <div class="mt-8 text-center">
                <a href="{{ route('login') }}" class="text-sm text-gray-400 hover:text-bleuone transition flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Retour à la connexion
                </a>
            </div>
        </div>
    </div>
</body>
</html>