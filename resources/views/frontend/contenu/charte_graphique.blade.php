@extends('frontend.master')

@section('title', 'Charte graphique - Oneduc.fr')
@section('description', 'Référentiel visuel d Oneduc : couleurs, typographies, logo, iconographie, composants d interface et principes d usage.')

@section('home')
@php
  $principes = [
    [
      'index' => '01',
      'title' => 'Clarté immédiate',
      'text' => 'Chaque écran doit rester lisible, respirant et simple à parcourir, même pour un public peu à l aise avec le numérique.',
    ],
    [
      'index' => '02',
      'title' => 'Chaleur pédagogique',
      'text' => 'L identité visuelle doit inspirer la confiance, l accompagnement et la proximité, sans tomber dans une esthétique froide ou trop institutionnelle.',
    ],
    [
      'index' => '03',
      'title' => 'Progression guidée',
      'text' => 'La hiérarchie visuelle doit aider l utilisateur à savoir où il est, quoi faire ensuite et comment revenir en arrière.',
    ],
    [
      'index' => '04',
      'title' => 'Accessibilité concrète',
      'text' => 'Contrastes, tailles de texte, formes généreuses et contenus lisibles restent prioritaires sur les effets purement décoratifs.',
    ],
  ];

  $couleurs = [
    [
      'name' => 'Bleu Oneduc',
      'hex' => '#004461',
      'text' => 'text-white',
      'usage' => 'Couleur de structure : titres, navigation, confiance, éléments cadres.',
    ],
    [
      'name' => 'Orange Oneduc',
      'hex' => '#E94D2A',
      'text' => 'text-white',
      'usage' => 'Couleur d action : appels à l action, accents, surlignage éditorial.',
    ],
    [
      'name' => 'Vert repère',
      'hex' => '#01C69C',
      'text' => 'text-slate-950',
      'usage' => 'Couleur de validation ou de signal positif, à utiliser avec parcimonie.',
    ],
    [
      'name' => 'Blanc cassé',
      'hex' => '#F8F7FA',
      'text' => 'text-slate-900',
      'usage' => 'Fond principal du site, permet de garder une interface lumineuse et douce.',
    ],
    [
      'name' => 'Brume bleutée',
      'hex' => '#E7EEF3',
      'text' => 'text-slate-900',
      'usage' => 'Fonds secondaires, séparateurs, encadrements discrets.',
    ],
    [
      'name' => 'Ardoise lisible',
      'hex' => '#334155',
      'text' => 'text-white',
      'usage' => 'Texte courant, compléments d information et contrastes de lecture.',
    ],
  ];

  $typos = [
    [
      'name' => 'Raleway',
      'role' => 'Titres et prises de parole fortes',
      'class' => 'font-raleway',
      'sample' => 'Une plateforme pensée pour accompagner la progression.',
      'details' => 'À utiliser pour les titres de pages, sections majeures et messages de marque.',
    ],
    [
      'name' => 'Varela Round',
      'role' => 'Interface, navigation et labels',
      'class' => 'font-varela',
      'sample' => 'Des repères simples, accueillants et immédiatement compréhensibles.',
      'details' => 'À réserver aux boutons, sous-titres, menus, filtres et repères de navigation.',
    ],
    [
      'name' => 'OpenDyslexic / Arial',
      'role' => 'Lecture longue et contenus pédagogiques',
      'class' => 'font-lisible',
      'sample' => 'Oneduc privilégie une lecture confortable, espacée et tolérante pour des publics très variés.',
      'details' => 'Pour les paragraphes, les descriptions, les explications et les contenus d accompagnement.',
    ],
  ];

  $regles = [
    'Conserver des angles arrondis et une sensation d interface accueillante plutôt que rigide.',
    'Privilégier les contrastes forts entre bleu, orange et fonds clairs.',
    'Limiter les effets visuels décoratifs quand ils nuisent à la lecture ou à la compréhension.',
    'Maintenir des boutons d action principaux en orange et des repères structurels en bleu.',
  ];

  $bonnesPratiques = [
    'Utiliser une icône simple en tracé fin pour les actions rapides.',
    'Réserver la couleur orange aux actions importantes ou aux accents de hiérarchie.',
    'Prévoir une confirmation avant toute suppression irréversible.',
    'Laisser de l espace entre les blocs pour réduire la charge cognitive.',
  ];

  $aEviter = [
    'Multiplier les styles d icônes différents sur une même vue.',
    'Utiliser un rouge saturé partout alors que l univers Oneduc repose d abord sur le bleu et l orange.',
    'Surcharger les écrans avec trop de bordures épaisses ou d ombres lourdes.',
    'Employer des intitulés vagues quand une consigne directe peut mieux guider l utilisateur.',
  ];

  $trashIcons = [
    ['index' => '01', 'title' => 'Classique arrondie', 'note' => 'Une forme douce et familière, proche d une action pédagogique.' ],
    ['index' => '02', 'title' => 'Fine et verticale', 'note' => 'Une proposition discrète, légère et facile à intégrer dans des tableaux.' ],
    ['index' => '03', 'title' => 'Compacte à lattes', 'note' => 'Un dessin plus dense, utile quand l icône doit rester très lisible en petit.' ],
    ['index' => '04', 'title' => 'Équilibrée trois traits', 'note' => 'Un style intermédiaire entre interface moderne et repère classique.' ],
    ['index' => '05', 'title' => 'Couvercle mobile', 'note' => 'Une variante plus expressive pour signifier une action volontaire.' ],
    ['index' => '06', 'title' => 'Corps arrondi', 'note' => 'Un rendu un peu plus chaleureux, cohérent avec les formes Oneduc.' ],
    ['index' => '07', 'title' => 'Très structurée', 'note' => 'Une icône nette et rassurante, adaptée aux interfaces de gestion.' ],
    ['index' => '08', 'title' => 'Monobloc stable', 'note' => 'Une silhouette franche, efficace pour les écrans d administration.' ],
    ['index' => '09', 'title' => 'Minimaliste', 'note' => 'Une interprétation très simple, presque signalétique.' ],
    ['index' => '10', 'title' => 'Suppression affirmée', 'note' => 'Une version plus marquée pour signaler une action sensible.' ],
  ];
@endphp

<div class="relative overflow-hidden bg-[#f8f7fa]">
  <div class="absolute inset-x-0 top-0 h-[460px] bg-gradient-to-br from-orangeone/10 via-white to-bleuone/10"></div>
  <div class="absolute left-0 top-20 h-56 w-56 rounded-full bg-orangeone/10 blur-3xl"></div>
  <div class="absolute right-0 top-28 h-72 w-72 rounded-full bg-bleuone/10 blur-3xl"></div>

  <div class="relative mx-auto max-w-[1248px] px-4 pt-8 pb-16">
    <div class="overflow-hidden rounded-[32px] border border-white/70 bg-white/90 shadow-xl backdrop-blur">
      <div class="grid gap-8 px-6 py-8 md:px-10 md:py-10 lg:grid-cols-[1.15fr_0.85fr] lg:items-center">
        <div>
          <p class="inline-flex items-center rounded-full border border-orangeone/20 bg-orangeone/10 px-4 py-1 text-sm font-varela text-orangeone">
            Référentiel visuel officiel
          </p>
          <h1 class="mt-5 font-raleway text-4xl font-medium leading-tight text-bleuone md:text-6xl">
            Charte graphique
          </h1>
          <p class="mt-3 font-varela text-2xl text-orangeone md:text-3xl">
            Références visuelles et usages de la marque
          </p>
          <p class="mt-6 max-w-2xl font-lisible text-lg leading-relaxed text-slate-700">
            Cette page fixe les repères visuels de la marque Oneduc : couleurs, typographies, logo, iconographie, composants d interface et principes d usage. Elle sert de base commune pour garder une expérience cohérente, inclusive et identifiable sur tout le site.
          </p>

          <div class="mt-8 flex flex-wrap gap-3">
            <a href="#couleurs" class="btn-oneduc">
              Voir la palette
            </a>
            <a href="#interfaces" class="btn-oneduc-outline">
              Voir les composants
            </a>
          </div>

          <div class="mt-8 flex flex-wrap gap-3 text-sm font-varela text-slate-600">
            <a href="#fondamentaux" class="rounded-full border border-slate-200 bg-white px-4 py-2 transition hover:border-orangeone hover:text-orangeone">Fondamentaux</a>
            <a href="#couleurs" class="rounded-full border border-slate-200 bg-white px-4 py-2 transition hover:border-orangeone hover:text-orangeone">Couleurs</a>
            <a href="#typographies" class="rounded-full border border-slate-200 bg-white px-4 py-2 transition hover:border-orangeone hover:text-orangeone">Typographies</a>
            <a href="#logos" class="rounded-full border border-slate-200 bg-white px-4 py-2 transition hover:border-orangeone hover:text-orangeone">Logos</a>
            <a href="#interfaces" class="rounded-full border border-slate-200 bg-white px-4 py-2 transition hover:border-orangeone hover:text-orangeone">Interface</a>
            <a href="#icones" class="rounded-full border border-slate-200 bg-white px-4 py-2 transition hover:border-orangeone hover:text-orangeone">Icônes</a>
            <a href="#ton" class="rounded-full border border-slate-200 bg-white px-4 py-2 transition hover:border-orangeone hover:text-orangeone">Ton éditorial</a>
          </div>
        </div>

        <div class="grid gap-4">
          <div class="rounded-[28px] bg-bleuone p-6 text-white shadow-lg shadow-bleuone/15">
            <p class="text-sm font-varela uppercase tracking-[0.24em] text-orange-200">Signature</p>
            <div class="mt-5 rounded-[22px] bg-white p-5">
              <img src="{{ asset('frontend/assets/img/front-pages/branding/LogoOneducPositionG.svg') }}" alt="Logo Oneduc sur fond clair" class="h-16 w-auto">
            </div>
            <p class="mt-4 font-lisible text-sm leading-relaxed text-white/85">
              Une identité visuelle chaleureuse, structurée et rassurante, pensée pour des contextes de formation où la lisibilité reste prioritaire.
            </p>
          </div>

          <div class="grid gap-4 md:grid-cols-2">
            <div class="rounded-[24px] border border-slate-200 bg-[#f8f7fa] p-5">
              <p class="text-sm font-varela uppercase tracking-[0.2em] text-bleuone">3 couleurs repères</p>
              <p class="mt-3 font-lisible text-sm text-slate-600">Bleu pour la structure, orange pour l action, vert pour la validation.</p>
            </div>
            <div class="rounded-[24px] border border-orangeone/15 bg-orange-50 p-5">
              <p class="text-sm font-varela uppercase tracking-[0.2em] text-orangeone">3 familles typographiques</p>
              <p class="mt-3 font-lisible text-sm text-slate-600">Une hiérarchie claire entre marque, interface et lecture longue.</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="mt-5">
      <x-oneduc.breadcrumb :items="[['label' => 'Accueil', 'url' => route('index')], ['label' => 'Charte graphique']]" />
    </div>
  </div>
</div>

<section id="fondamentaux" class="bg-white py-16 md:py-20">
  <div class="mx-auto max-w-[1248px] px-4">
    <div class="max-w-3xl">
      <p class="text-sm font-varela uppercase tracking-[0.24em] text-orangeone">Fondamentaux</p>
      <h2 class="mt-4 font-varela text-3xl font-normal text-orangeone md:text-4xl">Les principes qui guident toute l identité visuelle</h2>
    </div>

    <div class="mt-10 grid gap-5 md:grid-cols-2 xl:grid-cols-4">
      @foreach ($principes as $principe)
        <article class="rounded-[28px] border border-slate-200 bg-slate-50 p-6 transition hover:-translate-y-1 hover:border-orangeone/25">
          <p class="text-sm font-varela uppercase tracking-[0.24em] text-orangeone">{{ $principe['index'] }}</p>
          <h3 class="mt-3 text-xl font-semibold text-bleuone">{{ $principe['title'] }}</h3>
          <p class="mt-3 font-lisible leading-relaxed text-slate-600">{{ $principe['text'] }}</p>
        </article>
      @endforeach
    </div>
  </div>
</section>

<section id="couleurs" class="bg-[#f8f7fa] py-16 md:py-20">
  <div class="mx-auto max-w-[1248px] px-4">
    <div class="grid gap-8 lg:grid-cols-[0.9fr_1.1fr] lg:items-start">
      <div class="rounded-[32px] bg-bleuone p-8 text-white">
        <p class="text-sm font-varela uppercase tracking-[0.24em] text-orange-200">Palette</p>
        <h2 class="mt-4 font-varela text-3xl font-normal text-orangeone">Un système simple, contrasté et mémorisable</h2>
        <p class="mt-5 font-lisible text-lg leading-relaxed text-white/85">
          La palette Oneduc repose sur peu de couleurs fortes. Cette sobriété facilite la reconnaissance de la marque et réduit la fatigue visuelle sur les écrans à forte densité d information.
        </p>

        <div class="mt-8 space-y-3">
          @foreach ($regles as $regle)
            <div class="flex items-start gap-3 rounded-2xl bg-white/10 px-4 py-3">
              <span class="mt-1 h-2.5 w-2.5 rounded-full bg-orangeone"></span>
              <p class="font-lisible text-sm leading-relaxed text-white/85">{{ $regle }}</p>
            </div>
          @endforeach
        </div>
      </div>

      <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
        @foreach ($couleurs as $couleur)
          <article class="overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-sm">
            <div class="h-32" style="background-color: {{ $couleur['hex'] }}"></div>
            <div class="p-5">
              <div class="flex items-center justify-between gap-3">
                <h3 class="text-lg font-semibold text-bleuone">{{ $couleur['name'] }}</h3>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-varela uppercase tracking-[0.14em] text-slate-600">{{ $couleur['hex'] }}</span>
              </div>
              <p class="mt-3 font-lisible text-sm leading-relaxed text-slate-600">{{ $couleur['usage'] }}</p>
            </div>
          </article>
        @endforeach
      </div>
    </div>
  </div>
</section>

<section id="typographies" class="bg-white py-16 md:py-20">
  <div class="mx-auto max-w-[1248px] px-4">
    <div class="max-w-3xl">
      <p class="text-sm font-varela uppercase tracking-[0.24em] text-orangeone">Typographies</p>
      <h2 class="mt-4 font-varela text-3xl font-normal text-orangeone md:text-4xl">Trois rôles, trois voix visuelles complémentaires</h2>
    </div>

    <div class="mt-10 grid gap-6 lg:grid-cols-3">
      @foreach ($typos as $typo)
        <article class="rounded-[30px] border border-slate-200 bg-white p-7 shadow-sm">
          <p class="text-sm font-varela uppercase tracking-[0.2em] text-orangeone">{{ $typo['role'] }}</p>
          <h3 class="mt-3 text-2xl font-semibold text-bleuone">{{ $typo['name'] }}</h3>
          <p class="mt-5 {{ $typo['class'] }} text-2xl leading-snug text-slate-900">{{ $typo['sample'] }}</p>
          <p class="mt-5 font-lisible text-sm leading-relaxed text-slate-600">{{ $typo['details'] }}</p>
        </article>
      @endforeach
    </div>
  </div>
</section>

<section id="logos" class="bg-[#f8f7fa] py-16 md:py-20">
  <div class="mx-auto max-w-[1248px] px-4">
    <div class="grid gap-6 lg:grid-cols-[1.1fr_0.9fr]">
      <div class="grid gap-6 md:grid-cols-2">
        <article class="rounded-[32px] border border-slate-200 bg-white p-8">
          <p class="text-sm font-varela uppercase tracking-[0.2em] text-orangeone">Version principale</p>
          <div class="mt-6 rounded-[24px] border border-slate-200 bg-white p-6">
            <img src="{{ asset('frontend/assets/img/front-pages/branding/LogoOneducPositionG.svg') }}" alt="Logo principal Oneduc" class="h-16 w-auto">
          </div>
          <p class="mt-4 font-lisible text-sm leading-relaxed text-slate-600">
            À privilégier sur fonds clairs ou très peu texturés. C est la signature de référence.
          </p>
        </article>

        <article class="rounded-[32px] bg-bleuone p-8 text-white">
          <p class="text-sm font-varela uppercase tracking-[0.2em] text-orange-200">Version inversée</p>
          <div class="mt-6 rounded-[24px] bg-white/10 p-6">
            <img src="{{ asset('frontend/assets/img/front-pages/landing-page/LogoBlanc.svg') }}" alt="Logo inversé Oneduc" class="h-16 w-auto">
          </div>
          <p class="mt-4 font-lisible text-sm leading-relaxed text-white/85">
            À utiliser sur fond bleu ou orange quand le contraste avec la version principale ne serait pas suffisant.
          </p>
        </article>
      </div>

      <aside class="rounded-[32px] border border-orangeone/15 bg-orange-50 p-8">
        <p class="text-sm font-varela uppercase tracking-[0.24em] text-orangeone">Règles logo</p>
        <h2 class="mt-4 font-varela text-3xl font-normal text-orangeone">Toujours préserver la respiration de la marque</h2>
        <div class="mt-6 space-y-4 font-lisible leading-relaxed text-slate-700">
          <p>Ne pas déformer le logo, ne pas le recolorer hors palette et éviter les ombres lourdes ou les fonds trop chargés.</p>
          <p>Laisser un espace libre autour du bloc-logo afin de préserver sa lisibilité. Cet espace doit rester cohérent avec sa hauteur visuelle.</p>
          <p>Sur les supports numériques, viser une taille confortable plutôt qu un affichage miniature qui affaiblit la présence de marque.</p>
        </div>
      </aside>
    </div>
  </div>
</section>

<section id="interfaces" class="bg-white py-16 md:py-20">
  <div class="mx-auto max-w-[1248px] px-4">
    <div class="max-w-3xl">
      <p class="text-sm font-varela uppercase tracking-[0.24em] text-orangeone">Interface</p>
      <h2 class="mt-4 font-varela text-3xl font-normal text-orangeone md:text-4xl">Composants clés et comportements attendus</h2>
      <p class="mt-5 font-lisible text-lg leading-relaxed text-slate-700">
        L univers Oneduc repose sur des composants francs, faciles à reconnaître et cohérents d une page à l autre. Les actions principales ressortent en orange, les repères structurels restent en bleu et les suppressions demandent une validation explicite.
      </p>
    </div>

    <div class="mt-10 grid gap-6 lg:grid-cols-[1.05fr_0.95fr]">
      <div class="rounded-[32px] border border-slate-200 bg-[#f8f7fa] p-8">
        <p class="text-sm font-varela uppercase tracking-[0.2em] text-bleuone">Boutons et repères</p>
        <div class="mt-6 flex flex-wrap gap-4">
          <button type="button" class="btn-oneduc">Action principale</button>
          <button type="button" class="btn-oneduc-outline !px-8 !py-3">
            Action secondaire
          </button>
          <span class="inline-flex items-center rounded-full border border-orangeone/20 bg-orangeone/10 px-4 py-2 text-sm font-varela text-orangeone">
            Filtre actif
          </span>
        </div>

        <div class="mt-8 rounded-[24px] border border-slate-200 bg-white p-6">
          <div class="flex items-center justify-between gap-4">
            <div>
              <p class="font-varela text-sm uppercase tracking-[0.16em] text-slate-500">Action dense en tableau</p>
              <p class="mt-2 font-lisible text-sm text-slate-600">Pour les suppressions en liste, l icône seule dans une pastille bleue reste acceptable si une fenêtre de confirmation suit systématiquement.</p>
            </div>

            <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-bleuone/20 bg-bleuone/10 text-bleuone">
              <x-icons.trash-iconify class="h-5 w-5" />
            </button>
          </div>
        </div>
      </div>

      <div class="grid gap-6">
        <article class="rounded-[32px] bg-bleuone p-8 text-white">
          <p class="text-sm font-varela uppercase tracking-[0.2em] text-orange-200">Iconographie</p>
          <h3 class="mt-4 text-2xl font-raleway font-bold">Préférer des icônes en tracé simple</h3>
          <p class="mt-4 font-lisible leading-relaxed text-white/85">
            Sur le front et dans les espaces formateur, privilégier les SVG inline en style contour. Dans l admin, les Tabler Icons peuvent rester utilisés quand l écran est déjà construit autour de cette librairie.
          </p>
          <div class="mt-6 flex gap-4">
            <div class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-white/10">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
              </svg>
            </div>
            <div class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-white/10">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18"></path>
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 6V4h8v2"></path>
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 6l-1 14H6L5 6"></path>
              </svg>
            </div>
            <div class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-white/10">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14"></path>
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 5l7 7-7 7"></path>
              </svg>
            </div>
          </div>
        </article>

        <article class="rounded-[32px] border border-slate-200 bg-white p-8">
          <p class="text-sm font-varela uppercase tracking-[0.2em] text-orangeone">Bonnes pratiques</p>
          <div class="mt-5 space-y-3">
            @foreach ($bonnesPratiques as $item)
              <div class="flex items-start gap-3 rounded-2xl bg-slate-50 px-4 py-3">
                <span class="mt-1 inline-flex h-5 w-5 items-center justify-center rounded-full bg-vertone/15 text-[11px] font-bold text-vertone">+</span>
                <p class="font-lisible text-sm leading-relaxed text-slate-600">{{ $item }}</p>
              </div>
            @endforeach
          </div>
        </article>
      </div>
    </div>
  </div>
</section>

<section id="icones" class="bg-[#f8f7fa] py-16 md:py-20">
  <div class="mx-auto max-w-[1248px] px-4">
    <div class="max-w-3xl">
      <p class="text-sm font-varela uppercase tracking-[0.24em] text-orangeone">Icônes</p>
      <h2 class="mt-4 font-varela text-3xl font-normal text-orangeone md:text-4xl">10 propositions de corbeilles à comparer</h2>
      <p class="mt-5 font-lisible text-lg leading-relaxed text-slate-700">
        Cette galerie rassemble dix pistes visuelles pour l action de suppression. Elles sont volontairement présentées dans le même cadre pour comparer la lisibilité, la personnalité et la cohérence avec l univers Oneduc.
      </p>
    </div>

    <div class="mt-10 grid gap-5 md:grid-cols-2 xl:grid-cols-5">
      @foreach ($trashIcons as $icon)
        <article class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:border-bleuone/25">
          <div class="flex items-center justify-between gap-3">
            <span class="inline-flex items-center rounded-full bg-orangeone/10 px-3 py-1 text-xs font-varela uppercase tracking-[0.16em] text-orangeone">
              {{ $icon['index'] }}
            </span>
            <span class="text-xs font-varela uppercase tracking-[0.14em] text-slate-400">Corbeille</span>
          </div>

          <div class="mt-6 flex h-24 items-center justify-center rounded-[22px] border border-bleuone/10 bg-white text-bleuone shadow-inner shadow-bleuone/5">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-14 w-14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              @switch($icon['index'])
                @case('01')
                  <path stroke-width="1.8" d="M4.5 7.5h15" />
                  <path stroke-width="1.8" d="M9 7.5V6a1.5 1.5 0 011.5-1.5h3A1.5 1.5 0 0115 6v1.5" />
                  <path stroke-width="1.8" d="M7.5 7.5v10.125A1.875 1.875 0 009.375 19.5h5.25A1.875 1.875 0 0016.5 17.625V7.5" />
                  <path stroke-width="1.8" d="M10 10.5v6" />
                  <path stroke-width="1.8" d="M14 10.5v6" />
                  @break

                @case('02')
                  <path stroke-width="1.75" d="M5 6.75h14" />
                  <path stroke-width="1.75" d="M9 6.75V5.5c0-.55.45-1 1-1h4c.55 0 1 .45 1 1v1.25" />
                  <path stroke-width="1.75" d="M8 9.25l.75 9a1.5 1.5 0 001.49 1.25h3.52a1.5 1.5 0 001.49-1.25l.75-9" />
                  <path stroke-width="1.75" d="M10.5 11v5.5" />
                  <path stroke-width="1.75" d="M13.5 11v5.5" />
                  @break

                @case('03')
                  <path stroke-width="1.9" d="M6 7h12" />
                  <path stroke-width="1.9" d="M9.25 7V5.75c0-.69.56-1.25 1.25-1.25h3c.69 0 1.25.56 1.25 1.25V7" />
                  <path stroke-width="1.9" d="M8.5 8.5v9.25c0 .97.78 1.75 1.75 1.75h3.5c.97 0 1.75-.78 1.75-1.75V8.5" />
                  <path stroke-width="1.9" d="M10 10h4" />
                  <path stroke-width="1.9" d="M10 12.75h4" />
                  <path stroke-width="1.9" d="M10 15.5h4" />
                  @break

                @case('04')
                  <path stroke-width="1.85" d="M4.75 7.25h14.5" />
                  <path stroke-width="1.85" d="M9.75 7.25v-1c0-.97.78-1.75 1.75-1.75h1c.97 0 1.75.78 1.75 1.75v1" />
                  <path stroke-width="1.85" d="M7.25 8.75l.8 8.9A2 2 0 0010.04 19.5h3.92a2 2 0 001.99-1.85l.8-8.9" />
                  <path stroke-width="1.85" d="M9.5 10.25v6" />
                  <path stroke-width="1.85" d="M12 10.25v6" />
                  <path stroke-width="1.85" d="M14.5 10.25v6" />
                  @break

                @case('05')
                  <path stroke-width="1.8" d="M6 8h11.5" />
                  <path stroke-width="1.8" d="M8 5.75h6" />
                  <path stroke-width="1.8" d="M8 8l.6 9.25A1.5 1.5 0 0010.09 18.5h3.82a1.5 1.5 0 001.49-1.25L16 8" />
                  <path stroke-width="1.8" d="M17.5 6.25l-1.25 1.25" />
                  <path stroke-width="1.8" d="M10.5 10.25v5.25" />
                  <path stroke-width="1.8" d="M13.5 10.25v5.25" />
                  @break

                @case('06')
                  <path stroke-width="1.9" d="M5 7.5h14" />
                  <path stroke-width="1.9" d="M10 7.5V6c0-.83.67-1.5 1.5-1.5h1A1.5 1.5 0 0114 6v1.5" />
                  <path stroke-width="1.9" d="M8 9.5c.15 4.8.47 7.52.95 8.17.29.39.75.63 1.24.63h3.62c.49 0 .95-.24 1.24-.63.48-.65.8-3.37.95-8.17" />
                  <path stroke-width="1.9" d="M10.25 11.25v4.75" />
                  <path stroke-width="1.9" d="M13.75 11.25v4.75" />
                  <path stroke-width="1.9" d="M9 18.25h6" />
                  @break

                @case('07')
                  <path stroke-width="1.8" d="M3.5 6h17" />
                  <path stroke-width="1.8" d="M8.5 6V4.5h7V6" />
                  <path stroke-width="1.8" d="M18.5 6l-1 12.75A2 2 0 0115.51 20h-7.02a2 2 0 01-1.99-1.25L5.5 6" />
                  <path stroke-width="1.8" d="M10 10.5v6.25" />
                  <path stroke-width="1.8" d="M14 10.5v6.25" />
                  @break

                @case('08')
                  <path stroke-width="1.85" d="M5.5 7h13" />
                  <path stroke-width="1.85" d="M9.5 7V5.75c0-.69.56-1.25 1.25-1.25h2.5c.69 0 1.25.56 1.25 1.25V7" />
                  <path stroke-width="1.85" d="M7.75 9h8.5v8.5A2 2 0 0114.25 19.5h-4.5a2 2 0 01-2-2V9z" />
                  <path stroke-width="1.85" d="M10 11.25v5" />
                  <path stroke-width="1.85" d="M12 11.25v5" />
                  <path stroke-width="1.85" d="M14 11.25v5" />
                  @break

                @case('09')
                  <path stroke-width="1.8" d="M7 7.25h10" />
                  <path stroke-width="1.8" d="M10 7.25V6h4v1.25" />
                  <path stroke-width="1.8" d="M8.5 9.25h7v8a2 2 0 01-2 2h-3a2 2 0 01-2-2v-8z" />
                  <path stroke-width="1.8" d="M12 11v6" />
                  @break

                @case('10')
                  <path stroke-width="2" d="M4.5 7h15" />
                  <path stroke-width="2" d="M9 7V5.75A1.25 1.25 0 0110.25 4.5h3.5A1.25 1.25 0 0115 5.75V7" />
                  <path stroke-width="2" d="M7.25 8.5l.6 8.4A2.25 2.25 0 0010.09 19h3.82a2.25 2.25 0 002.24-2.1l.6-8.4" />
                  <path stroke-width="2" d="M9.75 10.5l4.5 4.5" />
                  <path stroke-width="2" d="M14.25 10.5l-4.5 4.5" />
                  @break
              @endswitch
            </svg>
          </div>

          <h3 class="mt-5 text-lg font-semibold text-bleuone">{{ $icon['title'] }}</h3>
          <p class="mt-2 font-lisible text-sm leading-relaxed text-slate-600">{{ $icon['note'] }}</p>
        </article>
      @endforeach
    </div>

    <div class="mt-14 rounded-[32px] border border-slate-200 bg-white p-8 shadow-sm">
      <div class="max-w-3xl">
        <p class="text-sm font-varela uppercase tracking-[0.24em] text-bleuone">Présentation de l icône retenue</p>
        <h3 class="mt-4 text-2xl font-raleway font-medium text-bleuone">6 manières de mettre en scène la corbeille Iconify</h3>
        <p class="mt-4 font-lisible text-base leading-relaxed text-slate-600">
          Ici, le dessin ne change plus. Seule sa présentation évolue selon le contexte d usage : tableau dense, bouton d action, confirmation, variante discrète ou suppression plus sensible.
        </p>
      </div>

      <div class="mt-8 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
        <article class="rounded-[24px] border border-slate-200 bg-[#f8f7fa] p-6">
          <p class="text-sm font-varela uppercase tracking-[0.16em] text-orangeone">Option A</p>
          <h4 class="mt-2 text-lg font-semibold text-bleuone">Pastille bleue légère</h4>
          <div class="mt-5 flex items-center justify-center">
            <button type="button" class="inline-flex h-11 w-11 items-center justify-center rounded-full border border-bleuone/20 bg-bleuone/10 text-bleuone transition hover:border-bleuone hover:bg-bleuone hover:text-white">
              <x-icons.trash-iconify class="h-5 w-5" />
            </button>
          </div>
          <p class="mt-5 font-lisible text-sm leading-relaxed text-slate-600">
            La plus proche de l actuel. Très adaptée aux tableaux et aux listes denses.
          </p>
        </article>

        <article class="rounded-[24px] border border-slate-200 bg-[#f8f7fa] p-6">
          <p class="text-sm font-varela uppercase tracking-[0.16em] text-orangeone">Option B</p>
          <h4 class="mt-2 text-lg font-semibold text-bleuone">Pastille bleue pleine</h4>
          <div class="mt-5 flex items-center justify-center">
            <button type="button" class="inline-flex h-11 w-11 items-center justify-center rounded-full border border-bleuone bg-bleuone text-white transition hover:bg-white hover:text-bleuone">
              <x-icons.trash-iconify class="h-5 w-5" />
            </button>
          </div>
          <p class="mt-5 font-lisible text-sm leading-relaxed text-slate-600">
            Plus visible et plus franche. Bonne option si l action doit être repérée rapidement.
          </p>
        </article>

        <article class="rounded-[24px] border border-slate-200 bg-[#f8f7fa] p-6">
          <p class="text-sm font-varela uppercase tracking-[0.16em] text-orangeone">Option C</p>
          <h4 class="mt-2 text-lg font-semibold text-bleuone">Carré arrondi discret</h4>
          <div class="mt-5 flex items-center justify-center">
            <button type="button" class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-bleuone/15 bg-white text-bleuone transition hover:border-bleuone hover:bg-bleuone/5">
              <x-icons.trash-iconify class="h-5 w-5" />
            </button>
          </div>
          <p class="mt-5 font-lisible text-sm leading-relaxed text-slate-600">
            Plus structurée, plus “outil”. Fonctionne bien dans les interfaces de gestion.
          </p>
        </article>

        <article class="rounded-[24px] border border-slate-200 bg-[#f8f7fa] p-6">
          <p class="text-sm font-varela uppercase tracking-[0.16em] text-orangeone">Option D</p>
          <h4 class="mt-2 text-lg font-semibold text-bleuone">Bouton avec libellé</h4>
          <div class="mt-5 flex items-center justify-center">
            <button type="button" class="btn-oneduc-outline !px-4 !py-2 !text-sm">
              <x-icons.trash-iconify class="h-4 w-4" />
              Supprimer
            </button>
          </div>
          <p class="mt-5 font-lisible text-sm leading-relaxed text-slate-600">
            Recommandée quand il faut lever toute ambiguïté sur l action.
          </p>
        </article>

        <article class="rounded-[24px] border border-slate-200 bg-[#f8f7fa] p-6">
          <p class="text-sm font-varela uppercase tracking-[0.16em] text-orangeone">Option E</p>
          <h4 class="mt-2 text-lg font-semibold text-bleuone">Version alerte douce</h4>
          <div class="mt-5 flex items-center justify-center">
            <button type="button" class="inline-flex h-11 w-11 items-center justify-center rounded-full border border-red-200 bg-red-50 text-red-600 transition hover:bg-red-600 hover:text-white">
              <x-icons.trash-iconify class="h-5 w-5" />
            </button>
          </div>
          <p class="mt-5 font-lisible text-sm leading-relaxed text-slate-600">
            Plus explicite pour une suppression sensible, mais plus éloignée de la palette Oneduc.
          </p>
        </article>

        <article class="rounded-[24px] border border-slate-200 bg-[#f8f7fa] p-6">
          <p class="text-sm font-varela uppercase tracking-[0.16em] text-orangeone">Option F</p>
          <h4 class="mt-2 text-lg font-semibold text-bleuone">Entête de confirmation</h4>
          <div class="mt-5 flex items-center justify-center">
            <div class="inline-flex h-14 w-14 items-center justify-center rounded-full bg-bleuone/10 text-bleuone">
              <x-icons.trash-iconify class="h-6 w-6" />
            </div>
          </div>
          <p class="mt-5 font-lisible text-sm leading-relaxed text-slate-600">
            Idéale dans une modale de validation pour rappeler visuellement l action avant confirmation.
          </p>
        </article>
      </div>
    </div>
  </div>
</section>

<section id="ton" class="bg-[#f8f7fa] py-16 md:py-20">
  <div class="mx-auto max-w-[1248px] px-4">
    <div class="grid gap-6 lg:grid-cols-2">
      <div class="rounded-[32px] border border-slate-200 bg-white p-8">
        <p class="text-sm font-varela uppercase tracking-[0.24em] text-orangeone">Ton éditorial</p>
        <h2 class="mt-4 font-varela text-3xl font-normal text-orangeone">Une marque qui guide sans écraser</h2>
        <div class="mt-6 space-y-4 font-lisible leading-relaxed text-slate-700">
          <p>Le ton Oneduc reste simple, rassurant et concret. Il doit aider l utilisateur à agir, sans jargon inutile ni surcharge d autorité.</p>
          <p>Les formulations courtes, utiles et chaleureuses sont à privilégier. Le langage doit rester compréhensible pour des profils variés, y compris en situation d apprentissage ou de fragilité numérique.</p>
        </div>
      </div>

      <div class="rounded-[32px] bg-white p-8 shadow-sm">
        <p class="text-sm font-varela uppercase tracking-[0.24em] text-bleuone">À éviter</p>
        <div class="mt-5 space-y-3">
          @foreach ($aEviter as $item)
            <div class="flex items-start gap-3 rounded-2xl bg-red-50 px-4 py-3">
              <span class="mt-1 inline-flex h-5 w-5 items-center justify-center rounded-full bg-red-100 text-[11px] font-bold text-red-600">x</span>
              <p class="font-lisible text-sm leading-relaxed text-slate-600">{{ $item }}</p>
            </div>
          @endforeach
        </div>
      </div>
    </div>

    <div class="mt-10 rounded-[36px] bg-gradient-to-r from-bleuone to-[#005d85] p-8 text-white md:p-10">
      <div class="grid gap-6 lg:grid-cols-[1fr_auto] lg:items-center">
        <div>
          <p class="text-sm font-varela uppercase tracking-[0.24em] text-orange-200">Usage</p>
          <h2 class="mt-4 font-varela text-3xl font-normal text-orangeone">Cette charte sert de base pour les futures pages et interfaces Oneduc</h2>
          <p class="mt-4 max-w-3xl font-lisible text-lg leading-relaxed text-white/85">
            Elle peut évoluer, mais elle fixe déjà une direction claire : une identité plus cohérente, plus accessible et plus immédiatement reconnaissable sur tout l écosystème.
          </p>
        </div>

        <div class="flex flex-wrap gap-3">
          <a href="{{ route('contact') }}" class="btn-oneduc !px-8 !py-3">
            Nous contacter
          </a>
          <a href="{{ route('projet') }}" class="btn-oneduc-outline !border-white/30 !bg-white/10 !px-8 !py-3 !text-white hover:!border-white hover:!bg-white hover:!text-bleuone">
            Voir le projet
          </a>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection
