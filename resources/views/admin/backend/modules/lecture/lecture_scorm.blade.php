@extends('admin.admin_dashboard')

@section('admin')
<div class="page-content">
    <div class="container-fluid">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-xl font-bold text-bleuone">Prévisualisation : {{ $lecture->lecture_title }}</h2>
                <p class="text-xs text-gray-500">Chemin : {{ $lecture->scorm_path }}</p>
            </div>
            <a href="{{ route('admin.lectures.edit', $lecture->id) }}" class="btn btn-secondary btn-sm">Retour</a>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden" style="height: 80vh;">
            <iframe src="{{ asset($lecture->scorm_path) }}" class="w-full h-full border-0" allowfullscreen></iframe>
        </div>
    </div>
</div>
@endsection