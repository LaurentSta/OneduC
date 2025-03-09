@extends('admin.admin_dashboard')
@section('admin')
<div class="container">
    <h2>Créer un Groupe</h2>

    <form action="{{ route('instructor.groupes.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="nom" class="form-label">Nom du Groupe</label>
            <input type="text" name="nom" id="nom" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary">Créer</button>
    </form>
</div>
@endsection
