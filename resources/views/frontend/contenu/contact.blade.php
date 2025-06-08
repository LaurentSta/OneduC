@extends('frontend.master')
@section('title', 'Contactez-nous')
@section('home')

<div class="container mx-auto px-4 pt-8 pb-2">
    <div class="bg-white rounded-[20px] shadow-md px-8 py-0 mb-4 w-full max-w-[1285px] mx-auto">
        <div class="grid grid-cols-12 gap-6 items-center">
            <div class="col-span-12 md:col-span-8">
                <x-typography variant="titre">Formulaire de contact</x-typography>
                <x-typography variant="sous-titre" class="font-varela text-sous-titre text-orangeone">
                    Une question, une remarque ? Écrivez-nous.
                </x-typography>
                <x-typography>
                    Remplissez ce formulaire, notre équipe vous répondra dans les plus brefs délais.
                    Merci d’indiquer votre profil pour adapter au mieux notre réponse.
                </x-typography>
            </div>
            <div class="col-span-12 md:col-span-4 flex justify-center md:justify-end">
                <div class="w-full max-w-xs">
                    {!! file_get_contents(public_path('frontend/assets/img/illustrations/AssociationOneduc.svg')) !!}
                </div>
            </div>
        </div>
    </div>
</div>

<div class="content-wrapper bg-white py-10">
    <div class="mx-auto px-4 max-w-[1285px]">
        <div class="card shadow-sm p-6">
            <p class="text-sm text-gray-600 mb-6">* Champs obligatoires</p>

            <form action="{{ route('contact.send') }}" method="POST" class="space-y-6">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Prénom -->
                    <div>
                        <label for="prenom" class="block text-sm font-medium text-gray-700">Prénom</label>
                        <input type="text" id="prenom" name="prenom" value="{{ old('prenom') }}"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                    </div>

                    <!-- Nom -->
                    <div>
                        <label for="nom" class="block text-sm font-medium text-gray-700">Nom *</label>
                        <input type="text" id="nom" name="nom" value="{{ old('nom') }}" required
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                    </div>

                    <!-- Type d'utilisateur -->
            <!-- Type d'utilisateur (radio) -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Je suis *</label>
                <div class="flex items-center gap-6">
                    <label class="flex items-center">
                        <input type="radio" name="type_utilisateur" value="formateur" checked onchange="toggleObjetOptions(this.value)"
                            class="form-radio text-orangeone focus:ring-orangeone" />
                        <span class="ml-2 text-sm text-gray-700">Formateur</span>
                    </label>
                    <label class="flex items-center">
                        <input type="radio" name="type_utilisateur" value="stagiaire" onchange="toggleObjetOptions(this.value)"
                            class="form-radio text-orangeone focus:ring-orangeone" />
                        <span class="ml-2 text-sm text-gray-700">Stagiaire</span>
                    </label>
                </div>
            </div>



                    <!-- Objet conditionnel -->
                    <div>
                        <label for="objet" class="block text-sm font-medium text-gray-700">Objet *</label>
                        <div id="objet-formateur" class="hidden">
                            <label for="objet_formateur" class="block text-sm text-gray-500">Formateur :</label>
                            <select name="objet_formateur" id="objet_formateur"
                                class="mt-2 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                <option value="">Sélectionnez un objet</option>
                                <option value="demande_info">Demande d'information</option>
                                <option value="support">Support technique</option>
                                <option value="creation_module">Demande de création de leçon/module</option>
                                <option value="autre">Autre</option>
                            </select>
                        </div>


                        <div id="objet-stagiaire" class="hidden">
                            <label class="block text-sm text-gray-500">Stagiaire :</label>
                            <select name="objet_stagiaire" class="mt-2 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                <option value="">Sélectionnez un objet</option>
                                <option value="bug">Signaler un bug</option>
                                <option value="incomprehension">Incompréhension sur une leçon</option>
                                <option value="probleme_connexion">Problème de connexion</option>
                            </select>
                        </div>
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email *</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                    </div>

                    <!-- Téléphone -->
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700">Téléphone</label>
                        <input type="text" id="phone" name="phone" value="{{ old('phone') }}"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                    </div>

                    <!-- Créneau d’appel -->
                    <div>
                        <label for="heure_appel" class="block text-sm font-medium text-gray-700">Meilleur moment pour être appelé</label>
                        <select name="heure_appel" id="heure_appel"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                            <option value="">Sélectionner une heure</option>
                            @for ($h = 8; $h <= 18; $h++)
                                <option value="{{ $h }}h">{{ $h }}h</option>
                                <option value="{{ $h }}h30">{{ $h }}h30</option>
                            @endfor
                        </select>
                    </div>
                </div>

                <!-- Message -->
                <div>
                    <label for="message" class="block text-sm font-medium text-gray-700">Votre message / question *</label>
                    <textarea id="message" name="message" required rows="4"
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2"></textarea>
                </div>

                <!-- Boutons -->
                <div class="pt-4 flex space-x-4">
                    <button type="submit" class="btn-oneduc">Envoyer</button>
                    <button type="reset" class="btn btn-outline-secondary">Annuler</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    function toggleObjetOptions(type) {
        document.getElementById('objet-formateur').classList.add('hidden');
        document.getElementById('objet-stagiaire').classList.add('hidden');

        if (type === 'formateur') {
            document.getElementById('objet-formateur').classList.remove('hidden');
        } else if (type === 'stagiaire') {
            document.getElementById('objet-stagiaire').classList.remove('hidden');
        }
    }

    // Afficher le bon bloc au chargement (Formateur par défaut)
    document.addEventListener("DOMContentLoaded", function () {
        toggleObjetOptions('formateur');
    });
</script>

<script>
    function toggleObjetOptions(type) {
        document.getElementById('objet-formateur').classList.add('hidden');
        document.getElementById('objet-stagiaire').classList.add('hidden');

        if (type === 'formateur') {
            document.getElementById('objet-formateur').classList.remove('hidden');
        } else if (type === 'stagiaire') {
            document.getElementById('objet-stagiaire').classList.remove('hidden');
        }
    }
</script>

@endsection
