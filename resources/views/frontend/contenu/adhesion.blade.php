@extends('frontend.master')
@section('title', 'Adhérer à l’association Onéduc')
@section('description', "Adhérez à l'association Onéduc pour soutenir la plateforme et continuer à utiliser l'espace formateur au-delà de la période de découverte.")

@section('home')
<section class="relative overflow-hidden bg-gray-50 py-16 md:py-20">
  <div class="pointer-events-none absolute inset-0 overflow-hidden" aria-hidden="true">
    <img src="{{ asset('frontend/assets/img/front-pages/akene-seeds/seed-11.svg') }}" alt="" class="absolute left-5 top-16 w-20 -rotate-12 opacity-10 md:left-20 md:w-32">
    <img src="{{ asset('frontend/assets/img/front-pages/akene-seeds/seed-18.svg') }}" alt="" class="absolute right-8 top-28 w-16 rotate-[18deg] opacity-10 md:right-24 md:w-24">
    <img src="{{ asset('frontend/assets/img/front-pages/akene-seeds/seed-23.svg') }}" alt="" class="absolute -right-10 bottom-16 w-32 rotate-12 opacity-10 md:right-10 md:w-44">
  </div>

  <div class="relative mx-auto max-w-[1248px] px-6">
    <x-oneduc.breadcrumb :items="[['label' => 'Accueil', 'url' => route('index')], ['label' => 'Adhérer à l’association']]" />

    <div class="mt-10 grid items-center gap-14 lg:grid-cols-[1.05fr_0.95fr]">
      <div>
        @if(session('success'))
          <div class="mb-6 rounded-lg border border-green-200 bg-green-50 p-4 font-lisible text-green-800" role="alert">{{ session('success') }}</div>
        @endif

        @if(session('warning'))
          <div class="mb-6 rounded-lg border border-amber-200 bg-amber-50 p-4 font-lisible text-amber-800" role="alert">{{ session('warning') }}</div>
        @endif

        @auth
          <form method="POST" action="{{ route('logout') }}" class="mb-6">
            @csrf
            <button type="submit" class="inline-flex items-center justify-center rounded-full border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-bleuone shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-4 focus:ring-blue-100">
              Se déconnecter
            </button>
          </form>
        @endauth

        <h1 class="flex items-center gap-4 font-raleway text-[36px] font-extrabold leading-tight text-bleuone md:text-[48px]">
          <img src="{{ asset('frontend/assets/img/front-pages/icons/etoile8.gif') }}" width="60" height="61" alt="" aria-hidden="true" class="h-[60px] w-[60px] flex-none object-contain">
          <span>Adhérer à l’association Onéduc</span>
        </h1>

        <p class="mt-6 max-w-[68ch] font-lisible text-xl leading-relaxed text-slate-600">
          L’adhésion soutient l’association loi 1901 qui porte Onéduc.fr dans la durée : hébergement, évolutions, vie associative et accompagnement des usages.
        </p>

        <p class="mt-5 max-w-[68ch] font-lisible text-lg leading-relaxed text-slate-600">
          Pour les formateurs, elle est proposée après l’inscription afin de soutenir le projet associatif. Votre espace est ouvert automatiquement pendant la période de découverte.
        </p>

        <div class="mt-8 flex flex-wrap gap-4">
          <a href="#formulaire-adhesion" class="btn-oneduc">Remplir le formulaire</a>
          <a href="{{ route('projet') }}" class="btn-oneduc-outline">Comprendre le projet</a>
        </div>
      </div>

      <div class="relative mx-auto w-full max-w-lg">
        <div class="absolute -inset-3 rotate-2 rounded-[28px] border-2 border-orangeone/35 bg-orangeone/5"></div>
        <div class="relative overflow-hidden rounded-[28px] bg-bleuone p-8 text-white shadow-xl shadow-bleuone/20">
          <p class="font-varela text-sm font-semibold uppercase tracking-[0.2em] text-orange-200">Association loi 1901</p>
          <h2 class="mt-4 font-raleway text-3xl font-extrabold leading-tight">Un soutien concret pour un outil utile au terrain.</h2>
          <div class="mt-7 grid gap-4">
            @foreach([
              'Faire vivre l’hébergement et la maintenance',
              'Soutenir les évolutions utiles aux formateurs',
              'Participer à une dynamique pédagogique collective',
            ] as $item)
              <div class="flex gap-3 rounded-lg bg-white/[0.08] p-4">
                <span class="mt-2 h-2 w-2 flex-none rounded-full bg-orangeone"></span>
                <p class="font-lisible leading-relaxed text-white/85">{{ $item }}</p>
              </div>
            @endforeach
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="bg-white py-16 md:py-20">
  <div class="mx-auto max-w-[1248px] px-6">
    <div class="mx-auto max-w-3xl text-center">
      <h2 class="font-raleway text-[34px] font-extrabold leading-tight text-bleuone md:text-[40px]">Deux démarches, deux besoins</h2>
      <p class="mt-5 font-lisible text-lg leading-relaxed text-slate-600">
        L’inscription crée votre compte formateur et ouvre l’accès à la plateforme. L’adhésion confirme votre participation au projet associatif et soutient sa continuité.
      </p>
    </div>

    <div class="mt-10 grid gap-6 lg:grid-cols-2">
      <article class="relative overflow-hidden rounded-lg border border-slate-200 bg-white p-7 shadow-sm shadow-slate-200/70 md:p-9">
        <span class="absolute inset-x-7 top-0 h-1 rounded-full bg-bleuone"></span>
        <p class="font-varela text-sm font-semibold uppercase tracking-[0.18em] text-bleuone">Utiliser la plateforme</p>
        <h3 class="mt-4 font-raleway text-3xl font-extrabold leading-tight text-bleuone">Créer un compte formateur</h3>
        <p class="mt-5 font-lisible text-lg leading-relaxed text-slate-600">
          Pour préparer vos modules, gérer vos groupes, tester les outils numériques et accompagner vos apprenants dans Onéduc.fr dès votre inscription.
        </p>
        <a href="{{ route('formateur.inscription.form') }}" class="mt-7 inline-flex items-center justify-center rounded-full border-2 border-bleuone bg-bleuone px-6 py-3 font-varela font-semibold text-white transition hover:bg-white hover:text-bleuone focus:outline-none focus:ring-4 focus:ring-blue-100">
          Créer mon compte
        </a>
      </article>

      <article class="relative overflow-hidden rounded-lg border border-orangeone/20 bg-orangeone p-7 text-white shadow-xl shadow-orangeone/20 md:p-9">
        <span class="absolute inset-x-7 top-0 h-1 rounded-full bg-white/80"></span>
        <p class="font-varela text-sm font-semibold uppercase tracking-[0.18em] text-white/85">Soutenir le projet</p>
        <h3 class="mt-4 font-raleway text-3xl font-extrabold leading-tight">Adhérer à l’association</h3>
        <p class="mt-5 font-lisible text-lg leading-relaxed text-white/90">
          Pour contribuer à la continuité du projet, rejoindre sa vie associative et soutenir un outil pensé avec les réalités du terrain.
        </p>
        <a href="#formulaire-adhesion" class="mt-7 inline-flex items-center justify-center rounded-full border-2 border-white bg-white px-6 py-3 font-varela font-semibold text-orangeone transition hover:bg-transparent hover:text-white focus:outline-none focus:ring-4 focus:ring-white/25">
          Adhérer maintenant
        </a>
      </article>
    </div>
  </div>
</section>

<section class="relative overflow-hidden bg-gray-50 py-16 md:py-20">
  <img src="{{ asset('frontend/assets/img/front-pages/akene-seeds/seed-21.svg') }}" alt="" aria-hidden="true" class="absolute left-[6%] bottom-14 w-16 rotate-[24deg] opacity-10 md:w-24">

  <div class="relative mx-auto max-w-[1248px] px-6">
    <div class="grid gap-10 lg:grid-cols-[0.9fr_1.1fr] lg:items-start">
      <div>
        <h2 class="flex items-center gap-4 font-raleway text-[34px] font-extrabold leading-tight text-bleuone md:text-[40px]">
          <img src="{{ asset('frontend/assets/img/front-pages/icons/etoile8.gif') }}" width="54" height="55" alt="" aria-hidden="true" class="h-[54px] w-[54px] flex-none object-contain">
          <span>Ce que votre adhésion rend possible</span>
        </h2>
        <p class="mt-5 font-lisible text-lg leading-relaxed text-slate-600">
          Le montant de la cotisation annuelle apparaît directement dans le formulaire HelloAsso. Les ressources sont orientées vers la vie du projet et son amélioration progressive.
        </p>
      </div>

      <div class="grid gap-4 sm:grid-cols-2">
        @foreach([
          ['title' => 'Hébergement', 'desc' => 'Maintenir une plateforme disponible, stable et suivie dans le temps.', 'line' => 'bg-orangeone'],
          ['title' => 'Évolutions', 'desc' => 'Faire avancer les fonctionnalités utiles aux formateurs et aux apprenants.', 'line' => 'bg-bleuone'],
          ['title' => 'Vie associative', 'desc' => 'Organiser un cadre collectif, lisible et transparent autour du projet.', 'line' => 'bg-vertone'],
          ['title' => 'Accessibilité', 'desc' => 'Continuer à améliorer l’usage pour des publics très différents.', 'line' => 'bg-orangeone'],
        ] as $support)
          <article class="relative overflow-hidden rounded-lg border border-slate-200 bg-white p-6 shadow-sm shadow-slate-200/70">
            <span class="absolute inset-x-5 top-0 h-1 rounded-full {{ $support['line'] }}"></span>
            <h3 class="font-raleway text-xl font-bold text-bleuone">{{ $support['title'] }}</h3>
            <p class="mt-3 font-lisible leading-relaxed text-slate-600">{{ $support['desc'] }}</p>
          </article>
        @endforeach
      </div>
    </div>
  </div>
</section>

<section id="formulaire-adhesion" class="bg-white py-16 md:py-20">
  <div class="mx-auto max-w-[1248px] px-6">
    <div class="grid gap-8 lg:grid-cols-[0.82fr_1.18fr] lg:items-start">
      <aside class="rounded-lg bg-bleuone p-7 text-white shadow-xl shadow-bleuone/20 md:p-9">
        <p class="font-varela text-sm font-semibold uppercase tracking-[0.18em] text-orange-200">Formulaire sécurisé</p>
        <h2 class="mt-4 font-raleway text-[34px] font-extrabold leading-tight md:text-[40px]">Adhésion via HelloAsso</h2>
        <div class="mt-6 space-y-4 font-lisible text-lg leading-relaxed text-white/85">
          <p>Le formulaire ci-contre permet de finaliser votre adhésion à l’association Onéduc.</p>
          <p>Après validation, vous recevez la confirmation de votre adhésion dans le cadre prévu par les statuts.</p>
        </div>

        <div class="mt-8 grid gap-3">
          @foreach([
            'Remplissez vos coordonnées',
            'Choisissez la cotisation indiquée',
            'Recevez votre confirmation',
          ] as $step)
            <div class="flex items-center gap-3 rounded-lg bg-white/[0.08] p-4">
              <span class="h-2.5 w-2.5 rounded-full bg-orangeone"></span>
              <span class="font-lisible text-white/90">{{ $step }}</span>
            </div>
          @endforeach
        </div>

        <div class="mt-8 flex flex-wrap gap-4">
          <a href="{{ route('association') }}" class="inline-flex items-center justify-center rounded-full border-2 border-white bg-white px-6 py-3 font-varela font-semibold text-bleuone transition hover:bg-transparent hover:text-white focus:outline-none focus:ring-4 focus:ring-white/20">
            Voir l’association
          </a>
          <a href="{{ asset('docs/statutsONEDUCsignes.pdf') }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center rounded-full border-2 border-white/30 px-6 py-3 font-varela font-semibold text-white transition hover:border-white hover:bg-white hover:text-bleuone focus:outline-none focus:ring-4 focus:ring-white/20">
            Télécharger les statuts
          </a>
        </div>
      </aside>

      <div class="rounded-lg border border-slate-200 bg-white p-3 shadow-xl shadow-slate-200/80 md:p-4">
        <iframe
          id="haWidget"
          title="Formulaire d’adhésion Onéduc sur HelloAsso"
          src="https://www.helloasso.com/associations/oneduc/adhesions/formulaire-d-adhesion-oneduc/widget"
          loading="lazy"
          class="h-[780px] w-full rounded-lg border-0">
        </iframe>
      </div>
    </div>
  </div>
</section>
@endsection
