

@extends('frontend.master')

@section('home')


  <section class="bg-white">
    <div class="max-w-[1248px] mx-auto min-h-[500px] flex flex-col md:flex-row items-center justify-center">

      <!-- Colonne gauche AVEC image de fond plein hauteur -->
      <div class="w-full md:w-1/2 flex justify-center md:justify-start bg-no-repeat bg-cover bg-center"
           style="background-image: url('{{ asset('frontend/assets/img/front-pages/backgrounds/EnteteBackground.gif') }}');">
        <div class="max-w-2xl text-center md:text-left space-y-6 p-6 flex flex-col justify-center min-h-[500px]">
          <h1 class="text-[40px] md:text-[48px] font-raleway text-bleuone">OUTIL NUMÉRIQUE ÉDUCATIF</h1>
          <h2 class="text-sous-titre text-orangeone font-bold font-varela text-gray-700">Qui favorise l’inclusion numérique</h2>
          <div class="flex flex-wrap justify-center md:justify-start gap-4 font-lisible">
            <a href="{{ route('projet') }}" class="btn-oneduc">Le projet</a>
            <a href="{{ route('formateur.inscription.form') }}" class="inline-block px-4 py-2 text-base tracking-wide font-varela text-orangeone bg-white border-4 border-orangeone rounded-full transition duration-300 hover:bg-orangeone hover:text-white active:scale-95">
              S'inscrire gratuitement
            </a>
          </div>
        </div>
      </div>

      <!-- Colonne droite avec vidéo ET padding vertical -->
      <div class="w-full md:w-1/2 flex justify-center items-center py-10">
        <div class="aspect-video w-full max-w-xl">
          <iframe
            src="https://www.youtube.com/embed/Bw4_SlnqZj8?autoplay=1&mute=1&loop=1&playlist=Bw4_SlnqZj8&controls=0&showinfo=0&modestbranding=1" width="560" height="315"
            frameborder="0" allowfullscreen allow="autoplay">
          </iframe>
        </div>
      </div>

    </div>
  </section>

  <section class="py-16 bg-gray-50">
    <div class="max-w-[1248px] mx-auto px-4">
      <div class="flex flex-col md:flex-row items-center gap-10">

        <!-- Image à gauche -->
        <div class="w-full md:w-1/2">
          <img src="{{ asset('frontend/assets/img/front-pages/landing-page/LivreOneduc.svg') }}" alt="Livre Oneduc" class="w-full rounded  min-h-[300px] object-cover">
        </div>

        <!-- Texte à droite -->
        <div class="w-full md:w-1/2 space-y-6 font-lisible">
          <h2 class="text-3xl font-semibold flex items-center gap-4 font-raleway text-bleuone">
            <img src="{{ asset('frontend/assets/img/front-pages/icons/etoile8.gif') }}" width="60" height="61" alt="Étoile">
            Oneduc.fr c’est quoi ?
          </h2>
          <p class="text-lg">Onéduc.fr est une plateforme de formation en ligne conçue pour favoriser l’inclusion numérique de tous.</p>
          <p class="text-lg">Dans un monde numérique en constante évolution, l’accès à l’éducation et à l’inclusion numérique est plus qu’une nécessité, c’est un droit.</p>
          <p class="text-lg">Créée par des enseignants, des formateurs et éducateurs spécialisés regroupés en association, cette plateforme s’adresse tout particulièrement aux personnes éloignées de l’emploi, ayant des difficultés d’apprentissage ou en situation de illectronisme…</p>
        </div>

      </div>
    </div>
  </section>

  <section class="py-16 bg-white">
    <div class="max-w-[1248px] mx-auto px-4">
      <div class="flex flex-col-reverse md:flex-row items-center gap-10">

        <!-- Texte à gauche -->
        <div class="w-full md:w-1/2 space-y-6 font-lisible">
          <h2 class="text-3xl font-semibold flex items-center gap-4 font-raleway text-bleuone">
            <img src="{{ asset('frontend/assets/img/front-pages/icons/etoile8.gif') }}" width="60" height="61" alt="Étoile">
            À qui s’adresse Onéduc ?
          </h2>
          <p class="text-lg font-semibold">Onéduc s’adresse à tous les formateurs désireux d’utiliser un outil numérique open source pour former leurs apprenants :</p>
          <ul class="list-disc list-inside text-lg space-y-1">
            <li><strong>Utilisation de contenu</strong> de formations fourni par la plateforme.</li>
            <li><strong>Intégration de contenu personnalisé</strong> par les formateurs eux-mêmes.</li>
          </ul>
          <p class="text-lg">Nous accompagnons des <strong>formateurs individuels</strong>, des <strong>centres de formation</strong>, des <strong>EA</strong> (Entreprises Adaptées) et des <strong>ESAT</strong> (Établissements et services d’aide par le travail).</p>
        </div>

        <!-- Image à droite -->
        <div class="w-full md:w-1/2">
          <img src="{{ asset('frontend/assets/img/front-pages/landing-page/LandingPage2.svg') }}" alt="Livre Oneduc" class="w-full rounded min-h-[300px] object-cover">
        </div>

      </div>
    </div>
  </section>

  <section class="py-16 bg-gray-50">
    <div class="max-w-[1248px] mx-auto px-4">
      <h2 class="text-center text-3xl font-raleway font-bold text-bleuone mb-12">
        Les avantages de la formation par Oneduc.fr
      </h2>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-10 font-lisible">
        @foreach([
            ['src' => 'iconeEntete-04.svg', 'title' => 'Formation hybride', 'desc' => 'Présentiel et distanciel, selon les besoins.'],
            ['src' => 'iconeEntete-06.svg', 'title' => 'Contenus interactifs', 'desc' => 'Vidéos, audios, animations…'],
            ['src' => 'iconeEntete-02.svg', 'title' => 'Création & évaluation', 'desc' => 'Quiz, devoirs, suivi des progrès'],
            ['src' => 'iconeEntete-03.svg', 'title' => 'Engagement & gamification', 'desc' => 'Badges, niveaux, attestations'],
            ['src' => 'iconeEntete-10.svg', 'title' => 'Gestion des stagiaires', 'desc' => 'Suivi individuel ou en groupe.'],
            ['src' => 'iconeEntete-11.svg', 'title' => 'Communication directe', 'desc' => 'Chat et forum avec les formateurs.'],
            ['src' => 'iconeEntete-12.svg', 'title' => 'Notifications automatiques', 'desc' => 'Emails, popups pour un meilleur suivi.'],
            ['src' => 'iconeEntete-09.svg', 'title' => 'Rapports détaillés', 'desc' => 'Évaluations et progression des apprenants.'],
        ] as $block)
        <div class="flex flex-col items-center text-center space-y-4">
          <img src="{{ asset('frontend/assets/img/front-pages/icons/' . $block['src']) }}" alt="{{ $block['title'] }}" class="w-28">
          <div>
            <h5 class="text-xl font-semibold text-gray-800">{{ $block['title'] }}</h5>
            <p class="italic text-sm text-gray-600">{{ $block['desc'] }}</p>
          </div>
        </div>
        @endforeach
      </div>
    </div>
  </section>

  <section class="py-16 bg-white">
    <div class="max-w-[1248px] mx-auto px-4">
      <div class="flex flex-col md:flex-row items-center gap-10">

        <!-- Col gauche : texte -->
        <div class="w-full md:w-1/2 space-y-6 font-lisible">
          <h2 class="text-3xl font-semibold flex items-center gap-4 font-raleway text-bleuone">
            <img src="{{ asset('frontend/assets/img/front-pages/icons/etoile8.gif') }}" width="60" height="61" alt="Étoile">
            Oneduc.fr c’est quoi ?
          </h2>
          <p class="text-lg">Onéduc est une association regroupant des formateurs ayant créé une plateforme open source de digitalisation des formations.</p>
          <p class="text-lg">Elle met à disposition des formateurs une plateforme de formation en ligne fonctionnant sur tous supports. Utilisée dans le cadre de formations financées par des OPCO ou le CPF (Compte personnel de Formation).</p>
        </div>

        <!-- Col droite : image -->
        <div class="w-full md:w-1/2">
          <img src="{{ asset('frontend/assets/img/front-pages/landing-page/module.svg') }}" alt="Module Oneduc" class="w-full rounded min-h-[300px] object-cover">
        </div>

      </div>
    </div>
  </section>

  @endsection

