@component('mail::message')
# Bienvenue sur Onéduc

Bonjour {{ trim(($user->prenom ?? '') . ' ' . ($user->name ?? '')) ?: 'à vous' }},

Vous avez été ajouté au groupe **{{ $group->name }}**.

Votre identifiant de connexion est : **{{ $user->code_acces }}**

@component('mail::button', ['url' => $loginUrl])
Se connecter
@endcomponent

Vous pouvez utiliser le lien ci-dessus pour accéder à votre espace stagiaire.

— L’équipe Onéduc
@endcomponent

