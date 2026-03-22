@extends('admin.admin_dashboard')
@section('admin')

@include('admin.backend.observateur.partials.form', [
    'title' => 'Modifier un observateur',
    'subtitle' => 'Ajustez les informations du compte et la liste des groupes observés.',
    'action' => route('admin.observateurs.update', $observateur),
    'method' => 'PUT',
])

@endsection
