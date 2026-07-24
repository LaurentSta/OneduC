<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion Formateur - Oneduc</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @vite('resources/css/app.css')
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-100 font-sans antialiased">

<div class="flex h-screen">
    {{-- Colonne gauche : Illustration --}}
    <div class="w-1/2 h-screen hidden lg:block">
        <img src="{{ asset('frontend/assets/img/illustrations/LogoOneduc.svg') }}"
             alt="Illustration Oneduc"
             class="object-cover w-full h-full">
    </div>

    {{-- Colonne droite : Formulaire --}}
    <div class="w-full lg:w-1/2 bg-white flex flex-col justify-center items-center px-6 sm:px-16 md:px-24 lg:px-32">

        {{-- Logo cliquable --}}
        <a href="{{ route('index') }}" class="mb-8 block relative group w-fit" title="Retour à la page principale">
            <img src="{{ asset('frontend/assets/img/front-pages/branding/LogoOneducPositionG.svg') }}"
                alt="Logo Oneduc" class="h-16 mx-auto">
            <div class="absolute -bottom-8 left-1/2 -translate-x-1/2 px-3 py-1 text-xs text-white rounded bg-[#004461] opacity-0 group-hover:opacity-100 transition pointer-events-none shadow">
                Retour à l'accueil
            </div>
        </a>

        <h2 class="text-2xl font-bold text-gray-800 mb-2">Espace <span class="text-bleuone">Formateur</span></h2>
        <p class="text-sm text-gray-600 mb-6 text-center">Identifiez-vous pour gérer vos parcours et vos apprenants.</p>

        @if ($errors->any())
            <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg w-full text-sm">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('login.process') }}" class="w-full space-y-4">
            @csrf
            <div>
                <label for="email" class="block text-gray-700 text-sm mb-1 font-medium">Email ou utilisateur</label>
                <input type="text" name="email" id="email"
                       class="w-full border border-gray-300 rounded-lg py-3 px-4 focus:ring-2 focus:ring-bleuone outline-none transition"
                       placeholder="votre@email.fr" required>
            </div>

            <div>
                <label for="password" class="block text-gray-700 text-sm mb-1 font-medium">Mot de passe</label>
                <input type="password" name="password" id="password"
                       class="w-full border border-gray-300 rounded-lg py-3 px-4 focus:ring-2 focus:ring-bleuone outline-none transition"
                       placeholder="••••••••••••" required>
            </div>

            <div class="flex justify-between items-center text-sm">
                <label class="flex items-center text-gray-600">
                    <input type="checkbox" name="remember" class="mr-2 rounded border-gray-300 text-bleuone focus:ring-bleuone">
                    Se souvenir de moi
                </label>
                <a href="{{ route('password.request') }}" class="text-bleuone hover:underline font-medium">Oublié ?</a>
            </div>

            <button type="submit" class="btn-oneduc-blue w-full !py-3">
                Se connecter
            </button>
        </form>

        {{-- SÉPARATEUR --}}
        <div class="w-full flex items-center my-6">
            <div class="flex-grow border-t border-gray-200"></div>
            <span class="px-4 text-gray-400 text-xs uppercase tracking-widest">ou</span>
            <div class="flex-grow border-t border-gray-200"></div>
        </div>

        <a href="{{ route('google.redirect') }}"
           class="w-full flex items-center justify-center gap-3 border border-gray-300 rounded-lg py-3 px-4 text-gray-700 font-medium hover:bg-gray-50 transition">
            <svg class="w-5 h-5" viewBox="0 0 24 24">
                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z"/>
                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.99.66-2.25 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.85A10.99 10.99 0 0 0 12 23z"/>
                <path fill="#FBBC05" d="M5.84 14.09A6.6 6.6 0 0 1 5.5 12c0-.73.13-1.43.34-2.09V7.06H2.18A11 11 0 0 0 1 12c0 1.77.43 3.45 1.18 4.94l3.66-2.85z"/>
                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1a10.99 10.99 0 0 0-9.82 6.06l3.66 2.85c.87-2.6 3.3-4.53 6.16-4.53z"/>
            </svg>
            Continuer avec Google
        </a>

        {{-- SÉPARATEUR --}}
        <div class="w-full flex items-center my-8">
            <div class="flex-grow border-t border-gray-200"></div>
            <span class="px-4 text-gray-400 text-xs uppercase tracking-widest">Nouveau ici ?</span>
            <div class="flex-grow border-t border-gray-200"></div>
        </div>

        {{-- BOUTON DEVENIR FORMATEUR (Plus gros et incitatif) --}}
        <div class="w-full">
            <a href="{{ route('formateur.inscription.form') }}" 
               class="btn-oneduc-outline !flex !w-full !flex-col !px-6 !py-4">
                <span class="text-lg">Devenir formateur sur Onéduc</span>
                <p class="text-xs font-normal mt-1 opacity-80">Créez votre compte gratuitement et commencez à former.</p>
            </a>
        </div>

    </div>
</div>

</body>
</html>
