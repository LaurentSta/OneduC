# Tableau blanc collaboratif

*Public : formateurs ; partie technique en fin de page pour les développeurs.*

**Statut au 14 juillet 2026** : développé côté code mais pas encore activé en production — voir le statut général sur la page [07 — Outils d'animation](../07-outils-animation.md).

Un tableau blanc partagé basé sur [Excalidraw](https://github.com/excalidraw/excalidraw) : formateur et stagiaires dessinent et annotent ensemble. Il n'existe qu'un seul tableau blanc actif par groupe (pas de multi-tableaux à gérer).

## En bref

| Modalité | Valeur |
|---|---|
| Rythme | Synchrone (coédition en temps quasi réel par sauvegardes régulières) |
| Lieu | Présentiel ou distanciel |
| Participation | Collaborative — formateur et stagiaires dessinent ensemble |
| Compte requis | Oui, appartenance au groupe |
| Portée | Un tableau unique par groupe (pas de session à créer/fermer) |
| Activable/désactivable | Toujours actif |

## Comment ça marche

1. Chaque groupe dispose d'un tableau blanc unique, créé à la demande (`GroupWhiteboard::ensureForGroup`-like, une seule ligne par `group_id`).
2. Le formateur y accède depuis `/formateur/groupes/{group}/tableau-blanc` ; les stagiaires depuis `/stagiaire/tableau-blanc/groupes/{group}`.
3. Les modifications (formes, traits, textes) sont sauvegardées via l'export Excalidraw (`excalidraw-save`) et versionnées (`version` incrémentale) pour détecter les sauvegardes concurrentes.
4. Un point d'entrée `items` permet aussi de manipuler des éléments un par un (`upsert` / `destroy`) en dehors du flux Excalidraw natif.
5. Le formateur peut vider entièrement le tableau (`clear`).

## Dans quel contexte l'utiliser

- Brainstorming collectif : chacun pose des idées sur un espace visuel partagé, en présentiel autour d'un écran ou à distance chacun depuis son poste.
- Co-construction d'un schéma, d'une carte mentale ou d'une frise pendant l'atelier.
- Contrairement aux activités "session" (quiz, sondage...), il n'y a rien à ouvrir ni fermer : le tableau du groupe est toujours disponible, ce qui en fait un outil de fond plutôt qu'un temps fort ponctuel.

## Partie technique

**Routes formateur** (`routes/formateur.php`, préfixe `/formateur/groupes/{group}/tableau-blanc`, nom `formateur.groupes.whiteboard.`) : `show`, `snapshot`, `excalidraw.save`, `items.upsert`, `items.destroy`, `clear` (`WhiteboardController`).

**Routes stagiaire** (`routes/stagiaire.php`, préfixe `/stagiaire/tableau-blanc`, nom `stagiaire.whiteboard.`) : `index`, `notification-status`, `groupes/{group}` (show), `groupes/{group}/snapshot`, `groupes/{group}/excalidraw-save`, `groupes/{group}/items` (upsert), `groupes/{group}/items/{item}` (destroy).

**Modèles / tables** : `GroupWhiteboard` (`group_whiteboards`, unique par `group_id` : `settings` JSON, `excalidraw_data` JSON, `version`), `GroupWhiteboardItem` (`group_whiteboard_items` : position, taille, rotation, `z_index`, `payload` JSON, `style` JSON).

---

[Retour au sommaire des outils](../07-outils-animation.md)
