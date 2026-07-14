# Vrai ou Faux

*Public : formateurs ; partie technique en fin de page pour les développeurs.*

**Statut au 14 juillet 2026** : développé côté code mais pas encore activé en production — voir le statut général sur la page [07 — Outils d'animation](../07-outils-animation.md).

Le formateur rédige des affirmations courtes avec une réponse attendue (Vrai/Faux) et, si besoin, une explication. Les stagiaires répondent depuis un code d'accès, le formateur voit les résultats en direct et peut commenter chaque bonne réponse.

## En bref

| Modalité | Valeur |
|---|---|
| Rythme | Synchrone |
| Lieu | Présentiel ou distanciel |
| Participation | Individuelle en réponse, débrief collectif |
| Compte requis | Oui (utilisateur authentifié) |
| Activable/désactivable | `config('outils.vraifaux.enabled')` / `OUTILS_VRAIFAUX_ENABLED` (`true` par défaut) |

## Comment ça marche

1. Le formateur crée une série d'affirmations pour un groupe depuis `/formateur/vrai-faux`, chacune avec sa réponse correcte et une explication optionnelle.
2. Il ouvre la session (`toggle`), un code est généré.
3. Les stagiaires répondent via `/oneduc/vrai-faux/{code}`.
4. Le formateur suit les résultats en direct (`state`) et peut commenter chaque affirmation en s'appuyant sur l'explication préparée.

## Dans quel contexte l'utiliser

- Lever des idées reçues ou des confusions fréquentes sur un sujet, en confrontant le groupe à des affirmations volontairement piégeuses.
- Vérifier rapidement une compréhension avant de passer à la suite, avec un débrief argumenté immédiat grâce aux explications préparées à l'avance.

## Bon à savoir

- Quand l'outil est désactivé (`OUTILS_VRAIFAUX_ENABLED=false`), ni ses routes ni sa tuile formateur ne sont exposées — c'est un simple retrait d'affichage, les sessions déjà créées restent en base.

## Partie technique

**Routes formateur** (`routes/formateur.php`, préfixe `/formateur/vrai-faux`, nom `formateur.vraifaux.`) : `index`, `store`, `{trueFalseSession}` (show), `{trueFalseSession}/toggle`, `{trueFalseSession}/state` (`OutilsVraiFauxController`).

**Routes de participation** (`routes/web.php`, préfixe `/oneduc/vrai-faux`, sous middleware `auth`) : `home`, `resolveCode`, `join/{code}`, `submit` (throttle 60/min), `data` (`VraiFauxParticipationController`).

**Modèles / tables** : `TrueFalseSession` (`true_false_sessions`), `TrueFalseSessionResponse` (`true_false_session_responses`).

---

[Retour au sommaire des outils](../07-outils-animation.md)
