@extends('frontend.master')
@section('home')


{{-- BLOC INTRO — Les parcours --}}
<div class="container mx-auto px-4 pt-8 pb-2"> {{-- py réduite ici --}}
    <div class="bg-white rounded-[20px] shadow-md p-8 mb-4 w-full"> {{-- my-10 ➜ mb-4 pour coller à la suite --}}
        <div class="grid grid-cols-12 gap-6 items-center">

            {{-- Texte --}}
            <div class="col-span-12 md:col-span-8">
                <x-typography variant="titre">L'association</x-typography>
                <x-typography variant="sous-titre" class="font-varela text-sous-titre text-orangeone">
                    …en faveur de l’inclusion numérique.
                </x-typography>
                <x-typography>
                    Onéduc est une association loi 1901, fondée par Laurent Staelens, propriétaire et créateur de la plateforme onéduc.fr. Laurent Staelens confère à l’association Onéduc les droits d’utiliser, d’adapter, de développer, d’exploiter la plateforme, y compris à titre commercial.
                </x-typography>
            </div>

            {{-- Image --}}
            <div class="col-span-12 md:col-span-4 flex justify-center md:justify-end">
                <div class="w-full max-w-xs">
                    <img src="{{ asset('frontend/assets/img/illustrations/QrcodeHelloAsso.png')}}" width="249" height="249">
                </div>
            </div>

        </div>
    </div>
</div>


<div class="scrollspy-example" data-bs-spy="scroll"><iframe id="haWidget" src="https://www.helloasso.com/associations/oneduc/adhesions/formulaire-d-adhesion-oneduc/widget" style="width:100%;height:750px;border:none;"></iframe></div>


@endsection
