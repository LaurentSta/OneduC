@extends('formateur.dashboard')

@section('formateur')

@php
  $selectedGroupIds = collect(old('group_ids', $stagiaire->groupesStagiaire->pluck('id')->all()))
    ->map(fn ($groupId) => (int) $groupId)
    ->all();
@endphp

<div class="max-w-[1285px] mx-auto px-8">
  <header class="bg-white rounded-[20px] shadow-md px-8 pt-4 pb-6 w-full mb-6">
    <div class="grid grid-cols-12 gap-6 items-start">
      <div class="col-span-12">
        <x-typography variant="titre">Modifier le stagiaire</x-typography>
        <x-typography variant="sous-titre" class="font-varela text-sous-titre text-orangeone">
          Mettez à jour ses informations et ses groupes.
        </x-typography>
        <x-typography>
          Vous pouvez corriger ses données et ajuster son rattachement aux groupes sans repasser par le wizard de groupe.
        </x-typography>

        <nav class="text-sm font-varela text-gray-600 mt-2 mb-6" aria-label="Fil d'Ariane">
          <ol class="list-none p-0 inline-flex items-center space-x-1">
            <li class="flex items-center">
              <a href="{{ route('formateur.dashboard') }}" class="text-orangeone hover:underline flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 9.75L12 3l9 6.75V19a2 2 0 01-2 2h-4a1 1 0 01-1-1v-5H10v5a1 1 0 01-1 1H5a2 2 0 01-2-2V9.75z"/>
                </svg>
              </a>
              <span class="mx-2 text-gray-400">/</span>
            </li>
            <li class="flex items-center">
              <a href="{{ route('formateur.stagiaires.index') }}" class="hover:underline text-bleuone">Mes stagiaires</a>
              <span class="mx-2 text-gray-400">/</span>
            </li>
            <li class="text-gray-400">Modifier un stagiaire</li>
          </ol>
        </nav>
      </div>
    </div>
  </header>

  <main class="bg-white rounded-[20px] shadow-md px-8 py-10 w-full">
    @if ($errors->any())
      <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
        <ul class="list-disc list-inside space-y-1">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form method="POST" action="{{ route('formateur.stagiaires.update', $stagiaire->id) }}" class="space-y-8">
      @csrf
      @method('PUT')

      <section class="grid grid-cols-1 lg:grid-cols-[2fr_1fr] gap-6">
        <div class="rounded-[16px] border border-gray-200 p-6">
          <h2 class="text-lg font-bold text-bleuone font-raleway mb-1">Informations du stagiaire</h2>
          <p class="text-sm text-gray-600 font-lisible mb-6">Modifiez ses informations principales.</p>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label for="prenom" class="block mb-2 text-sm font-medium text-gray-900">Prénom</label>
              <input id="prenom" type="text" name="prenom" value="{{ old('prenom', $stagiaire->prenom) }}"
                     class="w-full rounded-lg border {{ $errors->has('prenom') ? 'border-red-400' : 'border-gray-300' }} bg-gray-50 px-4 py-2.5 text-sm focus:border-orangeone focus:ring-orangeone"
                     required>
            </div>

            <div>
              <label for="name" class="block mb-2 text-sm font-medium text-gray-900">Nom</label>
              <input id="name" type="text" name="name" value="{{ old('name', $stagiaire->name) }}"
                     class="w-full rounded-lg border {{ $errors->has('name') ? 'border-red-400' : 'border-gray-300' }} bg-gray-50 px-4 py-2.5 text-sm focus:border-orangeone focus:ring-orangeone"
                     required>
            </div>

            <div>
              <label for="email" class="block mb-2 text-sm font-medium text-gray-900">Adresse e-mail</label>
              <input id="email" type="email" name="email" value="{{ old('email', $stagiaire->email) }}"
                     class="w-full rounded-lg border {{ $errors->has('email') ? 'border-red-400' : 'border-gray-300' }} bg-gray-50 px-4 py-2.5 text-sm focus:border-orangeone focus:ring-orangeone"
                     required>
            </div>

            <div>
              <label for="password" class="block mb-2 text-sm font-medium text-gray-900">Nouveau mot de passe</label>
              <input id="password" type="password" name="password"
                     class="w-full rounded-lg border {{ $errors->has('password') ? 'border-red-400' : 'border-gray-300' }} bg-gray-50 px-4 py-2.5 text-sm focus:border-orangeone focus:ring-orangeone"
                     placeholder="Laisser vide si inchangé">
            </div>

            <div class="md:col-span-2">
              <label for="code_acces" class="block mb-2 text-sm font-medium text-gray-900">Code d'accès</label>
              <input id="code_acces" type="text" name="code_acces" value="{{ old('code_acces', $stagiaire->code_acces) }}"
                     maxlength="6"
                     class="w-full rounded-lg border {{ $errors->has('code_acces') ? 'border-red-400' : 'border-gray-300' }} bg-gray-50 px-4 py-2.5 font-mono text-sm uppercase tracking-widest text-orangeone focus:border-orangeone focus:ring-orangeone"
                     placeholder="Ex : CODE12">
            </div>
          </div>
        </div>

        <div class="rounded-[16px] border border-bleuone/20 bg-bleuone/5 p-6">
          <h2 class="text-lg font-bold text-bleuone font-raleway mb-1">Groupes associés</h2>
          <p class="text-sm text-gray-600 font-lisible mb-2">Choisissez ici les groupes du formateur auxquels ce stagiaire doit appartenir.</p>
          <p class="text-xs text-gray-500 font-lisible mb-4">C'est depuis cette fiche que vous pouvez maintenant l'inscrire à un groupe ou le retirer d'un groupe.</p>

          @if($groupes->isNotEmpty())
            <div class="space-y-3">
              @foreach($groupes as $groupe)
                <label class="flex items-start gap-3 rounded-xl border border-white bg-white px-4 py-3 shadow-sm hover:border-bleuone/20">
                  <input type="checkbox"
                         name="group_ids[]"
                         value="{{ $groupe->id }}"
                         @checked(in_array((int) $groupe->id, $selectedGroupIds, true))
                         class="mt-1 rounded border-gray-300 text-bleuone focus:ring-orangeone">
                  <span>
                    <span class="block text-sm font-semibold text-gray-900">{{ $groupe->name }}</span>
                    <span class="block text-xs text-gray-500">Le stagiaire sera visible dans ce groupe.</span>
                  </span>
                </label>
              @endforeach
            </div>
          @else
            <div class="rounded-xl border border-orangeone/20 bg-white px-4 py-3 text-sm text-gray-700">
              Aucun groupe n'est disponible.
              <a href="{{ route('formateur.groupes.create') }}" class="font-semibold text-orangeone hover:underline">
                Créer un groupe
              </a>
            </div>
          @endif
        </div>
      </section>

      <div class="flex flex-col md:flex-row items-center justify-between gap-4">
        <a href="{{ route('formateur.stagiaires.index') }}"
           class="btn-oneduc-outline !px-5 !py-2.5 !text-sm">
          Retour à la liste
        </a>

        <div class="flex w-full md:w-auto flex-col md:flex-row items-center gap-3">
          {{-- Bouton boîte aux lettres --}}
          <button type="button"
                  @click="$dispatch('open-messages')"
                  title="Messages avec {{ $stagiaire->prenom }} {{ $stagiaire->name }}"
                  class="relative inline-flex items-center justify-center w-10 h-10 rounded-full border border-gray-200 bg-white text-bleuone hover:bg-bleuone hover:text-white hover:border-bleuone transition-colors shadow-sm">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
              <path d="M3 8L10.89 13.26C11.2204 13.4793 11.6056 13.5963 12 13.5963C12.3944 13.5963 12.7796 13.4793 13.11 13.26L21 8M5 19H19C19.5304 19 20.0391 18.7893 20.4142 18.4142C20.7893 18.0391 21 17.5304 21 17V7C21 6.46957 20.7893 5.96086 20.4142 5.58579C20.0391 5.21071 19.5304 5 19 5H5C4.46957 5 3.96086 5.21071 3.58579 5.58579C3.21071 5.96086 3 6.46957 3 7V17C3 17.5304 3.21071 18.0391 3.58579 18.4142C3.96086 18.7893 4.46957 19 5 19Z"/>
            </svg>
            @if($messages->count() > 0)
              <span class="absolute -top-1 -right-1 inline-flex items-center justify-center min-w-[18px] h-[18px] px-1 rounded-full bg-orangeone text-white text-[10px] font-bold">
                {{ $messages->count() > 9 ? '9+' : $messages->count() }}
              </span>
            @endif
          </button>

          <button type="submit" class="btn-oneduc w-full md:w-auto px-8 py-3 text-lg">
            Enregistrer les modifications
          </button>
        </div>
      </div>
    </form>
  </main>
</div>

{{-- ═══════════════ DRAWER MESSAGES ═══════════════ --}}
<div
  x-data="{ open: {{ session('message_sent') || $errors->has('body') || $errors->has('channels') ? 'true' : 'false' }} }"
  @open-messages.window="open = true"
  @keydown.escape.window="open = false"
  x-cloak
>
  {{-- Backdrop --}}
  <div x-show="open"
       x-transition:enter="transition-opacity duration-300"
       x-transition:enter-start="opacity-0"
       x-transition:enter-end="opacity-100"
       x-transition:leave="transition-opacity duration-200"
       x-transition:leave-start="opacity-100"
       x-transition:leave-end="opacity-0"
       @click="open = false"
       class="fixed inset-0 bg-black/30 z-40">
  </div>

  {{-- Panneau latéral --}}
  <div x-show="open"
       x-transition:enter="transition-transform duration-1000 ease-in-out"
       x-transition:enter-start="translate-x-full"
       x-transition:enter-end="translate-x-0"
       x-transition:leave="transition-transform duration-1000 ease-in-out"
       x-transition:leave-start="translate-x-0"
       x-transition:leave-end="translate-x-full"
       class="fixed top-0 right-0 h-full w-full max-w-md bg-white shadow-xl z-50 flex flex-col">

    {{-- En-tête du drawer --}}
    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 shrink-0">
      <div>
        <p class="font-raleway font-bold text-bleuone text-base">Messages</p>
        <p class="text-xs text-gray-500 mt-0.5">{{ $stagiaire->prenom }} {{ $stagiaire->name }}</p>
      </div>
      <button @click="open = false" class="text-gray-400 hover:text-gray-600 transition-colors p-1 rounded-lg hover:bg-gray-100">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5">
          <path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z"/>
        </svg>
      </button>
    </div>

    {{-- Contenu scrollable --}}
    <div class="flex-1 overflow-y-auto px-6 py-5 space-y-6">

      {{-- Feedback envoi --}}
      @if(session('message_sent'))
        <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
          {{ session('message_sent') }}
        </div>
      @endif

      {{-- Formulaire de composition --}}
      <form method="POST" action="{{ route('formateur.stagiaires.message.send', $stagiaire->id) }}" class="space-y-4">
        @csrf

        @if($errors->has('channels'))
          <p class="text-xs text-red-600">{{ $errors->first('channels') }}</p>
        @endif

        <div>
          <label for="msg_subject" class="block mb-1.5 text-xs font-medium text-gray-700 uppercase tracking-wide">Sujet <span class="normal-case text-gray-400">(optionnel)</span></label>
          <input id="msg_subject" type="text" name="subject" value="{{ old('subject') }}"
                 placeholder="Ex : Rappel, Question sur votre avancement…"
                 class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm focus:border-orangeone focus:ring-orangeone">
        </div>

        <div>
          <label for="msg_body" class="block mb-1.5 text-xs font-medium text-gray-700 uppercase tracking-wide">Message <span class="text-red-500">*</span></label>
          <textarea id="msg_body" name="body" rows="4" required
                    placeholder="Rédigez votre message…"
                    class="w-full rounded-lg border {{ $errors->has('body') ? 'border-red-400' : 'border-gray-300' }} bg-gray-50 px-3 py-2 text-sm focus:border-orangeone focus:ring-orangeone resize-none">{{ old('body') }}</textarea>
          @error('body')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
          @enderror
        </div>

        <div
          x-data="{ checked: {{ old('include_access_code') ? 'true' : 'false' }} }"
          @click="checked = !checked"
          class="rounded-xl border border-orangeone/20 bg-orangeone/5 px-4 py-3 cursor-pointer select-none"
        >
          <div class="flex items-start gap-3">
            <input type="checkbox" name="include_access_code" value="1" x-model="checked" class="hidden">
            <div class="mt-0.5 w-4 h-4 rounded border-2 transition-colors flex items-center justify-center shrink-0"
                 :class="checked ? 'bg-orangeone border-orangeone' : 'bg-white border-orangeone/40'">
              <svg x-show="checked" class="w-2.5 h-2.5 text-white" fill="none" viewBox="0 0 10 10" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2 2l6 6M8 2l-6 6"/>
              </svg>
            </div>
            <div>
              <p class="text-sm font-semibold text-bleuone">Inclure le lien et le code d'accès du stagiaire</p>
              <p class="mt-1 text-xs leading-5 text-gray-500">
                Ajoute automatiquement le lien de connexion et le code
                <span class="font-mono font-semibold text-orangeone">{{ $stagiaire->code_acces ?: 'à générer' }}</span>
                à la fin du message envoyé.
              </p>
            </div>
          </div>
        </div>

        {{-- Cases à cocher canaux --}}
        <div>
          <p class="mb-2 text-xs font-medium text-gray-700 uppercase tracking-wide">Envoyer via</p>
          <div class="flex gap-4">
            <div x-data="{ checked: {{ old('send_notification') ? 'true' : 'false' }} }"
                 @click="checked = !checked"
                 class="flex items-center gap-2 cursor-pointer select-none">
              <input type="checkbox" name="send_notification" value="1" x-model="checked" class="hidden">
              <div class="w-4 h-4 rounded border-2 transition-colors flex items-center justify-center shrink-0"
                   :class="checked ? 'bg-orangeone border-orangeone' : 'bg-white border-gray-300'">
                <svg x-show="checked" class="w-2.5 h-2.5 text-white" fill="none" viewBox="0 0 10 10" stroke="currentColor" stroke-width="2.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M2 2l6 6M8 2l-6 6"/>
                </svg>
              </div>
              <span class="text-sm text-gray-700">Notification</span>
            </div>

            <div x-data="{ checked: {{ old('send_email') ? 'true' : 'false' }} }"
                 @click="checked = !checked"
                 class="flex items-center gap-2 cursor-pointer select-none">
              <input type="checkbox" name="send_email" value="1" x-model="checked" class="hidden">
              <div class="w-4 h-4 rounded border-2 transition-colors flex items-center justify-center shrink-0"
                   :class="checked ? 'bg-orangeone border-orangeone' : 'bg-white border-gray-300'">
                <svg x-show="checked" class="w-2.5 h-2.5 text-white" fill="none" viewBox="0 0 10 10" stroke="currentColor" stroke-width="2.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M2 2l6 6M8 2l-6 6"/>
                </svg>
              </div>
              <span class="text-sm text-gray-700">Email</span>
            </div>
          </div>
        </div>

        <button type="submit" class="btn-oneduc w-full py-2.5 text-sm">
          Envoyer
        </button>
      </form>

      {{-- Séparateur historique --}}
      @if($messages->isNotEmpty())
        <div class="border-t border-gray-100 pt-4">
          <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-3">Historique ({{ $messages->count() }})</p>
          <div class="space-y-2">
            @foreach($messages as $msg)
              <div x-data="{ open: false }"
                   class="rounded-xl border border-gray-100 bg-gray-50 overflow-hidden">
                <button type="button" @click="open = !open"
                        class="w-full flex items-center justify-between px-4 py-3 text-left hover:bg-gray-100 transition-colors">
                  <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2">
                      <p class="text-xs font-semibold text-gray-800 truncate">{{ $msg->subject ?: '(Sans sujet)' }}</p>
                      <div class="flex gap-1 shrink-0">
                        @if($msg->sent_as_notification)
                          <span title="Notification" class="w-4 h-4 rounded-full bg-bleuone/10 text-bleuone inline-flex items-center justify-center">
                            <svg viewBox="0 0 16 16" fill="currentColor" class="w-2.5 h-2.5"><path d="M2 6a6 6 0 1 1 12 0c0 3.5 1.5 4.5 1.5 4.5H.5S2 9.5 2 6ZM5.5 13a2.5 2.5 0 0 0 5 0h-5Z"/></svg>
                          </span>
                        @endif
                        @if($msg->sent_as_email)
                          <span title="Email" class="w-4 h-4 rounded-full bg-orangeone/10 text-orangeone inline-flex items-center justify-center">
                            <svg viewBox="0 0 16 16" fill="currentColor" class="w-2.5 h-2.5"><path d="M1 4a1.5 1.5 0 0 1 1.5-1.5h11A1.5 1.5 0 0 1 15 4v.217L8 8.6 1 4.217V4Zm14 1.616-5.967 3.7a1 1 0 0 1-1.066 0L1 5.616V12A1.5 1.5 0 0 0 2.5 13.5h11A1.5 1.5 0 0 0 15 12V5.616Z"/></svg>
                          </span>
                        @endif
                      </div>
                    </div>
                    <p class="text-[11px] text-gray-400 mt-0.5">{{ $msg->created_at->diffForHumans() }}</p>
                  </div>
                  <svg viewBox="0 0 20 20" fill="currentColor"
                       :class="open ? 'rotate-180' : ''"
                       class="w-3.5 h-3.5 text-gray-400 shrink-0 ml-2 transition-transform duration-150">
                    <path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/>
                  </svg>
                </button>
                <div x-show="open" x-cloak x-collapse.duration.400ms class="px-4 pb-3 border-t border-gray-100">
                  <p class="text-xs text-gray-700 whitespace-pre-wrap pt-3 font-lisible leading-relaxed">{{ $msg->body }}</p>
                </div>
              </div>
            @endforeach
          </div>
        </div>
      @endif

    </div>
  </div>
</div>

@endsection
