@extends('admin.admin_dashboard')
@section('admin')
<div class="container">
    <h2>Assigner des Stagiaires au Groupe : {{ $groupe->nom }}</h2>

    <form action="{{ route('admin.groupes.stagiaires.store', $groupe->id) }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="stagiaires" class="form-label">Sélectionner les Stagiaires</label>
            <select name="stagiaires[]" id="stagiaires" class="form-control" multiple>
                @foreach($stagiaires as $stagiaire)
                    <option value="{{ $stagiaire->id }}" @if($groupe->stagiaires->contains($stagiaire->id)) selected @endif>
                        {{ $stagiaire->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Assigner</button>
    </form>
</div>
@endsection
