@extends('frontend.master')

@section('home')

<section class="relative overflow-hidden bg-gray-50 py-16 md:py-20">
  <div class="pointer-events-none absolute inset-0 overflow-hidden" aria-hidden="true">
    <img src="{{ asset('frontend/assets/img/front-pages/akene-seeds/seed-11.svg') }}" alt="" class="absolute left-6 top-16 w-20 -rotate-12 opacity-10 md:left-16 md:w-32">
    <img src="{{ asset('frontend/assets/img/front-pages/akene-seeds/seed-18.svg') }}" alt="" class="absolute right-8 top-28 w-16 rotate-[18deg] opacity-10 md:right-24 md:w-24">
    <img src="{{ asset('frontend/assets/img/front-pages/akene-seeds/seed-23.svg') }}" alt="" class="absolute -right-6 bottom-16 w-28 rotate-12 opacity-10 md:right-10 md:w-40">
  </div>

  <div class="relative mx-auto max-w-[1248px] px-6">
    <div class="grid gap-12 lg:grid-cols-[0.9fr_1.1fr] lg:items-start">
      <aside class="space-y-8 lg:sticky lg:top-28">
        <div>
          <h1 class="flex items-center gap-4 font-raleway text-[36px] font-extrabold leading-tight text-bleuone md:text-[44px]">
            <img src="{{ asset('frontend/assets/img/front-pages/icons/etoile8.gif') }}" width="60" height="61" alt="" aria-hidden="true" class="h-[60px] w-[60px] flex-none object-contain">
            <span>Inscription formateur</span>
          </h1>
          <p class="mt-5 max-w-[58ch] font-lisible text-lg leading-relaxed text-slate-600">
            Créez votre espace Onéduc pour organiser vos parcours, accompagner vos groupes et suivre les progrès de vos apprenants.
          </p>
        </div>

        <div class="group relative mx-auto w-full max-w-md lg:mx-0">
          <div class="absolute -inset-3 rounded-[28px] border-2 border-orangeone/35 bg-orangeone/5 rotate-2 transition-transform duration-500 group-hover:rotate-0"></div>
          <div class="relative rounded-[28px] bg-white p-6 shadow-xl shadow-slate-200/80 transition-transform duration-500 group-hover:-translate-y-2">
            <div class="mx-auto max-w-[260px]">
              {!! file_get_contents(public_path('images/svg/Enseignant.svg')) !!}
            </div>
          </div>
        </div>

        <div class="grid gap-3 font-lisible sm:grid-cols-3 lg:grid-cols-1">
          <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm shadow-slate-200/60">
            <p class="font-semibold text-bleuone">Compte gratuit</p>
            <p class="mt-1 text-sm leading-relaxed text-slate-600">Aucune carte bancaire demandée pour démarrer.</p>
          </div>
          <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm shadow-slate-200/60">
            <p class="font-semibold text-bleuone">Prise en main rapide</p>
            <p class="mt-1 text-sm leading-relaxed text-slate-600">Créez vos premiers groupes après validation du compte.</p>
          </div>
          <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm shadow-slate-200/60">
            <p class="font-semibold text-bleuone">Pensé terrain</p>
            <p class="mt-1 text-sm leading-relaxed text-slate-600">Une interface adaptée aux contextes d'accompagnement.</p>
          </div>
        </div>
      </aside>

      <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-xl shadow-slate-200/80 md:p-8">
        <div class="mb-8 flex flex-col gap-4 border-b border-slate-100 pb-6 md:flex-row md:items-start md:justify-between">
          <div>
            <h2 class="font-raleway text-2xl font-extrabold leading-tight text-bleuone md:text-3xl">Créer mon compte</h2>
            <p class="mt-2 font-lisible text-base leading-relaxed text-slate-600">Les champs marqués d'un astérisque sont obligatoires.</p>
          </div>
          <a href="{{ route('connexion') }}" class="font-lisible text-sm font-semibold text-orangeone underline-offset-4 hover:underline">
            Déjà inscrit ?
          </a>
        </div>

        {{-- Messages de session --}}
        @if(session('success'))
          <div class="mb-6 rounded-lg border border-green-200 bg-green-50 p-4 font-lisible text-green-800" role="alert">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
          <div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4 font-lisible text-red-800" role="alert" aria-live="assertive">
            <p class="font-semibold">Le formulaire comporte des erreurs.</p>
            <ul class="mt-2 list-inside list-disc text-sm">
              @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
            </ul>
          </div>
        @endif

        <form action="{{ route('formateur.inscription') }}" method="POST" class="space-y-7" novalidate>
          @csrf

          {{-- Honeypot anti-bot --}}
          <div class="sr-only">
            <label for="website">Ne pas remplir</label>
            <input id="website" name="website" type="text" tabindex="-1" autocomplete="off">
          </div>

          <div>
            <h3 class="mb-4 font-raleway text-xl font-bold text-bleuone">Vos informations</h3>
            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
              {{-- Prénom --}}
              <div>
                <label for="prenom" class="block font-lisible text-sm font-semibold text-slate-700">Prénom *</label>
                <input type="text" id="prenom" name="prenom" value="{{ old('prenom') }}" required
                       autocomplete="given-name"
                       class="mt-2 block w-full rounded-lg border bg-white px-4 py-3 font-lisible text-base text-slate-800 shadow-sm outline-none transition focus:border-orangeone focus:ring-4 focus:ring-orangeone/10 {{ $errors->has('prenom') ? 'border-red-500' : 'border-slate-300' }}">
                @error('prenom') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
              </div>

              {{-- Nom --}}
              <div>
                <label for="name" class="block font-lisible text-sm font-semibold text-slate-700">Nom *</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required
                       autocomplete="family-name"
                       class="mt-2 block w-full rounded-lg border bg-white px-4 py-3 font-lisible text-base text-slate-800 shadow-sm outline-none transition focus:border-orangeone focus:ring-4 focus:ring-orangeone/10 {{ $errors->has('name') ? 'border-red-500' : 'border-slate-300' }}">
                @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
              </div>

              {{-- Email --}}
              <div>
                <label for="email" class="block font-lisible text-sm font-semibold text-slate-700">Email *</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required autocomplete="email"
                       class="mt-2 block w-full rounded-lg border bg-white px-4 py-3 font-lisible text-base text-slate-800 shadow-sm outline-none transition focus:border-orangeone focus:ring-4 focus:ring-orangeone/10 {{ $errors->has('email') ? 'border-red-500' : 'border-slate-300' }}">
                @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
              </div>

              {{-- Téléphone --}}
              <div>
                <label for="phone" class="block font-lisible text-sm font-semibold text-slate-700">Téléphone <span class="font-normal text-slate-400">(optionnel)</span></label>
                <input type="text" id="phone" name="phone" value="{{ old('phone') }}"
                       autocomplete="tel"
                       class="mt-2 block w-full rounded-lg border border-slate-300 bg-white px-4 py-3 font-lisible text-base text-slate-800 shadow-sm outline-none transition focus:border-orangeone focus:ring-4 focus:ring-orangeone/10">
                @error('phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
              </div>

              {{-- Société --}}
              <div>
                <label for="societe" class="block font-lisible text-sm font-semibold text-slate-700">Structure <span class="font-normal text-slate-400">(optionnel)</span></label>
                <input type="text" id="societe" name="societe" value="{{ old('societe') }}"
                       class="mt-2 block w-full rounded-lg border border-slate-300 bg-white px-4 py-3 font-lisible text-base text-slate-800 shadow-sm outline-none transition focus:border-orangeone focus:ring-4 focus:ring-orangeone/10">
                @error('societe') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
              </div>

              {{-- Adresse --}}
              <div>
                <label for="address" class="block font-lisible text-sm font-semibold text-slate-700">Adresse <span class="font-normal text-slate-400">(optionnel)</span></label>
                <input type="text" id="address" name="address" value="{{ old('address') }}"
                       class="mt-2 block w-full rounded-lg border border-slate-300 bg-white px-4 py-3 font-lisible text-base text-slate-800 shadow-sm outline-none transition focus:border-orangeone focus:ring-4 focus:ring-orangeone/10">
                @error('address') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
              </div>
            </div>
          </div>

          <div>
            <h3 class="mb-4 font-raleway text-xl font-bold text-bleuone">Sécuriser l'accès</h3>
            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
              {{-- Mot de passe --}}
              <div>
                <label for="password" class="block font-lisible text-sm font-semibold text-slate-700">Mot de passe *</label>
                <input type="password" id="password" name="password" required autocomplete="new-password"
                       class="mt-2 block w-full rounded-lg border bg-white px-4 py-3 font-lisible text-base text-slate-800 shadow-sm outline-none transition focus:border-orangeone focus:ring-4 focus:ring-orangeone/10 {{ $errors->has('password') ? 'border-red-500' : 'border-slate-300' }}">
                <p class="mt-1 font-lisible text-sm text-slate-500">Minimum 8 caractères.</p>
                @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
              </div>

              {{-- Confirmation mot de passe --}}
              <div>
                <label for="password_confirmation" class="block font-lisible text-sm font-semibold text-slate-700">Confirmation *</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password"
                       class="mt-2 block w-full rounded-lg border border-slate-300 bg-white px-4 py-3 font-lisible text-base text-slate-800 shadow-sm outline-none transition focus:border-orangeone focus:ring-4 focus:ring-orangeone/10">
              </div>
            </div>
          </div>

          {{-- reCAPTCHA --}}
          <div class="rounded-lg border border-slate-200 bg-gray-50 p-4">
            <p class="mb-3 font-lisible text-sm font-semibold text-slate-700">Vérification anti-spam *</p>
            @error('g-recaptcha-response')
              <p class="mb-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
            <div class="overflow-x-auto">
              {!! NoCaptcha::display(['data-theme' => 'light']) !!}
            </div>
          </div>

          {{-- Boutons --}}
          <div class="flex flex-col gap-3 border-t border-slate-100 pt-6 sm:flex-row sm:items-center">
            <button type="submit" class="btn-oneduc justify-center">Créer mon compte formateur</button>
            <button type="reset" class="inline-flex items-center justify-center rounded-full border-2 border-bleuone px-6 py-3 font-lisible font-semibold text-bleuone transition hover:bg-bleuone hover:text-white">Effacer le formulaire</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</section>

@push('scripts')
  {!! NoCaptcha::renderJs('fr') !!}
@endpush

@endsection
