@extends('frontend.master')

@section('home')

<!-- Content wrapper -->
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card mb-6">
            <h5 class="card-header">Inscription formateur</h5>
            <h8 class="card-header"> * (obligatoire)</h8>

            <!-- Gestion des messages -->
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form class="card-body" action="{{ route('instructor.register') }}" method="POST">
                @csrf

                <div class="row g-6">
                    <div class="col-md-6">
                        <label class="form-label" for="first_name">Prénom *</label>
                        <input type="text" id="first_name" name="name" class="form-control" placeholder="Laurent" value="{{ old('name') }}" required />
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="last_name">Nom *</label>
                        <input type="text" id="last_name" name="last_name" class="form-control" placeholder="Dupont" value="{{ old('last_name') }}" required />
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="email">Email *</label>
                        <input type="email" id="email" name="email" class="form-control" placeholder="exemple@email.com" value="{{ old('email') }}" required />
                    </div>

                    <!-- Mot de passe et confirmation sur la même ligne -->
                    <div class="col-md-6">
                        <label class="form-label" for="password">Mot de passe *</label>
                        <div class="input-group input-group-merge">
                            <input type="password" id="password" name="password" class="form-control" required />
                            <span class="input-group-text cursor-pointer"><i class="ti ti-eye-off"></i></span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="password_confirmation">Confirmez le mot de passe *</label>
                        <div class="input-group input-group-merge">
                            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required />
                            <span class="input-group-text cursor-pointer"><i class="ti ti-eye-off"></i></span>
                        </div>
                    </div>
                </div>

                <hr class="my-6 mx-n4" />

                <h6>2. Informations Complémentaires (optionnelles)</h6>
                <div class="row g-6">
                    <div class="col-md-6">
                        <label class="form-label" for="societe">Société ou Asso (optionnel)</label>
                        <input type="text" id="societe" name="societe" class="form-control" placeholder="ABC Insertion" value="{{ old('societe') }}" />
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="phone">Téléphone (optionnel)</label>
                        <input type="text" id="phone" name="phone" class="form-control" placeholder="06 12 34 56 78" value="{{ old('phone') }}" />
                    </div>
                </div>

                <div class="pt-6">
                    <button type="submit" class="btn btn-primary me-4">S'inscrire</button>
                    <button type="reset" class="btn btn-label-secondary">Annuler</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
