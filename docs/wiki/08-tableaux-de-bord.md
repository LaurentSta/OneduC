# 08 — Tableaux de bord

*Public : formateurs pour la première partie, administrateurs pour la section dédiée ; la partie technique en fin de page s'adresse aux développeurs.*

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

Le tableau de bord administrateur est orienté vers les actions à effectuer sur les comptes formateurs et stagiaires. Il affiche en premier :

- le nombre de comptes gérés, avec la répartition entre comptes actifs et inactifs ;
- le nombre de formateurs, avec les comptes encore à activer ;
- le nombre de stagiaires, avec ceux qui ne sont rattachés à aucun groupe ;
- le nombre de groupes et le nombre de groupes actifs.

Une zone « Points d'attention » donne un accès direct aux situations à traiter :

- comptes formateurs inactifs ;
- adhésions formateurs non actives ou arrivées à expiration ;
- stagiaires sans groupe ;
- groupes sans stagiaire.

Le tableau « Comptes récemment créés » présente les huit derniers comptes formateurs ou stagiaires, leur rôle, leur rattachement, leur statut et leur date de création. Le nombre de comptes créés depuis le début du mois est indiqué dans son en-tête.

Le bloc « Accès plateforme » affiche la proportion de comptes actifs parmi les formateurs et stagiaires. Les volumes du catalogue — catégories, sous-catégories, modules, sections et leçons — restent accessibles comme informations secondaires.

Des actions rapides permettent de créer un formateur ou un stagiaire, d'ouvrir la gestion centralisée des utilisateurs, ou d'accéder aux groupes et au catalogue.

### Gestion centralisée des utilisateurs

La liste `admin.utilisateurs.index` regroupe les comptes formateurs et stagiaires. Les filtres sont transmis en paramètres GET et portent sur :

- le rôle ;
- une recherche sur l'identité, l'adresse électronique ou la structure ;
- le statut actif ou inactif ;
- la présence ou l'absence d'un rattachement à un groupe ;
- l'ordre d'affichage et le nombre de résultats par page (`20`, `50` ou `100`).

Pour un formateur, le rattachement inclut aussi bien les groupes dont il est responsable (`groups.instructor_id`) que ceux qu'il co-anime via `group_user.role_in_group = formateur`.

La liste est paginée côté serveur. Elle présente l'identité, le rôle, les rattachements, l'accès au compte, l'adhésion pour les formateurs et les dates de mise à jour. Les actions disponibles sont la modification, l'activation ou la désactivation, la réinitialisation de progression pour un stagiaire et la suppression avec confirmation.

Ce tableau de bord fournit des indicateurs opérationnels de gestion des comptes. Il ne calcule pas de taux de complétion pédagogique moyen et n'affiche pas de score SCORM global.

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
| Comptes gérés | Somme des `User` ayant le rôle `formateur` ou `stagiaire` |
| Comptes actifs / inactifs | Champ `users.status` pour ces deux rôles |
| Formateurs à activer | `User` de rôle `formateur` avec `status = false` |
| Adhésions à régulariser | Formateurs dont `adhesion_status` n'est pas `active` ou dont `adhesion_valid_until` est passée |
| Stagiaires sans groupe | Relation `groupesStagiaire` absente |
| Groupes sans stagiaire | Relation `students` absente |
| Comptes récents | Huit derniers `User` formateurs ou stagiaires, triés par date de création décroissante |
| Créations du mois | Comptes formateurs ou stagiaires créés depuis le début du mois courant |
| Catégories / sous-catégories | `Category::count()` / `SubCategory::count()` |
| Modules / sections / leçons | `Module`, `ModuleSection`, `ModuleLecture` |
| Formateurs / stagiaires | `User::where('role', ...)` |
| Groupes / groupes actifs | `Group::count()` / champ `groups.is_active` |

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

Vérifications du socle administrateur au 14 juillet 2026 :

- `php artisan test tests/Feature/Admin` : 41 tests passés, 312 assertions ;
- `npm run build` : réussi, avec les avertissements connus sur la taille de certains bundles ;
- `php artisan view:cache` : réussi ;
- suite complète : 265 tests passés et 1 échec préexistant hors périmètre dans `QuizQuestionAuthoringTest`, sur une redirection de l'éditeur de questions formateur. Les fichiers concernés ne sont pas modifiés par la refonte administrateur.

---

[Retour au wiki](README.md)
