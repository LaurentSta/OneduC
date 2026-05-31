@if (($activeLessonPart ?? null) === 'ajouter-stagiaire')
@php
    $groupes = collect([
        (object) ['id' => 1, 'name' => 'Hygiène alimentaire 2026 - promo 1'],
    ]);

    $selectedGroupId = null;
    $finalisationUrl = $mixedPartUrls['ajustement-groupe-finalisation'] ?? '#';
@endphp

<div class="mx-auto w-full max-w-[1285px]">
    <main class="rounded-[20px] bg-white px-8 py-8 shadow-md">
        <form action="{{ $finalisationUrl }}" method="GET" class="mx-auto max-w-4xl space-y-6">

            <section class="rounded-[16px] border border-gray-200 p-6 md:p-8">
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div>
                        <label for="lesson_stagiaire_prenom" class="mb-2 block text-sm font-medium text-gray-900">Prenom</label>
                        <input id="lesson_stagiaire_prenom" name="prenom" type="text" required
                               value="{{ old('prenom') }}"
                               class="w-full rounded-lg border {{ $errors->has('prenom') ? 'border-red-400' : 'border-gray-300' }} bg-gray-50 px-4 py-2.5 text-sm focus:border-orangeone focus:ring-orangeone"
                               placeholder="Camille">
                    </div>

                    <div>
                        <label for="lesson_stagiaire_name" class="mb-2 block text-sm font-medium text-gray-900">Nom</label>
                        <input id="lesson_stagiaire_name" name="name" type="text" required
                               value="{{ old('name') }}"
                               class="w-full rounded-lg border {{ $errors->has('name') ? 'border-red-400' : 'border-gray-300' }} bg-gray-50 px-4 py-2.5 text-sm focus:border-orangeone focus:ring-orangeone"
                               placeholder="Martin">
                    </div>

                    <div class="md:col-span-2">
                        <label for="lesson_stagiaire_email" class="mb-2 block text-sm font-medium text-gray-900">Adresse e-mail</label>
                        <input id="lesson_stagiaire_email" name="email" type="email" required
                               value="{{ old('email') }}"
                               class="w-full rounded-lg border {{ $errors->has('email') ? 'border-red-400' : 'border-gray-300' }} bg-gray-50 px-4 py-2.5 text-sm focus:border-orangeone focus:ring-orangeone"
                               placeholder="camille.martin@entreprise.fr">
                        <p class="mt-2 text-xs text-gray-500">Si l'e-mail existe deja, le compte stagiaire sera reutilise.</p>
                    </div>

                    <div class="md:col-span-2">
                        <label for="lesson_stagiaire_group_id" class="mb-2 block text-sm font-medium text-gray-900">Groupe</label>
                        @if($groupes->isNotEmpty())
                            <select id="lesson_stagiaire_group_id" name="group_id" required
                                    class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2.5 text-sm focus:border-orangeone focus:ring-orangeone">
                                <option value="">Sélectionner un groupe</option>
                                @foreach($groupes as $groupe)
                                    <option value="{{ $groupe->id }}" @selected((string) old('group_id', $selectedGroupId) === (string) $groupe->id)>
                                        {{ $groupe->name }}
                                    </option>
                                @endforeach
                            </select>
                        @else
                            <div class="rounded-lg border border-orangeone/20 bg-orangeone/5 px-4 py-3 text-sm text-gray-700">
                                Aucun groupe disponible.
                            </div>
                        @endif
                    </div>
                </div>

                <div class="mt-8 border-t border-gray-100 pt-6">
                    <h3 class="text-sm font-bold uppercase tracking-wide text-gray-500">Mot de passe facultatif</h3>
                    <p class="mt-1 text-xs text-gray-500">Si un groupe est selectionne, le stagiaire utilisera le mot de passe du groupe.</p>

                    <div class="mt-4 grid grid-cols-1 gap-5 md:grid-cols-2">
                        <div>
                            <label for="lesson_stagiaire_password" class="mb-2 block text-sm font-medium text-gray-900">Mot de passe</label>
                            <input id="lesson_stagiaire_password" type="password"
                                   class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2.5 text-sm focus:border-orangeone focus:ring-orangeone"
                                   placeholder="Minimum 8 caracteres">
                        </div>

                        <div>
                            <label for="lesson_stagiaire_password_confirmation" class="mb-2 block text-sm font-medium text-gray-900">Confirmation</label>
                            <input id="lesson_stagiaire_password_confirmation" type="password"
                                   class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2.5 text-sm focus:border-orangeone focus:ring-orangeone"
                                   placeholder="Retapez le mot de passe">
                        </div>
                    </div>
                </div>
            </section>

            <div class="flex flex-col items-center justify-end gap-4 md:flex-row">
                <a href="{{ $mixedPartUrls['ajustement-groupe-suite'] ?? '#' }}" class="btn-oneduc-outline !px-5 !py-2.5 !text-sm">
                    Retour
                </a>

                <button type="submit" class="btn-oneduc w-full px-8 py-3 text-lg md:w-auto">
                    Creer le stagiaire
                </button>
            </div>
        </form>
    </main>
</div>
@endif
