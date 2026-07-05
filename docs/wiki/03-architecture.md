# 03 — Architecture technique

## Vue d'ensemble

Oneduc suit une architecture **Laravel MVC standard** avec une séparation nette par rôle utilisateur au niveau des routes, contrôleurs et vues.

```
routes/
├── web.php           ← routes publiques et authentification générale
├── admin.php         ← espace /admin (middleware: auth, role:admin, admin.activity)
├── formateur.php     ← espace /formateur (middleware: auth, role:formateur, association.member)
├── stagiaire.php     ← espace /stagiaire (middleware: auth, role:stagiaire, track.time)
├── observateur.php   ← espace /observateur (middleware: auth, role:observateur)
├── scorm.php         ← API SCORM (middleware partiel, routes sans CSRF pour iframe)
└── feedback.php      ← fichier présent mais non importé ; les routes feedback actives sont dans web.php
```

---

## Routing multi-rôle

| Espace | Préfixe URL | Middleware | Namespace contrôleur |
|--------|-------------|------------|----------------------|
| Admin | `/admin` | `auth, role:admin, admin.activity` | `App\Http\Controllers\` (sous-dossier `Backend/`) |
| Formateur | `/formateur` | `auth, role:formateur, association.member` | `App\Http\Controllers\Formateur\` |
| Stagiaire | `/stagiaire` | `auth, role:stagiaire, track.time`, puis `force.password.change` sauf première connexion | `App\Http\Controllers\Stagiaire\` |
| Observateur | `/observateur` | `auth, role:observateur` | `App\Http\Controllers\Observateur\` |

Le middleware `force.password.change` est appliqué dans l'espace stagiaire après les routes de première connexion. Il bloque l'accès jusqu'à ce que l'utilisateur définisse son mot de passe (`users.password_changed_at` nul). Le formateur n'a pas ce middleware dans `routes/formateur.php` au 5 juillet 2026.

Le dépôt expose 411 routes déclarées via `php artisan route:list --json`, dont 128 sous `/admin`, 144 sous `/formateur`, 49 sous `/stagiaire` et 9 sous `/observateur`.

---

## Structure des contrôleurs

```
app/Http/Controllers/
├── AdminController.php              ← Dashboard admin
├── FormateurController.php          ← Dashboard formateur (analytics)
├── StagiaireController.php          ← Dashboard stagiaire (794 lignes, progression extraite)
├── UserController.php               ← Auth, profil, connexion par code
├── LessonFeedbackController.php     ← Retours sur les leçons
│
├── Backend/                         ← Contrôleurs admin
│   ├── ModuleController.php         ← CRUD modules + navigation section/leçon (1185 lignes)
│   ├── ModuleSectionController.php
│   ├── ModuleLectureController.php
│   ├── ScormLibraryController.php   ← Import SCORM
│   ├── QuizQuestionController.php   ← Banque de questions (775 lignes)
│   ├── GroupeController.php         ← CRUD groupes admin
│   ├── StagiaireController.php      ← Gestion stagiaires admin
│   ├── PilotageController.php       ← Projets, tâches, journal (756 lignes)
│   └── EvaluationController.php
│
├── Formateur/
│   ├── GroupeController.php         ← Gestion groupes formateur (686 lignes)
│   ├── GroupeModuleLessonController.php ← Personnalisation leçons par groupe
│   ├── ModuleBuilderController.php  ← Orchestration HTTP du builder de modules formateur (455 lignes)
│   ├── MesFormationsController.php  ← FormateurParcours
│   ├── ParcoursController.php       ← Parcours onboarding formateur
│   ├── ProgressionGroupesController.php
│   ├── ProgressionStagiaireController.php ← Détail stagiaire (630 lignes)
│   └── [outils live]/ ...
│
└── Stagiaire/
    ├── QuizController.php            ← Quiz natifs (701 lignes, partagé formateur)
    └── [outils live]/ ...
```

---

## Monolithe modulaire

Oneduc reste volontairement un **monolithe Laravel** : une application, une base de données, un déploiement. La trajectoire recommandée est de le faire évoluer vers un **monolithe modulaire** : les contrôleurs gardent l'orchestration HTTP, tandis que la logique métier est extraite dans `app/Domains/`.

Objectifs :
- réduire les contrôleurs de plusieurs centaines de lignes ;
- faciliter les tests ciblés ;
- rendre les zones fonctionnelles plus lisibles pour les développeurs et pour l'IA ;
- éviter la complexité prématurée des microservices.

Domaines extraits :

```text
app/Domains/
├── ModulesFormateur/
│   ├── Actions/
│   │   ├── CreerModule.php
│   │   ├── DupliquerModuleCatalogue.php
│   │   ├── CreerChapitre.php
│   │   ├── CreerLecon.php
│   │   ├── ModifierLecon.php
│   │   ├── ReordonnerChapitres.php
│   │   ├── ReordonnerLecons.php
│   │   ├── DeplacerLecon.php
│   │   ├── PromouvoirLeconEnChapitre.php
│   │   ├── TeleverserImageModule.php
│   │   ├── TeleverserVideoModule.php
│   │   ├── TeleverserScormModule.php
│   │   ├── ModifierOptionsModule.php
│   │   └── AssignerGroupesModule.php
│   └── Support/
│       ├── AccesModule.php
│       ├── DonneesModule.php
│       └── NettoyeurBlocsModule.php
└── Learners/
    └── Support/
        └── LearnerModuleProgress.php
```

Les noms de fichiers/classes de `ModulesFormateur` sont volontairement en français (convention retenue pour tout le code métier propre à Oneduc, par opposition aux termes techniques génériques du framework Laravel qui restent en anglais). `ModuleBuilderController` délègue maintenant la création, duplication, réordonnancement, déplacement de leçons, promotion en chapitre, upload d'images/vidéos/SCORM, options module et assignation aux groupes à `ModulesFormateur`. `StagiaireController` délègue le calcul de progression des modules et les statuts de quiz natifs à `Learners\Support\LearnerModuleProgress`.

Prochaines zones candidates :
- `Backend/ModuleController` : navigation module/section/leçon, progression, médias ;
- `StagiaireController` : dashboard, modules, résultats, outils ;
- `Formateur/GroupeController` : création groupe, stagiaires, co-formateurs, invitations ;
- `ProgressionStagiaireController` : timeline, présence, risque d'abandon.

---

## Modèles Eloquent (61 modèles)

### Modèles centraux

| Modèle | Table | Rôle |
|--------|-------|------|
| `User` | `users` | Tous les profils (champ `role` : admin/formateur/stagiaire/observateur) |
| `Group` | `groups` | Groupe de formation, lié à un instructor et des modules |
| `Module` | `modules` | Unité d'apprentissage avec sections et leçons |
| `ModuleSection` | `module_sections` | Chapitre d'un module |
| `ModuleLecture` | `module_lectures` | Leçon individuelle |
| `FormateurParcours` | `formateur_parcours` | Parcours de formation créé par un formateur |
| `FormateurParcoursItem` | `formateur_parcours_items` | Élément ordonné d'un parcours |

### Modèles de progression

| Modèle | Table | Rôle |
|--------|-------|------|
| `Progression` | `progressions` | Marquage manuel d'une leçon comme terminée |
| `ScormScore` | `scorm_scores` | Score consolidé, statut, temps SCORM par leçon |
| `ScormResult` | `scorm_results` | Log brut de toutes les paires clé/valeur SCORM |
| `ScormInteraction` | `scorm_interactions` | Questions/réponses SCORM (non alimenté pour les leçons) |
| `QuizAttempt` | `quiz_attempts` | Tentative de quiz natif |
| `QuizAttemptQuestion` | `quiz_attempt_questions` | Réponse par question |
| `VideoSegmentTracking` | `video_segment_trackings` | Suivi de lecture vidéo |

### Modèles de pivot et relations

| Modèle | Table | Rôle |
|--------|-------|------|
| `GroupUser` (pivot) | `group_user` | Lien utilisateur-groupe avec `role_in_group` |
| `GroupModule` (pivot) | `group_module` | Modules affectés à un groupe avec `position` |
| `GroupModuleLecture` | `group_module_lectures` | Personnalisation leçons par groupe |
| `ScormPackage` | `scorm_packages` | Référence d'un package SCORM (slug stable) |
| `ScormPackageVersion` | `scorm_package_versions` | Version d'un package SCORM importé |

Le modèle `Module` utilise `SoftDeletes` et distingue les modules catalogue des modules créés par les formateurs via `is_trainer_authored`.

---

## Services métier

```
app/Services/
├── LearningAnalyticsService.php    ← Agrégation de toutes les sources de progression (502 lignes)
├── QuizService.php                 ← Logique quiz (démarrage, réponses, scoring)
├── CodeGeneratorService.php        ← Génération de codes d'accès 6 caractères alphanumériques
├── ModuleCompletionNotifier.php    ← Notification formateur en fin de module
├── TrainerPathQualityDashboardService.php ← Qualité du parcours formateur côté admin
└── Scorm/
    └── ScormImporter.php           ← Import ZIP SCORM sécurisé avec versioning
```

### `LearningAnalyticsService`

Service central pour tous les tableaux de bord. Il agrège :
- `Progression` (leçons validées manuellement)
- `ScormResult` / `ScormScore` (contenus SCORM)
- `ScormInteraction` (questions SCORM — vide pour les leçons standard)
- `VideoSegmentTracking` (vidéos)
- `QuizAttempt` / `QuizAttemptQuestion` (quiz natifs)

La méthode `collectSnapshots()` produit un snapshot unifié par paire `(user_id, lecture_id)`. La méthode `finalizeSnapshot()` détermine `is_started`, `is_successful` et `last_activity_at` selon une hiérarchie de sources.

---

## Écrans riches côté frontend

Les écrans Blade restent majoritaires, mais plusieurs interfaces chargent React à la demande via `resources/js/app.js`.

| Point de montage | Bundle chargé | Usage |
|------------------|---------------|-------|
| `[data-outline-editor]` | `resources/js/outline-editor/OutlineEditor.jsx` | Plan continu du builder de modules formateur |
| `[data-block-editor]` | `resources/js/formateur-module-builder-editor.jsx` | Édition riche du contenu d'une leçon en blocs |
| `[data-whiteboard-app]` | `resources/js/group-whiteboard-excalidraw.jsx` | Tableau blanc de groupe |
| `[data-parcours-builder]` | `resources/js/formateur-parcours-builder.jsx` | Assemblage de parcours formateur |

Le nouvel éditeur de plan du module builder utilise Tiptap/ProseMirror avec deux noeuds métier (`chapterHeading`, `lessonItem`). Le dossier `resources/js/outline-editor/` contient :
- `OutlineEditor.jsx` : montage React et configuration Tiptap ;
- `outline-keymap.js` : raccourcis clavier (`Entrée`, `Maj+Entrée`, `Alt+↑/↓`) ;
- `reconcile.js` et `sync-queue.js` : création/renommage avec debounce et file de synchronisation ;
- `api.js` : appels JSON vers `ModuleBuilderController`.

Le contenu détaillé des leçons reste séparé dans la page `resources/views/formateur/modules-builder/lecture-edit.blade.php`, qui monte l'éditeur de blocs existant.

---

## Middleware applicatif

| Middleware | Classe | Rôle |
|-----------|--------|------|
| `role` | `Role` | Vérifie `user.role` + `user.status = actif` |
| `association.member` | `EnsureAssociationMembership` | Vérifie l'adhésion formateur (active ou grâce d'un mois) |
| `track.time` | `TrackSessionTime` | Enregistre le temps de connexion stagiaire dans `users.total_site_time` |
| `force.password.change` | `ForcePasswordChange` | Bloque l'espace stagiaire jusqu'au changement de mot de passe initial |
| `admin.activity` | `RecordAdminActivity` | Journalise les actions POST/PUT/PATCH/DELETE admin |

---

## Pattern d'outils interactifs

La plupart des outils d'animation (Quiz live, Nuage de mots, Sondage, Échelle, Mur de questions, etc.) suivent le même patron de conception :

1. **Deux tables** : une table de session (`*_sessions`) avec `group_id`, `formateur_id`, `is_active`, `access_code` ; une table de réponses (`*_responses`) avec `user_id`, `session_id`
2. **Contrôleur formateur** dans `Formateur/` — CRUD + lancement/fermeture + endpoint JSON résultats
3. **Contrôleur stagiaire** dans `Stagiaire/` — affichage formulaire + soumission réponse
4. **Temps réel** : polling AJAX toutes les 2–3 secondes via Alpine.js ou fetch côté React selon l'écran (pas de WebSockets)
5. **Accès** : vérifié via `$group->students()->where('users.id', auth()->id())->exists()`

Exceptions notables :
- le tableau blanc utilise Excalidraw et persiste des éléments/snapshots de groupe ;
- le minuteur est unique par groupe (`GroupTimer`) ;
- les pages collaboratives ouvrent une instance HedgeDoc externe configurée par `HEDGEDOC_BASE_URL`.

Le Minuteur sert de pilote pour une structure par domaine (`app/Domains/Outils/<Outil>/` : contrôleurs, garde d'accès, routes et un `ServiceProvider` dédié), activable/désactivable indépendamment via `config/outils.php`, sans toucher au reste de l'application. Ce pattern est évalué comme modèle avant généralisation aux 6 autres outils.

---

## Configuration de l'apprentissage

Le fichier `config/learning_assets.php` centralise les chemins de stockage SCORM (mode legacy et moderne). Les packages importés sont stockés hors git (`storage/` et `public/modules/`).

---

## Tests

```
tests/
├── Feature/           ← Tests d'intégration (authentification, routes, contrôleurs)
└── Unit/              ← Tests unitaires
```

Framework : **Pest** (surcouche expressive de PHPUnit).

Commandes :
```bash
php artisan test                                      # Tous les tests
./vendor/bin/pest tests/Feature/SomeTest.php          # Fichier spécifique
php artisan test --filter NomDuTest                   # Filtre par nom
```

Dernière validation documentée le 5 juillet 2026 :

```bash
php artisan test
# 103 tests passés, 1 échec, 505 assertions
# Échec : Tests\Feature\Formateur\ModuleBuilderTest attend un champ JSON `path`
# après upload image, alors que le contrôleur retourne `media_id` et `url`.

npm run build
# Build réussi, avec avertissements de taille de chunks Vite
```

Les factories formateur initialisent désormais une adhésion active par défaut, ce qui évite les faux échecs liés au middleware `association.member`. Le module builder formateur est couvert par `tests/Feature/Formateur/ModuleBuilderTest.php` pour la création, la duplication, le réordonnancement, le déplacement de leçons, la promotion d'une leçon vide en chapitre et les contrôles d'accès.

---

## Schéma et migrations

Les migrations historiques ont été élaguées après génération d'une baseline. L'état de référence est :

```text
database/schema/mysql-schema.sql   # 72 tables
database/migrations/               # 5 migrations post-baseline
```

Les cinq migrations actuelles :
- suppriment `contacts` si la table legacy est vide ;
- suppriment `learning_objectives` si la table legacy est vide ;
- ajoutent `position` à `module_sections`.
- créent la table `media` de Spatie Media Library ;
- créent `content_block_scorm_results` et `content_block_scorm_scores` pour les blocs SCORM intégrés aux leçons.

```bash
php artisan migrate          # Appliquer les migrations en attente
php artisan migrate:status   # État de toutes les migrations
php artisan migrate:fresh    # Remettre à zéro (détruit les données !)
```

La baseline fournie est MySQL. Pour utiliser SQLite en développement, il faut générer une baseline SQLite ou restaurer une série complète de migrations compatible.

---

[Retour au wiki](README.md)
