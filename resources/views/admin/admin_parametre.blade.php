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
                        <a class="nav-link active" href="{{route('admin.parametre')}}"
                          ><i class="ti-sm ti ti-users me-1_5"></i> Compte</a
                        >
                      </li>
                      <li class="nav-item">
                        <a class="nav-link" href="{{route('admin.securite')}}"
                          ><i class="ti-sm ti ti-lock me-1_5"></i> Sécurité</a
                        >
                      </li>
                      <li class="nav-item">
                        <a class="nav-link" href="pages-account-settings-billing.html"
                          ><i class="ti-sm ti ti-bookmark me-1_5"></i> Billing & Plans</a
                        >
                      </li>
                      <li class="nav-item">
                        <a class="nav-link" href="pages-account-settings-notifications.html"
                          ><i class="ti-sm ti ti-bell me-1_5"></i> Notifications</a
                        >
                      </li>
                      <li class="nav-item">
                        <a class="nav-link" href="pages-account-settings-connections.html"
                          ><i class="ti-sm ti ti-link me-1_5"></i> Connections</a
                        >
                      </li>
                    </ul>
                  </div>

                <div class="card mb-6">
                    <!-- Account -->



                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.profil.store') }}" enctype="multipart/form-data">
                            @csrf
                            <div class="d-flex align-items-start align-items-sm-center gap-6">
                                <img
                                    src="{{ (!empty($profileData->photo)) ? url('upload/admin_images/'.$profileData->photo) : url('upload/admin_images/NoPhoto.png') }}"
                                    alt="user-avatar"
                                    class="d-block w-px-100 h-px-100 rounded"
                                    id="uploadedAvatar" />
                                <div class="button-wrapper">
                                    <label for="upload" class="btn btn-primary me-3 mb-4" tabindex="0">
                                        <span class="d-none d-sm-block">Télécharger une nouvelle photo</span>
                                        <i class="ti ti-upload d-block d-sm-none"></i>
                                        <input
                                            type="file"
                                            id="upload"
                                            name="photo"
                                            class="form-control"
                                            hidden
                                            accept="image/png, image/jpeg" />
                                    </label>
                                    <button type="button" class="btn btn-label-secondary account-image-reset mb-4" id="resetImage">
                                        <i class="ti ti-refresh-dot d-block d-sm-none"></i>
                                        <span class="d-none d-sm-block">Reset</span>
                                    </button>
                                    <div>JPG ou PNG autorisés. Taille maximale de 800 Ko.</div>
                                </div>
                            </div>

                            <div class="row pt-4">
                                <div class="mb-4 col-md-6">
                                    <label for="firstName" class="form-label">Prénom</label>
                                    <input class="form-control" type="text" id="firstName" name="firstName" value="{{ $profileData->username }}" autofocus />
                                </div>
                                <div class="mb-4 col-md-6">
                                    <label for="lastName" class="form-label">Nom</label>
                                    <input class="form-control" type="text" name="lastName" id="lastName" value="{{ $profileData->name }}" />
                                </div>
                                <div class="mb-4 col-md-6">
                                    <label for="email" class="form-label">E-mail</label>
                                    <input class="form-control" type="text" id="email" name="email" value="{{ $profileData->email }}" placeholder="John@example.com" />
                                </div>
                                <div class="mb-4 col-md-6">
                                    <label class="form-label" for="phoneNumber">Numéro de téléphone</label>
                                    <input type="text" id="phoneNumber" name="phoneNumber" class="form-control" value="{{ $profileData->phone }}" placeholder="XX XX XX XX XX" />
                                </div>
                                <div class="mt-2">
                                    <button type="submit" class="btn btn-primary me-3">Enregistrer les modifications</button>
                                    <button type="reset" class="btn btn-label-secondary">Annuler</button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <!-- /Account -->
                </div>
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

    @if(session('error'))
        <script>
            $(document).ready(function() {
                toastr.error('{{ session('error') }}', 'Erreur', {
                    "closeButton": true,
                    "progressBar": true,
                    "positionClass": "toast-top-right",
                    "timeOut": "10000"
                });
            });
        </script>
    @endif

    <script type="text/javascript">
        $(document).ready(function(){
            $('#upload').change(function(e){
                var reader = new FileReader();
                reader.onload = function(e){
                    $('#uploadedAvatar').attr('src', e.target.result);
                }
                reader.readAsDataURL(e.target.files[0]);
            });

            $('#resetImage').click(function() {
                $('#uploadedAvatar').attr('src', '{{ url('upload/admin_images/NoPhoto.png') }}');
                $('#upload').val('');
            });
        });
    </script>
@endsection
