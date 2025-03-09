@extends('admin.admin_dashboard')
@section('admin')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>

<!-- Content wrapper -->
<div class="content-wrapper">
    <!-- Content -->

    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card mb-6">
            <h5 class="card-header">Multi Column with Form Separator</h5>
            <form class="card-body" method="POST" action="{{ route('store.category') }}" enctype="multipart/form-data">
                @csrf
                <h6>1. Détails de la Catégorie</h6>
                <div class="row g-3">
                    <!-- Nom de la catégorie -->
                    <div class="col-md-4">
                        <label class="form-label" for="category_name">Nom de la Catégorie</label>
                        <input type="text" name="category_name" id="category_name" class="form-control" placeholder="Ex : Technologie" required>
                    </div>

                    <!-- Image de prévisualisation -->
                    <div class="col-md-8">
                        <img id="preview-image" src="{{ asset('upload/category_images/NoImage.png') }}" alt="Aperçu" class="d-block h-auto ms-0 ms-sm-6 rounded user-profile-img" style="max-width: 150px; max-height: 150px; object-fit: cover;">
                    </div>

                    <!-- Champ d'upload d'image -->
                    <div class="col-md-8">
                        <label class="form-label" for="category-image">Image</label>
                        <input type="file" class="form-control" name="category_image" id="category-image" accept="image/*" required> <!-- ✅ Corrigé -->
                    </div>
                </div>

                <div class="pt-3">
                    <button type="submit" class="btn btn-primary me-4 waves-effect waves-light">Sauvegarder</button>
                    <button type="reset" class="btn btn-label-secondary waves-effect">Annuler</button>
                </div>
            </form>

            <!-- Script pour l'aperçu de l'image -->
            <script>
            document.getElementById('category-image').addEventListener('change', function(event) {
                let reader = new FileReader();
                reader.onload = function() {
                    document.getElementById('preview-image').src = reader.result;
                }
                reader.readAsDataURL(event.target.files[0]);
            });
            </script>



          </div>




    <div class="content-backdrop fade"></div>
  </div>
<!-- jQuery (nécessaire pour Toastr) -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>

<!-- Toastr CSS et JS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

@if(session('success'))
    <script>
        $(document).ready(function() {
            toastr.success('{{ session('success') }}', 'Succès', {
                "closeButton": true,
                "progressBar": true,
                "positionClass": "toast-top-right",
                "timeOut": "5000"
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
                "timeOut": "5000"
            });
        });
    </script>
@endif

@endsection
