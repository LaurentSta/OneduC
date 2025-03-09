<div class="container">
    <h2>Mes Groupes</h2>
    <a href="{{ route('instructor.groupes.create') }}" class="btn btn-primary mb-3">Créer un groupe</a>

    <table class="table">
        <thead>
            <tr>
                <th>Nom du Groupe</th>
                <th>Stagiaires</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($groupes as $groupe)
            <tr>
                <td>{{ $groupe->nom }}</td>
                <td>
                    @foreach($groupe->stagiaires as $stagiaire)
                        {{ $stagiaire->name }}<br>
                    @endforeach
                </td>
                <td>
                    <a href="{{ route('instructor.groupes.edit', $groupe->id) }}" class="btn btn-warning">Modifier</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
