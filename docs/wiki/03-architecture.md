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
├── scorm.php         ← API SCORM (middleware partiel, sans CSRF)
└── feedback.php      ← retours stagiaires
```

---

## Routing multi-rôle

| Espace | Préfixe URL | Middleware | Namespace contrôleur |
|--------|-------------|------------|----------------------|
| Admin | `/admin` | `auth, role:admin, admin.activity` | `App\Http\Controllers\` (sous-dossier `Backend/`) |
| Formateur | `/formateur` | `auth, role:formateur, association.member` | `App\Http\Controllers\Formateur\` |
| Stagiaire | `/stagiaire` | `auth, role:stagiaire, track.time` | `App\Http\Controllers\Stagiaire\` |
| Observateur | `/observateur` | `auth, role:observateur` | `App\Http\Controllers\Observateur\` |

Le middleware `force.password.change` est appliqué comme groupe interne dans les espaces stagiaire et formateur. Il bloque l'accès jusqu'à ce que l'utilisateur définisse son mot de passe à la première connexion (`users.password_changed_at` nul).

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

## Modèles Eloquent (58 modèles)

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
| `track.time` | `TrackLearningTime` | Enregistre le temps de connexion stagiaire |
| `force.password.change` | `ForcePasswordChange` | Bloque jusqu'au changement de mot de passe initial |
| `admin.activity` | `RecordAdminActivity` | Journalise les actions POST/PUT/PATCH/DELETE admin |

---

## Pattern d'outils interactifs

Tous les outils d'animation (Quiz live, Nuage de mots, Sondage, etc.) suivent le même patron de conception :

1. **Deux tables** : une table de session (`*_sessions`) avec `group_id`, `formateur_id`, `is_active`, `access_code` ; une table de réponses (`*_responses`) avec `user_id`, `session_id`
2. **Contrôleur formateur** dans `Formateur/` — CRUD + lancement/fermeture + endpoint JSON résultats
3. **Contrôleur stagiaire** dans `Stagiaire/` — affichage formulaire + soumission réponse
4. **Temps réel** : polling AJAX toutes les 2–3 secondes via Alpine.js `setInterval` (pas de WebSockets)
5. **Accès** : vérifié via `$group->students()->where('users.id', auth()->id())->exists()`

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

État actuel : **40 tests en échec sur 85** principalement à cause du middleware `association.member` qui n'est pas satisfait dans les factories par défaut. Voir [Roadmap](11-roadmap.md) pour le plan de correction.

---

## Migrations

102 migrations tracent l'évolution complète du schéma depuis la création du projet. Elles sont nommées chronologiquement et documentent chaque évolution du modèle de données.

```bash
php artisan migrate          # Appliquer les migrations en attente
php artisan migrate:status   # État de toutes les migrations
php artisan migrate:fresh    # Remettre à zéro (détruit les données !)
```

---

[Retour au wiki](README.md)
