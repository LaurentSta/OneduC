@extends('formateur.dashboard')

@section('formateur')
    @include('shared.module_presentation_content', [
        'presentationMode' => 'formateur',
    ])
@endsection
