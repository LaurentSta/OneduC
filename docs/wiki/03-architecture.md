# 03 — Architecture technique

*Public : développeurs.*

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

Au 14 juillet 2026, le dépôt expose 513 routes déclarées via `php artisan route:list --json`, dont 135 sous `/admin`, 208 sous `/formateur`, 53 sous `/stagiaire` et 9 sous `/observateur`.

---

## Structure des contrôleurs

```
app/Http/Controllers/
├── AdminController.php              ← Dashboard admin
├── UtilisateurController.php        ← Gestion unifiée admin des formateurs et stagiaires
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
│   ├── GroupeController.php         ← CRUD groupes admin et rattachement des stagiaires
│   ├── StagiaireController.php      ← Remise à zéro transactionnelle de la progression
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

### Gestion administrateur des formateurs et stagiaires

`UtilisateurController` centralise le répertoire, la création, la modification et l'activation des comptes `formateur` et `stagiaire`. Les administrateurs et observateurs restent gérés hors de ce contrôleur. Le répertoire est filtrable par rôle, statut, rattachement à un groupe et recherche textuelle ; il propose les tris récent, nom et ancien, avec une pagination serveur de 20, 50 ou 100 lignes.

Les routes correspondantes sont regroupées sous `admin.utilisateurs.*` :

| Action | Méthode et URL | Route nommée |
|--------|----------------|--------------|
| Répertoire | `GET /admin/utilisateurs` | `admin.utilisateurs.index` |
| Formulaire de création | `GET /admin/utilisateurs/create` | `admin.utilisateurs.create` |
| Création | `POST /admin/utilisateurs` | `admin.utilisateurs.store` |
| Formulaire de modification | `GET /admin/utilisateurs/{utilisateur}/edit` | `admin.utilisateurs.edit` |
| Modification | `PUT /admin/utilisateurs/{utilisateur}` | `admin.utilisateurs.update` |
| Activation ou désactivation | `PATCH /admin/utilisateurs/{utilisateur}/statut` | `admin.utilisateurs.statut.update` |

Le rôle est choisi à la création parmi `formateur` et `stagiaire`, puis devient immuable : la mise à jour se fonde sur le rôle déjà enregistré, sans accepter de changement de rôle dans la requête. Pour un formateur, le contrôleur administre notamment l'entreprise et l'adhésion. Pour un stagiaire, il synchronise les groupes avec `group_user.role_in_group = 'stagiaire'`, conserve un formateur principal dans `users.formateur_id` et génère un code d'accès unique de six caractères si aucun code n'est fourni. Les emails sont uniques à la création comme à la modification, avec exclusion du compte courant lors d'une mise à jour.

Les suppressions restent portées par les routes historiques `admin.formateurs.destroy` et `admin.stagiaires.destroy`. Elles déclenchent les événements destructifs du modèle `User` décrits dans [Sécurité & RGPD](10-securite-rgpd.md).

Le CRUD groupes admin est porté par `Backend\GroupeController`. La création et la modification sont transactionnelles, exigent un formateur principal existant dont le rôle vaut `formateur`, et n'acceptent comme membres que des comptes existants dont le rôle vaut `stagiaire`. Les comptes supprimés logiquement sont exclus par la validation. La synchronisation écrit explicitement le rôle `stagiaire` dans le pivot ; la suppression du groupe est réversible depuis le 14 juillet 2026 (`Group` utilise `SoftDeletes`, voir [10-securite-rgpd.md](10-securite-rgpd.md)).

La remise à zéro de progression est réservée aux comptes `stagiaire` et s'exécute dans une transaction. Elle efface les tentatives de quiz, les progressions, les suivis vidéo, les données SCORM classiques, d'évaluation et de blocs de contenu modernes (`content_block_scorm_*`), puis remet `total_site_time` à zéro. Une erreur provoque le rollback de l'ensemble.

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
│   │   ├── GenererLeconIA.php
│   │   ├── GenererStructureFormationIA.php
│   │   ├── GenererAudioLecon.php
│   │   ├── ImporterImagesDocument.php
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
│       ├── NettoyeurBlocsModule.php
│       ├── ExtracteurTexteDocument.php
│       ├── MistralClient.php
│       ├── PiperTtsClient.php
│       ├── GardeFouPromptIA.php
│       └── LimiteurGenerationIA.php
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
| `admin.activity` | `RecordAdminActivity` | Journalise les actions POST/PUT/PATCH/DELETE admin réussies en excluant notamment les champs nominatifs, de contact, de mot de passe et de code d'accès des formulaires utilisateurs |

La connexion standard par email ajoute `status = true` aux identifiants transmis à `Auth::attempt()` dans `LoginRequest`. Un compte inactif est donc refusé avant l'ouverture de la session, même si son mot de passe est correct ; le contrôle du middleware `role` reste une seconde barrière sur les espaces protégés.

---

## Pattern d'outils interactifs

La plupart des outils d'animation (Quiz live, Nuage de mots, Sondage, Vrai/Faux, Buzzer Quiz, Échelle, Zone de clic, Pendu, Mémoire, Mur de questions, etc.) suivent le même patron de conception :

1. **Deux tables** : une table de session (`*_sessions`) avec `group_id`, `formateur_id`, `is_active`, `access_code` ; une table de réponses (`*_responses`) avec `user_id`, `session_id`
2. **Contrôleur formateur** dans `Formateur/` — CRUD + lancement/fermeture + endpoint JSON résultats
3. **Contrôleur stagiaire** dans `Stagiaire/` — affichage formulaire + soumission réponse
4. **Temps réel** : polling AJAX toutes les 2–3 secondes via Alpine.js ou fetch côté React selon l'écran (pas de WebSockets)
5. **Accès** : vérifié via `$group->students()->where('users.id', auth()->id())->exists()`

Exceptions notables :
- le Pendu et le Jeu de mémoire n'utilisent aucun modèle Eloquent : chaque domaine contient ses dépôts Query Builder, sa logique métier, ses contrôleurs, ses routes, ses vues namespacées, sa configuration et ses migrations ;
- le Buzzer Quiz ajoute `buzzer_questions`, `buzzer_attempts` et `buzzer_participants` pour gérer le buzz, le verdict et le classement ;
- Zone de clic stocke l'image et les zones dans `component_finder_sessions`, puis les scores dans `component_finder_attempts` ;
- le tableau blanc utilise Excalidraw et persiste des éléments/snapshots de groupe ;
- le minuteur est unique par groupe (`GroupTimer`) ;
- les pages collaboratives ouvrent une instance HedgeDoc externe configurée par `HEDGEDOC_BASE_URL`.

Le Minuteur sert de pilote pour une structure par domaine. Pendu et Mémoire appliquent la version autonome complète : `PenduServiceProvider` et `MemoireServiceProvider` chargent conditionnellement routes, migrations, vues et composers de dashboard. Les variables `OUTILS_PENDU_ENABLED` et `OUTILS_MEMOIRE_ENABLED` peuvent retirer chaque outil sans requête résiduelle dans les contrôleurs communs. Les hubs possèdent un point d'extension générique, ce qui évite d'ajouter les noms ou modèles de ces outils à `OutilsNumeriquesController` et `StagiaireController`.

### Contrat d'un outil autonome

Pendu et Mémoire regroupent leur implémentation dans un seul domaine :

```text
app/Domains/Outils/<Outil>/
├── Http/Controllers/Formateur/
├── Http/Controllers/Stagiaire/
├── Support/
├── database/migrations/
├── resources/views/
├── config.php
├── routes.php
└── <Outil>ServiceProvider.php
```

Le provider constitue le point d'entrée du domaine. Quand sa clé `enabled` vaut `false`, son démarrage s'arrête avant le chargement des routes, migrations, vues et composers. Quand l'outil est actif, ses composers alimentent les points d'extension génériques des hubs formateur et stagiaire. Les deux seuls raccords avec le socle sont donc l'enregistrement du provider dans `bootstrap/providers.php` et les collections génériques rendues par les vues de dashboard.

Les accès aux données de ces deux domaines utilisent exclusivement `DB` et `Schema` (Query Builder). Aucun modèle Eloquent ni relation ajoutée aux modèles communs n'est nécessaire. La suppression d'un domaine ou sa désactivation ne doit donc pas imposer de modification aux contrôleurs centraux ni aux autres outils.

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
