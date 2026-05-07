@extends('frontend.master')
@section('title', 'Le projet Onéduc.fr')

@section('home')
<section class="relative overflow-hidden bg-gray-50 py-16 md:py-20">
  <div class="pointer-events-none absolute inset-0 overflow-hidden" aria-hidden="true">
    <img src="{{ asset('frontend/assets/img/front-pages/akene-seeds/seed-11.svg') }}" alt="" class="absolute left-6 top-16 w-24 -rotate-12 opacity-10 md:left-20 md:w-36">
    <img src="{{ asset('frontend/assets/img/front-pages/akene-seeds/seed-18.svg') }}" alt="" class="absolute right-8 top-32 w-16 rotate-[18deg] opacity-10 md:right-24 md:w-24">
    <img src="{{ asset('frontend/assets/img/front-pages/akene-seeds/seed-23.svg') }}" alt="" class="absolute -right-8 bottom-16 w-28 rotate-12 opacity-10 md:right-10 md:w-44">
  </div>

  <div class="relative mx-auto max-w-[1248px] px-6">
    <x-oneduc.breadcrumb :items="[['label' => 'Accueil', 'url' => route('index')], ['label' => 'Le projet Onéduc.fr']]" />

    <div class="mt-10 grid items-center gap-14 lg:grid-cols-[1.05fr_0.95fr]">
      <div>
        <h1 class="flex items-center gap-4 font-raleway text-[36px] font-extrabold leading-tight text-bleuone md:text-[48px]">
          <img src="{{ asset('frontend/assets/img/front-pages/icons/etoile8.gif') }}" width="60" height="61" alt="" aria-hidden="true" class="h-[60px] w-[60px] flex-none object-contain">
          <span>Le projet Onéduc.fr</span>
        </h1>

        <p class="mt-6 max-w-[66ch] font-lisible text-xl leading-relaxed text-slate-600">
          Onéduc.fr est né d’un constat de terrain : les formateurs veulent digitaliser leurs parcours, mais les outils restent souvent trop complexes, peu lisibles ou mal adaptés aux publics accompagnés.
        </p>

        <p class="mt-5 max-w-[66ch] font-lisible text-lg leading-relaxed text-slate-600">
          Le projet vise un environnement plus simple, plus humain et plus accessible, capable de soutenir les pratiques pédagogiques sans éloigner les apprenants du sens de la formation.
        </p>

        <div class="mt-8 flex flex-wrap gap-4">
          <a href="{{ route('adhesion') }}" class="btn-oneduc">Soutenir le projet</a>
          <a href="{{ route('association') }}" class="btn-oneduc-outline">Découvrir l'association</a>
        </div>
      </div>

      <div class="group relative mx-auto w-full max-w-md lg:max-w-lg">
        <div class="absolute -inset-3 rounded-[28px] border-2 border-orangeone/35 bg-orangeone/5 rotate-2 transition-transform duration-500 group-hover:rotate-0"></div>
        <div class="relative rounded-[28px] bg-white p-8 shadow-xl shadow-slate-200/80 transition-transform duration-500 group-hover:-translate-y-2">
          {!! file_get_contents(public_path('images/svg/PointDInterrogation.svg')) !!}
        </div>
      </div>
    </div>
  </div>
</section>

<section class="bg-white py-16 md:py-20">
  <div class="mx-auto max-w-[1248px] px-6">
    <div class="grid gap-6 lg:grid-cols-[1.1fr_0.9fr]">
      <article class="rounded-lg border border-slate-200 bg-white p-7 shadow-sm shadow-slate-200/70 md:p-9">
        <h2 class="font-raleway text-[34px] font-extrabold leading-tight text-bleuone md:text-[40px]">Une genèse ancrée dans le terrain</h2>
        <div class="mt-6 space-y-4 font-lisible text-lg leading-relaxed text-slate-600">
          <p>
            Le projet s’est construit à partir d’expériences menées auprès de publics fragiles, de personnes en situation de handicap et de professionnels confrontés aux limites des outils numériques existants.
          </p>
          <p>
            Au fil des accompagnements, le même constat revient : les plateformes sont souvent lourdes, difficiles à prendre en main et déconnectées des usages réels des formateurs.
          </p>
        </div>
      </article>

      <article class="rounded-lg bg-bleuone p-7 text-white shadow-xl shadow-bleuone/20 md:p-9">
        <p class="font-lisible text-sm font-semibold uppercase tracking-[0.18em] text-orange-200">La promesse</p>
        <h2 class="mt-4 font-raleway text-[34px] font-extrabold leading-tight md:text-[40px]">Redonner de la maîtrise aux acteurs de la formation.</h2>
        <p class="mt-6 font-lisible text-lg leading-relaxed text-white/82">
          Onéduc.fr aide les formateurs à organiser leurs parcours, accompagner les apprenants, suivre les progrès et faire évoluer leurs pratiques avec des outils simples et progressifs.
        </p>
      </article>
    </div>
  </div>
</section>

<section class="relative overflow-hidden bg-gray-50 py-16 md:py-20">
  <img src="{{ asset('frontend/assets/img/front-pages/akene-seeds/seed-21.svg') }}" alt="" aria-hidden="true" class="absolute left-[6%] bottom-14 w-16 rotate-[24deg] opacity-10 md:w-24">
  <div class="relative mx-auto max-w-[1248px] px-6">
    <div class="grid items-center gap-14 lg:grid-cols-[0.9fr_1.1fr]">
      <div class="group relative mx-auto w-full max-w-md lg:mx-0">
        <div class="absolute -inset-3 rounded-[28px] border-2 border-orangeone/35 bg-orangeone/5 -rotate-2 transition-transform duration-500 group-hover:rotate-0"></div>
        <div class="relative overflow-hidden rounded-[28px] bg-white p-4 shadow-xl shadow-slate-200/80 transition-transform duration-500 group-hover:-translate-y-2">
          <img
            src="{{ asset('upload/formateur_images/202505311622_laurent_staelens.jpg') }}"
            alt="Portrait de Laurent Staelens, fondateur d'Onéduc"
            class="h-full w-full rounded-2xl object-cover">
        </div>
      </div>

      <div class="space-y-6">
        <h2 class="flex items-center gap-4 font-raleway text-[34px] font-extrabold leading-tight text-bleuone md:text-[40px]">
          <img src="{{ asset('frontend/assets/img/front-pages/icons/etoile8.gif') }}" width="60" height="61" alt="" aria-hidden="true" class="h-[60px] w-[60px] flex-none object-contain">
          <span>Avant d’être un logiciel, Onéduc est une démarche de terrain.</span>
        </h2>
        <div class="space-y-4 font-lisible text-lg leading-relaxed text-slate-600">
          <p>
            Le projet est porté par Laurent Staelens et nourri par des retours d’usage de formateurs, d’accompagnants et de structures qui travaillent avec des publics très différents.
          </p>
          <p>
            Cette dimension humaine permet de faire évoluer la plateforme à partir de situations réelles, de besoins pédagogiques concrets et de contraintes souvent invisibles pour les outils généralistes.
          </p>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
          <div class="rounded-lg border border-slate-200 bg-white p-5">
            <p class="font-semibold text-bleuone">Retours du terrain</p>
            <p class="mt-2 font-lisible text-sm leading-relaxed text-slate-600">Chaque évolution utile cherche d’abord à simplifier la vie du formateur.</p>
          </div>
          <div class="rounded-lg border border-orangeone/20 bg-orangeone/5 p-5">
            <p class="font-semibold text-bleuone">Développement progressif</p>
            <p class="mt-2 font-lisible text-sm leading-relaxed text-slate-600">La version bêta avance pas à pas, dans une logique sobre, utile et testée.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="bg-white py-16 md:py-20">
  <div class="mx-auto max-w-[1248px] px-6">
    <div class="mx-auto max-w-3xl text-center">
      <h2 class="font-raleway text-[34px] font-extrabold leading-tight text-bleuone md:text-[40px]">À qui s’adresse Onéduc.fr ?</h2>
      <p class="mt-5 font-lisible text-lg leading-relaxed text-slate-600">
        Aux formateurs, éducateurs, enseignants et structures qui souhaitent digitaliser leurs formations avec un outil plus accessible, plus lisible et plus proche du terrain.
      </p>
    </div>

    <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
      @foreach([
        ['title' => 'Formateurs indépendants', 'desc' => 'Structurer leurs parcours, gérer leurs groupes et suivre leurs apprenants sans complexité inutile.', 'color' => 'bg-orangeone'],
        ['title' => 'Organismes et associations', 'desc' => 'Disposer d’un outil adaptable à leurs contextes, leurs publics et leurs contraintes de terrain.', 'color' => 'bg-bleuone'],
        ['title' => 'Éducateurs et accompagnants', 'desc' => 'Proposer des parcours progressifs et mieux ajustés à des publics fragiles ou hétérogènes.', 'color' => 'bg-vertone'],
        ['title' => 'Apprenants', 'desc' => 'Accéder à des formations plus lisibles, plus engageantes et mieux accompagnées.', 'color' => 'bg-orangeone'],
      ] as $public)
        <article class="relative overflow-hidden rounded-lg border border-slate-200 bg-white p-6 shadow-sm shadow-slate-200/70">
          <span class="absolute inset-x-5 top-0 h-1 rounded-full {{ $public['color'] }}"></span>
          <h3 class="font-raleway text-xl font-bold text-bleuone">{{ $public['title'] }}</h3>
          <p class="mt-3 font-lisible leading-relaxed text-slate-600">{{ $public['desc'] }}</p>
        </article>
      @endforeach
    </div>
  </div>
</section>

<section class="relative overflow-hidden bg-gray-50 py-16 md:py-20">
  <img src="{{ asset('frontend/assets/img/front-pages/akene-seeds/seed-15.svg') }}" alt="" aria-hidden="true" class="absolute right-8 top-16 w-20 rotate-[18deg] opacity-10 md:right-20 md:w-28">
  <div class="relative mx-auto max-w-[1248px] px-6">
    <div class="mx-auto max-w-3xl text-center">
      <h2 class="font-raleway text-[34px] font-extrabold leading-tight text-bleuone md:text-[40px]">Les grands enjeux du projet</h2>
      <p class="mt-5 font-lisible text-lg leading-relaxed text-slate-600">
        Onéduc.fr ne répond pas à un seul besoin technique. Le projet articule plusieurs enjeux complémentaires qui structurent sa vision.
      </p>
    </div>

    <div class="mt-10 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
      @foreach([
        ['n' => '01', 'title' => 'Transformer les pratiques pédagogiques', 'desc' => 'Passer d’une logique de transmission à une logique de parcours, d’activités, de feedbacks et d’accompagnement.'],
        ['n' => '02', 'title' => 'Faire monter les formateurs en compétence', 'desc' => 'Aider à prendre en main les outils, comprendre les usages du e-learning et gagner en autonomie.'],
        ['n' => '03', 'title' => 'Favoriser l’engagement des apprenants', 'desc' => 'Proposer des parcours plus motivants, plus souples et mieux suivis dans le temps.'],
        ['n' => '04', 'title' => 'Renforcer l’inclusion', 'desc' => 'Simplifier la navigation, diversifier les supports et rendre les contenus plus accessibles à tous.'],
        ['n' => '05', 'title' => 'Offrir un outil fiable et simple', 'desc' => 'Réduire les freins techniques, faciliter l’usage quotidien et garantir une expérience stable.'],
        ['n' => '06', 'title' => 'Construire une dynamique durable', 'desc' => 'Faire vivre le projet à travers des partenariats, des usages réels et un modèle soutenable.'],
      ] as $enjeu)
        <article class="rounded-lg border border-orangeone/20 bg-white p-6 shadow-sm shadow-slate-200/70">
          <p class="font-lisible text-sm font-bold uppercase tracking-[0.18em] text-orangeone">{{ $enjeu['n'] }}</p>
          <h3 class="mt-3 font-raleway text-xl font-bold leading-snug text-bleuone">{{ $enjeu['title'] }}</h3>
          <p class="mt-3 font-lisible leading-relaxed text-slate-600">{{ $enjeu['desc'] }}</p>
        </article>
      @endforeach
    </div>
  </div>
</section>

<section class="bg-white py-16 md:py-20">
  <div class="mx-auto max-w-[1248px] px-6">
    <div class="grid gap-6 lg:grid-cols-2">
      <article class="rounded-lg border border-slate-200 bg-white p-7 shadow-sm shadow-slate-200/70 md:p-9">
        <h2 class="font-raleway text-[34px] font-extrabold leading-tight text-bleuone md:text-[40px]">Les bénéfices attendus</h2>
        <p class="mt-5 font-lisible text-lg leading-relaxed text-slate-600">
          À terme, le projet doit améliorer l’autonomie des formateurs, la qualité pédagogique des parcours et l’accessibilité des formations pour les apprenants.
        </p>
        <div class="mt-8 grid gap-3 sm:grid-cols-2">
          @foreach(['Autonomie accrue', 'Gain de temps', 'Suivi pédagogique lisible', 'Parcours plus cohérents', 'Accessibilité renforcée', 'Dynamique collective'] as $benefice)
            <div class="rounded-lg bg-gray-50 p-4 font-lisible font-semibold text-bleuone">{{ $benefice }}</div>
          @endforeach
        </div>
      </article>

      <article class="rounded-lg bg-slate-950 p-7 text-white shadow-xl shadow-slate-900/20 md:p-9">
        <h2 class="font-raleway text-[34px] font-extrabold leading-tight md:text-[40px]">Ce que la plateforme cherche à rendre possible</h2>
        <div class="mt-6 grid gap-4">
          @foreach([
            ['title' => 'Pour le formateur', 'desc' => 'Piloter ses groupes, organiser ses modules, suivre les progrès et ajuster ses parcours.'],
            ['title' => 'Pour l’apprenant', 'desc' => 'Bénéficier d’un parcours plus fluide, plus lisible, plus interactif et mieux accompagné.'],
            ['title' => 'Pour les structures', 'desc' => 'Disposer d’un outil pédagogique évolutif, capable d’articuler accès, suivi, animation et qualité.'],
          ] as $item)
            <div class="rounded-lg bg-white/10 p-4">
              <p class="font-semibold text-orange-200">{{ $item['title'] }}</p>
              <p class="mt-2 font-lisible text-white/80">{{ $item['desc'] }}</p>
            </div>
          @endforeach
        </div>
      </article>
    </div>
  </div>
</section>

<section class="bg-gray-50 py-16 md:py-20">
  <div class="mx-auto max-w-[1248px] px-6">
    <div class="grid gap-6 lg:grid-cols-[0.95fr_1.05fr]">
      <article class="rounded-lg bg-orangeone p-7 text-white shadow-xl shadow-orangeone/20 md:p-9">
        <p class="font-lisible text-sm font-semibold uppercase tracking-[0.18em] text-orange-100">Association Onéduc</p>
        <h2 class="mt-4 font-raleway text-[34px] font-extrabold leading-tight md:text-[40px]">Une structure associative pour faire vivre et développer le projet.</h2>
      </article>

      <article class="rounded-lg border border-slate-200 bg-white p-7 shadow-sm shadow-slate-200/70 md:p-9">
        <div class="space-y-4 font-lisible text-lg leading-relaxed text-slate-600">
          <p class="font-semibold text-bleuone">
            Le choix associatif permet de garder le cap sur l’utilité pédagogique et l’accessibilité du projet.
          </p>
          <p>
            L’association soutient les usages, structure le développement et fédère des acteurs engagés autour d’une vision pédagogique inclusive.
          </p>
          <p>
            Elle donne au projet une assise collective et ouvre des perspectives de partenariats, d’accompagnement et de diffusion plus larges.
          </p>
        </div>

        <div class="mt-8 grid gap-4 md:grid-cols-3">
          @foreach([
            ['title' => 'Gouvernance', 'desc' => 'Un cadre collectif pour clarifier les orientations.'],
            ['title' => 'Soutenabilité', 'desc' => 'Des soutiens pour faire vivre l’hébergement, les évolutions et l’accompagnement.'],
            ['title' => 'Éthique', 'desc' => 'Un cadre lisible pour les partenaires et les structures engagées.'],
          ] as $axis)
            <div class="rounded-lg bg-gray-50 p-4">
              <p class="font-semibold text-bleuone">{{ $axis['title'] }}</p>
              <p class="mt-2 font-lisible text-sm leading-relaxed text-slate-600">{{ $axis['desc'] }}</p>
            </div>
          @endforeach
        </div>
      </article>
    </div>
  </div>
</section>

<section class="bg-slate-950 py-16 md:py-20">
  <div class="mx-auto max-w-[1248px] px-6">
    <div class="rounded-lg border border-white/10 bg-white/[0.04] p-8 md:p-12">
      <p class="font-lisible text-sm font-semibold uppercase tracking-[0.18em] text-orange-200">Vision</p>
      <h2 class="mt-4 max-w-4xl font-raleway text-[34px] font-extrabold leading-tight text-white md:text-[40px]">
        Onéduc.fr n’a pas vocation à être seulement une plateforme de plus.
      </h2>
      <div class="mt-6 max-w-4xl space-y-5 font-lisible text-lg leading-relaxed text-white/80">
        <p>
          Le projet porte une vision plus large : accompagner la transformation des pratiques pédagogiques, soutenir la montée en compétence numérique des formateurs et rendre les parcours plus accessibles, plus lisibles et plus adaptables.
        </p>
        <p>
          Son développement s’inscrit dans le temps long, avec une recherche d’équilibre entre utilité sociale, qualité pédagogique, ancrage terrain et modèle de fonctionnement durable.
        </p>
      </div>

      <div class="mt-8 flex flex-wrap gap-4">
        <a href="{{ route('contact') }}" class="btn-oneduc">Nous contacter</a>
        <a href="{{ route('association') }}" class="inline-flex items-center justify-center rounded-full border-2 border-white/30 px-8 py-3 font-lisible font-semibold text-white transition-all duration-300 hover:border-white hover:bg-white hover:text-slate-950">
          Découvrir l'association
        </a>
      </div>
    </div>
  </div>
</section>
@endsection
