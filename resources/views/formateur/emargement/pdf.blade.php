<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Émargement {{ $seance->reference }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #172033; }
        h1 { font-size: 16px; color: #004461; margin-bottom: 4px; }
        .meta { margin-bottom: 16px; color: #555; }
        .meta span { display: inline-block; margin-right: 18px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; vertical-align: middle; }
        th { background-color: #004461; color: #fff; font-size: 11px; text-transform: uppercase; }
        .signature-img { height: 35px; }
        .statut-present { color: #01c69c; font-weight: bold; }
        .statut-absent { color: #c0392b; font-weight: bold; }
        .statut-en_attente { color: #888; }
        .footer { margin-top: 24px; font-size: 11px; color: #555; }
    </style>
</head>
<body>
    <h1>Feuille d'émargement — {{ $group->name }}</h1>
    <div class="meta">
        <span><strong>Référence :</strong> {{ $seance->reference }}</span>
        <span><strong>Date :</strong> {{ $seance->date->format('d/m/Y') }}</span>
        <span><strong>Créneau :</strong> {{ $creneauLabel }}</span>
        @if ($seance->titre)
            <span><strong>Intitulé :</strong> {{ $seance->titre }}</span>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>Stagiaire</th>
                <th>Statut</th>
                <th>Signature</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($lignes as $ligne)
                <tr>
                    <td>{{ $ligne['nom'] }}</td>
                    <td class="statut-{{ $ligne['statut'] }}">
                        @if ($ligne['statut'] === 'present') Présent
                        @elseif ($ligne['statut'] === 'absent') Absent @if ($ligne['motif_absence']) ({{ $ligne['motif_absence'] }}) @endif
                        @else En attente
                        @endif
                    </td>
                    <td>
                        @if ($ligne['signature'])
                            <img class="signature-img" src="{{ $ligne['signature'] }}" alt="Signature">
                        @else
                            —
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p class="footer">
        Formateur : {{ optional($seance->formateur)->prenom }} {{ optional($seance->formateur)->name }}
        — Exporté le {{ now()->format('d/m/Y à H:i') }}
    </p>
</body>
</html>
