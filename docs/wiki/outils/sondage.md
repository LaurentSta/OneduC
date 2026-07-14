# Sondage

*Public : formateurs ; partie technique en fin de page pour les développeurs.*

**Statut au 14 juillet 2026** : développé côté code mais pas encore activé en production — voir le statut général sur la page [07 — Outils d'animation](../07-outils-animation.md).

Le formateur crée une ou plusieurs questions à choix unique ou multiple, les stagiaires votent depuis leur appareil, et les résultats s'affichent en barres ou en camembert dès les premières réponses.

## En bref

| Modalité | Valeur |
|---|---|
| Rythme | Synchrone (session active pilotée par le formateur) |
| Lieu | Présentiel ou distanciel |
| Participation | Individuelle en vote, restitution collective |
| Compte requis | Oui (utilisateur authentifié) |
| Activable/désactivable | Toujours actif |

## Comment ça marche

1. Le formateur crée un sondage depuis `/formateur/sondages` : titre, groupe, une ou plusieurs questions avec leurs options (choix unique ou multiple).
2. Il ouvre la session (`toggle`) : un code d'accès est généré pour le groupe.
3. Les stagiaires rejoignent via `/oneduc/sondage/{code}` et votent ; les résultats se mettent à jour en direct côté formateur (`state`).
4. Le formateur peut fermer la session à tout moment.

## Dans quel contexte l'utiliser

- Prendre la température d'un groupe sur une question fermée avant de lancer une discussion.
- Faire arbitrer un choix collectif (ordre des ateliers, sujet à approfondir).
- Se lance en quelques secondes, sans préparation lourde — adapté à une utilisation spontanée en cours de séance, à la différence de la banque de questions qui demande une rédaction en amont.

## Partie technique

**Routes formateur** (`routes/formateur.php`, préfixe `/formateur/sondages`, nom `formateur.sondages.`) : `index`, `store`, `{pollSession}` (show), `{pollSession}/toggle`, `{pollSession}/state` (`OutilsSondageController`).

**Routes de participation** (`routes/web.php`, préfixe `/oneduc/sondage`, sous middleware `auth`) : `home`, `resolveCode`, `join/{code}`, `submit` (throttle 60/min), `data` (`PollParticipationController`).

**Modèles / tables** : `PollSession` (`formateur_id`, `group_id`, `access_code`, questions en JSON), `PollSessionResponse`.

---

[Retour au sommaire des outils](../07-outils-animation.md)
