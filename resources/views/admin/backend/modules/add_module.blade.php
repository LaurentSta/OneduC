@extends('admin.admin_dashboard')
@section('admin')

<div class="w-full px-6 lg:px-8">
  <div class="form-oneduc-card p-6 my-6 w-full">

    {{-- En-tête minimal --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between border-b border-gray-100 pb-4 mb-6">
      <div>
        <h1 class="form-oneduc-title">Ajouter un module</h1>
        <p class="form-oneduc-subtitle">Créer un module, définir sa catégorisation, ses médias et ses options.</p>
      </div>

      <div class="flex flex-wrap gap-2">
        <a href="{{ route('admin.modules') }}"
           class="btn-oneduc-sm btn-oneduc-sm--outline">
          <i class="ti ti-arrow-left"></i>
          Retour
        </a>

        <button type="submit" form="formAddModule"
                class="btn-oneduc-sm btn-oneduc-sm--primary">
          <i class="ti ti-check"></i>
          Enregistrer
        </button>
      </div>
    </div>

    {{-- Erreurs --}}
    @if ($errors->any())
      <div class="mb-6 p-4 rounded-lg border border-red-200 bg-red-50 text-red-800 text-sm">
        <ul class="list-disc list-inside">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form id="formAddModule" method="POST" action="{{ route('admin.modules.store') }}" enctype="multipart/form-data" class="space-y-6">
      @csrf

      {{-- 1. Informations générales --}}
      <section class="form-oneduc-section">
        <h2 class="form-oneduc-section-title">1. Informations générales</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="form-oneduc-label">Nom technique <span class="text-red-600">*</span></label>
            <input name="module_name" value="{{ old('module_name') }}" required
                   class="form-oneduc-input" placeholder="Ex : word_debutant_01">
          </div>

          <div>
            <label class="form-oneduc-label">Titre affiché <span class="text-red-600">*</span></label>
            <input name="module_title" value="{{ old('module_title') }}" required
                   class="form-oneduc-input" placeholder="Ex : Word – Débuter">
          </div>

          <div>
            <label class="form-oneduc-label">Slug</label>
            <input name="module_name_slug" value="{{ old('module_name_slug') }}"
                   class="form-oneduc-input" placeholder="Ex : word-debuter">
          </div>

          <div>
            <label class="form-oneduc-label">Téléverser une vidéo locale</label>
            <input id="module_video_file" type="file" name="module_video_file"
                   accept="video/mp4,video/x-m4v,video/quicktime,video/x-msvideo,video/webm"
                   class="form-oneduc-file">
            <div class="form-oneduc-help">Formats acceptés : MP4, M4V, MOV, AVI, WEBM. Le fichier sera enregistré dans `public/modules/videos/modules/module_{id}`.</div>
            <video id="moduleVideoPreview" class="mt-3 hidden w-full max-w-md rounded-lg border border-gray-200 bg-black" controls preload="metadata"></video>
          </div>

          <div>
            <label class="form-oneduc-label">Label</label>
            <input name="label" value="{{ old('label') }}"
                   class="form-oneduc-input" placeholder="Ex : Gratuit / Premium">
          </div>

          <div>
            <label class="form-oneduc-label">Durée</label>
            <input name="duree" value="{{ old('duree') }}"
                   class="form-oneduc-input" placeholder="Ex : 2h, 3 jours">
          </div>

          <div>
            <label class="form-oneduc-label">Temps estimé par question (secondes)</label>
            <input type="number" min="1" max="600" name="estimated_question_seconds"
                   value="{{ old('estimated_question_seconds', 30) }}"
                   class="form-oneduc-input" placeholder="30">
            <div class="form-oneduc-help">Cette valeur est utilisée pour calculer la durée estimée du module en fonction des questions.</div>
          </div>
        </div>
      </section>

      {{-- 2. Catégorisation --}}
      <section class="form-oneduc-section form-oneduc-section--alt">
        <h2 class="form-oneduc-section-title">2. Catégorisation</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="form-oneduc-label">Catégorie <span class="text-red-600">*</span></label>
            <select name="category_id" required class="form-oneduc-select">
              <option value="">-- Choisir une catégorie --</option>
              @foreach ($categories as $cat)
                <option value="{{ $cat->id }}" @selected(old('category_id') == $cat->id)>{{ $cat->category_name }}</option>
              @endforeach
            </select>
          </div>

          <div>
            <label class="form-oneduc-label">Sous-catégorie <span class="text-red-600">*</span></label>
            <select name="subcategory_id" required class="form-oneduc-select">
              <option value="">-- Choisir une sous-catégorie --</option>
              @foreach ($subcategories as $sub)
                <option value="{{ $sub->id }}" @selected(old('subcategory_id') == $sub->id)>{{ $sub->subcategory_name }}</option>
              @endforeach
            </select>
            <div class="form-oneduc-help">Rendu obligatoire pour éviter l’erreur “champ vide”.</div>
          </div>

          <div>
            <label class="form-oneduc-label">Évaluation (optionnelle)</label>
            <select name="evaluation_id" class="form-oneduc-select">
              <option value="">-- Aucune --</option>
              @foreach ($evaluations as $eval)
                <option value="{{ $eval->id }}" @selected(old('evaluation_id') == $eval->id)>{{ $eval->titre }}</option>
              @endforeach
            </select>
          </div>

          <div>
            <label class="form-oneduc-label">Formateur <span class="text-red-600">*</span></label>
            <select name="formateur_id" required class="form-oneduc-select">
              <option value="">-- Choisir un formateur --</option>
              @foreach ($formateurs as $f)
                <option value="{{ $f->id }}" @selected(old('formateur_id') == $f->id)>{{ $f->name }}</option>
              @endforeach
            </select>
          </div>

          <div>
            <label class="form-oneduc-label">Certificat <span class="text-red-600">*</span></label>
            <select name="certificat" required class="form-oneduc-select">
              <option value="">-- Avec certificat ? --</option>
              <option value="1" @selected(old('certificat') === "1")>Oui</option>
              <option value="0" @selected(old('certificat') === "0")>Non</option>
            </select>
          </div>
        </div>
      </section>

      {{-- 3. Ressources & images --}}
      <section class="form-oneduc-section">
        <h2 class="form-oneduc-section-title">3. Ressources & images</h2>

        <div class="grid grid-cols-1 gap-6">
          <div>
            <label class="form-oneduc-label">Ressources (URL ou chemin)</label>
            <input name="resources" value="{{ old('resources') }}"
                   class="form-oneduc-input" placeholder="Ex : https://... ou storage/...">
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
          <div>
            <label class="form-oneduc-label">Image d’en-tête</label>
            <input id="header_image" type="file" name="header_image" accept="image/*,.svg,.svg+xml"
                   class="form-oneduc-file">
            <div class="mt-3">
              <img id="showHeaderImage" src="{{ asset('upload/module_images/NoImage.png') }}"
                   alt="Aperçu image d’en-tête" class="w-40 h-40 object-cover rounded-lg border border-gray-200">
            </div>
          </div>

          <div>
            <label class="form-oneduc-label">Image principale</label>
            <input id="module_image" type="file" name="module_image" accept="image/*,.svg,.svg+xml"
                   class="form-oneduc-file">
            <div class="mt-3">
              <img id="showImage" src="{{ asset('upload/module_images/NoImage.png') }}"
                   alt="Aperçu image principale" class="w-40 h-40 object-cover rounded-lg border border-gray-200">
            </div>
          </div>
        </div>
      </section>

      {{-- 4. Contenu pédagogique --}}
      <section class="form-oneduc-section">
        <h2 class="form-oneduc-section-title">4. Contenu pédagogique</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="form-oneduc-label">Prérequis</label>
            <textarea name="prerequi" rows="5" class="form-oneduc-textarea"
                      placeholder="Ex : savoir utiliser la souris...">{{ old('prerequi') }}</textarea>
          </div>

          <div>
            <label class="form-oneduc-label">Description</label>
            <textarea name="description" rows="5" class="form-oneduc-textarea"
                      placeholder="Décrire le module...">{{ old('description') }}</textarea>
          </div>
        </div>
      </section>

      {{-- 5. Options --}}
      <section class="form-oneduc-section form-oneduc-section--alt">
        <h2 class="form-oneduc-section-title">5. Options</h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-sm">
          <label class="inline-flex items-center gap-2">
            <input type="checkbox" name="bestseller" value="1" @checked(old('bestseller'))>
            Bestseller
          </label>

          <label class="inline-flex items-center gap-2">
            <input type="checkbox" name="vedette" value="1" @checked(old('vedette'))>
            Vedette
          </label>

          <label class="inline-flex items-center gap-2">
            <input type="checkbox" name="surevalue" value="1" @checked(old('surevalue'))>
            Valeur ajoutée
          </label>

          <label class="inline-flex items-center gap-2">
            <input type="checkbox" name="status" value="1" @checked(old('status'))>
            Actif
          </label>
        </div>
      </section>

    </form>
  </div>
</div>

<script>
  // Prévisualisation images
  function bindPreview(inputId, imgId) {
    const input = document.getElementById(inputId);
    const img = document.getElementById(imgId);
    if (!input || !img) return;

    input.addEventListener('change', function() {
      if (!this.files || !this.files[0]) return;
      const reader = new FileReader();
      reader.onload = (e) => img.src = e.target.result;
      reader.readAsDataURL(this.files[0]);
    });
  }

  function bindVideoPreview(inputId, videoId) {
    const input = document.getElementById(inputId);
    const video = document.getElementById(videoId);
    if (!input || !video) return;

    input.addEventListener('change', function() {
      if (!this.files || !this.files[0]) {
        video.pause();
        video.removeAttribute('src');
        video.load();
        video.classList.add('hidden');
        return;
      }

      const objectUrl = URL.createObjectURL(this.files[0]);
      video.src = objectUrl;
      video.classList.remove('hidden');
      video.load();
    });
  }

  document.addEventListener('DOMContentLoaded', () => {
    bindPreview('header_image', 'showHeaderImage');
    bindPreview('module_image', 'showImage');
    bindVideoPreview('module_video_file', 'moduleVideoPreview');
  });
</script>

@endsection
