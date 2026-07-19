@php
    $itemsPourFormulaire = collect(old('items', $itemsInitiaux ?? []))
        ->map(function (array $item, int $index): array {
            $configuration = $item['configuration'] ?? [];

            return [
                'cle' => 'etape-existante-'.$index,
                'type' => $item['type'] ?? 'module',
                'module_id' => $item['module_id'] ?? '',
                'outil' => $item['outil'] ?? '',
                'configuration' => is_array($configuration)
                    ? json_encode($configuration, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                    : (string) $configuration,
            ];
        })
        ->values()
        ->all();
@endphp

@if ($errors->any())
    <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
        <p class="font-semibold">Le modèle n’a pas pu être enregistré.</p>
        <ul class="mt-2 list-disc space-y-1 pl-5">
            @foreach ($errors->all() as $erreur)
                <li>{{ $erreur }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ $action }}" class="space-y-5">
    @csrf
    @if (($methode ?? 'POST') !== 'POST')
        @method($methode)
    @endif

    <section class="rounded-xl border border-slate-200 bg-white p-5" aria-labelledby="informations-modele">
        <h2 id="informations-modele" class="text-base font-semibold text-slate-950">Informations générales</h2>
        <div class="mt-4 grid gap-4">
            <div>
                <label for="titre" class="block text-sm font-semibold text-slate-700">Titre du modèle</label>
                <input
                    id="titre"
                    name="titre"
                    type="text"
                    required
                    maxlength="255"
                    value="{{ old('titre', $modele->titre ?? '') }}"
                    class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-bleuone focus:ring-bleuone"
                >
            </div>
            <div>
                <label for="description" class="block text-sm font-semibold text-slate-700">Description</label>
                <textarea
                    id="description"
                    name="description"
                    rows="3"
                    maxlength="5000"
                    class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-bleuone focus:ring-bleuone"
                >{{ old('description', $modele->description ?? '') }}</textarea>
            </div>
        </div>
    </section>

    <section
        class="rounded-xl border border-slate-200 bg-white p-5"
        aria-labelledby="etapes-modele"
        x-data="{
            items: @js($itemsPourFormulaire),
            configurations: @js($configurationsParDefaut),
            prochaineCle: {{ count($itemsPourFormulaire) }},
            ajouter(type) {
                const outil = type === 'outil' ? Object.keys(this.configurations)[0] : '';
                this.items.push({
                    cle: `etape-nouvelle-${++this.prochaineCle}`,
                    type,
                    module_id: '',
                    outil,
                    configuration: outil ? JSON.stringify(this.configurations[outil], null, 2) : ''
                });
            },
            changerOutil(item) {
                item.configuration = JSON.stringify(this.configurations[item.outil] ?? {}, null, 2);
            },
            deplacer(index, delta) {
                const destination = index + delta;
                if (destination < 0 || destination >= this.items.length) return;
                const [item] = this.items.splice(index, 1);
                this.items.splice(destination, 0, item);
            }
        }"
    >
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h2 id="etapes-modele" class="text-base font-semibold text-slate-950">Étapes du parcours</h2>
                <p class="mt-1 text-sm text-slate-500">Les formations sont référencées ; les outils ne conservent que leur configuration pédagogique.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="button" x-on:click="ajouter('module')" class="inline-flex min-h-10 items-center gap-2 rounded-lg border border-slate-300 px-3 text-sm font-semibold text-slate-700 hover:border-bleuone hover:text-bleuone">
                    <i class="ti ti-book" aria-hidden="true"></i>
                    Ajouter une formation
                </button>
                <button type="button" x-on:click="ajouter('outil')" class="inline-flex min-h-10 items-center gap-2 rounded-lg bg-bleuone px-3 text-sm font-semibold text-white hover:bg-orangeone">
                    <i class="ti ti-tool" aria-hidden="true"></i>
                    Ajouter un outil
                </button>
            </div>
        </div>

        <div class="mt-5 space-y-3">
            <template x-for="(item, index) in items" :key="item.cle">
                <article class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-sm font-semibold text-slate-900">Étape <span x-text="index + 1"></span></p>
                        <div class="flex flex-wrap items-center justify-end gap-1">
                            <button type="button"
                                    x-on:click="deplacer(index, -1)"
                                    x-bind:disabled="index === 0"
                                    :aria-label="`Monter l'étape ${index + 1}`"
                                    class="inline-flex min-h-9 items-center gap-1 rounded-lg border border-slate-300 px-2 text-sm font-semibold text-slate-700 hover:border-bleuone hover:text-bleuone disabled:cursor-not-allowed disabled:opacity-40">
                                <i class="ti ti-arrow-up" aria-hidden="true"></i>
                                Monter
                            </button>
                            <button type="button"
                                    x-on:click="deplacer(index, 1)"
                                    x-bind:disabled="index === items.length - 1"
                                    :aria-label="`Descendre l'étape ${index + 1}`"
                                    class="inline-flex min-h-9 items-center gap-1 rounded-lg border border-slate-300 px-2 text-sm font-semibold text-slate-700 hover:border-bleuone hover:text-bleuone disabled:cursor-not-allowed disabled:opacity-40">
                                <i class="ti ti-arrow-down" aria-hidden="true"></i>
                                Descendre
                            </button>
                            <button type="button" x-on:click="items.splice(index, 1)" class="inline-flex min-h-9 items-center gap-1 rounded-lg px-2 text-sm font-semibold text-red-600 hover:bg-red-50" :aria-label="`Supprimer l'étape ${index + 1}`">
                                <i class="ti ti-trash" aria-hidden="true"></i>
                                Supprimer
                            </button>
                        </div>
                    </div>

                    <input type="hidden" :name="`items[${index}][type]`" x-model="item.type">

                    <div class="mt-3" x-show="item.type === 'module'">
                        <label :for="`module-${index}`" class="block text-sm font-semibold text-slate-700">Formation officielle</label>
                        <select :id="`module-${index}`" :name="`items[${index}][module_id]`" x-model="item.module_id" x-bind:required="item.type === 'module'" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-bleuone focus:ring-bleuone">
                            <option value="">Sélectionner une formation</option>
                            @foreach ($modules as $module)
                                <option value="{{ $module->id }}">
                                    {{ $module->module_title }}{{ $module->status ? '' : ' — inactive' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mt-3 grid gap-3" x-show="item.type === 'outil'">
                        <div>
                            <label :for="`outil-${index}`" class="block text-sm font-semibold text-slate-700">Outil activé</label>
                            <select :id="`outil-${index}`" :name="`items[${index}][outil]`" x-model="item.outil" x-on:change="changerOutil(item)" x-bind:required="item.type === 'outil'" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-bleuone focus:ring-bleuone">
                                <option value="">Sélectionner un outil</option>
                                @foreach ($outils as $cle => $outil)
                                    <option value="{{ $cle }}">{{ $outil['libelle'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label :for="`configuration-${index}`" class="block text-sm font-semibold text-slate-700">Configuration JSON</label>
                            <textarea :id="`configuration-${index}`" :name="`items[${index}][configuration]`" x-model="item.configuration" x-bind:required="item.type === 'outil'" rows="9" spellcheck="false" class="mt-1 w-full rounded-lg border-slate-300 font-mono text-xs focus:border-bleuone focus:ring-bleuone"></textarea>
                            <p class="mt-1 text-xs text-slate-500">Les codes d’accès, participants, réponses, résultats et scores sont refusés par le registre.</p>
                        </div>
                    </div>
                </article>
            </template>

            <div x-show="items.length === 0" class="rounded-xl border border-dashed border-slate-300 px-5 py-10 text-center text-sm text-slate-500">
                Ajoutez au moins une formation ou un outil au parcours.
            </div>
        </div>
    </section>

    <div class="flex flex-wrap items-center justify-end gap-3">
        <a href="{{ route('admin.modeles-parcours.index') }}" class="inline-flex min-h-10 items-center rounded-lg border border-slate-300 px-4 text-sm font-semibold text-slate-700 hover:border-bleuone hover:text-bleuone">Annuler</a>
        <button type="submit" class="inline-flex min-h-10 items-center gap-2 rounded-lg bg-orangeone px-4 text-sm font-semibold text-white hover:bg-orangeone-hover">
            <i class="ti ti-device-floppy" aria-hidden="true"></i>
            Enregistrer le brouillon
        </button>
    </div>
</form>
