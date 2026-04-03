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

  $famillesIcones = [
    [
      'title' => 'Repères métier',
      'accent' => 'text-bleuone',
      'tone' => 'bg-bleuone/10 text-bleuone',
      'items' => [
        [
          'name' => 'Stagiaire',
          'component' => 'icons.stagiaire-iconify',
          'usage' => 'Repère apprenant pour les compteurs, la navigation de suivi et les synthèses individuelles.',
        ],
        [
          'name' => 'Groupe',
          'component' => 'icons.group-iconify',
          'usage' => 'Repère collectif pour les vues de progression par groupe et les totaux de cohortes.',
        ],
        [
          'name' => 'Module',
          'component' => 'icons.module-iconify',
          'usage' => 'Repère contenu pour les modules de formation, les suivis pédagogiques et les synthèses associées.',
        ],
      ],
    ],
    [
      'title' => 'Actions d interface',
      'accent' => 'text-orangeone',
      'tone' => 'bg-orangeone/10 text-orangeone',
      'items' => [
        [
          'name' => 'Filtrer',
          'component' => 'icons.filter-iconify',
          'usage' => 'À utiliser sur les boutons de tri, de recherche et de filtrage de tableau.',
        ],
        [
          'name' => 'Voir',
          'component' => 'icons.eye-iconify',
          'usage' => 'Réservée aux actions de consultation, d aperçu, de parcours et de détail.',
        ],
        [
          'name' => 'Modifier',
          'component' => 'icons.edit-iconify',
          'usage' => 'Action d édition sur un élément existant, dans un tableau ou une fiche.',
        ],
        [
          'name' => 'Supprimer',
          'component' => 'icons.trash-iconify',
          'usage' => 'Suppression d un élément avec confirmation explicite avant validation.',
        ],
        [
          'name' => 'Ajouter un stagiaire',
          'component' => 'icons.add-stagiaire-button-iconify',
          'usage' => 'CTA principal pour l ajout d un nouvel apprenant dans les vues formateur.',
        ],
        [
          'name' => 'Ajout stagiaire alternatif',
          'component' => 'icons.add-stagiaire-iconify',
          'usage' => 'Variante plus dense utilisée sur certains repères secondaires ou compteurs liés aux apprenants.',
        ],
      ],
    ],
    [
      'title' => 'Compte et préférences',
      'accent' => 'text-vertone',
      'tone' => 'bg-vertone/10 text-vertone',
      'items' => [
        [
          'name' => 'Profil',
          'component' => 'icons.profile-iconify',
          'usage' => 'Entrée d accès à la fiche utilisateur et aux informations personnelles.',
        ],
        [
          'name' => 'Paramètres',
          'component' => 'icons.settings-iconify',
          'usage' => 'Navigation vers les préférences et réglages du compte.',
        ],
        [
          'name' => 'Sécurité',
          'component' => 'icons.security-iconify',
          'usage' => 'Accès aux réglages sensibles liés au mot de passe et à la protection du compte.',
        ],
      ],
    ],
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
      <h2 class="mt-4 font-varela text-3xl font-normal text-orangeone md:text-4xl">Bibliothèque active des icônes Oneduc</h2>
      <p class="mt-5 font-lisible text-lg leading-relaxed text-slate-700">
        Cette section regroupe les pictogrammes déjà posés dans les interfaces Oneduc. Elle sert de base commune pour garder une iconographie cohérente entre les vues publiques, formateur et observateur.
      </p>
    </div>

    <div class="mt-10 grid gap-6 xl:grid-cols-3">
      @foreach ($famillesIcones as $famille)
        <article class="rounded-[32px] border border-slate-200 bg-white p-8 shadow-sm">
          <p class="text-sm font-varela uppercase tracking-[0.2em] {{ $famille['accent'] }}">{{ $famille['title'] }}</p>

          <div class="mt-6 grid gap-4">
            @foreach ($famille['items'] as $icone)
              <div class="rounded-[24px] border border-slate-200 bg-[#f8f7fa] p-5">
                <div class="flex items-center gap-4">
                  <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl {{ $famille['tone'] }}">
                    <x-dynamic-component :component="$icone['component']" class="h-6 w-6" />
                  </span>

                  <div>
                    <h3 class="font-raleway text-xl font-medium text-bleuone">{{ $icone['name'] }}</h3>
                    <p class="mt-1 text-sm font-varela text-slate-500">Identité visuelle standardisée</p>
                  </div>
                </div>

                <p class="mt-4 font-lisible text-sm leading-relaxed text-slate-600">{{ $icone['usage'] }}</p>
              </div>
            @endforeach
          </div>
        </article>
      @endforeach
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
