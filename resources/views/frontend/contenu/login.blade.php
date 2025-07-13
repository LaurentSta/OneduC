<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion - Oneduc</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Chargement de Tailwind (via Vite) --}}
    @vite('resources/css/app.css')

    {{-- Alpine.js pour gérer l’infobulle --}}
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-100 font-sans antialiased">

<div class="flex h-screen">

    <div class="w-1/2 h-screen hidden lg:block">
        <img src="{{ asset('frontend/assets/img/illustrations/LogoOneduc.svg') }}"
             alt="Illustration Oneduc"
             class="object-cover w-full h-full">
    </div>


    <!-- Colonne droite : formulaire -->
    <div class="w-full lg:w-1/2 bg-white flex flex-col justify-center items-center px-6 sm:px-16 md:px-24 lg:px-32">

        <!-- Logo cliquable avec infobulle -->
        <a href="{{ route('index') }}"
        class="mb-8 block relative group w-fit"
        title="Retour à la page principale">
            <img src="{{ asset('frontend/assets/img/front-pages/branding/LogoOneducPositionG.svg') }}"
                alt="Logo Oneduc"
                class="h-16 mx-auto">
            
            <!-- Infobulle (optionnelle, si tu veux du visuel personnalisé en plus de title) -->
            <div class="absolute -bottom-8 left-1/2 -translate-x-1/2 px-3 py-1 text-xs text-white rounded bg-[#004461] opacity-0 group-hover:opacity-100 transition pointer-events-none shadow">
                Retour à la page principale
            </div>
        </a>


        <!-- Titre -->
        <h2 class="text-2xl font-bold text-gray-800 mb-2">Bienvenue sur <span class="text-orangeone">Oneduc.fr</span> !</h2>
        <p class="text-sm text-gray-600 mb-6">Veuillez vous connecter à votre compte et commencer l’aventure</p>

        <!-- Affichage des erreurs -->
        @if ($errors->any())
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded w-full">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <!-- 🔐 Formulaire de connexion -->
        <form method="POST" action="{{ route('login.process') }}" class="w-full space-y-4">
            @csrf

            <!-- Champ email ou username -->
            <div>
                <label for="email" class="block text-gray-700 text-sm mb-1">Email ou nom d'utilisateur</label>
                <input type="text" name="email" id="email"
                       class="w-full border border-gray-300 rounded-[4px] py-2 px-3 focus:outline-none focus:ring-1 focus:ring-orangeone"
                       placeholder="exemple@domaine.fr" required>
            </div>

            <!-- Champ mot de passe -->
            <div>
                <label for="password" class="block text-gray-700 text-sm mb-1">Mot de passe</label>
                <input type="password" name="password" id="password"
                       class="w-full border border-gray-300 rounded-[4px] py-2 px-3 focus:outline-none focus:ring-1 focus:ring-orangeone"
                       placeholder="••••••••••••" required>
            </div>

            <!-- Checkbox + lien mot de passe oublié -->
            <div class="flex justify-between items-center text-sm text-gray-600">
                <label class="flex items-center">
                    <input type="checkbox" name="remember" class="mr-2 border-gray-300 text-orangeone focus:ring-orangeone">
                    Se souvenir de moi
                </label>
                <a href="{{ route('password.request') }}" class="text-orangeone hover:underline">Mot de passe oublié ?</a>
            </div>

            <!-- ✅ Bouton principal : Se connecter -->
            <button type="submit" class="btn-oneduc w-full text-center">
                Se connecter
            </button>

        </form>

        <!-- ✅ Bouton secondaire : connexion par code stagiaire + infobulle -->
<!-- ✅ Bouton secondaire : connexion par code stagiaire + infobulle -->
<div x-data="{ showTip: false }" class="relative w-full mt-4">
    <a href="{{ route('stagiaire.code.form') }}"
       @mouseenter="showTip = true"
       @mouseleave="showTip = false"
       class="block text-center w-full text-[#004461] bg-white border-4 border-[#004461] font-semibold py-2 px-4 rounded-[4px] transition duration-300 hover:bg-[#004461] hover:text-white">
        Connexion avec un code stagiaire
    </a>
    <div x-show="showTip"
         x-transition
         class="absolute z-10 mt-2 w-full text-sm text-white rounded px-4 py-2 shadow-lg"
         style="top: 100%; left: 0; background-color: #004461;">
        Ce code vous est remis par votre formateur au moment de votre inscription.
    </div>
</div>




        <!-- 🔗 Lien : devenir formateur -->
        <div class="mt-6 text-center text-sm text-gray-600">
            Vous souhaitez rejoindre Oneduc en tant que formateur ?
            <a href="{{ route('formateur.inscription.form') }}" class="text-orangeone hover:underline ml-1">
                Devenir formateur
            </a>
        </div>
    </div>
</div>

</body>
</html>
