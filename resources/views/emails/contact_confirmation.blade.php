@component('mail::message')
# Merci, nous avons bien reçu votre message

Bonjour {{ trim(($payload['prenom'] ?? '').' '.$payload['nom']) }},

Nous accusons réception de votre message (profil **{{ $payload['type_utilisateur'] }}**, objet **{{ $payload['objet'] }}**).
Notre équipe vous répondra dès que possible à **{{ $payload['email'] }}**.

@component('mail::panel')
**Votre message :**
{{ $payload['message'] }}
@endcomponent

@component('mail::button', ['url' => url('/')])
Aller sur oneduc.fr
@endcomponent

Ceci est un accusé automatique.  
Si vous n’êtes pas à l’origine de cette demande, ignorez cet email.

— L’équipe Oneduc
@endcomponent
