@component('mail::message')
# Nouveau formateur

- Nom : {{ $data['prenom'] }} {{ $data['nom'] }}
- Email : {{ $data['email'] }}
- Téléphone : {{ $data['phone'] ?: '—' }}
- Société : {{ $data['societe'] ?: '—' }}

@component('mail::button', ['url' => url('/admin/formateurs')])
Voir les formateurs
@endcomponent
@endcomponent
