<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Première Connexion - Oneduc</title>

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;600;700&family=Varela+Round&display=swap" rel="stylesheet">

    {{-- Chargement de Vite (CSS/JS) --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-800 antialiased min-h-screen flex flex-col justify-center items-center py-12 sm:px-6 lg:px-8">

    {{-- Logo --}}
    <div class="sm:mx-auto sm:w-full sm:max-w-md mb-6 flex justify-center">
        {{-- Remets ici ton composant logo ou une image --}}
        <img src="{{ asset('images/logo.svg') }}" alt="Oneduc" class="h-16 w-auto">
    </div>

    {{-- Carte --}}
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-4 shadow-lg rounded-[20px] sm:px-10 border border-gray-100">
            
            <div class="mb-6 text-center">
                <h2 class="text-2xl font-bold text-bleuone mb-2 font-raleway">
                    Sécurisez votre compte
                </h2>
                <p class="text-sm text-gray-600 leading-relaxed font-varela">
                    Bonjour <strong class="text-gray-900">{{ Auth::user()->prenom }}</strong>.<br>
                    C'est votre première connexion. Veuillez définir votre mot de passe personnel.
                </p>
            </div>

            <form class="space-y-6" action="{{ route('stagiaire.password.init.store') }}" method="POST">
                @csrf

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">
                        Nouveau mot de passe
                    </label>
                    <div class="mt-1">
                        <input id="password" name="password" type="password" required 
                            class="appearance-none block w-full px-3 py-3 border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-orangeone focus:border-orangeone sm:text-sm"
                            placeholder="Minimum 8 caractères">
                    </div>
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
                        Confirmer le mot de passe
                    </label>
                    <div class="mt-1">
                        <input id="password_confirmation" name="password_confirmation" type="password" required 
                            class="appearance-none block w-full px-3 py-3 border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-orangeone focus:border-orangeone sm:text-sm"
                            placeholder="Répétez votre mot de passe">
                    </div>
                </div>

                @if ($errors->any())
                    <div class="rounded-md bg-red-50 p-4 border border-red-200">
                        <div class="flex">
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800">Attention</h3>
                                <div class="mt-2 text-sm text-red-700">
                                    <ul class="list-disc pl-5 space-y-1">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <div>
                    <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-bold text-white bg-bleuone hover:bg-bleuone/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orangeone transition-colors">
                        Accéder à ma formation
                    </button>
                </div>
            </form>

            <div class="mt-6 text-center">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-xs text-gray-500 hover:text-orangeone underline transition">
                        Me déconnecter
                    </button>
                </form>
            </div>
        </div>
    </div>

</body>
</html>
