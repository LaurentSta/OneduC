@props([
    'formAction',
    'errorBag' => 'accountDeletion',
    'modalSessionKey' => 'openDeleteAccountModal',
    'title' => 'Supprimer mon compte',
    'description' => null,
    'modalTitle' => 'Supprimer définitivement le compte',
    'modalDescription' => null,
    'consequences' => [],
    'submitLabel' => 'Supprimer définitivement mon compte',
    'passwordLabel' => 'Mot de passe actuel',
    'passwordPlaceholder' => 'Confirmez avec votre mot de passe actuel',
])

@php
    $deletionErrors = $errors->getBag($errorBag);
    $shouldOpenModal = session($modalSessionKey, false) || $deletionErrors->any();
@endphp

<section
    class="rounded-[20px] border border-red-100 bg-white p-8 shadow-md"
    x-data="{ openDeleteAccountModal: @js($shouldOpenModal) }"
    x-init="if (openDeleteAccountModal) { document.body.classList.add('overflow-y-hidden'); $nextTick(() => $refs.deleteAccountPassword?.focus()); } $watch('openDeleteAccountModal', value => { document.body.classList.toggle('overflow-y-hidden', value); if (value) { $nextTick(() => $refs.deleteAccountPassword?.focus()); } })"
    @keydown.escape.window="openDeleteAccountModal = false"
    aria-labelledby="delete-account-zone-title"
>
    <div class="flex flex-col gap-6 md:flex-row md:items-start md:justify-between">
        <div class="space-y-3">
            <div class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-red-50 text-red-500">
                <x-icons.trash-iconify class="h-6 w-6" />
            </div>

            <div class="space-y-2">
                <h2 id="delete-account-zone-title" class="font-varela text-2xl text-red-600">
                    {{ $title }}
                </h2>

                @if ($description)
                    <p class="max-w-3xl text-base text-slate-600">
                        {{ $description }}
                    </p>
                @endif
            </div>
        </div>

        <button
            type="button"
            class="btn-oneduc-danger !px-6 !py-2.5 !text-sm"
            @click="openDeleteAccountModal = true"
        >
            <x-icons.trash-iconify class="h-5 w-5" />
            Supprimer le compte
        </button>
    </div>

    <div class="mt-6 rounded-2xl border border-red-100 bg-red-50/70 p-5 text-sm text-red-700">
        Cette action est définitive. Une confirmation par mot de passe est demandée avant toute suppression.
    </div>

    <div
        x-show="openDeleteAccountModal"
        x-transition.opacity
        class="fixed inset-0 z-[80] flex items-center justify-center bg-slate-950/55 px-4 py-8"
        style="display: none;"
        aria-modal="true"
        role="dialog"
    >
        <div
            class="w-full max-w-3xl rounded-[28px] bg-white shadow-2xl"
            @click.outside="openDeleteAccountModal = false"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
        >
            <div class="border-b border-slate-100 px-8 py-6">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-start gap-4">
                        <span class="inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-red-50 text-red-500">
                            <x-icons.trash-iconify class="h-6 w-6" />
                        </span>

                        <div class="space-y-2">
                            <h3 class="font-varela text-2xl text-[#004461]">
                                {{ $modalTitle }}
                            </h3>

                            @if ($modalDescription)
                                <p class="text-sm leading-6 text-slate-600">
                                    {{ $modalDescription }}
                                </p>
                            @endif
                        </div>
                    </div>

                    <button
                        type="button"
                        class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 text-slate-500 transition hover:border-slate-300 hover:text-slate-700"
                        @click="openDeleteAccountModal = false"
                        aria-label="Fermer la fenêtre"
                    >
                        <span class="text-xl leading-none">&times;</span>
                    </button>
                </div>
            </div>

            <div class="space-y-6 px-8 py-6">
                @if (! empty($consequences))
                    <div class="rounded-2xl border border-orangeone/15 bg-orange-50/50 p-5">
                        <p class="font-varela text-lg text-orangeone">Conséquences de la suppression</p>
                        <ul class="mt-4 space-y-3 text-sm leading-6 text-slate-700">
                            @foreach ($consequences as $consequence)
                                <li class="flex items-start gap-3">
                                    <span class="mt-1 inline-flex h-2.5 w-2.5 shrink-0 rounded-full bg-orangeone"></span>
                                    <span>{{ $consequence }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if ($deletionErrors->any())
                    <div class="rounded-2xl border border-red-200 bg-red-50 p-4">
                        <p class="text-sm font-semibold text-red-700">La suppression n’a pas pu être confirmée.</p>
                        <ul class="mt-2 list-disc pl-5 text-sm text-red-700">
                            @foreach ($deletionErrors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ $formAction }}" class="space-y-6">
                    @csrf
                    @method('DELETE')

                    <div>
                        <label for="delete-account-password" class="block text-sm font-medium text-slate-700">
                            {{ $passwordLabel }}
                        </label>
                        <input
                            id="delete-account-password"
                            x-ref="deleteAccountPassword"
                            type="password"
                            name="password"
                            class="mt-2 w-full rounded-xl border-gray-300 focus:border-orangeone focus:ring-orangeone"
                            placeholder="{{ $passwordPlaceholder }}"
                            autocomplete="current-password"
                            required
                        >
                    </div>

                    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                        <button
                            type="button"
                            class="btn-oneduc-outline !px-6 !py-2.5 !text-sm"
                            @click="openDeleteAccountModal = false"
                        >
                            Annuler
                        </button>

                        <button
                            type="submit"
                            class="btn-oneduc-danger !px-6 !py-2.5 !text-sm"
                        >
                            <x-icons.trash-iconify class="h-5 w-5" />
                            {{ $submitLabel }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
