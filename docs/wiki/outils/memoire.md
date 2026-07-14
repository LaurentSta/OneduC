# Jeu de mémoire

*Public : formateurs ; partie technique en fin de page pour les développeurs.*

**Statut au 14 juillet 2026** : développé côté code mais pas encore activé en production — voir le statut général sur la page [07 — Outils d'animation](../07-outils-animation.md).

Le formateur prépare de trois à dix paires (terme-définition, image-concept...). Chaque stagiaire retourne les cartes de son côté, et son nombre de coups, ses erreurs et sa durée alimentent un classement en direct.

## En bref

| Modalité | Valeur |
|---|---|
| Rythme | Asynchrone — chaque stagiaire joue à son rythme, le classement se construit au fil des parties |
| Lieu | Présentiel ou distanciel |
| Participation | Individuelle (une tentative par stagiaire), lecture collective du classement |
| Compte requis | Oui, appartenance au groupe |
| Activable/désactivable | `config('outils.memoire.enabled')` / `OUTILS_MEMOIRE_ENABLED` (`true` par défaut) |

## Comment ça marche

1. Le formateur prépare une session avec 3 à 10 paires depuis `/formateur/memoire`.
2. Les stagiaires rejoignent via `/oneduc/memoire/{code}` et retournent les cartes (mélangées côté serveur à chaque partie) jusqu'à reconstituer toutes les paires.
3. Une seule tentative est acceptée par stagiaire.
4. Le nombre d'erreurs est **recalculé côté serveur** à partir du nombre de paires et du nombre de coups (et non à partir d'une valeur envoyée par le navigateur), pour fiabiliser le classement.

## Dans quel contexte l'utiliser

- Faire mémoriser des associations (vocabulaire/traduction, sigle/signification, image/concept) sous une forme ludique et auto-corrective.
- Comme chaque stagiaire joue de façon autonome et indépendante des autres, l'outil se prête bien à un usage asynchrone : chacun peut y jouer quand il le souhaite, sans que le formateur ait besoin d'animer une session en direct.

## Partie technique

C'est un **domaine autonome**, encapsulé dans `app/Domains/Outils/Memoire/` : vues namespacées, migrations, routes et fournisseur de service (`MemoireServiceProvider`) dédiés. Le dépôt de données utilise Query Builder sans modèle Eloquent (`Support/DepotMemoire.php`), la logique de jeu est dans `Support/JeuMemoire.php` (`construireJeu` mélange les cartes, `calculerErreurs` recalcule les erreurs serveur).

**Routes formateur** (préfixe `/formateur/memoire`, nom `formateur.memoire.`) : `index`, `store`, `{sessionId}` (show), `{sessionId}/toggle`, `{sessionId}/state`, `destroy`.

**Routes de participation** (préfixe `/oneduc/memoire`, sous middleware `auth`) : `home`, `resolveCode`, `join/{code}`, `submit` (throttle 30/min).

**Tables** : `memory_sessions`, `memory_attempts`.

**Désactivation** : `OUTILS_MEMOIRE_ENABLED=false` retire routes, vues de dashboard et requêtes liées, sans toucher aux données existantes.

---

[Retour au sommaire des outils](../07-outils-animation.md)
