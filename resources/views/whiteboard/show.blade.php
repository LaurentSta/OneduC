@extends($layoutView)

@section($layoutSection)
@php
    $whiteboardConfigJson = json_encode($whiteboardConfig, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $studentCount = $group->students()->count();
@endphp

<div class="mx-auto max-w-[1500px] px-6 py-2 space-y-6">
    <section class="overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-sm">
        <div class="bg-[linear-gradient(135deg,#083344_0%,#0f766e_52%,#f97316_100%)] px-6 py-6 text-white md:px-8">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <div class="max-w-3xl">
                    <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-orange-100">{{ $pageEyebrow }}</p>
                    <h1 class="mt-2 text-3xl font-bold text-white md:text-4xl">{{ $pageTitle }}</h1>
                    <p class="mt-3 max-w-2xl text-sm text-slate-100 md:text-base">
                        {{ $pageDescription }}
                    </p>
                    <div class="mt-4 flex flex-wrap items-center gap-3 text-xs font-semibold text-slate-100">
                        <span class="rounded-full border border-white/20 bg-white/10 px-3 py-1.5">
                            Groupe : {{ $group->name }}
                        </span>
                        <span class="rounded-full border border-white/20 bg-white/10 px-3 py-1.5">
                            {{ $studentCount }} participant{{ $studentCount > 1 ? 's' : '' }}
                        </span>
                        <span class="rounded-full border border-white/20 bg-white/10 px-3 py-1.5">
                            Mode libre, synchronise toutes les 2 secondes
                        </span>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <a href="{{ $secondaryUrl }}"
                       class="inline-flex items-center justify-center rounded-xl border border-white/20 px-4 py-2 text-sm font-semibold text-white transition hover:bg-white/10">
                        {{ $secondaryLabel }}
                    </a>
                    <a href="{{ $backUrl }}"
                       class="inline-flex items-center justify-center rounded-xl bg-white px-4 py-2 text-sm font-semibold text-slate-900 transition hover:bg-slate-100">
                        {{ $backLabel }}
                    </a>
                </div>
            </div>
        </div>

        <div class="border-b border-slate-200 bg-slate-50 px-6 py-3 md:px-8">
            <p class="text-xs text-slate-500">Dessinez, annotez, insérez des formes et du texte. Tout est sauvegardé automatiquement et partagé avec le groupe en temps réel.</p>
        </div>

        <div class="p-0">
            <div data-whiteboard-app></div>
            <script type="application/json" data-whiteboard-config>{!! $whiteboardConfigJson !!}</script>
        </div>
    </section>

    <noscript>
        <div class="rounded-2xl border border-orange-200 bg-orange-50 px-5 py-4 text-sm text-orange-900">
            Activez JavaScript pour utiliser le tableau blanc collaboratif.
        </div>
    </noscript>
</div>
@endsection
