@extends('frontend.master')

@section('home')
{{-- HERO SECTION --}}
  <section class="relative overflow-hidden bg-white pt-10 pb-20 lg:pt-20">
    <div class="max-w-[1248px] mx-auto px-6">
      <div class="flex flex-col lg:flex-row items-center gap-12" >
        
        <div class="w-full lg:w-1/2 space-y-8 z-10 justify-center md:justify-start bg-no-repeat bg-cover bg-center" style="background-image: url('{{ asset('frontend/assets/img/front-pages/backgrounds/EnteteBackground.gif') }}');">
          
          <h1 class="font-raleway text-bleuone">
            <span class="text-titre block">Outil Numérique Éducatif</span>
            {{-- Sous-titre explicatif pour les nouveaux visiteurs --}}
            <span class="text-2xl font-varela text-gray-500 block mt-3">Une plateforme simple pour former et accompagner vos apprenants</span>
          </h1>

          <p class="prose-oneduc text-xl max-w-xl text-gray-600">
            Créez des parcours de formation sur-mesure, adaptés aux besoins et aux niveaux de chaque apprenant, sans barrière technique.
          </p>

          {{-- Deux portes d'entrée claires : formateur et stagiaire avec code --}}
          <div class="flex flex-wrap gap-4 pt-4">
            <a href="{{ route('formateur.inscription.form') }}" class="btn-oneduc">
              Je suis formateur
            </a>
            <a href="{{ route('stagiaire.code.form') }}" class="btn-oneduc-outline">
              J'ai un code d'accès
            </a>
          </div>
          {{-- Texte rassurant sous les CTA --}}
          <p class="text-sm text-gray-500 mt-2">Inscription en 2 minutes — aucune carte bancaire demandée.</p>
        </div>

        <div class="w-full lg:w-1/2 relative">
          {{-- Correction layout : suppression du w-1/2 interne qui réduisait la vidéo à 25% de la largeur --}}
          <div class="w-full flex justify-center items-center py-10">
            <div class="aspect-video w-full max-w-xl rounded-xl overflow-hidden shadow-lg">
              <iframe
                src="https://www.youtube.com/embed/Bw4_SlnqZj8?autoplay=1&mute=1&loop=1&playlist=Bw4_SlnqZj8&controls=0&showinfo=0&modestbranding=1"
                width="560" height="315"
                class="w-full h-full"
                frameborder="0"
                allowfullscreen
                allow="autoplay"
                title="Présentation de la plateforme Onéduc">
              </iframe>
            </div>
          </div>
        </div>
    </div>
  </section>

{{-- SECTION "COMMENT ÇA MARCHE ?" - 3 étapes pour rassurer avant toute action --}}
<section class="py-20 bg-bleuone text-white">
  <div class="max-w-[1248px] mx-auto px-6">
    <h2 class="text-center text-3xl md:text-4xl font-raleway font-bold mb-4">Comment ça marche ?</h2>
    <p class="text-center text-white/80 text-lg mb-16 max-w-2xl mx-auto font-lisible">En quelques minutes, vous êtes prêt à former.</p>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-10 font-lisible">

      <div class="flex flex-col items-center text-center space-y-4">
        <div class="w-16 h-16 rounded-full bg-orangeone flex items-center justify-center text-white text-2xl font-raleway font-bold shadow-lg">1</div>
        <h3 class="text-xl font-varela font-semibold">Créez votre compte</h3>
        <p class="text-white/80">Inscrivez-vous gratuitement en 2 minutes. Aucune installation nécessaire — tout se passe dans votre navigateur.</p>
      </div>

      <div class="flex flex-col items-center text-center space-y-4">
        <div class="w-16 h-16 rounded-full bg-orangeone flex items-center justify-center text-white text-2xl font-raleway font-bold shadow-lg">2</div>
        <h3 class="text-xl font-varela font-semibold">Préparez votre formation</h3>
        <p class="text-white/80">Créez vos groupes, ajoutez vos contenus et organisez votre parcours. Les outils sont simples et guidés pas à pas.</p>
      </div>

      <div class="flex flex-col items-center text-center space-y-4">
        <div class="w-16 h-16 rounded-full bg-orangeone flex items-center justify-center text-white text-2xl font-raleway font-bold shadow-lg">3</div>
        <h3 class="text-xl font-varela font-semibold">Invitez vos apprenants</h3>
        <p class="text-white/80">Donnez un code d'accès à vos apprenants. Ils rejoignent votre formation en quelques secondes, sans compte à créer.</p>
      </div>

    </div>
  </div>
</section>

{{-- SECTION CAPTURES D'ÉCRAN --}}
{{-- TODO : remplacer les deux blocs gris par de vraies captures d'écran --}}
{{-- Asset 1 : capture du tableau de bord formateur (vue groupes + progression) --}}
{{-- Asset 2 : capture de la vue module côté apprenant --}}
<section class="py-20 bg-white">
  <div class="max-w-[1248px] mx-auto px-6">
    <h2 class="text-center text-3xl md:text-4xl font-raleway font-bold text-bleuone mb-4">À quoi ressemble la plateforme ?</h2>
    <p class="text-center text-gray-600 text-lg mb-16 max-w-2xl mx-auto font-lisible">Une interface claire, pensée pour être utilisée sans formation technique.</p>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
      <div class="bg-gray-100 rounded-2xl aspect-video flex flex-col items-center justify-center gap-3 border-2 border-dashed border-gray-300">
        <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
        <p class="text-gray-500 font-varela text-sm text-center px-4">TODO : capture du tableau de bord formateur</p>
      </div>
      <div class="bg-gray-100 rounded-2xl aspect-video flex flex-col items-center justify-center gap-3 border-2 border-dashed border-gray-300">
        <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
        <p class="text-gray-500 font-varela text-sm text-center px-4">TODO : capture de la vue module apprenant</p>
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
            <h2 class="text-3xl md:text-4xl font-raleway font-extrabold text-bleuone">Onéduc, c'est quoi ?</h2>
          </div>
          
          <div class="prose-oneduc">
            <p class="font-bold text-bleuone text-xl border-l-4 border-orangeone pl-4">
              Une plateforme de formation en ligne conçue par et pour le terrain.
            </p>
            <p>
              Onéduc facilite l'organisation de parcours accessibles. Pensée pour accompagner des personnes qui débutent avec l'ordinateur, elle permet aux formateurs de se concentrer sur l'essentiel : <strong>l'accompagnement pédagogique</strong>.
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
            <h2 class="text-3xl md:text-4xl font-raleway font-extrabold text-bleuone">À qui s'adresse Onéduc ?</h2>
        </div>


          
        <div class="prose-oneduc space-y-4">
          <p class="text-lg leading-relaxed font-semibold text-gray-800 border-l-4 border-orangeone pl-4">
            Onéduc s'adresse aux formateurs, éducateurs et professionnels de l'accompagnement
            qui souhaitent un outil simple pour organiser et adapter leurs formations.
          </p>

          <p class="text-lg leading-relaxed">
            La plateforme est particulièrement adaptée aux contextes où les apprenants
            <strong>débutent avec l'ordinateur</strong> ou ont peu d'expérience du numérique.
            Elle permet de créer des parcours progressifs, à leur rythme.
          </p>

          <p class="text-lg leading-relaxed">
            Onéduc accompagne aussi bien des <strong>formateurs individuels</strong> que des
            <strong>structures de formation</strong> (centres de formation, associations),
            souhaitant disposer d'un outil fiable pour
            <strong>suivre les apprenants</strong>, <strong>ajuster les parcours</strong> et
            <strong>renforcer l'accompagnement pédagogique</strong>.
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
            ['src' => 'iconeEntete-04.svg', 'title' => 'Parcours de formation adaptables', 'desc' => 'Organisez les modules selon les besoins de chaque groupe, avec une progression claire et évolutive.'],
            ['src' => 'iconeEntete-06.svg', 'title' => 'Prise en main simple', 'desc' => 'Une interface pensée pour les formateurs non techniciens. Pas besoin de formation informatique pour démarrer.'],
            ['src' => 'iconeEntete-02.svg', 'title' => 'Adapté aux apprenants débutants', 'desc' => 'Ajustez les rythmes et les contenus pour répondre aux niveaux et aux situations de chaque apprenant.'],
            ['src' => 'iconeEntete-03.svg', 'title' => 'Suivi pédagogique lisible', 'desc' => 'Visualisez la progression, les acquis et les points à renforcer, sans tableaux complexes.'],
            ['src' => 'iconeEntete-10.svg', 'title' => 'Gain de temps pour le formateur', 'desc' => "Centralisez les contenus, le suivi et les résultats pour vous concentrer sur l'accompagnement."],
            ['src' => 'iconeEntete-11.svg', 'title' => 'Contenus et activités variés', 'desc' => 'Intégrez facilement vidéos, documents, activités interactives et évaluations.'],
            ['src' => 'iconeEntete-12.svg', 'title' => 'Valorisation des progrès', 'desc' => 'Mettez en évidence les avancées des apprenants grâce à des indicateurs clairs et des badges de réussite.'],
            ['src' => 'iconeEntete-09.svg', 'title' => 'Présentiel et en ligne, selon vos besoins', 'desc' => 'Combinez facilement les séances en salle et les activités en ligne selon les contraintes de votre terrain.'],
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
              Onéduc est né de l'expérience de formateurs, d'enseignants et d'éducateurs spécialisés confrontés aux réalités du terrain et aux difficultés d'accès au numérique.
            </p>
            <p>
              Le projet repose sur une conviction simple : un outil de formation ne doit pas imposer des pratiques, mais accompagner les formateurs dans la construction de parcours accessibles, adaptés aux publics et aux contextes d'intervention.
            </p>
          </div>
        </div>

        <!-- Image -->
        <div class="w-full md:w-1/2">
          <img src="{{ asset('frontend/assets/img/front-pages/landing-page/module.svg') }}"
               alt="Illustration d'un module Oneduc"
               class="w-full rounded min-h-[300px] object-cover">
        </div>

      </div>
    </div>
  </section>


{{-- SECTION TÉMOIGNAGES --}}
{{-- TODO : remplacer les 3 placeholders par de vrais verbatims de formateurs --}}
{{-- Fournir : citation, prénom + initiale du nom, rôle et structure (ex: "Formatrice numérique, association X") --}}
<section class="py-20 bg-gray-50">
  <div class="max-w-[1248px] mx-auto px-6">
    <h2 class="text-center text-3xl md:text-4xl font-raleway font-bold text-bleuone mb-4">Ils utilisent Onéduc</h2>
    <p class="text-center text-gray-600 text-lg mb-16 max-w-2xl mx-auto font-lisible">Des formateurs comme vous nous font confiance au quotidien.</p>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 font-lisible">
      @foreach([
        ['quote' => 'TODO : insérer un vrai témoignage de formateur.', 'name' => 'Prénom N.', 'role' => 'Formatrice numérique'],
        ['quote' => 'TODO : insérer un vrai témoignage de formateur.', 'name' => 'Prénom N.', 'role' => 'Éducateur spécialisé'],
        ['quote' => 'TODO : insérer un vrai témoignage de formateur.', 'name' => 'Prénom N.', 'role' => 'Responsable formation, association'],
      ] as $temoignage)
        <div class="bg-white rounded-[20px] shadow-md p-8 space-y-4">
          <svg class="w-8 h-8 text-orangeone" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/>
          </svg>
          <p class="text-gray-700 italic leading-relaxed">« {{ $temoignage['quote'] }} »</p>
          <div>
            <p class="font-semibold text-bleuone">{{ $temoignage['name'] }}</p>
            <p class="text-sm text-gray-500">{{ $temoignage['role'] }}</p>
          </div>
        </div>
      @endforeach
    </div>
  </div>
</section>

{{-- SECTION FAQ - accordéon Alpine.js (plugin @alpinejs/collapse déjà chargé) --}}
<section class="py-20 bg-white" x-data="{ open: null }">
  <div class="max-w-3xl mx-auto px-6">
    <h2 class="text-center text-3xl md:text-4xl font-raleway font-bold text-bleuone mb-4">Questions fréquentes</h2>
    <p class="text-center text-gray-600 text-lg mb-16 font-lisible">Tout ce que vous voulez savoir avant de commencer.</p>

    <div class="space-y-4 font-lisible">
      @foreach([
        ['q' => 'Est-ce vraiment gratuit ?',             'r' => "Oui, Onéduc est entièrement gratuit pour les formateurs et les apprenants. Pas d'abonnement, pas de carte bancaire."],
        ['q' => "Que se passe-t-il après l'inscription ?", 'r' => "Vous accédez directement à votre espace formateur. Vous pouvez créer votre premier groupe, ajouter des contenus et inviter vos apprenants avec un code d'accès."],
        ['q' => 'Faut-il installer quelque chose ?',     'r' => "Non. Onéduc fonctionne entièrement dans votre navigateur internet (Chrome, Firefox, Edge…). Aucune installation n'est nécessaire, ni sur votre ordinateur ni sur celui de vos apprenants."],
        ['q' => 'Mes apprenants doivent-ils créer un compte ?', 'r' => "Non. Vos apprenants rejoignent votre formation en saisissant simplement le code d'accès que vous leur donnez. Pas de compte, pas de mot de passe à retenir."],
      ] as $i => $faq)
        <div class="border border-gray-200 rounded-xl overflow-hidden">
          <button
            type="button"
            class="w-full text-left px-6 py-5 flex justify-between items-center font-varela font-semibold text-bleuone hover:bg-gray-50 transition"
            @click="open = open === {{ $i }} ? null : {{ $i }}"
            :aria-expanded="(open === {{ $i }}).toString()"
          >
            {{ $faq['q'] }}
            <svg :class="{ 'rotate-180': open === {{ $i }} }" class="w-5 h-5 text-orangeone transition-transform flex-shrink-0 ml-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
            </svg>
          </button>
          <div x-show="open === {{ $i }}" x-collapse class="px-6 pb-5 text-gray-700 leading-relaxed">
            {{ $faq['r'] }}
          </div>
        </div>
      @endforeach
    </div>
  </div>
</section>

@endsection
