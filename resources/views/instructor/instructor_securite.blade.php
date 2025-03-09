@extends('admin.admin_dashboard')
@section('admin')

<!-- Content wrapper -->
<div class="content-wrapper">
    <!-- Content -->
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-md-12">
                <div class="nav-align-top">
                    <ul class="nav nav-pills flex-column flex-md-row mb-6 gap-2 gap-lg-0">
                      <li class="nav-item">
                        <a class="nav-link " href="{{route('admin.parametre')}}"
                          ><i class="ti-sm ti ti-users me-1_5"></i> Compte</a
                        >
                      </li>
                      <li class="nav-item">
                        <a class="nav-link active" href="{{route('admin.securite')}}"
                          ><i class="ti-sm ti ti-lock me-1_5"></i> Sécurité</a
                        >
                      </li>

                    </ul>
                  </div>

                  <form method="POST" action="{{ route('admin.securite') }}">
                    @csrf
                    <div class="mb-4">
                        <label for="currentPassword">Mot de passe actuel</label>
                        <input type="password" name="currentPassword" id="currentPassword" class="form-control" required />
                    </div>

                    <div class="mb-4">
                        <label for="newPassword">Nouveau mot de passe</label>
                        <input type="password" name="newPassword" id="newPassword" class="form-control" required />
                    </div>

                    <div class="mb-4">
                        <label for="newPassword_confirmation">Confirmer le nouveau mot de passe</label>
                        <input type="password" name="newPassword_confirmation" id="newPassword_confirmation" class="form-control" required />
                    </div>

                    <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                </form>
                @if(session('message'))
    <script>
        toastr.success('{{ session('message') }}', 'Succès');
    </script>
@endif

@if(session('error'))
    <script>
        toastr.error('{{ session('error') }}', 'Erreur');
    </script>
@endif
            </div>
        </div>
    </div>
    <!-- / Content -->

    <div class="content-backdrop fade"></div>
</div>
<!-- Content wrapper -->

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
<!-- Toastr JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    @if(session('message'))
        <script>
            $(document).ready(function() {
                toastr.success('{{ session('message') }}', 'Succès', {
                    "closeButton": true,
                    "progressBar": true,
                    "positionClass": "toast-top-right",
                    "timeOut": "10000"
                });
            });
        </script>
    @endif


@endsection
