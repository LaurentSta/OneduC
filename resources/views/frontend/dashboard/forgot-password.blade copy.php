<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        Vous avez oublié votre mot de passe ? Pas de problème. Indiquez-nous votre adresse électronique et nous vous enverrons un lien de réinitialisation du mot de passe qui vous permettra d'en choisir un nouveau.
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                Lien de réinitialisation du mot de passe par courriel
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
