@extends('frontend.master')

@section('title', $module->module_title . ' - Onéduc')
@section('description', \Illuminate\Support\Str::limit(strip_tags((string) $module->description) ?: 'Découvrez ce module de formation sur Onéduc, la plateforme d\'inclusion numérique.', 155))
@section('canonical', route('frontend.modules.show', ['category' => $module->category_id, 'module' => $module->id]))

@php
    $courseSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'Course',
        'name' => $module->module_title,
        'description' => \Illuminate\Support\Str::limit(strip_tags((string) $module->description) ?: $module->module_title, 300),
        'provider' => [
            '@type' => 'EducationalOrganization',
            'name' => 'Onéduc',
            'sameAs' => route('index'),
        ],
    ];
@endphp
@push('structured-data')
<script type="application/ld+json">{!! json_encode($courseSchema, JSON_UNESCAPED_UNICODE) !!}</script>
@endpush

@section('home')
    @include('shared.module_presentation_content', [
        'presentationMode' => 'public',
    ])
@endsection
