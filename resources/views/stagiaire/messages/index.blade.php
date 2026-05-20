@extends('stagiaire.master')

@section('content')

<div class="max-w-[1285px] mx-auto px-8">

  <header class="bg-white rounded-[20px] shadow-md px-8 pt-4 pb-6 w-full mb-6">
    <div class="grid grid-cols-12 gap-6 items-center">
      <div class="col-span-12 md:col-span-8">
        <p class="font-raleway text-titre text-bleuone leading-tight mb-4">Mes messages</p>
        <p class="font-varela text-sous-titre text-orangeone leading-snug mb-3">
          Messages reçus de votre formateur.
        </p>
        <p class="font-lisible text-lg text-gray-800 leading-loose mb-6">
          Retrouvez ici tous les messages que votre formateur vous a envoyés.
        </p>

        <nav class="text-sm font-varela text-gray-600 mt-2" aria-label="Fil d'Ariane">
          <ol class="inline-flex items-center space-x-1">
            <li class="flex items-center">
              <a href="{{ route('stagiaire.dashboard') }}" class="text-orangeone hover:underline flex items-center">
                <span class="sr-only">Accueil</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 9.75L12 3l9 6.75V19a2 2 0 01-2 2h-4a1 1 0 01-1-1v-5H10v5a1 1 0 01-1 1H5a2 2 0 01-2-2V9.75z"/>
                </svg>
              </a>
              <span class="mx-2 text-gray-400" aria-hidden="true">/</span>
            </li>
            <li class="text-gray-400">Messages</li>
          </ol>
        </nav>
      </div>
    </div>
  </header>

  <main class="bg-white rounded-[20px] shadow-md px-8 py-8 w-full">

    @if($messages->isEmpty())
      <div class="flex flex-col items-center justify-center py-16 text-center text-gray-500">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 mb-4 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/>
        </svg>
        <p class="text-base font-medium">Aucun message pour le moment.</p>
        <p class="text-sm mt-1">Votre formateur vous enverra des messages ici.</p>
      </div>
    @else
      <div class="space-y-3" x-data="{}">
        @foreach($messages as $message)
          @php
            $formateurName = $message->formateur
              ? trim($message->formateur->prenom . ' ' . $message->formateur->name)
              : 'Votre formateur';
          @endphp
          <div x-data="{ open: false }"
               class="rounded-[16px] border overflow-hidden transition-colors"
               :class="open ? 'border-bleuone/30' : 'border-gray-200'">

            <button type="button"
                    @click="open = !open"
                    class="w-full flex items-start gap-4 px-5 py-4 text-left hover:bg-gray-50 transition-colors">

              <div class="mt-0.5 shrink-0 w-9 h-9 rounded-full bg-bleuone/10 flex items-center justify-center text-bleuone font-bold text-sm uppercase">
                {{ mb_substr($formateurName, 0, 1) }}
              </div>

              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                  <p class="text-sm font-semibold text-gray-900">
                    {{ $message->subject ?: '(Sans sujet)' }}
                  </p>
                  <div class="flex gap-1.5">
                    @if($message->sent_as_notification)
                      <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-bleuone/10 text-bleuone text-[11px] font-medium">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="w-3 h-3">
                          <path d="M3 6a5 5 0 1 1 10 0c0 3 1.5 4 1.5 4H1.5S3 9 3 6ZM6 13a2 2 0 0 0 4 0H6Z"/>
                        </svg>
                        Notif
                      </span>
                    @endif
                    @if($message->sent_as_email)
                      <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-orangeone/10 text-orangeone text-[11px] font-medium">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="w-3 h-3">
                          <path d="M1.5 3.5A1.5 1.5 0 0 1 3 2h10a1.5 1.5 0 0 1 1.5 1.5v.5L8 8 1.5 4v-.5Zm0 1.61V12A1.5 1.5 0 0 0 3 13.5h10A1.5 1.5 0 0 0 14.5 12V5.11L8 9.2 1.5 5.11Z"/>
                        </svg>
                        Email
                      </span>
                    @endif
                  </div>
                </div>
                <p class="text-xs text-gray-500 mt-0.5">
                  {{ $formateurName }} · {{ $message->created_at->diffForHumans() }}
                </p>
                <p x-show="!open" class="text-xs text-gray-600 mt-1 truncate">
                  {{ mb_strimwidth($message->body, 0, 100, '…') }}
                </p>
              </div>

              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                   :class="open ? 'rotate-180' : ''"
                   class="w-4 h-4 text-gray-400 shrink-0 mt-1 transition-transform duration-200">
                <path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/>
              </svg>
            </button>

            <div x-show="open" x-cloak x-collapse class="px-5 pb-5 border-t border-gray-100">
              <p class="text-sm text-gray-700 whitespace-pre-wrap pt-4 font-lisible leading-relaxed">{{ $message->body }}</p>
              <p class="text-xs text-gray-400 mt-3">Reçu le {{ $message->created_at->format('d/m/Y à H:i') }}</p>
            </div>
          </div>
        @endforeach
      </div>
    @endif

  </main>
</div>

@endsection
