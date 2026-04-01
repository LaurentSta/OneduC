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
