@extends('frontend.master')
@section('home')


<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row g-6">
      <!-- Colonne gauche : Contenu vidéo -->
      <div class="col-lg-9">
        <div class="card">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center flex-wrap mb-6 gap-2">
              <div class="me-1">
                <h5 class="mb-0">{{ $lecture->lecture_title }}</h5>
                <p class="mb-0">Par : <span class="fw-medium text-heading">
                  {{ $lecture->section->module->formateur->name ?? 'Formateur' }}
                </span></p>
              </div>
              <div class="d-flex align-items-center">
                <span class="badge bg-label-primary">Module</span>
              </div>
            </div>

            <div class="card academy-content shadow-none border">
              <div class="p-2">
                <video class="w-100" controls>
                  <source src="{{ asset($lecture->url) }}" type="video/mp4" />
                </video>
              </div>

              <div class="card-body pt-4">
                <h5>Description</h5>
                <p>{!! $lecture->content !!}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Colonne droite : Progression -->
      <div class="col-lg-3">
        <div class="accordion stick-top accordion-custom-button" id="courseContent">
          @foreach ($module->sections as $index => $section)
            <div class="accordion-item {{ $index === 0 ? 'active mb-0' : '' }}">
              <div class="accordion-header" id="heading{{ $index }}">
                <button class="accordion-button {{ $index !== 0 ? 'collapsed' : '' }}"
                  data-bs-toggle="collapse"
                  data-bs-target="#chapter{{ $index }}"
                  aria-expanded="{{ $index === 0 ? 'true' : 'false' }}"
                  aria-controls="chapter{{ $index }}">
                  <span class="d-flex flex-column">
                    <span class="h5 mb-0">{{ $section->section_title }}</span>
                    <span class="text-body fw-normal">
                      {{ $section->lectures->count() }} leçon(s)
                    </span>
                  </span>
                </button>
              </div>
              <div id="chapter{{ $index }}" class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}" data-bs-parent="#courseContent">
                <div class="accordion-body py-4">
                  @foreach ($section->lectures as $lec)
                    <div class="form-check d-flex align-items-center gap-1 mb-3">
                      <input
                        class="form-check-input"
                        type="checkbox"
                        disabled
                        {{ $lec->id == $lecture->id ? 'checked' : '' }}
                      />
                      <label class="form-check-label ms-4">
                        <a href="{{ route('lecture.show', $lec->id) }}" class="text-decoration-none">
                          <span class="mb-0 h6">{{ $loop->iteration }}. {{ $lec->lecture_title }}</span>
                        </a>
                      </label>
                    </div>
                  @endforeach
                </div>
              </div>
            </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>


@endsection
