@extends('admin.admin_dashboard')
@section('admin')

@include('admin.backend.observateur.partials.form', [
    'title' => 'Créer un observateur',
    'subtitle' => 'Créez un compte observateur puis rattachez-le aux groupes à suivre.',
    'action' => route('admin.observateurs.store'),
    'method' => 'POST',
])

@endsection
