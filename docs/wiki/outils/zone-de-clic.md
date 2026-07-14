# Zone de clic

*Public : formateurs ; partie technique en fin de page pour les développeurs.*

**Statut au 14 juillet 2026** : développé côté code mais pas encore activé en production — voir le statut général sur la page [07 — Outils d'animation](../07-outils-animation.md).

Le formateur importe une image, dessine des zones à retrouver et nomme chaque composant. Les stagiaires doivent cliquer au bon endroit ; leur score et la réussite par composant remontent côté formateur.

## En bref

| Modalité | Valeur |
|---|---|
| Rythme | Synchrone (session ouverte par le formateur) |
| Lieu | Présentiel ou distanciel |
| Participation | Individuelle, notée (score par composant) |
| Compte requis | Oui (utilisateur authentifié) |
| Activable/désactivable | `config('outils.composants.enabled')` / `OUTILS_COMPOSANTS_ENABLED` (`true` par défaut) |

## Comment ça marche

1. Le formateur importe une image depuis `/formateur/trouve-le-composant`, dessine les zones à retrouver et nomme chaque composant.
2. Il ouvre la session pour un groupe (`toggle`), un code est généré.
3. Les stagiaires rejoignent via `/oneduc/composant/{code}` et cliquent sur l'image pour désigner chaque composant demandé.
4. Le score et la réussite par composant remontent côté formateur.

## Dans quel contexte l'utiliser

- Vérifier la reconnaissance visuelle d'éléments sur un schéma, une interface logicielle, un plan ou une photo (repérer les organes d'une machine, les zones d'un formulaire, les parties d'un diagramme réseau...).
- Adapté aux formations à forte composante visuelle ou technique où le repérage précis compte autant que la définition théorique.

## Partie technique

**Routes formateur** (`routes/formateur.php`, préfixe `/formateur/trouve-le-composant`, nom `formateur.composants.`) : `index`, `store`, `{componentFinderSession}` (show), `{componentFinderSession}/toggle`, `destroy` (`OutilsComposantController`, upload de l'image via `Storage`).

**Routes de participation** (`routes/web.php`, préfixe `/oneduc/composant`, sous middleware `auth`) : `home`, `resolveCode`, `join/{code}`, `submit` (throttle 60/min) (`ComponentFinderParticipationController`).

**Modèles / tables** : `ComponentFinderSession` (`component_finder_sessions`, stocke l'image et les zones à trouver), `component_finder_attempts` (scores).

---

[Retour au sommaire des outils](../07-outils-animation.md)
