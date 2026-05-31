@php
    $moduleUrl = $currentModule['url'] ?? route('formateur.parcours.index');
    $moduleThreeUrl = route('formateur.parcours.modules.show', ['module' => 'gerer-ses-groupes']);
    $dashboardUrl = route('formateur.dashboard');
    $activityStatusMap = $activityStatusMap ?? [];
    $durationToMinutes = function (?string $duration): float {
        preg_match_all('/\d+/', (string) $duration, $matches);
        $numbers = array_map('intval', $matches[0] ?? []);

        if ($numbers === []) {
            return 0;
        }

        return count($numbers) >= 2 ? (($numbers[0] + $numbers[1]) / 2) : $numbers[0];
    };

    $chapterResults = [];
    $totalTracked = 0;
    $totalCompleted = 0;
    $totalEstimatedMinutes = 0;
    $completedEstimatedMinutes = 0;

    foreach (($currentModule['chapters'] ?? []) as $chapterKey => $chapter) {
        $lessonResults = [];
        $chapterTracked = 0;
        $chapterCompleted = 0;
        $chapterEstimatedMinutes = 0;
        $chapterCompletedMinutes = 0;

        foreach (($chapter['lessons'] ?? []) as $lessonKey => $lesson) {
            $isBilan = ($lesson['type'] ?? 'objectif') === 'bilan';
            $activityKey = $lesson['activity_page']['key'] ?? ($lesson['completion_activity_key'] ?? null);
            $isTracked = is_string($activityKey) && $activityKey !== '';
            $statusKey = $isTracked ? implode('.', [$chapterKey, $lessonKey, $activityKey]) : null;
            $isCompleted = $statusKey !== null && (($activityStatusMap[$statusKey] ?? false) === true);
            $minutes = $durationToMinutes($lesson['duration'] ?? $lesson['duration_label'] ?? '');

            if ($isTracked) {
                $chapterTracked++;
                $chapterEstimatedMinutes += $minutes;

                if ($isCompleted) {
                    $chapterCompleted++;
                    $chapterCompletedMinutes += $minutes;
                }
            }

            $lessonResults[] = [
                'title' => $lesson['title'] ?? 'Lecon',
                'duration' => $lesson['duration'] ?? '',
                'is_bilan' => $isBilan,
                'is_tracked' => $isTracked,
                'is_completed' => $isCompleted,
            ];
        }

        $totalTracked += $chapterTracked;
        $totalCompleted += $chapterCompleted;
        $totalEstimatedMinutes += $chapterEstimatedMinutes;
        $completedEstimatedMinutes += $chapterCompletedMinutes;

        $chapterResults[] = [
            'code' => $chapter['code'] ?? '',
            'title' => $chapter['title'] ?? 'Chapitre',
            'completed' => $chapterCompleted,
            'tracked' => $chapterTracked,
            'percent' => $chapterTracked > 0 ? (int) round(($chapterCompleted / $chapterTracked) * 100) : 100,
            'estimated_minutes' => $chapterEstimatedMinutes,
            'completed_minutes' => $chapterCompletedMinutes,
            'lessons' => $lessonResults,
        ];
    }

    $moduleProgressPercent = $totalTracked > 0 ? (int) round(($totalCompleted / $totalTracked) * 100) : 100;
    $completedTimePercent = $totalEstimatedMinutes > 0 ? (int) round(($completedEstimatedMinutes / $totalEstimatedMinutes) * 100) : 100;
    $formatMinutes = fn (float $minutes): string => rtrim(rtrim(number_format($minutes, 1, ',', ' '), '0'), ',');
@endphp

<div class="mx-auto flex min-h-full w-full max-w-[1285px] items-center px-4 py-6 sm:px-6 lg:px-8">
    <section class="w-full overflow-hidden rounded-[26px] border border-gray-100 bg-white shadow-md">
        <div class="grid lg:grid-cols-[minmax(0,1fr)_minmax(360px,0.72fr)]">
            <div class="order-2 flex flex-col justify-center px-6 py-8 sm:px-8 lg:order-1 lg:px-10 xl:px-12">
                <p class="text-xs font-black uppercase tracking-[0.24em] text-vertone">
                    Bilan du module 2
                </p>

                <h1 class="mt-3 max-w-3xl font-raleway text-3xl font-semibold leading-tight text-bleuone md:text-4xl">
                    Votre progression dans Organiser ses parcours
                </h1>

                <p class="mt-4 max-w-3xl text-base leading-8 text-slate-600">
                    Cette vue reprend les validations enregistrees pendant le module. Elle permet de voir rapidement
                    ce qui est acquis et ce qui reste a terminer avant de passer au module suivant.
                </p>

                <div class="mt-7 grid gap-3 sm:grid-cols-3">
                    <article class="rounded-[18px] border border-bleuone/10 bg-bleuone/5 px-4 py-4">
                        <p class="text-[11px] font-black uppercase tracking-[0.18em] text-bleuone">Activites</p>
                        <p class="mt-2 text-3xl font-black text-bleuone">{{ $totalCompleted }}/{{ $totalTracked }}</p>
                        <p class="mt-1 text-sm text-slate-600">activites validees</p>
                    </article>

                    <article class="rounded-[18px] border border-orangeone/10 bg-orangeone/5 px-4 py-4">
                        <p class="text-[11px] font-black uppercase tracking-[0.18em] text-orangeone">Avancement</p>
                        <p class="mt-2 text-3xl font-black text-orangeone">{{ $moduleProgressPercent }}%</p>
                        <p class="mt-1 text-sm text-slate-600">du parcours valide</p>
                    </article>

                    <article class="rounded-[18px] border border-vertone/10 bg-vertone/10 px-4 py-4">
                        <p class="text-[11px] font-black uppercase tracking-[0.18em] text-vertone">Temps estime</p>
                        <p class="mt-2 text-3xl font-black text-vertone">{{ $formatMinutes($completedEstimatedMinutes) }}</p>
                        <p class="mt-1 text-sm text-slate-600">min validees / {{ $formatMinutes($totalEstimatedMinutes) }}</p>
                    </article>
                </div>

                <div class="mt-7 rounded-[20px] border border-slate-200 bg-slate-50/70 p-5">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <h2 class="font-raleway text-xl font-bold text-bleuone">Detail par chapitre</h2>
                            <p class="mt-1 text-sm leading-6 text-slate-500">Chaque etape, bilan compris, doit etre terminee pour valider le module.</p>
                        </div>
                        <span class="rounded-full bg-white px-3 py-1 text-xs font-bold text-slate-500 shadow-sm">
                            {{ $totalTracked - $totalCompleted }} restant(s)
                        </span>
                    </div>

                    <div class="mt-5 space-y-4">
                        @foreach ($chapterResults as $chapterResult)
                            <article class="rounded-[16px] border border-white bg-white p-4 shadow-sm">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                    <div>
                                        <p class="text-[11px] font-black uppercase tracking-[0.18em] text-orangeone">{{ $chapterResult['code'] }}</p>
                                        <h3 class="mt-1 font-raleway text-lg font-bold text-bleuone">{{ $chapterResult['title'] }}</h3>
                                    </div>
                                    <span class="{{ $chapterResult['percent'] === 100 ? 'bg-vertone/10 text-vertone' : 'bg-orangeone/10 text-orangeone' }} rounded-full px-3 py-1 text-sm font-black">
                                        {{ $chapterResult['completed'] }}/{{ $chapterResult['tracked'] }} validees
                                    </span>
                                </div>

                                <div class="mt-4 h-2.5 overflow-hidden rounded-full bg-slate-100">
                                    <div class="h-full rounded-full {{ $chapterResult['percent'] === 100 ? 'bg-vertone' : 'bg-orangeone' }}" style="width: {{ $chapterResult['percent'] }}%"></div>
                                </div>

                                <div class="mt-4 space-y-2">
                                    @foreach ($chapterResult['lessons'] as $lessonResult)
                                        <div class="flex items-center justify-between gap-3 rounded-[12px] bg-slate-50 px-3 py-2">
                                            <div class="min-w-0">
                                                <p class="truncate text-sm font-semibold text-slate-700">{{ $lessonResult['title'] }}</p>
                                                <p class="text-xs text-slate-400">{{ $lessonResult['duration'] }}</p>
                                            </div>
                                            @if ($lessonResult['is_completed'])
                                                <span class="shrink-0 rounded-full bg-vertone/10 px-3 py-1 text-xs font-bold text-vertone">Validee</span>
                                            @elseif ($lessonResult['is_bilan'])
                                                <span class="shrink-0 rounded-full bg-orangeone/10 px-3 py-1 text-xs font-bold text-orangeone">Bilan a terminer</span>
                                            @else
                                                <span class="shrink-0 rounded-full bg-orangeone/10 px-3 py-1 text-xs font-bold text-orangeone">A terminer</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>

                <div class="mt-7 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ $moduleUrl }}" class="btn-oneduc-outline justify-center !px-6 !py-3 !text-sm">
                        Revoir le module 2
                    </a>
                    <a href="{{ $dashboardUrl }}" class="btn-oneduc justify-center !px-6 !py-3 !text-sm">
                        Retour au tableau de bord
                    </a>
                </div>
            </div>

            <div class="order-1 flex min-h-[360px] items-center justify-center overflow-hidden border-b border-orangeone/10 bg-orangeone/5 px-6 py-8 lg:order-2 lg:min-h-full lg:border-b-0 lg:border-l lg:px-10">
                <div class="w-full max-w-[640px]">
                    <div class="mb-5 flex justify-center lg:justify-end">
                        <span class="inline-flex items-center rounded-full border border-vertone/20 bg-white px-4 py-2 text-sm font-black uppercase tracking-[0.18em] text-vertone shadow-sm">
                            {{ $moduleProgressPercent === 100 ? 'Parcours valide' : 'Parcours en cours' }}
                        </span>
                    </div>

                    <img
                        src="{{ asset('images/svg/Finish.svg') }}"
                        alt="Module termine"
                        class="mx-auto h-auto w-full max-w-[560px] drop-shadow-[0_26px_28px_rgba(0,68,97,0.16)]"
                    >

                    <div class="mt-7 flex justify-center lg:justify-end">
                        <a href="{{ $moduleThreeUrl }}" class="btn-oneduc justify-center !px-7 !py-3 !text-sm">
                            Aller au module 3
                        </a>
                    </div>

                    <div class="mt-8 rounded-[20px] border border-white/70 bg-white/80 p-5 shadow-sm">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-bleuone">Temps estime valide</p>
                                <p class="mt-2 text-3xl font-black text-bleuone">{{ $completedTimePercent }}%</p>
                            </div>
                            <div class="relative h-24 w-24 shrink-0 rounded-full" style="background: conic-gradient(#20c997 {{ $completedTimePercent }}%, #e2e8f0 0);">
                                <div class="absolute inset-3 rounded-full bg-white"></div>
                            </div>
                        </div>

                        <div class="mt-5 space-y-3">
                            @foreach ($chapterResults as $chapterResult)
                                @php
                                    $chapterTimePercent = $chapterResult['estimated_minutes'] > 0
                                        ? (int) round(($chapterResult['completed_minutes'] / $chapterResult['estimated_minutes']) * 100)
                                        : 100;
                                @endphp
                                <div>
                                    <div class="mb-1 flex justify-between gap-3 text-xs font-bold text-slate-600">
                                        <span>{{ $chapterResult['title'] }}</span>
                                        <span>{{ $formatMinutes($chapterResult['completed_minutes']) }}/{{ $formatMinutes($chapterResult['estimated_minutes']) }} min</span>
                                    </div>
                                    <div class="h-2 overflow-hidden rounded-full bg-slate-200">
                                        <div class="h-full rounded-full bg-bleuone" style="width: {{ $chapterTimePercent }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
