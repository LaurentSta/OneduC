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
└── feedback.php      ← retours stagiaires (fichier présent mais non importé dans bootstrap/app.php)
```

---

## Routing multi-rôle

| Espace | Préfixe URL | Middleware | Namespace contrôleur |
|--------|-------------|------------|----------------------|
| Admin | `/admin` | `auth, role:admin, admin.activity` | `App\Http\Controllers\` (sous-dossier `Backend/`) |
| Formateur | `/formateur` | `auth, role:formateur, association.member` | `App\Http\Controllers\Formateur\` |
| Stagiaire | `/stagiaire` | `auth, role:stagiaire, track.time`, puis `force.password.change` sauf première connexion | `App\Http\Controllers\Stagiaire\` |
| Observateur | `/observateur` | `auth, role:observateur` | `App\Http\Controllers\Observateur\` |

Le middleware `force.password.change` est appliqué dans l'espace stagiaire après les routes de première connexion. Il bloque l'accès jusqu'à ce que l'utilisateur définisse son mot de passe (`users.password_changed_at` nul). Le formateur n'a pas ce middleware dans `routes/formateur.php` au 3 juillet 2026.

Le dépôt expose environ 400 routes : 402 routes déclarées via `php artisan route:list --json`, dont 128 sous `/admin`, 137 sous `/formateur`, 49 sous `/stagiaire` et 9 sous `/observateur`.

---

## Structure des contrôleurs

```
app/Http/Controllers/
├── AdminController.php              ← Dashboard admin
├── FormateurController.php          ← Dashboard formateur (analytics)
├── StagiaireController.php          ← Dashboard stagiaire
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
│   ├── ModuleBuilderController.php  ← Création/édition de modules formateur (546 lignes)
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

## Modèles Eloquent (59 modèles)

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

État vérifié le 3 juillet 2026 :

```bash
php artisan test
# 102 tests passés, 501 assertions

npm run build
# Build réussi, avec avertissements de taille de chunks Vite
```

Les factories formateur initialisent désormais une adhésion active par défaut, ce qui évite les faux échecs liés au middleware `association.member`.

---

## Schéma et migrations

Les migrations historiques ont été élaguées après génération d'une baseline. L'état de référence est :

```text
database/schema/mysql-schema.sql   # 72 tables
database/migrations/               # 3 migrations post-baseline
```

Les trois migrations actuelles :
- suppriment `contacts` si la table legacy est vide ;
- suppriment `learning_objectives` si la table legacy est vide ;
- ajoutent `position` à `module_sections`.

```bash
php artisan migrate          # Appliquer les migrations en attente
php artisan migrate:status   # État de toutes les migrations
php artisan migrate:fresh    # Remettre à zéro (détruit les données !)
```

La baseline fournie est MySQL. Pour utiliser SQLite en développement, il faut générer une baseline SQLite ou restaurer une série complète de migrations compatible.

---

[Retour au wiki](README.md)
