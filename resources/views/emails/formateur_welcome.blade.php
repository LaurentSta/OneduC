@component('mail::message')
# Félicitations, votre compte formateur est créé

Bonjour {{ $data['prenom'] }} {{ $data['nom'] }},  
Votre inscription formateur est acceptée. Vous pouvez vous connecter et démarrer.

@component('mail::button', ['url' => url('/connexion')])
Se connecter
@endcomponent

— L’équipe Onéduc
@endcomponent
