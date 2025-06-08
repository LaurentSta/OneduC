
@extends('frontend.master')
@section('home')

<div class="max-w-[1248px] mx-auto px-4">
    <div class="bg-white rounded-[20px] shadow-md p-8 my-10 w-full">
        <div class="grid grid-cols-12 gap-6 items-center">

            {{-- Colonne texte : 8 colonnes sur 12 --}}
            <div class="col-span-12 md:col-span-8">
                <x-typography variant="titre">Le projet Onéduc.fr</x-typography>
                <x-typography variant="sous-titre" class="font-varela text-sous-titre text-orangeone">
                    …en faveur de l’inclusion numérique
                </x-typography>
                <div class="prose-oneduc">
                    Onéduc.fr est né d’un double engagement : l’inclusion numérique et l’accessibilité à tous.
  Initié par Laurent Staelens, éducateur spécialisé passionné de numérique, le projet vise à offrir une plateforme de formation gratuite et accessible à long terme.
  Notre ambition : faire du digital un levier d’égalité et d’autonomie pour tous les publics.
                </div>
            </div>

            {{-- Colonne image : 4 colonnes sur 12 --}}
            <div class="col-span-12 md:col-span-4 flex justify-center md:justify-end">
                <div class="w-full max-w-xs">
                    {!! file_get_contents(public_path('images/svg/PointDInterrogation.svg')) !!}
                </div>
            </div>

        </div>

    </div>
</div>

<section class="py-16 bg-white">
    <div class="max-w-[1248px] mx-auto px-4">
      <div class="flex flex-col-reverse md:flex-row items-center gap-10">

        <!-- Texte à gauche -->
        <div class="w-full md:w-1/2 space-y-6 font-lisible">
          <h2 class="text-3xl font-semibold flex items-center gap-4 font-raleway text-bleuone">
            <img src="{{ asset('frontend/assets/img/front-pages/icons/etoile8.gif') }}" width="60" height="61" alt="Étoile">
            Un engagement pour l’inclusion numérique
          </h2>
          <p class="prose-oneduc">Dans un monde numérique en constante évolution, l’accès à l’éducation et à <strong>l’inclusion numérique</strong> est plus qu’une nécessité, c’est un droit. Je suis Laurent Staelens, fondateur d’Oneduc.fr, et mon parcours en tant qu’éducateur spécialisé m’a toujours poussé à chercher des solutions innovantes pour l’inclusion et l’éducation. Passionné par le potentiel du numérique, j’ai orienté ma carrière pour fusionner technologie et pédagogie, avec un objectif clair : rendre la formation accessible à tous, notamment aux personnes en situation de handicap.</p>
        <p class="prose-oneduc">Oneduc.fr est né de cette conviction, avec l’ambition de devenir une plateforme de formation numérique accessible à tous, gratuitement, sur le long terme. Nous nous engageons à transformer les défis en opportunités et à faire de l’apprentissage numérique une expérience enrichissante et accessible à tous.&nbsp;</p>
        </div>

        <!-- Image à droite -->
        <div class="w-full md:w-1/2">
          <img src="{{ asset('frontend/assets/img/illustrations/Engagement.svg')}}" alt="Livre Oneduc" class="w-full rounded min-h-[300px] object-cover">
        </div>

      </div>
    </div>
  </section>

  <section class="py-16 bg-white">
    <div class="max-w-[1248px] mx-auto px-4">
      <div class="flex flex-col md:flex-row items-center gap-10">

        <!-- Image gauche -->
        <div class="w-full md:w-1/2">
          <img src="{{ asset('frontend/assets/img/illustrations/CreationGroupe.svg') }}"
               alt="Création groupe"
               class="w-full rounded min-h-[300px] object-cover">
        </div>

        <!-- Texte droite -->
        <div class="w-full md:w-1/2 space-y-6 font-lisible">
          <h2 class="text-3xl font-semibold flex items-center gap-3 font-raleway text-bleuone">
            <img src="{{ asset('frontend/assets/img/front-pages/icons/etoile8.gif') }}" width="60" height="61" alt="Étoile">
            Gratuité et accessibilité sur le long terme
          </h2>
          <p class="text-lg">Mon projet actuel est d’offrir une plateforme de formation numérique entièrement gratuite à long terme.</p>
          <p class="text-lg">L’objectif est clair : garantir une accessibilité maximale et une inclusion totale, en veillant particulièrement à l’accessibilité pour les personnes atteintes de handicap. Ce projet à long terme est le reflet de notre engagement envers une éducation ouverte, accessible et évolutive, adaptée aux besoins de tous.</p>
          <p class="text-lg">Cela s’inscrit dans une perspective durable et engagée, visant à révolutionner la manière dont la formation est dispensée et reçue, en particulier pour les éducateurs et formateurs.</p>
          <p class="text-lg">Pour cela, les fondateurs de Onéduc se sont regroupés en association.</p>
        </div>

      </div>
    </div>
  </section>

  <section class="py-16 bg-white">
    <div class="max-w-[1248px] mx-auto px-4">
      <div class="flex flex-col md:flex-row items-center gap-10">

        <!-- Texte à gauche -->
        <div class="w-full md:w-1/2 flex items-center justify-center md:justify-start text-center md:text-left font-lisible">
          <div class="space-y-6">
            <h2 class="text-3xl font-semibold flex items-center gap-3 font-raleway text-bleuone justify-center md:justify-start">
              <img src="{{ asset('frontend/assets/img/front-pages/icons/etoile8.gif') }}"
                   alt="Étoile" width="60" height="61">
              L’association Onéduc
            </h2>
            <p class="text-lg font-semibold">
              Onéduc est une association regroupant des formateurs ayant créé une plateforme open source de digitalisation des formations.
              Elle met à disposition des formateurs une plateforme de formation en ligne fonctionnant sur tous supports.
            </p>
            <p class="text-lg">
              La plateforme de Digital learning est utilisée dans le cadre de formations financées par des OPCO ou par le CPF (Compte personnel de Formation).
            </p>
            <div class="text-center md:text-left">
              <a href="#"
                 class="btn-oneduc inline-block mt-4">
                Découvrez l’outil
              </a>
            </div>
          </div>
        </div>

        <!-- Image à droite -->
        <div class="w-full md:w-1/2">
          <img src="{{ asset('frontend/assets/img/illustrations/AssociationOneduc.svg') }}"
               alt="Association Oneduc"
               class="w-full rounded object-cover min-h-[300px]">
        </div>

      </div>
    </div>
  </section>



@endsection
