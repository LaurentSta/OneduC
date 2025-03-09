
@extends('frontend.master')
@section('home')

<div class="container py-4 py-xl-5">
    <div class="entete">
        <h1>Le projet Onéduc.fr</h1>
        <h2>…en faveur de l’inclusion numérique.</h2>
        <figure>
            <blockquote class="blockquote">
                <p class="mb-0"><em>Mon parcours en tant qu’éducateur spécialisé et webmaster m’a poussé à chercher des solutions innovantes pour favoriser l’inclusion et l’éducation au numérique.</em></p>
            </blockquote>
            <figcaption class="blockquote-footer">Laurent Staelens – Fondateur de Oneduc.fr</figcaption>
        </figure>
    </div>
</div>
<div class="container py-4 py-xl-5">
    <div class="row gy-4 gy-md-0">
        <div class="col-md-6 text-center text-md-start d-flex d-sm-flex d-md-flex justify-content-center align-items-center justify-content-md-start align-items-md-center justify-content-xl-center">
            <div>
                <h2><img class="pe-0 me-4" width="32" height="32" src="{{ asset('frontend/assets/img/front-pages/icons/etoile8.gif')}}">Un engagement pour l’inclusion numérique</h2>
                <p class="fs-5">Dans un monde numérique en constante évolution, l’accès à l’éducation et à <strong>l’inclusion numérique</strong> est plus qu’une nécessité, c’est un droit. Je suis Laurent Staelens, fondateur d’Oneduc.fr, et mon parcours en tant qu’éducateur spécialisé m’a toujours poussé à chercher des solutions innovantes pour l’inclusion et l’éducation. Passionné par le potentiel du numérique, j’ai orienté ma carrière pour fusionner technologie et pédagogie, avec un objectif clair : rendre la formation accessible à tous, notamment aux personnes en situation de handicap.</p>
                <p class="fs-5">Oneduc.fr est né de cette conviction, avec l’ambition de devenir une plateforme de formation numérique accessible à tous, gratuitement, sur le long terme. Nous nous engageons à transformer les défis en opportunités et à faire de l’apprentissage numérique une expérience enrichissante et accessible à tous.&nbsp;</p>
            </div>
        </div>
        <div class="col-md-6">
            <div class="p-xl-5 m-xl-5"><img class="rounded img-fluid w-100 fit-cover" style="min-height:300px;" src="{{ asset('frontend/assets/img/illustrations/Engagement.svg')}}"></div>
        </div>
    </div>
</div>
<div class="container py-4 py-xl-5 mt-7">
    <div class="row gy-4 gy-md-0">
        <div class="col-md-6">
            <div class="p-xl-5 m-xl-5"><img class="rounded img-fluid w-100 fit-cover" style="min-height:300px;" src="{{ asset('frontend/assets/img/illustrations/CreationGroupe.svg')}}"></div>
        </div>
        <div class="col-md-6 align-items-md-center align-items-md-center">
            <h2><img class="pe-0 me-4" width="32" height="32" src="{{ asset('frontend/assets/img/front-pages/icons/etoile8.gif')}}">Gratuité et accessibilité sur le long terme</h2>
            <p class="fs-5">Mon projet actuel est d’offrir une plateforme de formation numérique entièrement gratuite à long terme.</p>
            <p class="fs-5">L’objectif est clair : garantir une accessibilité maximale et une inclusion totale, en veillant particulièrement à l’accessibilité pour les personnes atteintes de handicap. Ce projet à long terme est le reflet de notre engagement envers une éducation ouverte, accessible et évolutive, adaptée aux besoins de tous.&nbsp;</p>
            <p class="fs-5">Cela s’inscrit dans une perspective durable et engagée, visant à révolutionner la manière dont la formation est dispensée et reçue, en particulier pour les éducateurs et formateurs.</p>
            <p class="fs-5">Pour cela les fondateur de Onéduc se sont regroupés en association.</p>
        </div>
    </div>
</div>
<div class="container py-4 py-xl-5">
    <div class="row gy-4 gy-md-0">
        <div class="col-md-6 text-center text-md-start d-flex d-sm-flex d-md-flex justify-content-center align-items-center justify-content-md-start align-items-md-center justify-content-xl-center">
            <div>
                <h2><img class="pe-0 me-4" width="32" height="32" src="{{ asset('frontend/assets/img/front-pages/icons/etoile8.gif')}}">L’association Onéduc</h2>
                <p class="fs-5"><strong>Onéduc est une association regroupant des formateurs ayant créé une plateforme open source de digitalisation des formations. Elle met à disposition des formateurs une plateforme de formation en ligne fonctionnant sur tous supports.</strong></p>
                <p class="fs-5">La plateforme de Digital learning est utilisée dans le cadre de formations financées par des OPCO ou par le CPF (Compte personnel de Formation).</p>
                <div class="text-center">
                    <a href="#" class="btn btn-primary btn-lg rounded-pill me-2 mb-0" type="button" style="margin-right:20px;">Découvrez l’outil</a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="p-xl-5 m-xl-5"><img class="rounded img-fluid w-100 fit-cover" style="min-height:300px;" src="{{ asset('frontend/assets/img/illustrations/AssociationOneduc.svg')}}" width="581" height="635"></div>
        </div>
    </div>
</div>
<div data-bs-spy="scroll" class="scrollspy-example">
    <section class="bg-body section-py landing-reviews pb-0" id="landingReviews">
        <hr class="m-0 mt-6 mt-md-12">
    </section>
</div>

@endsection
