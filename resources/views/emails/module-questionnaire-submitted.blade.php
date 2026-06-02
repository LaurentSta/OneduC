<h2>Nouveau questionnaire d’évaluation du module {{ $questionnaire['module']['number'] }}</h2>

<p><strong>Module :</strong> {{ $questionnaire['module']['title'] }}</p>
<p><strong>Date d’envoi :</strong> {{ $questionnaire['submitted_at'] }}</p>
<p><strong>Formatrice ou formateur :</strong> {{ $questionnaire['trainer']['full_name'] }}</p>
<p><strong>Identifiant :</strong> {{ $questionnaire['trainer']['username'] ?: 'Non renseigné' }}</p>
<p><strong>Adresse électronique :</strong> {{ $questionnaire['trainer']['email'] }}</p>

<hr>

@foreach (collect($questionnaire['closed_items'])->groupBy('dimension_label') as $dimensionLabel => $items)
    <h3>{{ $dimensionLabel }}</h3>

    <table cellpadding="6" cellspacing="0" border="1" style="border-collapse: collapse; width: 100%;">
        <thead>
            <tr>
                <th align="left">N°</th>
                <th align="left">Affirmation</th>
                <th align="left">Réponse</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($items as $item)
                <tr>
                    <td>{{ $item['item_number'] }}</td>
                    <td>
                        {{ $item['label'] }}
                        @if ($item['reversed'])
                            * <em>(item inversé à recoder lors de l’analyse)</em>
                        @endif
                    </td>
                    <td>{{ $item['answer_label'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endforeach

<h3>Questions ouvertes</h3>

@foreach ($questionnaire['open_questions'] as $question)
    <p>
        <strong>{{ $question['item_number'] }}. {{ $question['label'] }}</strong><br>
        <span style="white-space: pre-line">{{ $question['text'] ?: 'Aucune réponse.' }}</span>
    </p>
@endforeach
