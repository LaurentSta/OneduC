@extends('frontend.master')

@section('home')

  <section class="bg-white">
    <div class="max-w-[1248px] mx-auto min-h-[500px] flex flex-col md:flex-row items-center justify-center">

      <!-- Colonne gauche -->
      <div class="w-full md:w-1/2 flex justify-center md:justify-start bg-no-repeat bg-cover bg-center"
           style="background-image: url('{{ asset('frontend/assets/img/front-pages/backgrounds/EnteteBackground.gif') }}');">
        <div class="max-w-2xl text-center md:text-left space-y-6 p-6 flex flex-col justify-center min-h-[500px]">

          <h2 class="text-3xl md:text-4xl font-raleway leading-relaxed text-bleuone">
            <span class=" font-bold">Outil Numérique Éducatif<br/></span>
            pour créer des parcours de formation accessibles
          </h2>

          <div class="flex flex-wrap justify-center md:justify-start gap-4 font-lisible">
            <a href="{{ route('projet') }}" class="btn-oneduc">Le projet</a>

            <a href="{{ route('formateur.inscription.form') }}"
               class="inline-block px-4 py-2 text-base tracking-wide font-lisible font-semibold text-orangeone bg-white border-4 border-orangeone rounded-full transition duration-300 hover:bg-orangeone hover:text-white active:scale-95">
              S'inscrire gratuitement
            </a>
          </div>

        </div>
      </div>

      <!-- Colonne droite : vidéo -->
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
  </section>

  <section class="py-4 bg-gray-50">
    <div class="max-w-[1248px] mx-auto px-4">
      <div class="flex flex-col md:flex-row items-center gap-10">

        <!-- Image -->
        <div class="w-full md:w-1/2">
          <img src="{{ asset('frontend/assets/img/front-pages/landing-page/LivreOneduc.svg') }}"
               alt="Livre Oneduc"
               class="w-full rounded min-h-[300px] object-cover">
        </div>

        <!-- Texte -->
        <div class="w-full md:w-1/2 space-y-6 font-lisible text-gray-700">
          <h2 class="text-3xl font-raleway font-semibold text-bleuone flex items-center gap-4">
            <img src="{{ asset('frontend/assets/img/front-pages/icons/etoile8.gif') }}" width="60" height="61" alt="">
            Oneduc.fr, c’est quoi ?
          </h2>

          <div class="space-y-4 text-lg leading-relaxed ">
            <p class="font-semibold ">Onéduc est une plateforme de formation conçue pour permettre aux formateurs de créer, organiser et adapter des parcours de formation accessibles, en fonction des besoins et des niveaux des apprenants.</p>
            
            <p>Pensée pour les réalités du terrain, elle facilite le suivi de la progression, l’accompagnement des publics hétérogènes et l’utilisation du numérique, sans complexité technique.</p>
          </div>
        </div>

      </div>
    </div>
  </section>

  <section class="py-16 bg-white">
    <div class="max-w-[1248px] mx-auto px-4">
      <div class="flex flex-col-reverse md:flex-row items-center gap-10">

        <!-- Texte -->
        <div class="w-full md:w-1/2 space-y-6 font-lisible text-gray-700">
          <h2 class="text-3xl font-raleway font-semibold text-bleuone flex items-center gap-4">
            <img src="{{ asset('frontend/assets/img/front-pages/icons/etoile8.gif') }}" width="60" height="61" alt="">
            À qui s’adresse Onéduc ?
          </h2>

          <p class="text-lg leading-relaxed font-semibold text-gray-800">
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
              entreprises adaptées, ESAT), souhaitant disposer d’un outil fiable pour
              <strong>suivre les apprenants</strong>, <strong>ajuster les parcours</strong> et
              <strong>renforcer l’accompagnement pédagogique</strong>.
            </p>
        </div>

        <!-- Image -->
        <div class="w-full md:w-1/2">
          <img src="{{ asset('frontend/assets/img/front-pages/landing-page/LandingPage2.svg') }}"
               alt="Illustration Oneduc"
               class="w-full rounded min-h-[300px] object-cover">
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
                 class="w-28">
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
