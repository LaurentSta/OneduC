@extends('stagiaire.master')

@section('content')
    @include('shared.module_presentation_content', [
        'presentationMode' => 'stagiaire',
    ])
@endsection
