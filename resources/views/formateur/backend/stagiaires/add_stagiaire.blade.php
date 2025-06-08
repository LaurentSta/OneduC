@extends('formateur.dashboard')
@section('formateur')

<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">

        <!-- Carte -->
        <div class="card">
            <div class="card-header">
                <h5>Créer un nouveau stagiaire</h5>
            </div>

            <div class="card-body">

                {{-- Affichage des erreurs de validation --}}
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Formulaire de création --}}
                <form action="{{ route('formateur.stagiaires.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="prenom" class="form-label">Prénom du stagiaire</label>
                        <input type="text" name="prenom" id="prenom" class="form-control" required placeholder="Prénom">
                    </div>
                    <div class="mb-3">
                        <label for="name" class="form-label">Nom du stagiaire</label>
                        <input type="text" name="name" id="name" class="form-control" required placeholder="Nom complet">
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Adresse Email</label>
                        <input type="email" name="email" id="email" class="form-control" required placeholder="email@exemple.com">
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Mot de passe</label>
                        <input type="password" name="password" id="password" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Confirmer le mot de passe</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
                    </div>

                    <button type="submit" class="btn btn-primary">Créer le stagiaire</button>
                </form>

            </div>
        </div>

    </div>
</div>

@endsection
