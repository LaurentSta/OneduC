# Échelle de positionnement

*Public : formateurs ; partie technique en fin de page pour les développeurs.*

**Statut au 14 juillet 2026** : développé côté code mais pas encore activé en production — voir le statut général sur la page [07 — Outils d'animation](../07-outils-animation.md).

Les stagiaires positionnent leur avis sur un curseur de 1 à 10 (niveau de confiance, accord/désaccord, ressenti...). Le formateur voit la moyenne et la distribution des réponses en temps réel.

## En bref

| Modalité | Valeur |
|---|---|
| Rythme | Synchrone |
| Lieu | Présentiel ou distanciel |
| Participation | Individuelle en réponse, lecture collective de la distribution |
| Compte requis | Oui (utilisateur authentifié) |
| Activable/désactivable | `config('outils.echelle.enabled')` / `OUTILS_ECHELLE_ENABLED` (`true` par défaut) |

## Comment ça marche

1. Le formateur crée une ou plusieurs questions d'échelle pour un groupe depuis `/formateur/echelle`.
2. Il ouvre la session (`toggle`), un code est généré.
3. Les stagiaires répondent via `/oneduc/echelle/{code}` en plaçant un curseur entre 1 et 10.
4. Le formateur suit la moyenne et la distribution des réponses en direct (`state`).

## Dans quel contexte l'utiliser

- Mesurer le sentiment de compréhension ou de confort d'un groupe en fin de séquence, pour décider s'il faut reprendre un point.
- Faire un instantané avant/après sur une notion (comparer deux échelles) pour objectiver une progression perçue.
- Très rapide à lancer, adapté à une utilisation ponctuelle plutôt qu'à un usage préparé longtemps à l'avance.

## Partie technique

**Routes formateur** (`routes/formateur.php`, préfixe `/formateur/echelle`, nom `formateur.echelle.`) : `index`, `store`, `{scaleSession}` (show), `{scaleSession}/toggle`, `{scaleSession}/state` (`OutilsEchelleController`).

**Routes de participation** (`routes/web.php`, préfixe `/oneduc/echelle`, sous middleware `auth`) : `home`, `resolveCode`, `join/{code}`, `submit` (throttle 60/min), `data` (`ScaleParticipationController`).

**Modèles / tables** : `ScaleSession` (`scale_sessions`), `ScaleSessionResponse` (`scale_session_responses`).

---

[Retour au sommaire des outils](../07-outils-animation.md)
