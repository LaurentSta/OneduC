# Mur de questions

*Public : formateurs ; partie technique en fin de page pour les développeurs.*

**Statut au 14 juillet 2026** : développé côté code mais pas encore activé en production — voir le statut général sur la page [07 — Outils d'animation](../07-outils-animation.md).

Les stagiaires posent leurs questions en texte libre, avec ou sans anonymat. Le groupe peut voter pour faire remonter les questions prioritaires, et le formateur qualifie chaque question en direct (ouverte / traitée).

## En bref

| Modalité | Valeur |
|---|---|
| Rythme | Asynchrone — les questions s'accumulent au fil de la séance |
| Lieu | Présentiel ou distanciel |
| Participation | Individuelle (poser une question, voter), lecture collective |
| Compte requis | Consultation du mur ouverte à tous via le code ; poser une question ou voter demande un compte |
| Anonymat | Optionnel, au choix du stagiaire à l'envoi |
| Activable/désactivable | Toujours actif |

## Comment ça marche

1. Le formateur crée un mur pour un groupe depuis `/formateur/mur-questions` et l'ouvre (`toggle`).
2. Les stagiaires accèdent au mur via `/oneduc/questions/{code}` — **la lecture du mur ne nécessite pas d'être connecté**, mais poser une question ou voter (throttle 30/min pour poser, 60/min pour voter) demande un compte.
3. Chaque question peut être postée anonymement ou non, au choix du stagiaire.
4. Le groupe vote pour les questions qu'il juge prioritaires (un vote par utilisateur et par question).
5. Le formateur voit les questions arriver en temps réel, triées par statut puis par nombre de votes, et les marque comme traitées (`updateStatus`) au fur et à mesure qu'il y répond.

## Dans quel contexte l'utiliser

- Donner la parole à ceux qui n'osent pas la prendre à l'oral, surtout utile en grand groupe ou avec des profils réservés.
- Éviter de perdre les questions posées en distanciel, où lever la main dans un chat se noie vite dans le fil.
- Le vote permet de prioriser un temps de questions/réponses limité sur ce qui bloque vraiment le plus de monde.

## Partie technique

**Routes formateur** (`routes/formateur.php`, préfixe `/formateur/mur-questions`, nom `formateur.questions.`) : `index`, `store`, `{wall}` (show), `{wall}/toggle`, `{wall}/questions/{question}/status`, `{wall}/state` (`QuestionWallController`).

**Routes de participation** (`routes/web.php`, préfixe `/oneduc/questions`) : `join/{code}` en lecture libre (`QuestionWallParticipationController`), `questions` (poser une question, sous `auth` + throttle 30/min), `questions/{question}/vote` (sous `auth` + throttle 60/min).

**Routes stagiaire** : `stagiaire.question-wall.notification-status` pour la cloche.

**Modèles / tables** : `QuestionWall` (`question_walls` : `access_code`, `is_active`), `QuestionWallQuestion` (`question_wall_questions` : `is_anonymous`, `status` par défaut `open`, `acted_at`), `QuestionWallVote` (`question_wall_votes`, unique par question+utilisateur).

---

[Retour au sommaire des outils](../07-outils-animation.md)
