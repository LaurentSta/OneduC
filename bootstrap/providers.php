<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Domains\Outils\Minuteur\MinuteurServiceProvider::class,
    App\Domains\Outils\Pendu\PenduServiceProvider::class,
    App\Domains\Outils\Memoire\MemoireServiceProvider::class,
    App\Domains\Outils\Carrousel\CarrouselServiceProvider::class,
    App\Domains\Outils\CartesRetourner\CartesRetournerServiceProvider::class,
    App\Domains\Outils\TriCartes\TriCartesServiceProvider::class,
];
