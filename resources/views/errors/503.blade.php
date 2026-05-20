{{-- resources/views/errors/503.blade.php --}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Site en maintenance | Oneduc</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .bg-watermark {
            background: url('{{ asset('frontend/assets/img/important/LOGOOneducSVG.svg') }}') no-repeat center center;
            background-size: 100%;
            opacity: 0.05;
            position: absolute;
            inset: 0;
            z-index: 0;
        }
    </style>
</head>
<body class="bg-gray-100 font-sans antialiased relative overflow-hidden">
    {{-- Arrière-plan watermark --}}
    <div class="bg-watermark"></div>

    <div class="min-h-screen flex flex-col items-center justify-center px-6 relative z-10">
        
        {{-- Logo principal --}}
        <div class="mb-8">
            <img src="{{ asset('frontend/assets/img/important/LogoOneducPositionG-02.svg') }}" 
                 alt="Oneduc" class="h-20 mx-auto">
        </div>

        {{-- Bloc principal avec 2 colonnes --}}
        <div class="bg-white rounded-[20px] shadow-md p-10 text-center max-w-4xl w-full grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
            
            {{-- Colonne gauche : texte --}}
            <div>
                <h1 class="text-3xl font-bold text-[#004461] mb-4">Maintenance en cours</h1>
                <p class="text-gray-600 leading-relaxed">
                    Notre site est temporairement indisponible.<br>
                    Nous travaillons à l’amélioration de nos services et serons de retour très bientôt.
                </p>
                <a href="https://github.com/LaurentSta/Oneduc"
                   target="_blank"
                   rel="noopener noreferrer"
                   class="mt-6 inline-flex items-center justify-center gap-2 rounded-full border border-[#004461]/20 bg-[#004461] px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-[#E94D2A]">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M12 2C6.477 2 2 6.588 2 12.253c0 4.53 2.865 8.371 6.839 9.728.5.094.683-.222.683-.494 0-.244-.009-.89-.014-1.747-2.782.62-3.369-1.375-3.369-1.375-.455-1.186-1.11-1.502-1.11-1.502-.908-.636.069-.623.069-.623 1.004.073 1.532 1.057 1.532 1.057.892 1.566 2.341 1.114 2.91.852.091-.662.35-1.114.636-1.37-2.221-.259-4.555-1.139-4.555-5.066 0-1.119.39-2.034 1.03-2.751-.103-.26-.446-1.304.098-2.716 0 0 .84-.276 2.75 1.051A9.34 9.34 0 0 1 12 6.951a9.34 9.34 0 0 1 2.504.346c1.909-1.327 2.747-1.051 2.747-1.051.546 1.412.203 2.456.1 2.716.641.717 1.029 1.632 1.029 2.751 0 3.937-2.338 4.804-4.566 5.058.359.317.678.943.678 1.9 0 1.371-.012 2.477-.012 2.814 0 .274.18.593.688.492C19.138 20.62 22 16.78 22 12.253 22 6.588 17.523 2 12 2Z" clip-rule="evenodd" />
                    </svg>
                    Voir le code source sur GitHub
                </a>
            </div>

            {{-- Colonne droite : image --}}
            <div class="flex justify-center">
                <img src="{{ asset('images/svg/Maintenance.svg') }}" 
                     alt="Maintenance" 
                     class="max-w-[300px] w-full h-auto">
            </div>
        </div>

        {{-- Footer --}}
        <p class="mt-8 text-gray-500 text-sm">
            &copy; {{ date('Y') }} Oneduc. Tous droits réservés.
        </p>
    </div>
</body>
</html>
