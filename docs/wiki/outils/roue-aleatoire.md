# Roue aléatoire

*Public : formateurs ; partie technique en fin de page pour les développeurs.*

**Statut au 14 juillet 2026** : développé côté code mais pas encore activé en production — voir le statut général sur la page [07 — Outils d'animation](../07-outils-animation.md).

Le formateur charge une liste d'éléments (participants, sujets, rôles), la roue tourne, et un élément est désigné au hasard. Inspiration : [Picker](https://github.com/koddsson/picker).

## En bref

| Modalité | Valeur |
|---|---|
| Rythme | Synchrone |
| Lieu | Présentiel (écran projeté) ou distanciel (partage d'écran) |
| Participation | Spectateur — pas de compte requis pour regarder tourner la roue |
| Pilotage | Réservé au formateur |
| Activable/désactivable | Toujours actif |

## Comment ça marche

1. Le formateur crée une roue pour un groupe depuis `/formateur/roue-aleatoire`, avec une liste d'entrées (les entrées peuvent être synchronisées depuis les membres du groupe).
2. Il ajuste la liste des participants (`updateParticipants`), puis lance le tirage (`spin`) ; le résultat est mémorisé (`current_pick_id`, historique des tirages dans `picks`).
3. La roue peut être projetée à l'écran via `/oneduc/roue/{code}` : cette vue de participation est en lecture seule, pensée pour être affichée sur un écran commun plutôt que consultée individuellement. Depuis le 14 juillet 2026, elle exige une authentification et une appartenance au groupe actif (voir [10-securite-rgpd.md](../10-securite-rgpd.md)).
4. Le formateur peut réinitialiser (`reset`) pour relancer une série de tirages.

## Dans quel contexte l'utiliser

- Désigner un stagiaire au hasard pour une interrogation orale, un passage au tableau ou un rôle de rapporteur, sans effet de favoritisme perçu.
- Utilisable comme brise-glace en début de session (associer des sujets, tirer des sous-groupes).
- Fonctionne aussi bien en présentiel (projection sur grand écran) qu'en distanciel via partage d'écran, l'outil n'exigeant pas d'action des participants.

## Partie technique

**Routes formateur** (`routes/formateur.php`, préfixe `/formateur/roue-aleatoire`, nom `formateur.roue.`) : `index`, `store`, `{session}` (show), `{session}/participants`, `{session}/spin`, `{session}/reset`, `{session}/state` (`RoueAleatoireController`).

**Route de participation** (`routes/web.php`, sous middleware `auth`) : `GET /oneduc/roue/{code}` (`roue.join`) et `GET /oneduc/roue/{code}/state` (`RoueAleatoireParticipationController`) — vue projection en lecture seule.

**Modèles / tables** : `RandomWheelSession` (`random_wheel_sessions` : `access_code`, `entries` JSON, `active_entry_ids` JSON, `picks` JSON, `current_pick_id`, `spun_at`).

---

[Retour au sommaire des outils](../07-outils-animation.md)
