@extends('frontend.master')

@section('home')
{{-- HERO SECTION --}}
  <section class="relative overflow-hidden bg-white pt-10 pb-20 lg:pt-20">
    <div class="max-w-[1248px] mx-auto px-6">
      <div class="flex flex-col lg:flex-row items-center gap-12" >
        
        <div class="w-full lg:w-1/2 space-y-8 z-10 justify-center md:justify-start bg-no-repeat bg-cover bg-center" style="background-image: url('{{ asset('frontend/assets/img/front-pages/backgrounds/EnteteBackground.gif') }}');">
          
          <h1 class="font-raleway text-bleuone">
            <span class="text-titre block">Outil Numérique Éducatif</span>
            
          </h1>

          <p class="prose-oneduc text-xl max-w-xl text-gray-600">
            Créez des parcours de formation sur-mesure, adaptés aux besoins et aux niveaux de chaque apprenant, sans barrière technique.
          </p>

          <div class="flex flex-wrap gap-4 pt-4">
            <a href="{{ route('formateur.inscription.form') }}" class="btn-oneduc">
              Commencer gratuitement
            </a>
            <a href="{{ route('projet') }}" class="btn-oneduc-outline">
              Découvrir le projet
            </a>
          </div>
        </div>

        <div class="w-full lg:w-1/2 relative">
          <div class="w-full md:w-1/2 flex justify-center items-center py-10">
        <div class="aspect-video w-full max-w-xl">
          <iframe
            src="https://www.youtube.com/embed/Bw4_SlnqZj8?autoplay=1&mute=1&loop=1&playlist=Bw4_SlnqZj8&controls=0&showinfo=0&modestbranding=1"
            width="560" height="315"
            frameborder="0"
            allowfullscreen
            allow="autoplay">
          </iframe>
        </div>
      </div>

      </div>
    </div>
  </section>
 {{-- SECTION "C'EST QUOI ?" --}}
  <section class="py-20 bg-gray-50">
    <div class="max-w-[1248px] mx-auto px-6">
      <div class="flex flex-col md:flex-row items-center gap-16">
        <div class="w-full md:w-1/2">
          <div class="relative group">
            <div class="absolute inset-0 border-4 border-orangeone rounded-2xl rotate-3 group-hover:rotate-0 transition-transform duration-500"></div>
            <img src="{{ asset('frontend/assets/img/front-pages/landing-page/LivreOneduc.svg') }}"
                 alt="Illustration LMS Oneduc"
                 class="relative z-10 w-full rounded-2xl shadow-xl transform transition-transform duration-500 group-hover:-translate-y-2">
          </div>
        </div>

        <div class="w-full md:w-1/2 space-y-6">
          <div class="flex items-center gap-3">
            <img src="{{ asset('frontend/assets/img/front-pages/icons/etoile8.gif') }}" width="50" alt="" aria-hidden="true">
            <h2 class="text-3xl md:text-4xl font-raleway font-extrabold text-bleuone">Onéduc, c’est quoi ?</h2>
          </div>
          
          <div class="prose-oneduc">
            <p class="font-bold text-bleuone text-xl border-l-4 border-orangeone pl-4">
              Une plateforme de formation (LMS) conçue par et pour le terrain.
            </p>
            <p>
              Onéduc facilite l'organisation de parcours accessibles. Pensée pour les publics hétérogènes et éloignés du numérique, elle permet aux formateurs de se concentrer sur l'essentiel : <strong>l'accompagnement pédagogique</strong>.
            </p>
          </div>
        </div>
      </div>
    </div>
  </section>

  
<section class="py-16 bg-white overflow-hidden">
  <div class="max-w-[1248px] mx-auto px-4">
    <div class="flex flex-col-reverse md:flex-row items-center gap-10">

      <div class="w-full md:w-1/2 space-y-6 font-lisible text-gray-700">
        
        <div class="flex items-center gap-3">
            <img src="{{ asset('frontend/assets/img/front-pages/icons/etoile8.gif') }}" width="50" alt="" aria-hidden="true">
            <h2 class="text-3xl md:text-4xl font-raleway font-extrabold text-bleuone">À qui s’adresse Onéduc ?</h2>
        </div>


          
        <div class="prose-oneduc space-y-4">
          <p class="text-lg leading-relaxed font-semibold text-gray-800 border-l-4 border-orangeone pl-4">
            Onéduc s’adresse aux formateurs, éducateurs et professionnels de l’accompagnement
            qui souhaitent utiliser un outil numérique simple pour organiser et adapter leurs formations.
          </p>

          <p class="text-lg leading-relaxed">
            La plateforme est particulièrement adaptée aux contextes de formation accueillant des publics
            <strong>hétérogènes</strong>, <strong>débutants</strong> ou <strong>éloignés du numérique</strong>,
            nécessitant des parcours progressifs, accessibles et personnalisables.
          </p>

          <p class="text-lg leading-relaxed">
            Onéduc accompagne aussi bien des <strong>formateurs individuels</strong> que des
            <strong>structures de formation</strong> (centres de formation, associations,
            ESAT), souhaitant disposer d’un outil fiable pour
            <strong>suivre les apprenants</strong>, <strong>ajuster les parcours</strong> et
            <strong>renforcer l’accompagnement pédagogique</strong>.
          </p>
        </div>
      </div>

      <div class="w-full md:w-1/2 flex justify-center">
        <div class="relative group w-full"> {{-- Ajout de w-full pour occuper l'espace --}}
          <div class="absolute -inset-2 border-4 border-orangeone rounded-2xl -rotate-3 group-hover:rotate-0 transition-all duration-500 animate-pulse-slow"></div>
          
          <img src="{{ asset('frontend/assets/img/front-pages/landing-page/LandingPage2.svg') }}"
               alt="Illustration Oneduc"
               class="relative z-10 w-full rounded-2xl shadow-xl transform transition-transform duration-500 group-hover:-translate-y-2 object-cover">
        </div>
      </div>

    </div>
  </div>
</section>

  <section class="py-16 bg-gray-50">
    <div class="max-w-[1248px] mx-auto px-4">

      <h2 class="text-center text-3xl font-raleway font-bold text-bleuone mb-12">
        Les avantages de la formation par Oneduc.fr
      </h2>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-10 font-lisible text-gray-700">
        @foreach([
            ['src' => 'iconeEntete-04.svg', 'title' => 'Parcours de formation adaptables', 'desc' => 'Organiser les modules selon les besoins des groupes et des apprenants, avec une progression claire et évolutive'],
            ['src' => 'iconeEntete-06.svg', 'title' => 'Prise en main simple et accessible', 'desc' => 'Une interface pensée pour des utilisateurs non techniciens, facilitant l’entrée dans le numérique.'],
            ['src' => 'iconeEntete-02.svg', 'title' => 'Accompagnement des publics hétérogènes', 'desc' => 'Adapter les rythmes, les contenus et les modalités pour répondre aux niveaux et aux situations diverses.'],
            ['src' => 'iconeEntete-03.svg', 'title' => 'Suivi pédagogique lisible', 'desc' => 'Visualiser la progression, les acquis et les points à renforcer sans tableaux complexes.'],
            ['src' => 'iconeEntete-10.svg', 'title' => 'Gain de temps pour le formateur', 'desc' => 'Centraliser les contenus, le suivi et les résultats pour se concentrer sur l’accompagnement.'],
            ['src' => 'iconeEntete-11.svg', 'title' => 'Contenus et activités variés', 'desc' => 'Intégrer facilement vidéos, documents, activités interactives et évaluations.'],
            ['src' => 'iconeEntete-12.svg', 'title' => 'Valorisation des progrès', 'desc' => 'Mettre en évidence les avancées des apprenants grâce à des indicateurs clairs et des dispositifs de reconnaissance.'],
            ['src' => 'iconeEntete-09.svg', 'title' => 'Formation hybride facilitée', 'desc' => 'Articuler présentiel et activités en ligne selon les contraintes du terrain.'],
        ] as $block)
          <div class="flex flex-col items-center text-center space-y-4">
            <img src="{{ asset('frontend/assets/img/front-pages/icons/' . $block['src']) }}"
                 alt="{{ $block['title'] }}"
                 class="h-[200px] w-auto object-contain">
            <div class="space-y-1">
              <h5 class="text-xl font-semibold text-gray-800">{{ $block['title'] }}</h5>
              <p class="text-sm text-gray-600 leading-relaxed">{{ $block['desc'] }}</p>
            </div>
          </div>
        @endforeach
      </div>

    </div>
  </section>

  <section class="py-16 bg-white">
    <div class="max-w-[1248px] mx-auto px-4">
      <div class="flex flex-col md:flex-row items-center gap-10">

        <!-- Texte -->
        <div class="w-full md:w-1/2 space-y-6 font-lisible text-gray-700">
          <h2 class="text-3xl font-raleway font-semibold text-bleuone flex items-center gap-4">
            <img src="{{ asset('frontend/assets/img/front-pages/icons/etoile8.gif') }}" width="60" height="61" alt="" aria-hidden="true">
            Un projet créé par des formateurs
          </h2>

          <div class="max-w-[60ch] space-y-4 text-lg leading-relaxed">
            <p>
              Onéduc est né de l’expérience de formateurs, d’enseignants et d’éducateurs spécialisés confrontés aux réalités du terrain et aux difficultés d’accès au numérique.
            </p>
            <p>
              Le projet repose sur une conviction simple : un outil de formation ne doit pas imposer des pratiques, mais accompagner les formateurs dans la construction de parcours accessibles, adaptés aux publics et aux contextes d’intervention.
            </p>
          </div>
        </div>

        <!-- Image -->
        <div class="w-full md:w-1/2">
          <img src="{{ asset('frontend/assets/img/front-pages/landing-page/module.svg') }}"
               alt="Illustration d’un module Oneduc"
               class="w-full rounded min-h-[300px] object-cover">
        </div>

      </div>
    </div>
  </section>

@endsection
