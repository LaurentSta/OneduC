<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Oneduc - {{ $pageTitle ?? 'Parcours formateur' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@500;600;700;800&family=Varela+Round&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="icon" href="{{ asset('backend/assets/img/favicon/favicon.ico') }}" type="image/x-icon">
    <style>
        [x-cloak] { display: none !important; }
        .font-lisible { font-family: 'Open Sans', system-ui, sans-serif; }
        @keyframes consigne-invite {
            0%, 100% {
                box-shadow: 0 0 0 0 rgba(233, 77, 42, 0.42), 0 8px 18px -16px rgba(233, 77, 42, 0.8);
                border-color: rgba(233, 77, 42, 0.45);
            }

            50% {
                box-shadow: 0 0 0 10px rgba(233, 77, 42, 0.16), 0 16px 30px -18px rgba(233, 77, 42, 0.95);
                border-color: rgba(233, 77, 42, 0.9);
            }
        }

        @keyframes consigne-nudge {
            0%, 100% { transform: translateX(0); }
            8% { transform: translateX(-2px); }
            16% { transform: translateX(2px); }
            24% { transform: translateX(0); }
        }

        .consigne-invite {
            animation: consigne-invite 1.45s ease-in-out infinite, consigne-nudge 4s ease-in-out infinite;
        }

        .marc-pagination span[aria-current="page"] > span {
            border-color: #004461 !important;
            background-color: #004461 !important;
            color: #fff !important;
            font-weight: 800;
        }
    </style>
    @stack('styles')
</head>
@php
    $overviewModules = collect($parcoursModules ?? [])
        ->values()
	        ->map(fn ($module) => [
	            'label' => $module['label'] ?? 'Module',
	            'title' => $module['title'] ?? '',
	            'full_title' => $module['full_title'] ?? ($module['title'] ?? ''),
	            'description' => $module['description'] ?? null,
	            'time_label' => $module['duration_label'] ?? null,
	            'url' => $module['url'] ?? '#',
            'is_current' => isset($activeModuleKey) && isset($module['url']) && $activeModuleKey !== null && str_contains($module['url'], '/' . $activeModuleKey),
        ])
        ->values();
    $isOverviewHomeActive = ($activeModuleKey ?? null) === null;
    $hideParcoursHeader = trim($__env->yieldContent('hide_parcours_header')) === 'true';
    $hideParcoursBrandHeader = $hideParcoursHeader || trim($__env->yieldContent('hide_parcours_brand_header')) === 'true';
    $hideParcoursOverview = trim($__env->yieldContent('hide_parcours_overview')) === 'true';
@endphp
<body class="min-h-screen bg-slate-50 text-slate-900">
    @unless ($hideParcoursBrandHeader)
        <header class="sticky top-0 z-40 border-b border-slate-200 bg-white">
            <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-5 py-4 lg:px-8">
                <a href="{{ route('formateur.dashboard') }}" class="inline-flex items-center">
                    <img src="{{ asset('frontend/assets/img/front-pages/branding/LogoOneducPositionG.svg') }}" alt="Logo Oneduc" class="h-11 w-auto">
                </a>

                <div class="flex flex-wrap items-center justify-end gap-3">
                    <a href="{{ route('formateur.dashboard') }}" class="inline-flex items-center rounded-full border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-orangeone hover:text-orangeone">
                        Retour au tableau de bord
                    </a>
                </div>
            </div>
        </header>
    @endunless

    @unless ($hideParcoursOverview)
        <div class="relative z-30 border-t border-orange-100 bg-white">
            <div class="mx-auto max-w-7xl px-5 lg:px-8">
                <section
                    aria-labelledby="parcours-overview-title"
                    class="relative z-30 h-0 overflow-visible"
                    style="width: 100vw; margin-left: calc(50% - 50vw);"
                >
                    <div
                        id="parcours-overview-shell"
                        class="absolute inset-x-0 top-0 z-30 will-change-transform transition-transform duration-500 ease-[cubic-bezier(0.22,1,0.36,1)]"
                        style="transform: translateY(-100%);"
                    >
                        <div
                            id="parcours-overview-drawer"
                            class="overflow-hidden border-b-2 border-b-orangeone bg-white shadow-[0_24px_54px_-26px_rgba(0,68,97,0.42)]"
                        >
                            <div id="parcours-overview-panel" class="overflow-x-auto bg-white py-3">
                                <div class="mx-auto flex w-max items-stretch gap-0 px-5 lg:max-w-7xl lg:px-8 xl:min-w-0 xl:w-full xl:justify-between">
                                    <a
                                        href="{{ route('formateur.parcours.index') }}"
                                        class="flex h-[128px] w-[56px] flex-none items-center justify-center rounded-[20px] border-2 px-3 py-4 text-center transition hover:-translate-y-0.5 hover:shadow-md {{ $isOverviewHomeActive ? 'border-orangeone bg-orange-50/60' : 'border-bleuone bg-white' }}"
                                        aria-label="Accueil du parcours"
                                    >
                                        <span style="writing-mode: vertical-rl; transform: rotate(180deg);" class="text-[14px] font-semibold tracking-[0.18em] text-bleuone uppercase">
                                            Accueil
                                        </span>
                                    </a>

                                    @if ($overviewModules->isNotEmpty())
                                        <div class="flex w-[32px] flex-none items-center text-orangeone" aria-hidden="true">
                                            <span class="h-0.5 flex-1 bg-orangeone"></span>
                                            <svg xmlns="http://www.w3.org/2000/svg" class="-ml-1 h-8 w-8 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12h16m-5-5 5 5-5 5" />
                                            </svg>
                                        </div>
                                    @endif

                                    @foreach ($overviewModules as $module)
	                                    <a
	                                        href="{{ $module['url'] }}"
	                                        class="flex w-[176px] flex-none flex-col rounded-[20px] border-2 px-4 py-3 transition hover:-translate-y-0.5 hover:shadow-md xl:w-[176px] {{ $module['is_current'] ? 'border-orangeone bg-orange-50/60' : 'border-bleuone bg-white' }}"
	                                        data-parcours-tooltip="{{ $module['full_title'] }}"
	                                    >
                                            <div class="flex items-center justify-between gap-2">
                                                <span class="text-[10px] font-semibold uppercase tracking-[0.24em] text-orangeone">{{ $module['label'] }}</span>
                                                <span class="text-[10px] font-semibold text-slate-500">{{ $module['time_label'] }}</span>
                                            </div>
	                                        <span class="mt-2 text-[18px] leading-5 font-semibold text-bleuone">{{ $module['title'] }}</span>
	                                    </a>

                                        @if (! $loop->last)
                                            <div class="flex w-[32px] flex-none items-center text-orangeone" aria-hidden="true">
                                                <span class="h-0.5 flex-1 bg-orangeone"></span>
                                                <svg xmlns="http://www.w3.org/2000/svg" class="-ml-1 h-8 w-8 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12h16m-5-5 5 5-5 5" />
                                                </svg>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="relative h-0 overflow-visible">
                            <button
                                type="button"
                                id="parcours-overview-toggle"
                                class="absolute right-5 top-0 z-10 inline-flex items-center gap-3 rounded-b-[18px] border-2 border-t-0 border-orangeone bg-white px-5 py-2 text-sm font-semibold text-bleuone shadow-[0_14px_30px_-18px_rgba(0,68,97,0.5)] transition-transform duration-500 ease-[cubic-bezier(0.22,1,0.36,1)] lg:right-8"
                                aria-expanded="false"
                                aria-controls="parcours-overview-drawer"
                            >
                                <span id="parcours-overview-title">Vue d'ensemble du parcours</span>
                                <svg id="parcours-overview-icon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-orangeone transition-transform duration-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    @endunless

    <main class="relative z-10 w-full px-0 {{ $hideParcoursBrandHeader ? 'py-0' : 'py-6' }}">
        @yield('parcours_content')
    </main>

    <div
        id="parcours-step-tooltip"
        class="pointer-events-none fixed left-0 top-0 z-50 w-[240px] rounded-[18px] border border-bleuone bg-bleuone px-4 py-3 text-sm text-white opacity-0 shadow-[0_24px_48px_-24px_rgba(0,68,97,0.45)] transition duration-150"
        style="display: none;"
        aria-hidden="true"
    >
        <p id="parcours-step-tooltip-text" class="text-sm leading-6 text-white"></p>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const shell = document.getElementById('parcours-overview-shell');
            const toggle = document.getElementById('parcours-overview-toggle');
            const drawer = document.getElementById('parcours-overview-drawer');
            const icon = document.getElementById('parcours-overview-icon');
            const overviewTooltip = document.getElementById('parcours-step-tooltip');
            const overviewTooltipText = document.getElementById('parcours-step-tooltip-text');
            const overviewTooltipTriggers = Array.from(document.querySelectorAll('[data-parcours-tooltip]'));

            if (!shell || !toggle || !drawer || !icon) {
                return;
            }

            let isExpanded = false;
            let activeTooltipTrigger = null;

            const renderOverview = () => {
                const closedOffset = drawer.offsetHeight;

                shell.style.transform = isExpanded
                    ? 'translateY(0px)'
                    : `translateY(-${closedOffset}px)`;

                toggle.setAttribute('aria-expanded', String(isExpanded));
                icon.classList.toggle('rotate-180', isExpanded);
                toggle.setAttribute(
                    'aria-label',
                    isExpanded ? "Réduire la vue d'ensemble du parcours" : "Déployer la vue d'ensemble du parcours"
                );
            };

            renderOverview();
            window.addEventListener('resize', renderOverview);

            toggle.addEventListener('click', () => {
                isExpanded = !isExpanded;
                renderOverview();
            });

            if (!overviewTooltip || !overviewTooltipText || overviewTooltipTriggers.length === 0) {
                return;
            }

            const hideTooltip = () => {
                activeTooltipTrigger = null;
                overviewTooltip.classList.remove('translate-y-0', 'opacity-100');
                overviewTooltip.classList.add('translate-y-2', 'opacity-0');

                window.setTimeout(() => {
                    if (!activeTooltipTrigger) {
                        overviewTooltip.style.display = 'none';
                        overviewTooltip.setAttribute('aria-hidden', 'true');
                    }
                }, 150);
            };

            const positionTooltip = trigger => {
                const rect = trigger.getBoundingClientRect();
                const tooltipRect = overviewTooltip.getBoundingClientRect();
                const viewportPadding = 16;
                const preferredTop = rect.top - tooltipRect.height - 12;
                const shouldPlaceBelow = preferredTop < viewportPadding;
                const top = shouldPlaceBelow ? rect.bottom + 12 : preferredTop;
                const centeredLeft = rect.left + (rect.width / 2) - (tooltipRect.width / 2);
                const left = Math.min(
                    window.innerWidth - tooltipRect.width - viewportPadding,
                    Math.max(viewportPadding, centeredLeft)
                );

                overviewTooltip.style.top = `${top}px`;
                overviewTooltip.style.left = `${left}px`;
            };

            const showTooltip = trigger => {
                activeTooltipTrigger = trigger;
                overviewTooltipText.textContent = trigger.dataset.parcoursTooltip ?? '';
                overviewTooltip.style.display = 'block';
                overviewTooltip.setAttribute('aria-hidden', 'false');
                overviewTooltip.classList.remove('translate-y-2', 'opacity-0');
                overviewTooltip.classList.add('translate-y-0', 'opacity-100');
                positionTooltip(trigger);
            };

            overviewTooltipTriggers.forEach(trigger => {
                trigger.addEventListener('mouseenter', () => showTooltip(trigger));
                trigger.addEventListener('focus', () => showTooltip(trigger));
                trigger.addEventListener('mouseleave', hideTooltip);
                trigger.addEventListener('blur', hideTooltip);
            });

            window.addEventListener('resize', () => {
                if (activeTooltipTrigger) {
                    positionTooltip(activeTooltipTrigger);
                }
            });

            window.addEventListener('scroll', () => {
                if (activeTooltipTrigger) {
                    positionTooltip(activeTooltipTrigger);
                }
            }, { passive: true });

            document.addEventListener('keydown', event => {
                if (event.key === 'Escape') {
                    hideTooltip();
                }
            });
        });
    </script>
@include('partials.a11y-scripts')
</body>
</html>
