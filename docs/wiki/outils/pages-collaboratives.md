# Pages collaboratives (HedgeDoc)

*Public : formateurs ; partie technique en fin de page pour les développeurs.*

**Statut au 14 juillet 2026** : développé côté code mais pas encore activé en production — voir le statut général sur la page [07 — Outils d'animation](../07-outils-animation.md).

À la différence des 13 autres activités, cet outil n'a ni participation stagiaire suivie ni stockage de réponses dans Oneduc : c'est un lien direct vers un document Markdown collaboratif hébergé sur une instance [HedgeDoc](https://hedgedoc.org/) externe.

## En bref

| Modalité | Valeur |
|---|---|
| Rythme | Synchrone (édition simultanée) ou asynchrone (chacun contribue à son rythme) — géré entièrement par HedgeDoc |
| Lieu | Présentiel ou distanciel |
| Participation | Collaborative, sans limite technique liée au groupe Oneduc |
| Suivi Oneduc | Aucun — ni progression, ni traçabilité, ni lien avec un groupe |
| Activable/désactivable | Dépend uniquement de la configuration de l'instance HedgeDoc (`HEDGEDOC_BASE_URL`) |

## Comment ça marche

1. Le formateur ouvre `/formateur/pages-collaboratives`.
2. S'il existe une instance HedgeDoc configurée (`HEDGEDOC_BASE_URL` + `HEDGEDOC_NEW_PATH`), la page propose un lien direct vers la création d'un nouveau document.
3. Le formateur partage ensuite lui-même, hors Oneduc, le lien d'édition (et éventuellement un lien lecture seule) à ses stagiaires — texte, tableaux, images, listes en Markdown, coédité en temps réel par tous ceux qui ont le lien.
4. Si l'URL n'est pas configurée, l'interface affiche directement les variables d'environnement à renseigner plutôt qu'une page cassée.

## Dans quel contexte l'utiliser

- Prise de notes collective pendant une session (compte-rendu partagé, ordre du jour vivant).
- Rédaction collaborative d'un document plus long que ce que permet le tableau blanc (structuration en titres, listes, tableaux).
- Utilisable en présentiel comme en distanciel, en synchrone comme en asynchrone (le document reste accessible et modifiable après la session) — c'est HedgeDoc, pas Oneduc, qui gère cette flexibilité.

## Bon à savoir

- Parce que l'édition se fait entièrement hors Oneduc, ce document n'apparaît dans aucun tableau de bord ni suivi de progression : c'est un outil d'appoint, pas un livrable pédagogique tracé.

## Partie technique

**Route formateur** : `GET /formateur/pages-collaboratives` (`OutilsPagesCollaborativesController@index`, nom `formateur.pages-collaboratives.index`).

**Configuration** : `config('services.hedgedoc.base_url')` et `config('services.hedgedoc.new_path')`, alimentées par les variables d'environnement `HEDGEDOC_BASE_URL` et `HEDGEDOC_NEW_PATH`.

**Absence de stockage Oneduc** : aucune table dédiée — le contrôleur se contente de construire l'URL de création à partir de la configuration.

---

[Retour au sommaire des outils](../07-outils-animation.md)
