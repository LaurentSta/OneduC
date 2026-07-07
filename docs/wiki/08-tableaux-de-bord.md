# 08 — Tableaux de bord

*Public : formateurs pour la première partie ; la partie technique en fin de page s'adresse aux développeurs.*

Chaque rôle a un tableau de bord adapté à ses besoins. Cette page décrit ce que chacun affiche, et surtout la fiabilité de chaque chiffre — un point que tout formateur devrait connaître avant d'interpréter ses données.

---

## Ce que voit le formateur

Le tableau de bord formateur affiche :

- la liste de ses groupes, avec le nombre de stagiaires et leur statut ;
- une vue globale : taux de réussite moyen, stagiaires actifs, inactifs, non démarrés ;
- les groupes prioritaires (ceux qui ont le plus d'apprenants à risque) et les modules prioritaires (progression la plus faible) ;
- un graphique d'activité filtrable par période (jour, semaine, mois, an).

En complément, quatre vues de progression détaillées :

| Vue | Contenu |
|-----|---------|
| Par groupe | Tous les stagiaires du groupe avec statut global |
| Par stagiaire | Vue consolidée multi-modules |
| Par module | Tous les stagiaires sur un module donné |
| Détail stagiaire | Leçon par leçon, jusqu'à la question individuelle |

### Fiabilité des chiffres selon le contenu

Tous les indicateurs ne se valent pas. La fiabilité dépend du type de contenu suivi :

| Type de contenu | Fiabilité | Pourquoi |
|-----------------|-----------|----------|
| Quiz natifs | Excellente | Toutes les réponses et temps sont enregistrés |
| SCORM (scores) | Bonne | Scores et statuts enregistrés correctement |
| SCORM (détail des questions) | Nulle | Les interactions SCORM ne sont pas enregistrées pour les leçons (bug connu) |
| Vidéo | Partielle | Les segments de lecture sont suivis, pas la compréhension |
| Validation manuelle | Partielle | Présence validée, sans preuve pédagogique |

Concrètement : si un module est majoritairement en SCORM, les indicateurs "questions traitées" et "taux de réussite" seront sous-estimés. Privilégier les quiz natifs quand le détail des réponses compte.

---

## Ce que voit le stagiaire

Une interface épurée, pensée pour un public non expert :

- son formateur référent, affiché en permanence ;
- son temps d'apprentissage cumulé ;
- son taux de réussite et un graphique de ses scores par tentative ;
- "Ma formation" : les modules de son groupe avec progression par chapitre ;
- son temps moyen de réflexion par question.

L'approche est volontairement motivante : la progression globale est mise en avant, pas seulement les échecs.

Les limites de fiabilité côté formateur s'appliquent aussi ici : un contenu majoritairement SCORM sous-estime les questions traitées et le taux de réussite. Et un stagiaire dans plusieurs groupes actifs ne voit que le premier.

---

## Ce que voit l'administrateur

Un tableau de pilotage volumétrique : nombre de catégories, sous-catégories, modules, formateurs, stagiaires, groupes, sections, leçons. Suffisant pour surveiller la croissance de la plateforme ; insuffisant pour piloter la qualité pédagogique.

Manques identifiés : pas de taux d'activité ni de complétion moyenne, pas d'alertes qualité contenu, pas d'indicateurs par organisme ou par groupe, et les données SCORM calculées ne sont pas exploitées dans la vue actuelle.

---

## Améliorations prévues

1. Afficher la source de chaque indicateur (quiz, SCORM, vidéo, manuel) pour que le formateur sache d'où vient le chiffre.
2. Signaler quand les interactions SCORM sont vides, pour éviter les interprétations erronées.
3. Exporter les données de progression (CSV par groupe, PDF fiche individuelle).
4. Unifier les définitions : "actif", "terminé", "réussi" et "temps d'apprentissage" doivent signifier la même chose sur tous les écrans.

---

## Partie technique

### Fichiers

| Dashboard | Contrôleur | Vue |
|-----------|-----------|-----|
| Admin | `AdminController.php` | `resources/views/admin/index.blade.php` |
| Formateur | `FormateurController.php` (687 lignes) + `LearningAnalyticsService.php` (502 lignes) | `resources/views/formateur/index.blade.php` |
| Stagiaire | `StagiaireController.php` | `resources/views/stagiaire/index.blade.php` |

La donnée est centralisée via `LearningAnalyticsService` pour le formateur ; certains écrans stagiaires conservent des agrégations propres dans `StagiaireController`.

### Sources des indicateurs admin

| Indicateur | Source |
|------------|--------|
| Catégories / sous-catégories | `Category::count()` / `SubCategory::count()` |
| Modules / sections / leçons | `Module`, `ModuleSection`, `ModuleLecture` |
| Formateurs / stagiaires | `User::where('role', ...)` |
| Groupes | `Group::count()` |

### `LearningAnalyticsService::collectSnapshots()`

Le cœur analytique de la plateforme. La méthode :

1. Charge toutes les données de progression des groupes du formateur : `Progression` (validations manuelles), `ScormResult` et `ScormScore`, `ScormInteraction` (vide pour les leçons), `VideoSegmentTracking`, `QuizAttempt` et `QuizAttemptQuestion`.
2. Produit un snapshot unifié par paire `(user_id, lecture_id)` via `finalizeSnapshot()`.
3. `finalizeSnapshot()` détermine `is_started`, `is_successful` et `last_activity_at`.

### Sources des indicateurs stagiaire

| Indicateur | Source |
|------------|--------|
| Temps d'apprentissage | `VideoSegmentTracking` + `ScormScore.session_time` + `QuizAttempt.total_time_seconds` |
| Questions traitées | `ScormInteraction` (vide pour SCORM) + `QuizAttemptQuestion` |
| Taux de réussite | Ratio questions réussies / questions traitées |
| Progression par module | Ratio leçons terminées / leçons totales |
| Temps moyen de réflexion | Moyenne de `quiz_attempt_questions.time_spent_seconds` |

### Vues de progression formateur

| Vue | Contrôleur |
|-----|-----------|
| Par groupe | `ProgressionGroupesController` |
| Par stagiaire | `ProgressionStagiairesController` |
| Par module | `ProgressionModulesController` |
| Détail stagiaire | `ProgressionStagiaireController` (630 lignes) |

`ProgressionStagiaireController` agrège les mêmes sources que `LearningAnalyticsService`, avec un niveau de détail plus fin — jusqu'à la question individuelle.

Le graphique d'activité formateur passe par un endpoint AJAX avec cache côté serveur, pour éviter les requêtes répétées sur les grands groupes.

Point de maintenabilité : `StagiaireController` concentre encore dashboard, modules, résultats et outils ; un découpage est prévu (voir [Roadmap](11-roadmap.md)).

### État de vérification

Au 5 juillet 2026 :
- `php artisan test` : 103 tests passés, 1 échec, 505 assertions ;
- l'échec restant concerne le contrat d'upload image du module builder (`path` attendu par le test, `media_id`/`url` retournés par le contrôleur) ;
- `npm run build` : réussi, avec avertissements de bundles Vite volumineux.

---

[Retour au wiki](README.md)
