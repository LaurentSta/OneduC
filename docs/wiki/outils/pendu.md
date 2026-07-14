# Jeu du pendu

*Public : formateurs ; partie technique en fin de page pour les développeurs.*

**Statut au 14 juillet 2026** : développé côté code mais pas encore activé en production — voir le statut général sur la page [07 — Outils d'animation](../07-outils-animation.md).

Le formateur choisit un mot ou une expression, les stagiaires du groupe proposent collectivement des lettres, et la partie évolue pour tout le monde toutes les trois secondes.

## En bref

| Modalité | Valeur |
|---|---|
| Rythme | Synchrone |
| Lieu | Présentiel ou distanciel |
| Participation | Collective — un seul mot deviné par tout le groupe ensemble |
| Compte requis | Oui, appartenance au groupe |
| Pilotage | Le formateur et ses co-formateurs peuvent tous deux piloter la session |
| Activable/désactivable | `config('outils.pendu.enabled')` / `OUTILS_PENDU_ENABLED` (`true` par défaut) |

## Comment ça marche

1. Le formateur (ou un co-formateur du groupe) crée une partie depuis `/formateur/pendu` avec un mot ou une expression et un nombre d'essais autorisés.
2. Les stagiaires rejoignent via `/oneduc/pendu/{code}` et proposent des lettres (`submit`, throttle 60/min) ; la partie se met à jour pour tous toutes les 3 secondes environ.
3. La session est verrouillée pendant l'enregistrement d'une lettre pour éviter que deux propositions concurrentes ne se marchent dessus.
4. La partie se termine en victoire (toutes les lettres trouvées) ou défaite (nombre d'erreurs atteint le maximum autorisé).

## Dans quel contexte l'utiliser

- Réviser du vocabulaire technique de façon ludique et collective, en fin de séquence ou en pause active.
- Fonctionne aussi bien en présentiel (autour d'un écran commun) qu'en distanciel, puisque c'est le groupe entier — et non un seul stagiaire — qui fait avancer la partie ensemble.

## Partie technique

C'est un **domaine autonome**, encapsulé dans `app/Domains/Outils/Pendu/` : vues namespacées, migrations, routes et fournisseur de service (`PenduServiceProvider`) dédiés, sans dépendre du reste de `routes/formateur.php`. Le dépôt de données utilise Query Builder et des transactions SQL, sans modèle Eloquent (`Support/DepotPendu.php`, logique de jeu dans `Support/JeuPendu.php`).

**Routes formateur** (préfixe `/formateur/pendu`, nom `formateur.pendu.`) : `index`, `store`, `{sessionId}` (show), `{sessionId}/toggle`, `{sessionId}/state`, `destroy`.

**Routes de participation** (préfixe `/oneduc/pendu`, sous middleware `auth`) : `home`, `resolveCode`, `join/{code}`, `submit` (lettre proposée, throttle 60/min), `data` (throttle 120/min).

**Tables** : `hangman_sessions`, `hangman_guesses`.

**Désactivation** : `OUTILS_PENDU_ENABLED=false` retire routes, vues de dashboard et requêtes liées, sans toucher aux données existantes (réactivable plus tard).

---

[Retour au sommaire des outils](../07-outils-animation.md)
