@extends('frontend.master')
@section('home')
<div class="container">
    <div class="row bgentete" style="min-height: 500px;">
        <div class="col-md-6 text-center text-md-start d-flex d-sm-flex d-md-flex justify-content-center align-items-center justify-content-md-start align-items-md-center justify-content-xl-center">
            <div style="max-width:800px;">
                <h1 class="text-uppercase">OUTIL NUMÉRIQUE ÉDUCATIF</h1>
                <h2>Qui favorise l’inclusion numérique</h2>
                <a href="{{route('projet')}}" class="btn btn-primary btn-lg rounded-pill me-2 mb-0" type="button" style="margin-right:20px;">Le projet</a>
                <a href="#" class="btn btn-primary btn-lg inverse rounded-pill" type="button" style="background-color: #ffffff;color: #e94D2A;">S'inscrire gratuitement</a>
            </div>

        </div>
        <div class="col-md-6 text-center text-md-start d-flex d-sm-flex d-md-flex justify-content-center align-items-center justify-content-md-start align-items-md-center justify-content-xl-center">
            <div class="video-container">
                <iframe
                    src="https://www.youtube.com/embed/Bw4_SlnqZj8?autoplay=1&mute=1&loop=1&playlist=Bw4_SlnqZj8&controls=0&showinfo=0&modestbranding=1"
                    frameborder="0"
                    allowfullscreen
                    allow="autoplay">
                </iframe>
            </div>
        </div>
    </div>

    <div class="container py-4 py-xl-5">
        <div class="row gy-4 gy-md-0">
            <div class="col-md-6">
                <div class="p-xl-5 m-xl-5"><img class="rounded img-fluid w-100 fit-cover" style="min-height:300px;" src="{{ asset('frontend/assets/img/front-pages/landing-page/LivreOneduc.svg')}}"></div>
            </div>
            <div class="col-md-6 d-md-flex align-items-md-center">
                <div>
                    <h2><img class="pe-0 me-7" width="60" height="61" src="{{ asset('frontend/assets/img/front-pages/icons/etoile8.gif')}}">Oneduc.fr c’est quoi ?</h2>
                    <p class="fs-5">Onéduc.fr est une plateforme de formation en ligne conçue pour favoriser l’inclusion numérique de tous. </p>
                    <p class="fs-5">Dans un monde numérique en constante évolution, l’accès à l’éducation et à l’inclusion numérique est plus qu’une nécessité, c’est un droit. </p>
                    <p class="fs-5">Créée par des enseignants, des formateurs et éducateurs spécialisées regroupés en association, cette plateforme s’adresse tout particulièrement aux personnes éloignées de l’emploi, ayant des difficultés d’apprentissage ou en situation de illectronisme…</p>
                </div>
            </div>
        </div>
    </div>


    <div class="container py-4 py-xl-5">
        <div class="row gy-4 gy-md-0">
            <div class="col-md-6 d-md-flex align-items-md-center">
                <div>
                    <h2><img class="pe-0 me-7" width="60" height="61" src="{{ asset('frontend/assets/img/front-pages/icons/etoile8.gif')}}">A qui s’adresse Onéduc?</h2>
                    <p class="fs-5"><strong>Onéduc s’adresse à tous les formateurs désireux d’utiliser un outil numérique open source pour former leurs apprenants : </strong></p>
                    <ul class="fs-5">
                        <li>Les formateurs peuvent <strong>utiliser du contenu</strong> de formations fourni par la plateforme.</li>
                        <li>Les formateurs peuvent également <strong>intégrer leur propre contenu</strong> de formations sur la plateforme et l’utiliser avec leurs apprenants.</li>
                    </ul>
                    <p class="fs-5">Nous accompagnons ainsi des <strong>formateurs individuels</strong>, des <strong>centres de formation</strong>. Nous travaillons également avec des <strong>EA </strong>(Entreprises Adaptées) et des <strong>ESAT </strong>(Établissements et services d’aide par le travail) qui utilisent la plateforme pour accompagner leurs usagers et travailleurs par l’intermédiaire de leurs personnels socio-éducatifs.</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="p-xl-5 m-xl-5"><img class="rounded img-fluid w-100 fit-cover" style="min-height:300px;" src="{{ asset('frontend/assets/img/front-pages/landing-page/LandingPage2.svg')}}"></div>
            </div>
        </div>
    </div>

    <div class="container mt-7">
        <div class="row">
            <div class="col-md-12 pt-0">
                <h2 class="text-center">Les avantages de la formation par Oneduc.fr</h2>
            </div>
        </div>
        <div class="row">
            <div class="col-md-3">
                <div class="text-center d-flex flex-column align-items-center align-items-xl-center"><img src="{{ asset('frontend/assets/img/front-pages/icons/iconeEntete-04.svg')}}" width="200">
                    <div class="px-3">
                        <h5>Formation hybride</h5>
                        <p><em>Présentiel et distanciel, selon les besoins.</em></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="text-center d-flex flex-column align-items-center align-items-xl-center"><img src="{{ asset('frontend/assets/img/front-pages/icons/iconeEntete-06.svg')}}" width="200">
                    <div class="px-3">
                        <h5>Contenus interactifs</h5>
                        <p><em>Intégration de vidéos, audios, animations…</em></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="text-center d-flex flex-column align-items-center align-items-xl-center"><img src="{{ asset('frontend/assets/img/front-pages/icons/iconeEntete-02.svg')}}" width="200">
                    <div class="px-3">
                        <h5>Création &amp; évaluation</h5>
                        <p><em>Quiz, devoirs, suivi des progrès</em></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="text-center d-flex flex-column align-items-center align-items-xl-center"><img src="{{ asset('frontend/assets/img/front-pages/icons/iconeEntete-03.svg')}}" width="200">
                    <div class="px-3">
                        <h5>Engagement &amp; gamification</h5>
                        <p><em>Badges, niveaux, attestations</em></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="text-center d-flex flex-column align-items-center align-items-xl-center"><img src="{{ asset('frontend/assets/img/front-pages/icons/iconeEntete-10.svg')}}" width="200">
                    <div class="px-3">
                        <h5>Gestion des stagiaires</h5>
                        <p><em>Suivi individuel ou en groupe.</em></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="text-center d-flex flex-column align-items-center align-items-xl-center"><img src="{{ asset('frontend/assets/img/front-pages/icons/iconeEntete-11.svg')}}" width="200">
                    <div class="px-3">
                        <h5>Communication directe</h5>
                        <p><em>Chat et forum avec les formateurs.</em></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="text-center d-flex flex-column align-items-center align-items-xl-center"><img src="{{ asset('frontend/assets/img/front-pages/icons/iconeEntete-12.svg')}}" width="200">
                    <div class="px-3">
                        <h5>Notifications automatiques</h5>
                        <p><em>Emails, popups pour un meilleur suivi.</em></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="text-center d-flex flex-column align-items-center align-items-xl-center"><img src="{{ asset('frontend/assets/img/front-pages/icons/iconeEntete-09.svg')}}" width="200">
                    <div class="px-3">
                        <h5>Rapports détaillés</h5>
                        <p><em>Évaluations et progression des apprenants.</em></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container py-4 py-xl-5">
        <div class="row gy-4 gy-md-0">
            <div class="col-md-6 d-md-flex align-items-md-center">
                <div>
                    <h2><img class="pe-0 me-7" width="60" height="61" src="{{ asset('frontend/assets/img/front-pages/icons/etoile8.gif')}}">Oneduc.fr c’est quoi ?</h2>
                    <p class="fs-5">Onéduc est une association regroupant des formateurs ayant créé une plateforme open source de digitalisation des formations. Elle met à disposition des formateurs une plateforme de formation en ligne fonctionnant sur tous supports. </p>
                    <p class="fs-5">La plateforme de Digital learning est utilisée dans le cadre de formations financées par des OPCO ou par le CPF (Compte personnel de Formation).</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="p-xl-5 m-xl-5"><img class="rounded img-fluid w-100 fit-cover" style="min-height:300px;" src="{{ asset('frontend/assets/img/front-pages/landing-page/module.svg')}}"></div>
            </div>
        </div>
    </div>
</div>

@endsection
