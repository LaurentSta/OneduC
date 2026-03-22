<div class="max-w-[1285px] mx-auto px-6 lg:px-8">
    <div class="bg-white rounded-[20px] shadow-soft p-6 my-6 border border-gray-100">
        <div class="border-b border-gray-100 pb-4 mb-6">
            <h1 class="text-[20px] font-varela text-bleuone">{{ $title }}</h1>
            <p class="text-sm text-gray-600">{{ $subtitle }}</p>
        </div>

        <form method="POST" action="{{ $action }}" class="space-y-8">
            @csrf
            @if($method !== 'POST')
                @method($method)
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="prenom" class="block text-sm font-medium text-gray-700 mb-2">Prénom</label>
                    <input id="prenom" name="prenom" type="text" value="{{ old('prenom', $observateur->prenom) }}" class="w-full rounded-lg border border-gray-300 px-4 py-2.5 focus:border-orangeone focus:ring-orangeone" required>
                    @error('prenom') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Nom</label>
                    <input id="name" name="name" type="text" value="{{ old('name', $observateur->name) }}" class="w-full rounded-lg border border-gray-300 px-4 py-2.5 focus:border-orangeone focus:ring-orangeone" required>
                    @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-2">Username</label>
                    <input id="username" name="username" type="text" value="{{ old('username', $observateur->username) }}" class="w-full rounded-lg border border-gray-300 px-4 py-2.5 focus:border-orangeone focus:ring-orangeone">
                    @error('username') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Téléphone</label>
                    <input id="phone" name="phone" type="text" value="{{ old('phone', $observateur->phone) }}" class="w-full rounded-lg border border-gray-300 px-4 py-2.5 focus:border-orangeone focus:ring-orangeone">
                    @error('phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="md:col-span-2">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $observateur->email) }}" class="w-full rounded-lg border border-gray-300 px-4 py-2.5 focus:border-orangeone focus:ring-orangeone" required>
                    @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Mot de passe {{ $method === 'POST' ? '' : '(laisser vide pour conserver)' }}</label>
                    <input id="password" name="password" type="password" class="w-full rounded-lg border border-gray-300 px-4 py-2.5 focus:border-orangeone focus:ring-orangeone" {{ $method === 'POST' ? 'required' : '' }}>
                    @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">Confirmation du mot de passe</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" class="w-full rounded-lg border border-gray-300 px-4 py-2.5 focus:border-orangeone focus:ring-orangeone" {{ $method === 'POST' ? 'required' : '' }}>
                </div>
            </div>

            <div class="rounded-[16px] border border-gray-200 p-5">
                @php
                    $oldGroupIds = collect(old('group_ids', $selectedGroupIds))
                        ->map(fn ($id) => (int) $id)
                        ->all();
                @endphp

                <div class="flex items-center justify-between gap-3 mb-4">
                    <div>
                        <h2 class="text-lg font-semibold text-bleuone">Groupes observés</h2>
                        <p class="text-sm text-gray-600">L’observateur ne verra que les groupes sélectionnés.</p>
                    </div>
                    <label class="inline-flex items-center gap-2 text-sm font-medium text-gray-700">
                        <input type="hidden" name="status" value="0">
                        <input type="checkbox" name="status" value="1" class="rounded border-gray-300 text-orangeone focus:ring-orangeone" {{ old('status', $observateur->exists ? (int) $observateur->status : 1) ? 'checked' : '' }}>
                        Compte actif
                    </label>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($groups as $group)
                        @php
                            $checked = in_array((int) $group->id, $oldGroupIds, true);
                            $instructorName = trim(($group->instructor->prenom ?? '').' '.($group->instructor->name ?? ''));
                        @endphp
                        <label class="flex items-start gap-3 rounded-xl border border-gray-200 p-4 hover:border-orangeone/40 transition">
                            <input type="checkbox" name="group_ids[]" value="{{ $group->id }}" class="mt-1 rounded border-gray-300 text-orangeone focus:ring-orangeone" {{ $checked ? 'checked' : '' }}>
                            <span class="flex-1">
                                <span class="block font-semibold text-gray-900">{{ $group->name }}</span>
                                <span class="block text-sm text-gray-600">Formateur : {{ $instructorName !== '' ? $instructorName : 'Non renseigné' }}</span>
                                <span class="mt-2 inline-flex items-center gap-2 text-xs text-gray-500">
                                    <span>{{ $group->stagiaires_count }} stagiaire(s)</span>
                                    <span>•</span>
                                    <span>{{ $group->modules_count }} module(s)</span>
                                </span>
                            </span>
                        </label>
                    @endforeach
                </div>
                @error('group_ids') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                @error('group_ids.*') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('admin.observateurs.index') }}" class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Annuler</a>
                <button type="submit" class="px-4 py-2 rounded-lg bg-orangeone text-white hover:bg-orangeone-hover">
                    Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>
