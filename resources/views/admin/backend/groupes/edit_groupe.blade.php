@extends('admin.admin_dashboard')
@section('admin')

<div class="container">
    <h2>Modifier le Groupe : {{ $groupe->nom }}</h2>

    <form action="{{ route('admin.groupes.update', $groupe->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="nom" class="form-label">Nom du Groupe</label>
            <input type="text" name="nom" id="nom" class="form-control" value="{{ $groupe->nom }}" required>
        </div>

        <div class="mb-3">
            <label for="stagiaires" class="form-label">Stagiaires</label>
            <select name="stagiaires[]" id="stagiaires" class="form-control" multiple>
                @foreach($stagiaires as $stagiaire)
                    <option value="{{ $stagiaire->id }}" @if($groupe->stagiaires->contains($stagiaire->id)) selected @endif>
                        {{ $stagiaire->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="btn btn-success">Mettre à jour</button>
    </form>
</div>


@endsection
