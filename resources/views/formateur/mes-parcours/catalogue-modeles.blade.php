@extends('formateur.dashboard')

@section('formateur')
    <div class="mx-auto max-w-[1285px] px-8">
        <header class="mb-6 rounded-[20px] bg-white px-8 py-6 shadow-md">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <x-typography variant="titre">Modèles de parcours Oneduc</x-typography>
                    <p class="mt-2 text-sm text-gray-600">Copiez une structure officielle dans vos parcours pour l’adapter à vos groupes.</p>
                </div>
                <a href="{{ route('formateur.mes-parcours.index') }}" class="text-sm font-semibold text-bleuone hover:text-orangeone">Retour à mes parcours</a>
            </div>
        </header>

        @if ($errors->any())
            <div role="alert" class="mb-6 rounded-[12px] border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                <p class="font-semibold">Le modèle n’a pas pu être copié :</p>
                <ul class="mt-2 list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($modeles->isEmpty())
            <div class="rounded-[20px] bg-white px-8 py-16 text-center shadow-md">
                <p class="text-gray-500">Aucun modèle de parcours n’est actuellement publié.</p>
            </div>
        @else
            <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($modeles as $modele)
                    <article class="flex flex-col overflow-hidden rounded-[20px] bg-white shadow-md">
                        <div class="flex-1 px-6 py-5">
                            <span class="inline-flex rounded-full bg-bleuone/10 px-2.5 py-1 text-xs font-semibold text-bleuone">Catalogue Oneduc</span>
                            <h2 class="mt-3 text-lg font-semibold text-gray-900">{{ $modele->titre }}</h2>
                            @if ($modele->description)
                                <p class="mt-2 line-clamp-3 text-sm text-gray-600">{{ $modele->description }}</p>
                            @endif
                            <p class="mt-4 text-xs text-gray-500">{{ $modele->items_count }} étape{{ $modele->items_count > 1 ? 's' : '' }}</p>
                        </div>
                        <div class="border-t border-gray-100 bg-gray-50 px-6 py-4">
                            <form method="POST" action="{{ route('formateur.modeles-parcours.dupliquer', $modele) }}">
                                @csrf
                                <button type="submit" class="inline-flex w-full min-h-10 items-center justify-center gap-2 rounded-[10px] bg-orangeone px-4 text-sm font-semibold text-white hover:bg-orangeone-hover">
                                    <i class="ti ti-copy" aria-hidden="true"></i>
                                    Copier dans mes parcours
                                </button>
                            </form>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="mt-8">{{ $modeles->links() }}</div>
        @endif
    </div>
@endsection
