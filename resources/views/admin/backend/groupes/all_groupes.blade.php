@extends('admin.admin_dashboard')
@section('admin')
<div class="container">
    <h2>Gestion des Groupes</h2>
    <a href="{{ route('admin.groupes.create') }}" class="btn btn-primary mb-3">Créer un groupe</a>

    @if ($groupes->isEmpty())
        <p>Aucun groupe trouvé.</p>
    @else
        <table class="table">
            <thead>
                <tr>
                    <th>Nom du Groupe</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($groupes as $groupe)
                <tr>
                    <td>{{ $groupe->nom }}</td>
                    <td>
                        <a href="{{ route('admin.groupes.edit', $groupe->id) }}" class="btn btn-warning">Modifier</a>
                        <form action="{{ route('admin.groupes.destroy', $groupe->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Supprimer</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
