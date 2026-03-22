@extends('frontend.master')
@section('title', 'Le projet Oneduc.fr')

@section('home')
<div class="max-w-[1248px] mx-auto px-4 pt-8 pb-4">
  <div class="bg-white rounded-[24px] shadow-md p-8 my-10 w-full">
    <div class="grid gap-8 lg:grid-cols-[1.15fr_0.85fr] items-center">
      <div>
        <x-typography variant="titre">Le projet Onéduc.fr</x-typography>
        <x-typography variant="sous-titre" class="font-varela text-sous-titre text-orangeone">
          …en faveur de l’inclusion numérique
        </x-typography>

        <div class="prose-oneduc font-lisible">
          <p>
            Onéduc.fr est né d’un constat de terrain : beaucoup de formateurs, d’éducateurs et de structures souhaitent digitaliser leurs formations, mais se heurtent encore à des outils trop complexes, peu accessibles ou mal adaptés aux réalités pédagogiques.
          </p>
          <p>
            Le projet vise à proposer un environnement de formation plus simple, plus lisible et plus humain, capable de soutenir les pratiques, de sécuriser les parcours des apprenants et de faciliter l’appropriation du numérique, y compris pour les publics les plus éloignés.
          </p>
        </div>
      </div>

      <div class="flex justify-center lg:justify-end">
        <div class="w-full max-w-xs">
          {!! file_get_contents(public_path('images/svg/PointDInterrogation.svg')) !!}
        </div>
      </div>
    </div>
  </div>

  <div class="mt-4">
    <x-oneduc.breadcrumb :items="[['label' => 'Accueil', 'url' => route('index')], ['label' => 'Le projet Onéduc.fr']]" />
  </div>
</div>

<section class="bg-[#f8f7fa] py-16 md:py-20">
  <div class="max-w-[1248px] mx-auto px-4">
    <div class="grid gap-6 lg:grid-cols-[1.1fr_0.9fr]">
      <div class="rounded-[32px] bg-white p-8 md:p-10 border border-slate-200">
        <h2 class="text-3xl md:text-4xl font-raleway font-bold text-bleuone">
          Une genèse ancrée dans le terrain
        </h2>
        <div class="mt-6 space-y-4 text-lg leading-relaxed text-slate-700 font-lisible">
          <p>
            Le projet Oneduc.fr s’est construit à partir d’expériences de terrain menées depuis de nombreuses années auprès de publics fragiles, de personnes en situation de handicap et de professionnels confrontés aux limites des outils numériques existants.
          </p>
          <p>
            Au fil des accompagnements, le même constat est revenu : les plateformes sont souvent trop lourdes, peu inclusives, difficiles à prendre en main et déconnectées des usages réels des formateurs.
          </p>
          <p>
            Oneduc.fr est né de cette volonté de proposer un outil plus juste, plus sobre et plus utile, capable d’aider les professionnels à digitaliser leurs formations sans perdre le sens pédagogique ni la qualité de l’accompagnement.
          </p>
        </div>
      </div>

      <div class="rounded-[32px] bg-bleuone p-8 md:p-10 text-white">
        <p class="text-sm font-varela uppercase tracking-[0.25em] text-orange-200">La promesse</p>
        <h2 class="mt-4 text-3xl font-raleway font-bold leading-tight">
          Redonner de la maîtrise aux acteurs de la formation.
        </h2>
        <div class="mt-6 space-y-4 text-white/85 leading-relaxed font-lisible">
          <p>
            La plateforme a été pensée pour aider les formateurs à organiser leurs parcours, accompagner les apprenants, suivre les progrès et faire évoluer leurs pratiques.
          </p>
          <p>
            Elle ne se limite pas à un dépôt de contenus. Elle cherche à devenir un véritable environnement pédagogique, simple d’usage, progressif et évolutif.
          </p>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="bg-white py-16 md:py-20">
  <div class="max-w-[1248px] mx-auto px-4">
    <div class="grid gap-8 lg:grid-cols-[0.9fr_1.1fr] items-center">
      <div class="relative">
        <div class="absolute -inset-4 rounded-[32px] bg-orangeone/10 blur-2xl"></div>
        <div class="relative overflow-hidden rounded-[32px] border border-slate-200 bg-white shadow-xl">
          <img
            src="{{ asset('upload/formateur_images/202505311622_laurent_staelens.jpg') }}"
            alt="Portrait de Laurent Staelens, fondateur d'Oneduc"
            class="h-full w-full object-cover">
        </div>
      </div>

      <div class="space-y-6">
        <p class="text-sm font-varela uppercase tracking-[0.25em] text-orangeone">Un projet porte par des humains</p>
        <h2 class="text-3xl md:text-4xl font-raleway font-bold text-bleuone">
          Avant d'etre un logiciel, Oneduc est une demarche de terrain.
        </h2>
        <div class="space-y-4 text-lg leading-relaxed text-slate-700 font-lisible">
          <p>
            Le projet est porte par Laurent Staelens et nourri par des retours d'usage de formateurs, d'accompagnants et de structures qui travaillent avec des publics tres differents.
          </p>
          <p>
            Cette dimension humaine compte : elle permet de faire evoluer la plateforme a partir de situations reelles, de besoins pedagogiques concrets et de contraintes souvent invisibles pour les outils plus generalistes.
          </p>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
          <div class="rounded-3xl bg-slate-50 p-5 border border-slate-200">
            <h3 class="text-lg font-semibold text-bleuone">Retours du terrain</h3>
            <p class="mt-2 text-sm leading-relaxed text-slate-600 font-lisible">
              Chaque evolution utile cherche d'abord a simplifier la vie du formateur et l'entree dans la formation pour l'apprenant.
            </p>
          </div>
          <div class="rounded-3xl bg-orange-50 p-5 border border-orangeone/15">
            <h3 class="text-lg font-semibold text-bleuone">Developpement progressif</h3>
            <p class="mt-2 text-sm leading-relaxed text-slate-600 font-lisible">
              La version beta avance pas a pas, avec une logique de construction sobre, utile et testee dans des contextes reels.
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="bg-white py-16 md:py-20">
  <div class="max-w-[1248px] mx-auto px-4">
    <div class="w-full">
      <h2 class="text-3xl md:text-4xl font-raleway font-bold text-bleuone">
        À qui s’adresse Oneduc.fr ?
      </h2>
      <p class="mt-5 text-lg text-slate-700 leading-relaxed font-lisible">
        Oneduc.fr s’adresse d’abord aux formateurs, éducateurs, enseignants et structures qui souhaitent digitaliser leurs formations avec un outil plus accessible et plus lisible. Le projet s’adresse aussi, à travers eux, à des apprenants aux profils variés, notamment débutants, éloignés du numérique ou confrontés à des difficultés d’accessibilité.
      </p>
    </div>

    <div class="mt-10 grid gap-5 md:grid-cols-2 xl:grid-cols-4">
      <div class="rounded-3xl border border-slate-200 p-6 bg-slate-50">
        <h3 class="text-xl font-semibold text-bleuone">Formateurs indépendants</h3>
        <p class="mt-3 text-slate-600 leading-relaxed font-lisible">
          Pour structurer leurs parcours, gérer leurs groupes et suivre leurs apprenants sans complexité inutile.
        </p>
      </div>

      <div class="rounded-3xl border border-slate-200 p-6 bg-slate-50">
        <h3 class="text-xl font-semibold text-bleuone">Organismes et associations</h3>
        <p class="mt-3 text-slate-600 leading-relaxed font-lisible">
          Pour disposer d’un outil adaptable à leurs contextes, à leurs publics et à leurs contraintes de terrain.
        </p>
      </div>

      <div class="rounded-3xl border border-slate-200 p-6 bg-slate-50">
        <h3 class="text-xl font-semibold text-bleuone">Educateurs et accompagnants</h3>
        <p class="mt-3 text-slate-600 leading-relaxed font-lisible">
          Pour proposer des parcours plus progressifs et mieux ajustés à des publics fragiles ou hétérogènes.
        </p>
      </div>

      <div class="rounded-3xl border border-slate-200 p-6 bg-slate-50">
        <h3 class="text-xl font-semibold text-bleuone">Apprenants</h3>
        <p class="mt-3 text-slate-600 leading-relaxed font-lisible">
          Pour accéder à des formations plus lisibles, plus engageantes et mieux accompagnées.
        </p>
      </div>
    </div>
  </div>
</section>

<section class="bg-[#fff7f3] py-16 md:py-20">
  <div class="max-w-[1248px] mx-auto px-4">
    <div class="w-full">
      <h2 class="text-3xl md:text-4xl font-raleway font-bold text-bleuone">
        Les grands enjeux du projet
      </h2>
      <p class="mt-5 text-lg text-slate-700 leading-relaxed font-lisible">
        Les documents de cadrage et de modélisation montrent qu’Oneduc.fr ne répond pas à un seul besoin technique. Le projet articule plusieurs enjeux complémentaires qui structurent sa vision.
      </p>
    </div>

    <div class="mt-10 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
      <div class="rounded-3xl bg-white border border-orangeone/15 p-6">
        <p class="text-sm font-varela uppercase tracking-[0.2em] text-orangeone">01</p>
        <h3 class="mt-3 text-xl font-semibold text-bleuone">Transformer les pratiques pédagogiques</h3>
        <p class="mt-3 text-slate-600 leading-relaxed font-lisible">
          Passer d’une logique de transmission à une logique de parcours, d’activités, de feedbacks et d’accompagnement.
        </p>
      </div>

      <div class="rounded-3xl bg-white border border-orangeone/15 p-6">
        <p class="text-sm font-varela uppercase tracking-[0.2em] text-orangeone">02</p>
        <h3 class="mt-3 text-xl font-semibold text-bleuone">Faire monter les formateurs en compétence numérique</h3>
        <p class="mt-3 text-slate-600 leading-relaxed font-lisible">
          Aider à prendre en main les outils, à comprendre les usages du e-learning et à gagner en autonomie.
        </p>
      </div>

      <div class="rounded-3xl bg-white border border-orangeone/15 p-6">
        <p class="text-sm font-varela uppercase tracking-[0.2em] text-orangeone">03</p>
        <h3 class="mt-3 text-xl font-semibold text-bleuone">Favoriser l’engagement des apprenants</h3>
        <p class="mt-3 text-slate-600 leading-relaxed font-lisible">
          Proposer des parcours plus motivants, plus souples et mieux suivis dans le temps.
        </p>
      </div>

      <div class="rounded-3xl bg-white border border-orangeone/15 p-6">
        <p class="text-sm font-varela uppercase tracking-[0.2em] text-orangeone">04</p>
        <h3 class="mt-3 text-xl font-semibold text-bleuone">Renforcer l’inclusion</h3>
        <p class="mt-3 text-slate-600 leading-relaxed font-lisible">
          Simplifier la navigation, diversifier les supports et rendre les contenus plus accessibles à tous.
        </p>
      </div>

      <div class="rounded-3xl bg-white border border-orangeone/15 p-6">
        <p class="text-sm font-varela uppercase tracking-[0.2em] text-orangeone">05</p>
        <h3 class="mt-3 text-xl font-semibold text-bleuone">Offrir un outil fiable et simple</h3>
        <p class="mt-3 text-slate-600 leading-relaxed font-lisible">
          Réduire les freins techniques, faciliter l’usage quotidien et garantir une expérience stable.
        </p>
      </div>

      <div class="rounded-3xl bg-white border border-orangeone/15 p-6">
        <p class="text-sm font-varela uppercase tracking-[0.2em] text-orangeone">06</p>
        <h3 class="mt-3 text-xl font-semibold text-bleuone">Construire une dynamique durable</h3>
        <p class="mt-3 text-slate-600 leading-relaxed font-lisible">
          Faire vivre le projet dans le temps, à travers des partenariats, des usages réels et un modèle économique soutenable.
        </p>
      </div>
    </div>
  </div>
</section>

<section class="bg-white py-16 md:py-20">
  <div class="max-w-[1248px] mx-auto px-4">
    <div class="grid gap-6 lg:grid-cols-2">
      <div class="rounded-[32px] border border-slate-200 p-8 md:p-10">
        <h2 class="text-3xl font-raleway font-bold text-bleuone">
          Une approche pédagogique pensée pour l’usage réel
        </h2>
        <div class="mt-6 space-y-4 text-slate-700 leading-relaxed font-lisible">
          <p>
            La plateforme n’a pas été imaginée comme un simple espace de consultation. Les travaux de modélisation montrent une logique de parcours progressif, de micro-learning, d’aide contextuelle et de mise en pratique directe.
          </p>
          <p>
            L’objectif est que le formateur puisse apprendre dans un environnement proche de son usage professionnel réel, avec des ressources courtes, des repères clairs et des aides mobilisables au bon moment.
          </p>
          <p>
            Cette logique vise à réduire les freins techniques, soutenir l’autonomie et favoriser une appropriation progressive de l’outil.
          </p>
        </div>
      </div>

      <div class="rounded-[32px] bg-slate-950 p-8 md:p-10 text-white">
        <h2 class="text-3xl font-raleway font-bold">
          Ce que la plateforme cherche à rendre possible
        </h2>
        <div class="mt-6 grid gap-4">
          <div class="rounded-2xl bg-white/10 p-4">
            <p class="font-semibold text-orange-200">Pour le formateur</p>
            <p class="mt-2 text-white/80 font-lisible">
              Piloter ses groupes, organiser ses modules, suivre les progrès et ajuster ses parcours.
            </p>
          </div>
          <div class="rounded-2xl bg-white/10 p-4">
            <p class="font-semibold text-orange-200">Pour l’apprenant</p>
            <p class="mt-2 text-white/80 font-lisible">
              Bénéficier d’un parcours plus fluide, plus lisible, plus interactif et mieux accompagné.
            </p>
          </div>
          <div class="rounded-2xl bg-white/10 p-4">
            <p class="font-semibold text-orange-200">Pour les structures</p>
            <p class="mt-2 text-white/80 font-lisible">
              Disposer d’un outil pédagogique évolutif, capable d’articuler accès, suivi, animation et qualité.
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="bg-[#f8f7fa] py-16 md:py-20">
  <div class="max-w-[1248px] mx-auto px-4">
    <div class="w-full">
      <h2 class="text-3xl md:text-4xl font-raleway font-bold text-bleuone">
        Les bénéfices attendus
      </h2>
      <p class="mt-5 text-lg text-slate-700 leading-relaxed font-lisible">
        À terme, le projet doit permettre d’améliorer à la fois l’autonomie des formateurs, la qualité pédagogique des parcours et l’accessibilité des formations pour les apprenants.
      </p>
    </div>

    <div class="mt-10 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
      <div class="rounded-3xl bg-white p-6 border border-slate-200">
        <h3 class="text-xl font-semibold text-bleuone">Autonomie accrue</h3>
        <p class="mt-3 text-slate-600 leading-relaxed font-lisible">
          Les formateurs gagnent en maîtrise dans l’organisation et la personnalisation de leurs parcours.
        </p>
      </div>

      <div class="rounded-3xl bg-white p-6 border border-slate-200">
        <h3 class="text-xl font-semibold text-bleuone">Gain de temps</h3>
        <p class="mt-3 text-slate-600 leading-relaxed font-lisible">
          La gestion des groupes, le suivi des progressions et certains traitements peuvent être facilités.
        </p>
      </div>

      <div class="rounded-3xl bg-white p-6 border border-slate-200">
        <h3 class="text-xl font-semibold text-bleuone">Suivi pédagogique plus lisible</h3>
        <p class="mt-3 text-slate-600 leading-relaxed font-lisible">
          Les indicateurs de progression, de réussite ou de difficulté deviennent plus exploitables.
        </p>
      </div>

      <div class="rounded-3xl bg-white p-6 border border-slate-200">
        <h3 class="text-xl font-semibold text-bleuone">Parcours plus cohérents</h3>
        <p class="mt-3 text-slate-600 leading-relaxed font-lisible">
          La plateforme soutient une structuration plus claire des objectifs, des activités et des ressources.
        </p>
      </div>

      <div class="rounded-3xl bg-white p-6 border border-slate-200">
        <h3 class="text-xl font-semibold text-bleuone">Accessibilité renforcée</h3>
        <p class="mt-3 text-slate-600 leading-relaxed font-lisible">
          L’ambition est de réduire les obstacles d’accès à la formation pour des publics variés et parfois fragiles.
        </p>
      </div>

      <div class="rounded-3xl bg-white p-6 border border-slate-200">
        <h3 class="text-xl font-semibold text-bleuone">Dynamique collective</h3>
        <p class="mt-3 text-slate-600 leading-relaxed font-lisible">
          Le projet favorise aussi l’entraide, le partage de pratiques et la construction d’une communauté autour des usages.
        </p>
      </div>
    </div>
  </div>
</section>

<section class="bg-white py-16 md:py-20">
  <div class="max-w-[1248px] mx-auto px-4">
    <div class="grid gap-6 lg:grid-cols-[0.95fr_1.05fr]">
      <div class="rounded-[32px] bg-orangeone p-8 md:p-10 text-white">
        <p class="text-sm font-varela uppercase tracking-[0.25em] text-orange-100">Association Oneduc</p>
        <h2 class="mt-4 text-3xl md:text-4xl font-raleway font-bold leading-tight">
          Une structure associative pour faire vivre et développer le projet.
        </h2>
      </div>

      <div class="rounded-[32px] border border-slate-200 p-8 md:p-10">
        <div class="space-y-4 text-slate-700 leading-relaxed font-lisible">
          <p class="font-semibold text-bleuone">
            Le choix associatif permet de garder le cap sur l'utilite pedagogique et l'accessibilite du projet.
          </p>
          <p>
            Les statuts de l’association précisent qu’Oneduc a pour objet de créer des outils numériques et éducatifs, de promouvoir leurs usages dans les champs de la formation, de l’éducation et de l’enseignement, et d’accompagner la digitalisation des parcours à travers la plateforme Oneduc.fr.
          </p>
          <p>
            L’association porte donc une ambition concrète : soutenir les usages, structurer le développement du projet et fédérer des acteurs engagés autour d’une vision pédagogique inclusive.
          </p>
          <p>
            Cette dimension associative donne au projet une assise collective et ouvre des perspectives de partenariats, d’accompagnement et de diffusion plus larges.
          </p>
        </div>

        <div class="mt-8 grid gap-4 md:grid-cols-3">
          <div class="rounded-2xl bg-slate-50 p-4">
            <p class="text-sm font-semibold text-bleuone">Gouvernance</p>
            <p class="mt-2 text-sm text-slate-600 font-lisible">
              L'association donne un cadre collectif au projet et clarifie ses orientations.
            </p>
          </div>
          <div class="rounded-2xl bg-slate-50 p-4">
            <p class="text-sm font-semibold text-bleuone">Soutenabilite</p>
            <p class="mt-2 text-sm text-slate-600 font-lisible">
              Les adhesions et soutiens servent a faire vivre l'hebergement, les evolutions et l'accompagnement.
            </p>
          </div>
          <div class="rounded-2xl bg-slate-50 p-4">
            <p class="text-sm font-semibold text-bleuone">Ethique</p>
            <p class="mt-2 text-sm text-slate-600 font-lisible">
              Le cadre loi 1901 renforce la lisibilite du projet pour les partenaires et les structures engagees.
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="bg-slate-950 py-16 md:py-20">
  <div class="max-w-[1248px] mx-auto px-4">
    <div class="w-full rounded-[36px] border border-white/10 bg-white/[0.04] p-8 md:p-12">
      <p class="text-sm font-varela uppercase tracking-[0.25em] text-orange-200">Vision</p>
      <h2 class="mt-4 text-3xl md:text-5xl font-raleway font-bold text-white leading-tight">
        Oneduc.fr n’a pas vocation à être seulement une plateforme de plus.
      </h2>
      <div class="mt-6 space-y-5 text-lg leading-relaxed text-white/80 font-lisible">
        <p>
          Le projet porte une vision plus large : accompagner la transformation des pratiques pédagogiques, soutenir la montée en compétence numérique des formateurs et rendre les parcours de formation plus accessibles, plus lisibles et plus adaptables.
        </p>
        <p>
          Son développement s’inscrit dans le temps long, avec une recherche d’équilibre entre utilité sociale, qualité pédagogique, ancrage terrain et modèle de fonctionnement durable.
        </p>
      </div>

      <div class="mt-8 flex flex-wrap gap-4">
        <a href="{{ route('contact') }}" class="btn-oneduc">
          Nous contacter
        </a>
        <a href="{{ route('association') }}" class="inline-flex items-center justify-center rounded-full border-2 border-white/30 px-8 py-3 text-lg font-varela text-white transition-all duration-300 hover:border-white hover:bg-white hover:text-slate-950">
          Decouvrir l'association
        </a>
      </div>
    </div>
  </div>
</section>
@endsection
