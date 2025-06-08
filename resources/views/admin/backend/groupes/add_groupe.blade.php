@extends('admin.admin_dashboard')
@section('admin')

<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card mb-6">
            <h5 class="card-header">Ajouter un nouveau module (formation)</h5>

            <form action="{{ route('store.module') }}" method="POST" enctype="multipart/form-data" class="row g-3">
                @csrf

                <div class="form-group col-md-6">
                    <label class="form-label">Nom du module</label>
                    <input type="text" name="module_name" class="form-control" required>
                </div>

                <div class="form-group col-md-6">
                    <label class="form-label">Titre du module</label>
                    <input type="text" name="module_title" class="form-control" required>
                </div>

                <div class="form-group col-md-6">
                    <label class="form-label">Catégorie</label>
                    <select name="category_id" class="form-select" required>
                        <option disabled selected>Choisissez une catégorie</option>
                        @foreach ($modules as $cat)
                            <option value="{{ $cat->category_id }}">{{ $cat->module_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group col-md-6">
                    <label class="form-label">Sous-catégorie</label>
                    <select name="subcategory_id" class="form-select">
                        <option disabled selected>Choisissez une sous-catégorie</option>
                    </select>
                </div>

                <div class="form-group col-md-6">
                    <label class="form-label">Image du module</label>
                    <input type="file" name="module_image" id="module_image" class="form-control" accept="image/*">
                </div>

                <div class="col-md-6">
                    <img id="showImage" src="{{ url('upload/no_image.jpg') }}" class="rounded p-1 bg-primary" width="100">
                </div>

                <div class="form-group col-md-6">
                    <label class="form-label">Vidéo d'introduction</label>
                    <input type="file" name="video" class="form-control" accept="video/mp4,video/webm">
                </div>

                <div class="form-group col-md-6">
                    <label class="form-label">Label (prix ?)</label>
                    <input type="text" name="label" class="form-control">
                </div>

                <div class="form-group col-md-4">
                    <label class="form-label">Durée</label>
                    <input type="text" name="duree" class="form-control" placeholder="Ex : 2h, 3 jours">
                </div>

                <div class="form-group col-md-4">
                    <label class="form-label">Ressources</label>
                    <input type="text" name="resources" class="form-control">
                </div>

                <div class="form-group col-md-4">
                    <label class="form-label">Certificat</label>
                    <select name="certificat" class="form-select" required>
                        <option disabled selected>Certificat ?</option>
                        <option value="Yes">Oui</option>
                        <option value="No">Non</option>
                    </select>
                </div>

                <div class="form-group col-md-12">
                    <label class="form-label">Prérequis</label>
                    <textarea name="prerequi" class="form-control" rows="3"></textarea>
                </div>

                <!-- Options -->
                <div class="form-group col-md-4">
                    <label class="form-check-label">
                        <input class="form-check-input" type="checkbox" name="bestseller" value="1"> Bestseller
                    </label>
                </div>

                <div class="form-group col-md-4">
                    <label class="form-check-label">
                        <input class="form-check-input" type="checkbox" name="vedette" value="1"> Vedette
                    </label>
                </div>

                <div class="form-group col-md-4">
                    <label class="form-check-label">
                        <input class="form-check-input" type="checkbox" name="surevalue" value="1"> Surevalue
                    </label>
                </div>

                <!-- Status -->
                <div class="form-group col-md-4">
                    <label class="form-check-label">
                        <input class="form-check-input" type="checkbox" name="status" value="1"> Actif ?
                    </label>
                </div>

                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary">Enregistrer le module</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Image preview -->
<script>
    document.getElementById('module_image').addEventListener('change', function(e) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('showImage').src = e.target.result;
        }
        reader.readAsDataURL(this.files[0]);
    });
</script>

@endsection
