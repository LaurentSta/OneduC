@php
    $moduleThreeUrl = route('formateur.parcours.modules.show', ['module' => 'gerer-ses-groupes']);
    $dashboardUrl = route('formateur.dashboard');
    $bilanUrl = $mixedPartUrls['resultat-final'] ?? ($currentModule['url'] ?? route('formateur.parcours.index'));
    $dimensions = [
        [
            'id' => 'contenu_percu',
            'title' => 'Contenu perçu',
            'items' => [
                ['number' => 1, 'label' => 'Le libellé des textes était clair.'],
                ['number' => 2, 'label' => 'Le contenu (textes, images, voix off, vidéos) était facile à comprendre.'],
                ['number' => 3, 'label' => 'Le contenu m’a paru utile pour mon métier de formateur.'],
                ['number' => 4, 'label' => 'Le contenu correspondait à ce que j’attendais.'],
            ],
        ],
        [
            'id' => 'effort_cognitif_percu',
            'title' => 'Effort cognitif perçu',
            'items' => [
                ['number' => 5, 'label' => 'J’ai appris à utiliser le module rapidement.'],
                ['number' => 6, 'label' => 'Suivre le module s’est fait sans effort.'],
                ['number' => 7, 'label' => 'Suivre le module m’a fatigué.', 'reversed' => true],
            ],
        ],
        [
            'id' => 'guidage_visuel_percu',
            'title' => 'Guidage visuel perçu',
            'items' => [
                ['number' => 8, 'label' => 'Les couleurs m’ont aidé à distinguer les différents éléments à l’écran.'],
                ['number' => 9, 'label' => 'Les éléments mis en évidence m’ont aidé à repérer ce qui était important.'],
                ['number' => 10, 'label' => 'Le style des illustrations a soutenu ma compréhension.'],
            ],
        ],
        [
            'id' => 'reperage_dans_le_parcours',
            'title' => 'Repérage dans le parcours',
            'items' => [
                ['number' => 11, 'label' => 'Je savais toujours où j’en étais dans le parcours.'],
                ['number' => 12, 'label' => 'Je comprenais comment passer d’un écran au suivant.'],
                ['number' => 13, 'label' => 'L’enchaînement des leçons m’a paru logique.'],
            ],
        ],
        [
            'id' => 'activites_et_simulateurs',
            'title' => 'Activités et simulateurs',
            'items' => [
                ['number' => 14, 'label' => 'Les consignes des activités étaient claires.'],
                ['number' => 15, 'label' => 'Je comprenais ce qu’on attendait de moi dans chaque activité.'],
                ['number' => 16, 'label' => 'Les simulateurs reflétaient bien l’usage réel d’Onéduc.'],
                ['number' => 17, 'label' => 'Le retour après chaque activité m’a aidé à savoir si j’avais réussi.'],
            ],
        ],
    ];
    $scale = [
        ['value' => '1', 'short' => '1', 'label' => 'Pas du tout d’accord'],
        ['value' => '2', 'short' => '2', 'label' => 'Plutôt pas d’accord'],
        ['value' => '3', 'short' => '3', 'label' => 'Ni d’accord ni pas d’accord'],
        ['value' => '4', 'short' => '4', 'label' => 'Plutôt d’accord'],
        ['value' => '5', 'short' => '5', 'label' => 'Tout à fait d’accord'],
        ['value' => 'NA', 'short' => 'NA', 'label' => 'Non applicable'],
    ];
    $questionnaireItems = collect($dimensions)
        ->flatMap(function (array $dimension): array {
            return array_map(function (array $item) use ($dimension): array {
                return [
                    'item_number' => $item['number'],
                    'dimension' => $dimension['id'],
                    'dimension_label' => $dimension['title'],
                    'label' => $item['label'],
                    'reversed' => (bool) ($item['reversed'] ?? false),
                ];
            }, $dimension['items']);
        })
        ->values()
        ->all();
@endphp

<div class="mx-auto w-full max-w-[1285px] px-1 py-1 sm:px-2">
    <section class="overflow-hidden rounded-[26px] border border-gray-100 bg-white shadow-md">
        <header class="border-b border-bleuone/10 bg-bleuone/[0.04] px-6 py-7 sm:px-8 lg:px-10">
            <p class="text-xs font-black uppercase tracking-[0.24em] text-orangeone">Questionnaire d’évaluation</p>
            <h1 class="mt-3 max-w-4xl font-raleway text-3xl font-semibold leading-tight text-bleuone md:text-4xl">
                Votre avis sur le module 2
            </h1>
            <p class="mt-4 max-w-4xl text-base leading-8 text-slate-600">
                Ce questionnaire porte sur votre expérience du module « Mettre en place un environnement de formation ».
                Pour chaque affirmation, sélectionnez la réponse qui correspond le mieux à votre ressenti.
            </p>
        </header>

        <form id="module-2-usability-questionnaire" class="space-y-7 px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
            <section class="rounded-[18px] border border-slate-200 bg-slate-50/70 px-4 py-4">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="font-raleway text-lg font-bold text-bleuone">Échelle de réponse</h2>
                        <p class="mt-1 text-sm leading-6 text-slate-600">Déplacez le curseur de 1 à 5, ou choisissez NA si l’affirmation ne s’applique pas.</p>
                    </div>
                    <div class="flex shrink-0 items-center gap-2 rounded-full border border-orangeone/20 bg-white px-3 py-2 text-xs font-bold text-orangeone">
                        <span>1</span>
                        <span class="h-px w-8 bg-orangeone/30"></span>
                        <span>5</span>
                        <span class="ml-1 border-l border-slate-200 pl-3">NA</span>
                    </div>
                </div>

                <div class="mt-4 grid gap-x-6 gap-y-2 text-sm text-slate-600 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($scale as $scaleOption)
                        <p><span class="font-black text-orangeone">{{ $scaleOption['short'] }}</span> = {{ $scaleOption['label'] }}</p>
                    @endforeach
                </div>
                <p class="mt-3 text-xs leading-5 text-slate-500">
                    Les affirmations marquées d’un astérisque sont formulées en sens inverse et seront recodées lors de l’analyse.
                </p>
            </section>

            <p
                id="module-2-questionnaire-error-summary"
                class="hidden rounded-[16px] border border-orangeone/30 bg-orangeone/10 px-4 py-3 text-sm font-bold text-orangeone"
                role="alert"
                tabindex="-1"
            >
                Répondez à chaque affirmation avant d’envoyer le questionnaire.
            </p>

            @foreach ($dimensions as $dimensionIndex => $dimension)
                <section class="overflow-hidden rounded-[20px] border border-bleuone/10 bg-white shadow-sm">
                    <header class="border-b border-bleuone/10 bg-bleuone px-5 py-4 text-white sm:px-6">
                        <p class="text-[11px] font-black uppercase tracking-[0.2em] text-white/70">Dimension {{ $dimensionIndex + 1 }}</p>
                        <h2 class="mt-1 font-raleway text-xl font-bold">{{ $dimension['title'] }}</h2>
                    </header>

                    <div class="divide-y divide-slate-100">
                        @foreach ($dimension['items'] as $item)
                            <fieldset
                                x-data="{ score: null, nonApplicable: false }"
                                data-questionnaire-item="{{ $item['number'] }}"
                                class="px-4 py-5 sm:px-6 lg:grid lg:grid-cols-[minmax(0,1fr)_25rem] lg:items-center lg:gap-6"
                            >
                                <legend class="text-base font-semibold leading-7 text-slate-700">
                                    <span class="mr-1 text-orangeone">{{ $item['number'] }}.</span>
                                    {{ $item['label'] }}
                                    @if (!empty($item['reversed']))
                                        <span class="font-black text-orangeone" title="Item formulé en sens inverse">*</span>
                                    @endif
                                </legend>

                                <div class="mt-4 lg:mt-0">
                                    <input
                                        type="hidden"
                                        name="item_{{ $item['number'] }}"
                                        :value="nonApplicable ? 'NA' : (score ?? '')"
                                    >

                                    <div class="flex items-center gap-3">
                                        <div class="min-w-0 flex-1 rounded-[16px] border border-slate-200 bg-slate-50 px-3 py-2 transition" :class="nonApplicable ? 'opacity-45' : ''">
                                            <div class="flex items-center gap-3">
                                                <input
                                                    id="module-2-item-{{ $item['number'] }}"
                                                    type="range"
                                                    min="1"
                                                    max="5"
                                                    step="1"
                                                    value="3"
                                                    class="h-2 w-full cursor-pointer accent-orangeone disabled:cursor-not-allowed"
                                                    :disabled="nonApplicable"
                                                    @input="score = Number($event.target.value); nonApplicable = false; $el.closest('fieldset').querySelector('[data-questionnaire-item-error]').classList.add('hidden')"
                                                    aria-label="Note de 1 à 5 pour l’affirmation {{ $item['number'] }}"
                                                >
                                                <output
                                                    for="module-2-item-{{ $item['number'] }}"
                                                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-sm font-black transition"
                                                    :class="score === null || nonApplicable ? 'bg-white text-slate-400' : 'bg-orangeone text-white'"
                                                    x-text="nonApplicable ? 'NA' : (score ?? '—')"
                                                    aria-live="polite"
                                                ></output>
                                            </div>
                                            <div class="mt-1 flex justify-between px-0.5 text-[10px] font-black text-slate-400" aria-hidden="true">
                                                <span>1</span>
                                                <span>2</span>
                                                <span>3</span>
                                                <span>4</span>
                                                <span>5</span>
                                            </div>
                                        </div>

                                        <button
                                            type="button"
                                            class="inline-flex h-12 shrink-0 items-center justify-center rounded-full border px-4 text-sm font-black transition focus:outline-none focus:ring-4 focus:ring-orange-200"
                                            :class="nonApplicable ? 'border-orangeone bg-orangeone text-white' : 'border-slate-200 bg-white text-slate-500 hover:border-orangeone hover:text-orangeone'"
                                            :aria-pressed="nonApplicable.toString()"
                                            aria-label="NA — Non applicable"
                                            @click="nonApplicable = !nonApplicable; if (nonApplicable) score = null; $el.closest('fieldset').querySelector('[data-questionnaire-item-error]').classList.add('hidden')"
                                        >
                                            NA
                                            <span class="sr-only"> — Non applicable</span>
                                        </button>
                                    </div>

                                    <p data-questionnaire-item-error class="mt-2 hidden text-xs font-bold text-orangeone">
                                        Sélectionnez une note ou choisissez NA.
                                    </p>
                                </div>
                            </fieldset>
                        @endforeach
                    </div>
                </section>
            @endforeach

            <section class="overflow-hidden rounded-[20px] border border-orangeone/15 bg-white shadow-sm">
                <header class="border-b border-orangeone/10 bg-orangeone/5 px-5 py-4 sm:px-6">
                    <p class="text-[11px] font-black uppercase tracking-[0.2em] text-orangeone">Questions ouvertes facultatives</p>
                    <h2 class="mt-1 font-raleway text-xl font-bold text-bleuone">Pour aller plus loin</h2>
                </header>

                <div class="space-y-5 px-4 py-5 sm:px-6">
                    <div>
                        <label for="module-2-open-18" class="block text-base font-semibold leading-7 text-slate-700">
                            <span class="mr-1 text-orangeone">18.</span>
                            Qu’est-ce qui vous a le plus aidé dans ce module ?
                        </label>
                        <textarea id="module-2-open-18" name="open_18" rows="4" class="mt-2 w-full rounded-[14px] border-slate-300 text-sm leading-6 text-slate-700 shadow-sm focus:border-orangeone focus:ring-orangeone"></textarea>
                    </div>

                    <div>
                        <label for="module-2-open-19" class="block text-base font-semibold leading-7 text-slate-700">
                            <span class="mr-1 text-orangeone">19.</span>
                            Qu’est-ce qui mériterait d’être clarifié ou amélioré ?
                        </label>
                        <textarea id="module-2-open-19" name="open_19" rows="4" class="mt-2 w-full rounded-[14px] border-slate-300 text-sm leading-6 text-slate-700 shadow-sm focus:border-orangeone focus:ring-orangeone"></textarea>
                    </div>
                </div>
            </section>

            <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-6 sm:flex-row sm:items-center sm:justify-between">
                <a href="{{ $bilanUrl }}" class="btn-oneduc-outline justify-center !px-6 !py-3 !text-sm">
                    Retour au bilan
                </a>
                <button type="submit" class="btn-oneduc justify-center !px-7 !py-3 !text-sm">
                    Envoyer mes réponses
                </button>
            </div>
        </form>

        <section id="module-2-questionnaire-confirmation" class="hidden px-6 py-12 text-center sm:px-8" tabindex="-1">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-vertone/10 text-vertone">
                <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="m5 12 4 4L19 6" />
                </svg>
            </div>
            <h2 class="mt-5 font-raleway text-3xl font-semibold text-bleuone">Merci pour votre retour</h2>
            <p class="mx-auto mt-3 max-w-2xl text-base leading-8 text-slate-600">
                Vos réponses ont bien été préparées. Elles pourront être transmises dès que la gestion de la soumission sera branchée.
            </p>
            <div class="mt-7 flex flex-col justify-center gap-3 sm:flex-row">
                <a href="{{ $dashboardUrl }}" class="btn-oneduc-outline justify-center !px-6 !py-3 !text-sm">
                    Retour au tableau de bord
                </a>
                <a href="{{ $moduleThreeUrl }}" class="btn-oneduc justify-center !px-6 !py-3 !text-sm">
                    Aller au module 3
                </a>
            </div>
        </section>
    </section>
</div>

<script>
    (() => {
        const form = document.getElementById('module-2-usability-questionnaire');
        const confirmation = document.getElementById('module-2-questionnaire-confirmation');
        const errorSummary = document.getElementById('module-2-questionnaire-error-summary');
        const questionnaireItems = @js($questionnaireItems);

        if (!form || !confirmation) {
            return;
        }

        /**
         * Point d'extension : branchez ici l'enregistrement ou l'envoi des réponses.
         * L'objet reçu contient les 17 items fermés et les 2 réponses ouvertes.
         */
        window.handleModule2QuestionnaireSubmit = window.handleModule2QuestionnaireSubmit || function (questionnaireResponses) {
            // A compléter lors du branchement de la gestion de la soumission.
        };

        form.addEventListener('submit', (event) => {
            event.preventDefault();

            const formData = new FormData(form);
            const allowedValues = new Set(['1', '2', '3', '4', '5', 'NA']);
            const unansweredItems = questionnaireItems.filter((item) => {
                return !allowedValues.has(String(formData.get(`item_${item.item_number}`) || ''));
            });

            form.querySelectorAll('[data-questionnaire-item]').forEach((fieldset) => {
                const itemNumber = Number(fieldset.dataset.questionnaireItem);
                const hasError = unansweredItems.some((item) => item.item_number === itemNumber);

                fieldset.querySelector('[data-questionnaire-item-error]')?.classList.toggle('hidden', !hasError);
            });

            if (unansweredItems.length > 0) {
                errorSummary?.classList.remove('hidden');
                errorSummary?.focus();

                const firstUnansweredItem = unansweredItems[0];
                form.querySelector(`[data-questionnaire-item="${firstUnansweredItem.item_number}"] input[type="range"]`)?.focus();

                return;
            }

            errorSummary?.classList.add('hidden');

            const responses = {
                module: {
                    code: 'module-2',
                    title: 'Mettre en place un environnement de formation',
                },
                submitted_at: new Date().toISOString(),
                closed_items: questionnaireItems.map((item) => {
                    const rawValue = formData.get(`item_${item.item_number}`);

                    return {
                        ...item,
                        // L'item 7 est inversé et doit être recodé lors de l'analyse.
                        // Le texte source annonce deux items inversés mais n'en identifie qu'un.
                        value: rawValue === 'NA' ? 'NA' : Number(rawValue),
                    };
                }),
                open_questions: [
                    {
                        item_number: 18,
                        label: 'Qu’est-ce qui vous a le plus aidé dans ce module ?',
                        text: String(formData.get('open_18') || '').trim(),
                    },
                    {
                        item_number: 19,
                        label: 'Qu’est-ce qui mériterait d’être clarifié ou amélioré ?',
                        text: String(formData.get('open_19') || '').trim(),
                    },
                ],
            };

            window.handleModule2QuestionnaireSubmit(responses);
            window.dispatchEvent(new CustomEvent('oneduc:module-2-questionnaire-submitted', {
                detail: responses,
            }));

            form.classList.add('hidden');
            confirmation.classList.remove('hidden');
            confirmation.focus();
        });
    })();
</script>
