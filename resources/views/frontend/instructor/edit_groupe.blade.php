@extends('admin.admin_dashboard')
@section('admin')

<div class="container">
    <h2>Modifier le Groupe : {{ $groupe->nom }}</h2>

    <form action="{{ route('instructor.groupes.update', $groupe->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="nom" class="form-label">Nom du Groupe</label>
            <input type="text" name="nom" id="nom" class="form-control" value="{{ $groupe->nom }}" required>
        </div>

        <button type="submit" class="btn btn-success">Mettre à jour</button>
    </form>
</div>

@endsection
