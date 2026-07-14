# Buzzer Quiz

*Public : formateurs ; partie technique en fin de page pour les développeurs.*

**Statut au 14 juillet 2026** : développé côté code mais pas encore activé en production — voir le statut général sur la page [07 — Outils d'animation](../07-outils-animation.md).

Le formateur prépare une série de questions, lance la manche, et les stagiaires buzzent depuis leur appareil pour répondre les premiers. Le plus rapide répond à voix haute (présentiel) ou à l'écrit/oral à distance, le formateur valide ou refuse la réponse, et le classement se met à jour en direct.

## En bref

| Modalité | Valeur |
|---|---|
| Rythme | Synchrone, rythmé par le formateur |
| Lieu | Présentiel ou distanciel |
| Participation | Individuelle et compétitive (classement) |
| Compte requis | Oui (utilisateur authentifié) |
| Activable/désactivable | `config('outils.buzzer.enabled')` / `OUTILS_BUZZER_ENABLED` (`true` par défaut) |

## Comment ça marche

1. Le formateur prépare une série de questions pour un groupe depuis `/formateur/buzzer`.
2. Il démarre une question (`start`) : les stagiaires buzzent (`buzz`, limité à 30 requêtes/minute par throttle).
3. Le formateur valide (`correct`) ou refuse (`incorrect`) la réponse du premier arrivé, ou passe la question (`skip`), puis enchaîne (`next`).
4. Le classement se construit au fil des manches ; le formateur ferme la session (`close`) une fois terminé.

## Dans quel contexte l'utiliser

- Créer un temps compétitif et rythmé en fin de module, façon jeu télévisé, pour dynamiser une révision.
- Fonctionne aussi bien en présentiel (le premier qui buzze parle à voix haute) qu'à distance (réponse dans le chat ou à l'oral), tant que le formateur garde le contrôle de la validation.

## Partie technique

**Routes formateur** (`routes/formateur.php`, préfixe `/formateur/buzzer`, nom `formateur.buzzer.`) : `index`, `store`, `{buzzerSession}` (show), `{buzzerSession}/snapshot`, `/start`, `/correct`, `/incorrect`, `/skip`, `/next`, `/close`, `destroy` (`OutilsBuzzerController`).

**Routes de participation** (`routes/web.php`, préfixe `/oneduc/buzzer`, sous middleware `auth`) : `home`, `resolveCode`, `join/{code}`, `buzz` (throttle 30/min), `snapshot` (throttle 120/min) (`BuzzerParticipationController`).

**Modèles / tables** : `BuzzerSession` (`buzzer_sessions`), `buzzer_questions`, `buzzer_attempts`, `buzzer_participants` (gestion du classement).

---

[Retour au sommaire des outils](../07-outils-animation.md)
