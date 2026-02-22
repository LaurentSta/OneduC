@extends('frontend.master')

@section('home')
    @include('shared.module_presentation_content', [
        'presentationMode' => 'public',
    ])
@endsection
