@extends('frontend.master')
@section('home')
<div class="container mx-auto px-4 pt-8 pb-4">
  <div class="bg-white rounded-[24px] shadow-md p-8 w-full">
    <div class="grid gap-8 lg:grid-cols-[1.1fr_0.9fr] items-center">
      <div>
        <x-typography variant="titre">Adhesion a l'association Oneduc</x-typography>
        <x-typography variant="sous-titre" class="font-varela text-sous-titre text-orangeone">
          Comprendre la difference entre utiliser la plateforme et soutenir le projet.
        </x-typography>
        <div class="prose-oneduc font-lisible">
          <p>L'inscription formateur sert a creer un acces a la plateforme. L'adhesion, elle, concerne l'association loi 1901 qui porte le projet Oneduc dans la duree.</p>
          <p>Ces deux demarches peuvent se completer, mais elles ne repondent pas au meme besoin : l'une releve de l'usage, l'autre du soutien, de la gouvernance et de la vie associative.</p>
          <p>Le montant de la cotisation annuelle applicable apparait directement dans le formulaire HelloAsso ci-dessous.</p>
        </div>
      </div>

      <div class="grid gap-4">
        <div class="rounded-[28px] border border-slate-200 bg-slate-50 p-6">
          <h3 class="text-xl font-semibold text-bleuone">Ce que finance l'adhesion</h3>
          <p class="mt-3 text-sm leading-relaxed text-slate-600 font-lisible">
            L'hebergement, les evolutions de la plateforme, l'animation du projet et le cadre associatif qui lui donne une trajectoire collective.
          </p>
        </div>
        <div class="rounded-[28px] border border-orangeone/15 bg-orange-50 p-6">
          <h3 class="text-xl font-semibold text-bleuone">Ce que permet l'adhesion</h3>
          <p class="mt-3 text-sm leading-relaxed text-slate-600 font-lisible">
            Rejoindre la dynamique associative, soutenir un outil pense pour le terrain et participer a la vie de l'association selon les statuts.
          </p>
        </div>
      </div>
    </div>
  </div>
</div>

<section class="bg-[#f8f7fa] py-12">
  <div class="max-w-[1248px] mx-auto px-4">
    <div class="grid gap-5 md:grid-cols-3">
      <div class="rounded-3xl bg-white p-6 border border-slate-200 shadow-sm">
        <p class="text-xs font-varela uppercase tracking-[0.2em] text-orangeone">1. Utiliser</p>
        <h3 class="mt-3 text-xl font-semibold text-bleuone">Creer un compte formateur</h3>
        <p class="mt-3 text-slate-600 leading-relaxed font-lisible">
          Ouvrir un acces a la plateforme pour creer vos modules, gerer vos groupes et tester l'outil.
        </p>
        <a href="{{ route('formateur.inscription.form') }}" class="mt-5 inline-flex items-center text-orangeone font-semibold hover:underline">
          Aller a l'inscription
        </a>
      </div>

      <div class="rounded-3xl bg-white p-6 border border-slate-200 shadow-sm">
        <p class="text-xs font-varela uppercase tracking-[0.2em] text-orangeone">2. Adherer</p>
        <h3 class="mt-3 text-xl font-semibold text-bleuone">Soutenir l'association</h3>
        <p class="mt-3 text-slate-600 leading-relaxed font-lisible">
          Contribuer a la continuite du projet, a son hebergement, a ses evolutions et a sa vie associative.
        </p>
        <span class="mt-5 inline-flex items-center text-slate-500 font-semibold">
          Formulaire HelloAsso ci-dessous
        </span>
      </div>

      <div class="rounded-3xl bg-white p-6 border border-slate-200 shadow-sm">
        <p class="text-xs font-varela uppercase tracking-[0.2em] text-orangeone">3. Rassurer les apprenants</p>
        <h3 class="mt-3 text-xl font-semibold text-bleuone">Connexion simple des stagiaires</h3>
        <p class="mt-3 text-slate-600 leading-relaxed font-lisible">
          Les stagiaires peuvent se connecter avec un code d'acces, ce qui reduit les freins d'entree dans la formation.
        </p>
        <a href="{{ route('stagiaire.code.form') }}" class="mt-5 inline-flex items-center text-orangeone font-semibold hover:underline">
          Voir l'acces stagiaire
        </a>
      </div>
    </div>

    <div class="mt-8 rounded-[28px] bg-white p-6 border border-slate-200 shadow-sm">
      <div class="grid gap-6 lg:grid-cols-[0.9fr_1.1fr] items-center">
        <div>
          <h2 class="text-2xl md:text-3xl font-raleway font-bold text-bleuone">Comment se passe l'adhesion ?</h2>
          <div class="mt-5 space-y-4 text-slate-600 leading-relaxed font-lisible">
            <p>Vous completez le formulaire HelloAsso avec la cotisation en vigueur.</p>
            <p>Vous recevez ensuite la confirmation de votre adhesion et rejoignez le projet associatif dans le cadre prevu par les statuts.</p>
            <p>Si vous cherchez d'abord a utiliser la plateforme comme formateur, commencez plutot par l'inscription au service.</p>
          </div>
        </div>

        <div class="flex flex-wrap gap-4">
          <a href="{{ route('formateur.inscription.form') }}" class="btn-oneduc">Creer un compte formateur</a>
          <a href="{{ route('association') }}" class="btn-oneduc-outline">Comprendre le modele associatif</a>
        </div>
      </div>
    </div>
  </div>
</section>

<div class="scrollspy-example" data-bs-spy="scroll">
  <iframe
    id="haWidget"
    src="https://www.helloasso.com/associations/oneduc/adhesions/formulaire-d-adhesion-oneduc/widget"
    style="width:100%;height:750px;border:none;">
  </iframe>
</div>
@endsection
