@extends('frontend.master')

@section('home')
<section class="min-h-[80vh] flex items-center justify-center bg-[#f8f7fa] py-12">
    <div class="max-w-6xl mx-auto px-6 w-full">
        
        

        <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
            
            <a href="{{ route('stagiaire.code.form') }}" 
               class="group relative bg-white p-10 rounded-[40px] shadow-sm hover:shadow-2xl transition-all duration-500 border-2 border-transparent hover:border-orangeone flex flex-col items-center text-center overflow-hidden">
                
                {{-- Fond décoratif léger --}}
                <div class="absolute -top-10 -right-10 w-32 h-32 bg-orange-50 rounded-full group-hover:scale-150 transition-transform duration-700 opacity-50"></div>

                <div class="w-full aspect-square max-w-[240px] mb-8 z-10">
                    <img src="{{ asset('frontend/assets/img/illustrations/Stagiaires.svg') }}" 
                         alt="Espace Stagiaire" 
                         class="w-full h-full object-contain transform group-hover:-translate-y-2 transition-transform duration-500">
                </div>

                <h2 class="text-3xl font-bold text-bleuone mb-4 font-raleway">Je suis Stagiaire</h2>
                <p class="text-gray-500 leading-relaxed mb-8 font-lisible">
                    J'ai un code d'accès et je souhaite continuer mes modules de formation.
                </p>
                
                <div class="mt-auto bg-orangeone text-white px-10 py-3 rounded-full font-bold shadow-lg group-hover:scale-105 transition-all">
                    Accéder à mes cours
                </div>
            </a>

            <a href="{{ route('login') }}" 
               class="group relative bg-white p-10 rounded-[40px] shadow-sm hover:shadow-2xl transition-all duration-500 border-2 border-transparent hover:border-bleuone flex flex-col items-center text-center overflow-hidden">
                
                {{-- Fond décoratif léger --}}
                <div class="absolute -top-10 -right-10 w-32 h-32 bg-blue-50 rounded-full group-hover:scale-150 transition-transform duration-700 opacity-50"></div>

                <div class="w-full aspect-square max-w-[240px] mb-8 z-10">
                    <img src="{{ asset('frontend/assets/img/illustrations/Formateurs.svg') }}" 
                         alt="Espace Formateur" 
                         class="w-full h-full object-contain transform group-hover:-translate-y-2 transition-transform duration-500">
                </div>

                <h2 class="text-3xl font-bold text-bleuone mb-4 font-raleway">Je suis Formateur</h2>
                <p class="text-gray-500 leading-relaxed mb-8 font-lisible">
                    Je gère mes groupes de stagiaires et je consulte les statistiques de progression.
                </p>
                
                <div class="mt-auto bg-bleuone text-white px-10 py-3 rounded-full font-bold shadow-lg group-hover:scale-105 transition-all">
                    Espace Gestion
                </div>
            </a>

        </div>
    </div>
</section>
@endsection