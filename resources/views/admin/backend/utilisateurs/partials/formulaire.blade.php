@php
    $modeEdition = isset($utilisateur);
    $roleCourant = old('role', $utilisateur->role ?? $roleSelectionne ?? 'formateur');
    $groupesCoches = collect($errors->any() ? old('group_ids', []) : ($groupesSelectionnes ?? []))
        ->map(fn ($id) => (int) $id)
        ->all();
    $compteActif = (bool) old('status', $modeEdition ? $utilisateur->status : true);
    $classeChamp = 'mt-1 block h-10 w-full rounded-lg border-slate-300 px-3 py-2 text-sm text-slate-900 focus:border-bleuone focus:ring-bleuone';
    $classeLabel = 'block text-sm font-semibold text-slate-700';
@endphp

<div x-data="{ role: @js($roleCourant) }" class="space-y-5">
    @if ($errors->any())
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3" role="alert" aria-labelledby="erreurs-formulaire-titre">
            <div class="flex gap-3">
                <i class="ti ti-alert-circle mt-0.5 text-lg text-red-700" aria-hidden="true"></i>
                <div>
                    <h2 id="erreurs-formulaire-titre" class="text-sm font-semibold text-red-900">Le formulaire contient des erreurs.</h2>
                    <ul class="mt-1 list-disc space-y-0.5 pl-5 text-sm text-red-800">
                        @foreach ($errors->all() as $erreur)
                            <li>{{ $erreur }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <section class="rounded-xl border border-slate-200 bg-white" aria-labelledby="identite-titre">
        <div class="border-b border-slate-200 px-5 py-4">
            <h2 id="identite-titre" class="text-base font-semibold text-slate-950">Identité et coordonnées</h2>
            <p class="mt-0.5 text-sm text-slate-500">Informations utilisées dans les espaces de formation et de suivi.</p>
        </div>

        <div class="grid gap-4 p-5 md:grid-cols-2 xl:grid-cols-3">
            @unless ($modeEdition)
                <div class="md:col-span-2 xl:col-span-3">
                    <span class="{{ $classeLabel }}">Type de compte <span class="text-red-600">*</span></span>
                    <div class="mt-2 grid max-w-xl grid-cols-2 gap-2" role="radiogroup" aria-label="Type de compte">
                        <label class="cursor-pointer">
                            <input type="radio" name="role" value="formateur" x-model="role" class="peer sr-only" @checked($roleCourant === 'formateur')>
                            <span class="flex min-h-12 items-center gap-3 rounded-lg border border-slate-300 px-4 text-sm font-semibold text-slate-700 transition peer-checked:border-bleuone peer-checked:bg-bleuone/5 peer-checked:text-bleuone peer-focus-visible:ring-2 peer-focus-visible:ring-bleuone peer-focus-visible:ring-offset-2">
                                <i class="ti ti-chalkboard text-lg" aria-hidden="true"></i>
                                Formateur
                            </span>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="role" value="stagiaire" x-model="role" class="peer sr-only" @checked($roleCourant === 'stagiaire')>
                            <span class="flex min-h-12 items-center gap-3 rounded-lg border border-slate-300 px-4 text-sm font-semibold text-slate-700 transition peer-checked:border-bleuone peer-checked:bg-bleuone/5 peer-checked:text-bleuone peer-focus-visible:ring-2 peer-focus-visible:ring-bleuone peer-focus-visible:ring-offset-2">
                                <i class="ti ti-school text-lg" aria-hidden="true"></i>
                                Stagiaire
                            </span>
                        </label>
                    </div>
                    @error('role') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            @else
                <div class="md:col-span-2 xl:col-span-3">
                    <span class="admin-badge {{ $roleCourant === 'formateur' ? 'admin-badge--blue' : 'admin-badge--violet' }}">
                        <i class="ti {{ $roleCourant === 'formateur' ? 'ti-chalkboard' : 'ti-school' }}" aria-hidden="true"></i>
                        {{ $roleCourant === 'formateur' ? 'Compte formateur' : 'Compte stagiaire' }}
                    </span>
                    <p class="mt-2 text-xs text-slate-500">Le rôle du compte ne peut pas être modifié depuis cette fiche.</p>
                </div>
            @endunless

            <div>
                <label for="prenom" class="{{ $classeLabel }}">Prénom <span class="text-red-600">*</span></label>
                <input id="prenom" name="prenom" type="text" required autocomplete="given-name"
                       value="{{ old('prenom', $utilisateur->prenom ?? '') }}" class="{{ $classeChamp }}">
                @error('prenom') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="name" class="{{ $classeLabel }}">Nom <span class="text-red-600">*</span></label>
                <input id="name" name="name" type="text" required autocomplete="family-name"
                       value="{{ old('name', $utilisateur->name ?? '') }}" class="{{ $classeChamp }}">
                @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="username" class="{{ $classeLabel }}">Nom d’affichage</label>
                <input id="username" name="username" type="text" autocomplete="nickname"
                       value="{{ old('username', $utilisateur->username ?? '') }}" class="{{ $classeChamp }}">
                @error('username') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="email" class="{{ $classeLabel }}">Adresse email <span class="text-red-600">*</span></label>
                <input id="email" name="email" type="email" required autocomplete="email"
                       value="{{ old('email', $utilisateur->email ?? '') }}" class="{{ $classeChamp }}">
                @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="phone" class="{{ $classeLabel }}">Téléphone</label>
                <input id="phone" name="phone" type="tel" autocomplete="tel"
                       value="{{ old('phone', $utilisateur->phone ?? '') }}" class="{{ $classeChamp }}">
                @error('phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="address" class="{{ $classeLabel }}">Adresse</label>
                <input id="address" name="address" type="text" autocomplete="street-address"
                       value="{{ old('address', $utilisateur->address ?? '') }}" class="{{ $classeChamp }}">
                @error('address') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>
    </section>

    <section class="rounded-xl border border-slate-200 bg-white" aria-labelledby="acces-titre">
        <div class="border-b border-slate-200 px-5 py-4">
            <h2 id="acces-titre" class="text-base font-semibold text-slate-950">Accès au compte</h2>
            <p class="mt-0.5 text-sm text-slate-500">Activation et sécurité de connexion.</p>
        </div>

        <div class="grid gap-4 p-5 md:grid-cols-2">
            <div>
                <label for="password" class="{{ $classeLabel }}">
                    {{ $modeEdition ? 'Nouveau mot de passe' : 'Mot de passe initial' }}
                    @unless ($modeEdition) <span class="text-red-600">*</span> @endunless
                </label>
                <input id="password" name="password" type="password" autocomplete="new-password"
                       @required(! $modeEdition) class="{{ $classeChamp }}">
                <p class="mt-1 text-xs text-slate-500">12 caractères minimum{{ $modeEdition ? ' ; laissez vide pour le conserver.' : '.' }}</p>
                @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="password_confirmation" class="{{ $classeLabel }}">Confirmation du mot de passe</label>
                <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password"
                       @required(! $modeEdition) class="{{ $classeChamp }}">
            </div>

            <div class="md:col-span-2">
                <input type="hidden" name="status" value="0">
                <label class="inline-flex min-h-11 cursor-pointer items-center gap-3 rounded-lg border border-slate-200 px-4 py-2.5">
                    <input type="checkbox" name="status" value="1" @checked($compteActif)
                           class="h-4 w-4 rounded border-slate-300 text-bleuone focus:ring-bleuone">
                    <span>
                        <span class="block text-sm font-semibold text-slate-800">Compte actif</span>
                        <span class="block text-xs text-slate-500">Un compte inactif ne peut pas accéder à son espace par rôle.</span>
                    </span>
                </label>
                @error('status') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>
    </section>

    <section x-show="role === 'formateur'" x-cloak class="rounded-xl border border-slate-200 bg-white" aria-labelledby="formateur-titre">
        <div class="border-b border-slate-200 px-5 py-4">
            <h2 id="formateur-titre" class="text-base font-semibold text-slate-950">Paramètres formateur</h2>
            <p class="mt-0.5 text-sm text-slate-500">Structure de rattachement et statut d’adhésion à l’association.</p>
        </div>

        <div class="grid gap-4 p-5 md:grid-cols-3">
            <div>
                <label for="societe" class="{{ $classeLabel }}">Structure / société</label>
                <input id="societe" name="societe" type="text" autocomplete="organization"
                       value="{{ old('societe', $utilisateur->societe ?? '') }}" class="{{ $classeChamp }}">
                @error('societe') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="adhesion_status" class="{{ $classeLabel }}">Adhésion <span class="text-red-600">*</span></label>
                <select id="adhesion_status" name="adhesion_status" class="{{ $classeChamp }}">
                    @php $adhesionCourante = old('adhesion_status', $utilisateur->adhesion_status ?? 'pending'); @endphp
                    <option value="pending" @selected($adhesionCourante === 'pending')>En attente</option>
                    <option value="active" @selected($adhesionCourante === 'active')>Active</option>
                    <option value="expired" @selected($adhesionCourante === 'expired')>Expirée</option>
                </select>
                @error('adhesion_status') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="adhesion_valid_until" class="{{ $classeLabel }}">Valide jusqu’au</label>
                <input id="adhesion_valid_until" name="adhesion_valid_until" type="date"
                       value="{{ old('adhesion_valid_until', isset($utilisateur) ? $utilisateur->adhesion_valid_until?->format('Y-m-d') : '') }}"
                       class="{{ $classeChamp }}">
                <p class="mt-1 text-xs text-slate-500">Une année est appliquée par défaut si l’adhésion active n’a pas de date.</p>
                @error('adhesion_valid_until') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>
    </section>

    <section x-show="role === 'stagiaire'" x-cloak class="rounded-xl border border-slate-200 bg-white" aria-labelledby="stagiaire-titre">
        <div class="border-b border-slate-200 px-5 py-4">
            <h2 id="stagiaire-titre" class="text-base font-semibold text-slate-950">Rattachement stagiaire</h2>
            <p class="mt-0.5 text-sm text-slate-500">Formateur principal, code court et groupes de formation.</p>
        </div>

        <div class="grid gap-4 p-5 md:grid-cols-2">
            <div>
                <label for="formateur_id" class="{{ $classeLabel }}">Formateur principal</label>
                <select id="formateur_id" name="formateur_id" class="{{ $classeChamp }}">
                    <option value="">Déduit du premier groupe sélectionné</option>
                    @foreach ($formateurs as $formateur)
                        <option value="{{ $formateur->id }}" @selected((int) old('formateur_id', $utilisateur->formateur_id ?? 0) === $formateur->id)>
                            {{ trim($formateur->prenom.' '.$formateur->name) }}{{ $formateur->status ? '' : ' — inactif' }}
                        </option>
                    @endforeach
                </select>
                @error('formateur_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="code_acces" class="{{ $classeLabel }}">Code d’accès</label>
                <input id="code_acces" name="code_acces" type="text" maxlength="6" minlength="6" inputmode="text"
                       value="{{ old('code_acces', $utilisateur->code_acces ?? '') }}"
                       placeholder="Généré automatiquement" class="{{ $classeChamp }} uppercase tracking-[0.2em]">
                <p class="mt-1 text-xs text-slate-500">6 caractères alphanumériques ; laissez vide pour générer un code unique.</p>
                @error('code_acces') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <fieldset class="md:col-span-2">
                <legend class="{{ $classeLabel }}">Groupes</legend>
                <div class="mt-2 max-h-72 overflow-y-auto rounded-lg border border-slate-200">
                    @forelse ($groupes as $groupe)
                        <label class="flex min-h-12 cursor-pointer items-center gap-3 border-b border-slate-100 px-4 py-2.5 last:border-b-0 hover:bg-slate-50">
                            <input type="checkbox" name="group_ids[]" value="{{ $groupe->id }}"
                                   @checked(in_array($groupe->id, $groupesCoches, true))
                                   class="h-4 w-4 rounded border-slate-300 text-bleuone focus:ring-bleuone">
                            <span class="min-w-0 flex-1">
                                <span class="block truncate text-sm font-semibold text-slate-800">{{ $groupe->name }}</span>
                                <span class="block truncate text-xs text-slate-500">
                                    {{ $groupe->instructor ? trim($groupe->instructor->prenom.' '.$groupe->instructor->name) : 'Sans formateur' }}
                                </span>
                            </span>
                            <span class="admin-badge {{ $groupe->is_active ? 'admin-badge--success' : 'admin-badge--neutral' }}">
                                {{ $groupe->is_active ? 'Actif' : 'Inactif' }}
                            </span>
                        </label>
                    @empty
                        <p class="px-4 py-6 text-center text-sm text-slate-500">Aucun groupe disponible.</p>
                    @endforelse
                </div>
                @error('group_ids') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                @error('group_ids.*') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </fieldset>
        </div>
    </section>

    <div class="sticky bottom-3 z-10 flex flex-col-reverse gap-2 rounded-xl border border-slate-200 bg-white/95 p-3 backdrop-blur sm:flex-row sm:items-center sm:justify-end">
        <a href="{{ route('admin.utilisateurs.index', ['role' => $roleCourant]) }}"
           class="inline-flex min-h-10 items-center justify-center rounded-lg border border-slate-300 px-4 text-sm font-semibold text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-400 focus:ring-offset-2">
            Annuler
        </a>
        <button type="submit" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-orangeone px-5 text-sm font-semibold text-white hover:bg-orangeone-hover focus:outline-none focus:ring-2 focus:ring-orangeone focus:ring-offset-2">
            <i class="ti ti-device-floppy text-lg" aria-hidden="true"></i>
            {{ $modeEdition ? 'Enregistrer les modifications' : 'Créer le compte' }}
        </button>
    </div>
</div>
