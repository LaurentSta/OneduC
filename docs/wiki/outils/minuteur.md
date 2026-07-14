# Minuteur

*Public : formateurs ; partie technique en fin de page pour les développeurs.*

**Statut au 14 juillet 2026** : développé côté code mais pas encore activé en production — voir le statut général sur la page [07 — Outils d'animation](../07-outils-animation.md).

Un compte à rebours visible par tous les participants d'un groupe, contrôlé par le formateur (démarrer, mettre en pause, réinitialiser). Un seul minuteur actif par groupe.

## En bref

| Modalité | Valeur |
|---|---|
| Rythme | Synchrone |
| Lieu | Présentiel ou distanciel |
| Participation | Spectateur pour les stagiaires, pilotage réservé au formateur |
| Portée | Un minuteur unique par groupe |
| Activable/désactivable | `config('outils.minuteur.enabled')` / `OUTILS_MINUTEUR_ENABLED` (`true` par défaut) |

## Comment ça marche

1. Le formateur ouvre le minuteur de son groupe depuis `/formateur/groupes/{group}/minuteur` ; un minuteur est créé automatiquement s'il n'existe pas encore pour ce groupe.
2. Il configure la durée (30 s à 2 h) et un libellé optionnel (`configure`), puis démarre (`start`), met en pause (`pause`) ou réinitialise (`reset`).
3. Les stagiaires du groupe voient le décompte en lecture seule depuis `/stagiaire/minuteur/groupes/{group}`, avec l'état interrogé par polling (`status`).
4. Le minuteur passe par les états `idle`, `running`, `paused`, `finished` ; le temps écoulé est recalculé côté serveur à la reprise pour rester fiable même après une pause.

## Dans quel contexte l'utiliser

- Rythmer un atelier ou un exercice chronométré, avec un décompte visible par tout le monde sans que chacun ait à surveiller sa propre montre.
- Cadrer une pause (mode "Pomodoro") pendant une session longue, en présentiel comme en distanciel.

## Bon à savoir

- Bien qu'il ne soit pas présenté ainsi dans le reste de la documentation, le Minuteur est techniquement un **domaine autonome** au même titre que le Pendu et le Jeu de mémoire : ses routes, son contrôleur et son fournisseur de service (`MinuteurServiceProvider`) vivent dans `app/Domains/Outils/Minuteur/`, indépendamment de `routes/formateur.php`. Il est simplement plus simple, puisqu'il n'a pas de table de réponses à gérer.

## Partie technique

**Routes formateur** (`app/Domains/Outils/Minuteur/routes.php`, préfixe `/formateur/groupes/{group}/minuteur`, nom `formateur.groupes.timer.`) : `show`, `status`, `configure`, `start`, `pause`, `reset` (`Formateur\TimerController`).

**Routes stagiaire** (préfixe `/stagiaire/minuteur/groupes/{group}`, nom `stagiaire.timer.`) : `show`, `status` (`Stagiaire\TimerController`).

**Modèle / table** : `GroupTimer` (`group_timers`, unique par `group_id` : `label`, `duration_seconds`, `status` enum `idle`/`running`/`paused`/`finished`, `started_at`, `elapsed_seconds`).

---

[Retour au sommaire des outils](../07-outils-animation.md)
