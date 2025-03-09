@extends('admin.admin_dashboard')
@section('admin')

<!-- Contenu principal -->
<div class="content-wrapper">
    <!-- Contenu -->
    <div class="container-xxl flex-grow-1 container-p-y">

        <!-- En-tête -->
        <div class="row">
            <div class="col-12">
                <div class="card mb-6">
                    <div class="user-profile-header-banner">
                        <img src="{{ asset('backend/assets/img/pages/profile-banner.png') }}" alt="Image de bannière" class="rounded-top" />
                    </div>
                    <div class="user-profile-header d-flex flex-column flex-lg-row text-sm-start text-center mb-5">
                        <div class="flex-shrink-0 mt-n2 mx-sm-0 mx-auto">
                            <img src="{{ !empty($profileData->photo) ? asset('upload/admin_images/'.$profileData->photo) : asset('upload/admin_images/NoPhoto.png') }}" alt="user-avatar" class="d-block h-auto ms-0 ms-sm-6 rounded user-profile-img" style="max-width: 150px; max-height: 150px; object-fit: cover;">

                        </div>
                        <div class="flex-grow-1 mt-3 mt-lg-5">
                            <div class="d-flex align-items-md-end align-items-sm-start align-items-center justify-content-md-between justify-content-start mx-5 flex-md-row flex-column gap-4">
                                <div class="user-profile-info">
                                    <h4 class="mb-2 mt-lg-6">{{$profileData->username}}</h4>
                                    <ul class="list-inline mb-0 d-flex align-items-center flex-wrap justify-content-sm-start justify-content-center gap-4 my-2">
                                        <li class="list-inline-item d-flex gap-2 align-items-center">
                                            <span class="fw-medium">{{$profileData->name}}</span>
                                        </li>
                                        <li class="list-inline-item d-flex gap-2 align-items-center">
                                            <i class="ti ti-map-pin ti-lg"></i><span class="fw-medium">{{$profileData->address}}</span>
                                        </li>
                                        <li class="list-inline-item d-flex gap-2 align-items-center">
                                            <i class="ti ti-calendar ti-lg"></i><span class="fw-medium">{{$profileData->created_at}}</span>
                                        </li>
                                    </ul>
                                </div>
                                <a href="javascript:void(0)" class="btn btn-primary mb-1">
                                    <i class="ti ti-user-check ti-xs me-2"></i>Connecté
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--/ En-tête -->

        <!-- Navigation -->
        <div class="row">
            <div class="col-md-12">
                <div class="nav-align-top">
                    <ul class="nav nav-pills flex-column flex-sm-row mb-6 gap-2 gap-lg-0">
                        <li class="nav-item">
                            <a class="nav-link active" href="javascript:void(0);">
                                <i class="ti-sm ti ti-user-check me-1_5"></i> Profil
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="pages-profile-teams.html">
                                <i class="ti-sm ti ti-users me-1_5"></i> Équipes
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="pages-profile-projects.html">
                                <i class="ti-sm ti ti-layout-grid me-1_5"></i> Projets
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="pages-profile-connections.html">
                                <i class="ti-sm ti ti-link me-1_5"></i> Connexions
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <!--/ Navigation -->

        <!-- Contenu du profil utilisateur -->
        <div class="row">
            <div class="col-xl-4 col-lg-5 col-md-5">
                <!-- À propos de l'utilisateur -->
                <div class="card mb-6">
                    <div class="card-body">
                        <small class="card-text text-uppercase text-muted small">À propos</small>
                        <ul class="list-unstyled my-3 py-1">
                            <li class="d-flex align-items-center mb-4">
                                <i class="ti ti-user ti-lg"></i><span class="fw-medium mx-2">Nom complet :</span>
                                <span> {{$profileData->username}} {{$profileData->name}}</span>
                            </li>
                            <li class="d-flex align-items-center mb-4">
                                <i class="ti ti-check ti-lg"></i><span class="fw-medium mx-2">Statut :</span>
                                <span>Actif</span>
                            </li>
                            <li class="d-flex align-items-center mb-4">
                                <i class="ti ti-crown ti-lg"></i><span class="fw-medium mx-2">Rôle :</span>
                                <span>{{$profileData->role}}</span>
                            </li>

                        </ul>
                        <small class="card-text text-uppercase text-muted small">Contacts</small>
                        <ul class="list-unstyled my-3 py-1">
                            <li class="d-flex align-items-center mb-4">
                                <i class="ti ti-phone-call ti-lg"></i><span class="fw-medium mx-2">Téléphone :</span>
                                <span>{{$profileData->phone}}</span>
                            </li>

                            <li class="d-flex align-items-center mb-4">
                                <i class="ti ti-mail ti-lg"></i><span class="fw-medium mx-2">E-mail :</span>
                                <span>{{$profileData->email}}</span>
                            </li>
                        </ul>
                    </div>
                </div>
                <!--/ À propos de l'utilisateur -->
            </div>
            <div class="col-xl-8 col-lg-7 col-md-7">
                <!-- Chronologie des activités -->
                <div class="card card-action mb-6">
                    <div class="card-header align-items-center">
                        <h5 class="card-action-title mb-0">
                            <i class="ti ti-chart-bar ti-lg text-body me-4"></i>Chronologie des activités
                        </h5>
                    </div>
                    <div class="card-body pt-3">
                        <ul class="timeline mb-0">
                            <li class="timeline-item timeline-item-transparent">
                                <span class="timeline-point timeline-point-primary"></span>
                                <div class="timeline-event">
                                    <div class="timeline-header mb-3">
                                        <h6 class="mb-0">12 factures ont été payées</h6>
                                        <small class="text-muted">Il y a 12 minutes</small>
                                    </div>
                                    <p class="mb-2">Les factures ont été payées à l'entreprise</p>
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="badge bg-lighter rounded d-flex align-items-center">
                                            <img src="{{ asset('backend/assets/img/icons/misc/pdf.png') }}" alt="PDF" width="15" class="me-2" />
                                            <span class="h6 mb-0 text-body">factures.pdf</span>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            <li class="timeline-item timeline-item-transparent">
                                <span class="timeline-point timeline-point-success"></span>
                                <div class="timeline-event">
                                    <div class="timeline-header mb-3">
                                        <h6 class="mb-0">Réunion avec le client</h6>
                                        <small class="text-muted">Il y a 45 minutes</small>
                                    </div>
                                    <p class="mb-2">Réunion de projet avec John à 10h15</p>
                                    <div class="d-flex justify-content-between flex-wrap gap-2 mb-2">
                                        <div class="d-flex flex-wrap align-items-center mb-50">
                                            <div class="avatar avatar-sm me-3">
                                                <img src="{{ asset('backend/assets/img/avatars/1.png') }}" alt="Avatar" class="rounded-circle" />
                                            </div>
                                            <div>
                                                <p class="mb-0 small fw-medium">Lester McCarthy (Client)</p>
                                                <small>PDG de Pixinvent</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            <li class="timeline-item timeline-item-transparent">
                                <span class="timeline-point timeline-point-info"></span>
                                <div class="timeline-event">
                                    <div class="timeline-header mb-3">
                                        <h6 class="mb-0">Création d'un nouveau projet pour un client</h6>
                                        <small class="text-muted">Il y a 2 jours</small>
                                    </div>
                                    <p class="mb-2">6 membres de l'équipe sont affectés à ce projet</p>
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap border-top-0 p-0">
                                            <div class="d-flex flex-wrap align-items-center">
                                                <ul class="list-unstyled users-list d-flex align-items-center avatar-group m-0 me-2">
                                                    <li class="avatar pull-up" data-bs-toggle="tooltip" title="Vinnie Mostowy">
                                                        <img class="rounded-circle" src="{{ asset('backend/assets/img/avatars/1.png') }}" alt="Avatar" />
                                                    </li>
                                                    <li class="avatar pull-up" data-bs-toggle="tooltip" title="Allen Rieske">
                                                        <img class="rounded-circle" src="{{ asset('backend/assets/img/avatars/4.png') }}" alt="Avatar" />
                                                    </li>
                                                    <li class="avatar pull-up" data-bs-toggle="tooltip" title="Julee Rossignol">
                                                        <img class="rounded-circle" src="{{ asset('backend/assets/img/avatars/2.png') }}" alt="Avatar" />
                                                    </li>
                                                    <li class="avatar">
                                                        <span class="avatar-initial rounded-circle pull-up text-heading" data-bs-toggle="tooltip" title="3 de plus">+3</span>
                                                    </li>
                                                </ul>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
                <!--/ Chronologie des activités -->

                <div class="row">
                    <!-- Connexions -->
                    <div class="col-lg-12 col-xl-6">
                        <div class="card card-action mb-6">
                            <div class="card-header align-items-center">
                                <h5 class="card-action-title mb-0">Connexions</h5>
                                <div class="card-action-element">
                                    <div class="dropdown">
                                        <button type="button" class="btn btn-icon btn-text-secondary rounded-pill dropdown-toggle hide-arrow p-0 text-muted" data-bs-toggle="dropdown">
                                            <i class="ti ti-dots-vertical ti-md text-muted"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><a class="dropdown-item" href="javascript:void(0);">Partager les connexions</a></li>
                                            <li><a class="dropdown-item" href="javascript:void(0);">Suggérer des modifications</a></li>
                                            <li><hr class="dropdown-divider" /></li>
                                            <li><a class="dropdown-item" href="javascript:void(0);">Signaler un problème</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled mb-0">
                                    <li class="mb-4">
                                        <div class="d-flex align-items-center">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar me-2">
                                                    <img src="{{ asset('backend/assets/img/avatars/2.png') }}" alt="Avatar" class="rounded-circle" />
                                                </div>
                                                <div class="me-2">
                                                    <h6 class="mb-0">Cecilia Payne</h6>
                                                    <small>45 Connexions</small>
                                                </div>
                                            </div>
                                            <div class="ms-auto">
                                                <button class="btn btn-label-primary btn-icon">
                                                    <i class="ti ti-user-check ti-md"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </li>
                                    <!-- Ajoute d'autres connexions ici -->
                                </ul>
                            </div>
                        </div>
                    </div>
                    <!--/ Connexions -->

                    <!-- Équipes -->
                    <div class="col-lg-12 col-xl-6">
                        <div class="card card-action mb-6">
                            <div class="card-header align-items-center">
                                <h5 class="card-action-title mb-0">Équipes</h5>
                                <div class="card-action-element">
                                    <div class="dropdown">
                                        <button type="button" class="btn btn-icon btn-text-secondary dropdown-toggle hide-arrow p-0" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="ti ti-dots-vertical text-muted"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><a class="dropdown-item" href="javascript:void(0);">Partager les équipes</a></li>
                                            <li><a class="dropdown-item" href="javascript:void(0);">Suggérer des modifications</a></li>
                                            <li><hr class="dropdown-divider" /></li>
                                            <li><a class="dropdown-item" href="javascript:void(0);">Signaler un problème</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled mb-0">
                                    <li class="mb-4">
                                        <div class="d-flex align-items-center">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar me-2">
                                                    <img src="{{ asset('backend/assets/img/icons/brands/react-label.png') }}" alt="Avatar" class="rounded-circle" />
                                                </div>
                                                <div class="me-2">
                                                    <h6 class="mb-0">Développeurs React</h6>
                                                    <small>72 membres</small>
                                                </div>
                                            </div>
                                            <div class="ms-auto">
                                                <a href="javascript:void(0);"><span class="badge bg-label-danger">Développeur</span></a>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="mb-4">
                                        <div class="d-flex align-items-center">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar me-2">
                                                    <img src="{{ asset('backend/assets/img/icons/brands/support-label.png') }}" alt="Avatar" class="rounded-circle" />
                                                </div>
                                                <div class="me-2">
                                                    <h6 class="mb-0">Équipe de support</h6>
                                                    <small>122 membres</small>
                                                </div>
                                            </div>
                                            <div class="ms-auto">
                                                <a href="javascript:void(0);"><span class="badge bg-label-primary">Support</span></a>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="mb-4">
                                        <div class="d-flex align-items-center">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar me-2">
                                                    <img src="{{ asset('backend/assets/img/icons/brands/figma-label.png') }}" alt="Avatar" class="rounded-circle" />
                                                </div>
                                                <div class="me-2">
                                                    <h6 class="mb-0">Designers UI</h6>
                                                    <small>7 membres</small>
                                                </div>
                                            </div>
                                            <div class="ms-auto">
                                                <a href="javascript:void(0);"><span class="badge bg-label-info">Designer</span></a>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="mb-4">
                                        <div class="d-flex align-items-center">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar me-2">
                                                    <img src="{{ asset('backend/assets/img/icons/brands/vue-label.png') }}" alt="Avatar" class="rounded-circle" />
                                                </div>
                                                <div class="me-2">
                                                    <h6 class="mb-0">Développeurs Vue.js</h6>
                                                    <small>289 membres</small>
                                                </div>
                                            </div>
                                            <div class="ms-auto">
                                                <a href="javascript:void(0);"><span class="badge bg-label-danger">Développeur</span></a>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="mb-6">
                                        <div class="d-flex align-items-center">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar me-2">
                                                    <img src="{{ asset('backend/assets/img/icons/brands/twitter-label.png') }}" alt="Avatar" class="rounded-circle" />
                                                </div>
                                                <div class="me-2">
                                                    <h6 class="mb-0">Marketing digital</h6>
                                                    <small>24 membres</small>
                                                </div>
                                            </div>
                                            <div class="ms-auto">
                                                <a href="javascript:void(0);"><span class="badge bg-label-secondary">Marketing</span></a>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="text-center">
                                        <a href="javascript:void(0);">Voir toutes les équipes</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <!--/ Équipes -->

                </div>
            </div>

        <!--/ Contenu du profil utilisateur -->

    </div>
    <!-- / Contenu -->
</div>
<!-- Fin du contenu principal -->

@endsection
