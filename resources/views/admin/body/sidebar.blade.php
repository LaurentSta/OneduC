<!-- Menu -->

<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo">
      <a href="/" class="app-brand-link">
        <span class="app-brand-logo demo">
          <img src="{{ asset('backend/assets/img/logos/LOGOOneducSVG.svg')}}" alt="Logo Oneduc">
        </span>
        <span class="app-brand-text demo menu-text fw-bold">Oneduc.fr</span>
      </a>

      <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
        <i class="ti menu-toggle-icon d-none d-xl-block align-middle"></i>
        <i class="ti ti-x d-block d-xl-none ti-md align-middle"></i>
      </a>
    </div>

    <div class="menu-inner-shadow"></div>


    <ul class="menu-inner py-1">
        <li class="menu-item active">
            <a href="{{route('admin.dashboard')}}" class="menu-link">
            <i class="menu-icon tf-icons ti ti-home"></i>
            <div >ADMINISTRATEUR</div>
            </a>
        </li>
      <!-- Academy menu start -->
        <li class="menu-item">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
            <i class="menu-icon tf-icons ti ti-folder"></i>
            <div data-i18n="Academy">Catégories</div>
            </a>

            <ul class="menu-sub">
                <li class="menu-item ">
                    <a href="{{route('all.category')}}" class="menu-link">
                    <div data-i18n="Dashboard">Toutes les catégories</div>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="{{route('add.category')}}" class="menu-link">
                    <div data-i18n="My Course">Ajoutez une catégorie</div>
                    </a>
                </li>
            </ul>
            <li class="menu-item">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ti ti-folder"></i>
                <div data-i18n="Academy">Formateurs</div>
                </a>

                <ul class="menu-sub">
                    <li class="menu-item ">
                        <a href="{{route('all.instructor')}}" class="menu-link">
                        <div data-i18n="Dashboard">Tous les formateurs</div>
                        </a>
                    </li>
                </ul>

      <!-- Misc -->
      <li class="menu-header small">
        <span class="menu-header-text" data-i18n="Misc">Misc</span>
      </li>
      <li class="menu-item">
        <a href="https://pixinvent.ticksy.com/" target="_blank" class="menu-link">
          <i class="menu-icon tf-icons ti ti-lifebuoy"></i>
          <div data-i18n="Support">Support</div>
        </a>
      </li>
      <li class="menu-item">
        <a
          href="https://demos.pixinvent.com/vuexy-html-admin-template/documentation/"
          target="_blank"
          class="menu-link">
          <i class="menu-icon tf-icons ti ti-file-description"></i>
          <div data-i18n="Documentation">Documentation</div>
        </a>
      </li>
    </ul>
  </aside>
  <!-- / Menu -->
