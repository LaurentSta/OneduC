@extends('frontend.master')
@section('home')


<div class="container">
    <div class="row entete mt-7 mb-7">
        <div class="col-md-6 col-xxl-9">
            <h1>L'association</h1>
            <h2>…en faveur de l’inclusion numérique.</h2>
            <p class="fs-5">Onéduc est une association loi 1901, fondée par Laurent Staelens, propriétaire et créateur de la plateforme onéduc.fr. Laurent Staelens confère à l’association Onéduc les droits d’utiliser, d’adapter, de développer, d’exploiter la plateforme, y compris à titre commercial.</p>
        </div>
        <div class="col align-self-center" style="text-align:center;"><img src="{{ asset('frontend/assets/img/illustrations/QrcodeHelloAsso.png')}}" width="249" height="249"></div>
    </div>
</div>
<div class="scrollspy-example" data-bs-spy="scroll"><iframe id="haWidget" src="https://www.helloasso.com/associations/oneduc/adhesions/formulaire-d-adhesion-oneduc/widget" style="width:100%;height:750px;border:none;"></iframe></div>


@endsection
