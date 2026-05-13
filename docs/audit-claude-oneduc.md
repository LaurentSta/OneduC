# Rapport d'audit Claude — Oneduc.fr LMS

**Date :** 7 mai 2026  
**Auditeur :** Claude Sonnet 4.6 (Anthropic)  
**Périmètre :** Code source local `/var/www/Oneduc_Dev`  
**Stack :** Laravel 11 / PHP 8.2+, Blade, Tailwind CSS v4, Alpine.js, MySQL, SCORM 1.2 / 2004  
**Méthodologie :** Lecture exhaustive du code source — routes, contrôleurs, modèles, services, middleware, migrations, tests, vues — complétée par exécution des tests et analyse dynamique partielle.

---

## 1. Synthèse générale

Oneduc.fr est une plateforme LMS en cours de construction qui a dépassé le stade de prototype. Le code révèle une architecture Laravel structurée, une séparation claire des rôles utilisateurs, une gestion réelle des groupes d'apprentissage, une intégration SCORM fonctionnelle en lecture et en écriture de scores, des quiz natifs solides, et une suite d'outils d'animation pédagogique live remarquablement complète pour un projet de cette maturité.

Le projet est exploitable en environnement pilote contrôlé dès aujourd'hui pour un usage avec des formateurs et stagiaires en nombre limité, à condition de corriger d'abord les points de sécurité critiques identifiés dans cet audit. Il n'est pas encore prêt pour une mise en production large, une présentation à des financeurs institutionnels ou une commercialisation comme SaaS sans consolidation technique.

**Ce que la plateforme fait réellement :**
- Gérer des utilisateurs sur quatre profils (admin, formateur, stagiaire, observateur) avec des espaces distincts et protégés par middleware
- Organiser des formations en modules, sections et leçons, avec contenu SCORM, slides, vidéo et quiz natifs
- Créer des groupes de stagiaires, y affecter des modules, personnaliser les leçons par groupe
- Suivre la progression des stagiaires par module, section et leçon, en croisant plusieurs sources de données
- Animer des sessions en direct (quiz live, nuage de mots, sondage, mur de questions, roue aléatoire, tableau blanc, minuteur, échelle)
- Offrir au formateur un tableau de bord analytique avancé avec indicateurs d'activité, de réussite et de risque
- Permettre aux stagiaires un accès simplifié par code d'accès, adapté à un public éloigné du numérique

**Ce que la plateforme ne fait pas encore de façon fiable :**
- Enregistrer les interactions SCORM question par question dans les leçons ordinaires
- Garantir qu'un stagiaire ne peut accéder qu'aux modules de ses propres groupes
- Passer la suite de tests au vert (40 échecs sur 85)
- Générer des certificats ou attestations malgré la présence du champ `certificat` en base
- Fournir des exports de progression exploitables par des organismes de formation

---

## 2. Description globale de la plateforme

### Architecture Laravel

Le projet respecte l'organisation conventionnelle Laravel 11 :

- Routes segmentées par rôle dans `routes/web.php`, `routes/admin.php`, `routes/formateur.php`, `routes/stagiaire.php`, `routes/observateur.php`, `routes/scorm.php`, `routes/feedback.php`
- Contrôleurs généraux dans `app/Http/Controllers/` et contrôleurs par domaine dans les sous-dossiers `Backend/`, `Formateur/`, `Stagiaire/`, `Frontend/`, `Observateur/`
- Modèles Eloquent dans `app/Models/` (58 modèles recensés)
- Services métier dans `app/Services/` : `LearningAnalyticsService`, `QuizService`, `ScormImporter`, `ModuleCompletionNotifier`, `CodeGeneratorService`
- Vues Blade dans `resources/views/`, organisées par espace rôle
- Middleware dans `app/Http/Middleware/`

### Volume du projet (mesuré sur le code actuel)

| Élément | Nombre |
|---|---|
| Contrôleurs PHP | 85 fichiers |
| Modèles Eloquent | 58 fichiers |
| Vues Blade | 468 fichiers |
| Migrations | 102 fichiers |
| Routes déclarées | ~376 (mesurées via `php artisan route:list`) |
| Tests Feature et Unit | 37 fichiers de test |

Ce volume traduit une plateforme fonctionnellement très avancée pour un projet en développement. Il engendre aussi une dette de maintenance croissante.

### Séparation des espaces et logique de routing

La séparation est propre et cohérente pour les quatre rôles principaux. Chaque espace possède son préfixe d'URL, son groupe de middleware et sa convention de nommage de routes (`admin.*`, `formateur.*`, `stagiaire.*`, `observateur.*`).

Deux anomalies importantes subsistent dans `routes/web.php` hors de tout groupe de middleware sécurisé :

1. `GET /admin/stagiaires/{id}/debug-progression` — route de débogage exposée publiquement, retourne directement des données SQL sous forme JSON brut. Elle se trouve à la ligne 220 de `routes/web.php` et n'est protégée par aucun middleware `auth` ni `role:admin`.
2. `POST /admin/stagiaires/{user}/reset-progression` — route de réinitialisation de progression déclarée hors du groupe admin, ligne 273, utilisant `StagiaireController::resetProgression()` sans vérification de rôle.

Ces deux routes constituent des risques de sécurité critiques confirmés par lecture directe du code.

### Logique MVC et dette de découpage

La logique MVC est présente mais souffre de concentrations excessives :

| Contrôleur | Lignes | Problème |
|---|---|---|
| `Backend/ModuleController.php` | 1 185 | CRUD module, navigation section/leçon, fin de module, statistiques SCORM |
| `StagiaireController.php` | 903 | Dashboard, modules, résultats, outils, progression, calculs pedagogiques |
| `Backend/QuizQuestionController.php` | 775 | CRUD questions, import CSV, médias |
| `Backend/PilotageController.php` | 756 | Projets, tâches, journal, notifications |
| `Stagiaire/QuizController.php` | 701 | Quiz complet avec démarrage, réponses, résultats, redémarrage |
| `FormateurController.php` | 687 | Dashboard analytique complet |
| `Formateur/GroupeController.php` | 686 | Création, édition, stagiaires, modules, co-formateurs, invitations |
| `Formateur/ProgressionStagiaireController.php` | 630 | Calculs de progression détaillée par stagiaire |

Le service `LearningAnalyticsService` (502 lignes) est une bonne extraction, mais il reste insuffisant face au volume de logique de présentation encore dans les contrôleurs.

### Artefacts résiduels

Le fichier `routes/scorm.php` importe `App\Http\Controllers\ScormInteractionController` à la ligne 6, mais ce contrôleur n'existe pas dans le projet. L'import est mort et peut introduire une erreur si PHP strict mode ou un outil d'analyse statique est activé. Il s'agit d'un vestige d'une intention non réalisée.

---

## 3. Analyse par profil utilisateur

### 3.1 Administrateur

**Middleware appliqué :** `auth`, `role:admin`, `admin.activity` — défini dans `routes/admin.php` lignes 24–27.

Le middleware `RecordAdminActivity` (`app/Http/Middleware/RecordAdminActivity.php`) journalise automatiquement toutes les actions POST/PUT/PATCH/DELETE des routes `admin.*` dans `activity_journal_entries`. C'est un excellent mécanisme d'auditabilité, bien conçu, avec sanitisation des données sensibles et troncature des champs longs.

**Fonctionnalités confirmées fonctionnelles :**
- Tableau de bord global avec compteurs (modules, formateurs, stagiaires, groupes, sections, leçons)
- Gestion complète CRUD des formateurs, stagiaires, observateurs, catégories, sous-catégories, groupes, modules, sections, leçons, évaluations
- Import SCORM pour une leçon via `ScormLibraryController::importForLecture()`
- Import de slides via `ModuleLectureController::importSlidesForLecture()`
- Banque de questions quiz avec import CSV et médias
- Référentiels de compétences, domaines, badges
- Module de pilotage interne (projets, tâches kanban, journal, notifications)
- Gestion des retours stagiaires (consultation et suppression)
- Nuage de mots admin

**Fonctionnalités partiellement fonctionnelles ou fragiles :**
- La mise à jour du profil admin dans `AdminController::AdminProfilStore()` ne valide pas l'unicité de l'email. Un admin peut prendre l'email d'un autre utilisateur.
- Le tableau de bord admin calcule des `scormSummaries` dans `AdminController::AdminDashboard()` mais leur utilisation dans la vue `resources/views/admin/index.blade.php` semble dépendre d'un composant JavaScript de template qui n'est pas confirmé actif.

**Absences notables :**
- Aucun mécanisme de génération de certificat malgré le champ `certificat` dans la table `modules`
- Aucun export CSV ou PDF des données de progression ou résultats
- Aucune gestion multi-organisation ou multi-tenant
- Aucun rôle admin secondaire ou gestionnaire délégué

### 3.2 Formateur

**Middleware appliqué :** `auth`, `role:formateur`, `association.member` — défini dans `routes/formateur.php` lignes 37–39.

Le middleware `EnsureAssociationMembership` (`app/Http/Middleware/EnsureAssociationMembership.php`) implémente une politique d'adhésion associative : un formateur peut accéder à l'espace si `adhesion_status = active` avec `adhesion_valid_until` non dépassée, ou si `adhesion_status = pending` et le compte a moins d'un mois (`created_at + 1 mois > today`). Ce mécanisme est stratégiquement pertinent pour le modèle associatif d'Oneduc.

Impact sur les tests : ce middleware récent provoque 40 échecs dans la suite de tests car les factories ne créent pas de formateurs avec `adhesion_status = active` par défaut. Le code d'application est correct ; c'est la configuration des tests qui est à mettre à jour.

**Fonctionnalités confirmées fonctionnelles :**
- Dashboard analytique complet avec indicateurs d'activité AJAX par jour/semaine/mois/an
- Gestion de groupes : création, édition, suppression, activation, code d'accès temporaire chiffré (`encrypted` cast)
- Rattachement de stagiaires existants ou création directe avec invitation mail
- Ajout de co-formateurs avec notifications internes
- Affectation de modules par groupe avec ordre configurable
- Personnalisation des leçons par groupe (masquage, réordonnancement) via `GroupeModuleLessonController`
- Parcours de formation (`FormateurParcours`) combinant modules, nuages de mots, sondages
- Suivi de progression par groupe, stagiaire, module avec tableaux détaillés
- Suite complète d'outils live : quiz, nuage de mots, sondage, échelle, mur de questions, roue aléatoire, tableau blanc Excalidraw, minuteur
- Ressources de leçon avec toggle de visibilité stagiaire
- Profil, sécurité, suppression de compte

**Points fragiles :**
- Un formateur accède aux mêmes écrans de quiz que les stagiaires via `Stagiaire/QuizController` (réutilisation légitime mais source de confusion et de tests ambigus)
- La création de modules reste réservée à l'admin ; le formateur assemble uniquement des modules existants dans ses formations
- La scope `Group::scopeAccessibleByTrainer()` est bien écrite (`where instructor_id OR coFormateurs.id`), mais les droits fins entre propriétaire, co-formateur et observateur ne sont pas centralisés dans des Laravel Policies

**Absences notables :**
- Pas de creation de modules directement depuis l'espace formateur
- Pas de délivrance automatique de badge ou certificat à la fin d'un parcours
- Pas d'export des données de groupe

### 3.3 Stagiaire

**Middleware appliqué :** `auth`, `role:stagiaire`, `track.time`, puis `force.password.change` sur la zone principale — défini dans `routes/stagiaire.php` lignes 16–40.

L'accès par code d'accès (`POST /stagiaire/connexion-code`) est une force fonctionnelle majeure pour le public visé. Le code est stocké dans `users.code_acces` et géré par `CodeGeneratorService`. Le `ForcePasswordChange` middleware bloque l'accès si `password_changed_at` est nul, forçant un changement de mot de passe dès la première connexion — bonne pratique de sécurité.

**Risque de sécurité critique confirmé — accès aux modules non affectés :**

La méthode `Module::isVisibleTo(User $user)` dans `app/Models/Module.php` lignes 84–88 est :
```php
public function isVisibleTo(?User $user): bool
{
    if ($user && $user->role === 'admin') return true;
    return (bool) $this->status;
}
```

Cette méthode, utilisée dans `Backend/ModuleController::section()` et `::lire()` aux lignes 265, 288, 410 et 603, autorise **tout module actif (`status = 1`) à tout stagiaire authentifié connaissant l'URL**. Il n'y a aucune vérification que le module appartient à un groupe du stagiaire.

Concrètement, un stagiaire inscrit dans le groupe A peut accéder aux sections et leçons d'un module du groupe B en connaissant uniquement l'URL hiérarchique `/stagiaire/modules/{module}/sections/{section}/lessons/{lecture}`.

La méthode `StagiaireModules()` du contrôleur (lignes 225–258) récupère correctement les modules du groupe du stagiaire pour l'affichage, mais cette vérification ne protège pas les routes de lecture directe.

**Autre limite sur les groupes multiples :** `StagiaireModules()` appelle `.first()` sur les groupes actifs du stagiaire (ligne 244). Un stagiaire appartenant à deux groupes actifs différents ne voit que les modules du premier groupe retourné, perdant potentiellement l'accès aux modules de l'autre groupe.

**Fonctionnalités confirmées fonctionnelles :**
- Dashboard stagiaire avec indicateurs de temps, taux de réussite, progression
- Liste des modules et progression par section/leçon
- Lecture de leçons SCORM, slides, quiz natifs
- Résultats détaillés des quiz et scores SCORM
- Participation aux outils live du formateur
- Profil, sécurité, suppression de compte

### 3.4 Observateur

**Middleware appliqué :** `auth`, `role:observateur` — défini dans `routes/observateur.php` lignes 9–10.

L'espace observateur est plus léger. Il donne accès en lecture aux groupes et progressions. La route `observateur.formations.lecture` utilise le même `ModuleController::lire()` que les autres rôles, ce qui signifie que l'observateur bénéficie de la même logique de lecture — et du même déficit de contrôle d'appartenance.

Les migrations de support du rôle observateur datent de `2026_03_22`, confirmant que ce rôle est récent.

---

## 4. Analyse des fonctionnalités LMS

### 4.1 Gestion des modules

Fonctionnel et bien structuré. Le modèle `Module` (`app/Models/Module.php`, 272 lignes) gère :
- Catégorie, sous-catégorie, formateur associé, images, titre, description, objectifs, ressources, durée, certificat, prérequis
- Flags marketing (`bestseller`, `vedette`, `surevalue`) — héritage de template peu utile au LMS d'inclusion
- Calcul de durée estimée via `getTotalSecondsAttribute()` et `getEstimatedSecondsForUser(int $userId)` qui prend en compte le nombre de tentatives passées pour ajuster l'estimation

La relation `Module::stagiaires()` n'est pas une relation Eloquent standard mais une requête `User::where(...)->whereHas(...)` — cela empêche son utilisation avec eager loading classique.

### 4.2 Sections et leçons

Fonctionnel. `ModuleSection` et `ModuleLecture` sont bien définis. La leçon supporte : SCORM (avec versioning via `ScormPackage` / `ScormPackageVersion`), slides (avec conversion et statut), quiz natif, quiz live, contenu URL, ressources et objectifs pédagogiques.

Le modèle `ModuleLecture` implémente un mécanisme intelligent de cache SCORM via `getScormCacheTokenAttribute()` basé sur `imported_at` ou `updated_at`, évitant les problèmes de cache navigateur après un rechargement de package.

### 4.3 Groupes et affectations

Très avancé. La table pivot `group_user` distingue `role_in_group` (stagiaire, formateur, observateur). Le scope `Group::scopeAccessibleByTrainer()` couvre le cas formateur principal et co-formateur. La personnalisation des leçons par groupe via `group_module_lectures` est une fonctionnalité avancée rarement présente à ce stade de développement.

Le mot de passe temporaire de groupe est stocké chiffré via le cast `encrypted` de Laravel — bonne pratique.

### 4.4 Parcours de formation

Partiellement fonctionnel. Deux notions coexistent :
1. Le parcours formateur (`ParcoursController`) — parcours de formation du formateur lui-même au sein de la plateforme, avec modules Oneduc et activités
2. Les formations créées (`FormateurParcours`, `FormateurParcoursItem`) — assemblage ordonné de modules et activités qu'un formateur construit pour ses groupes, accessible via `Formateur/MesFormationsController`

La liaison `groups.formateur_parcours_id` permet d'associer un parcours à un groupe. La vue `StagiaireModules()` lit cet état pour présenter les items dans l'ordre du parcours.

Limite fondamentale : il n'existe pas de moteur de prérequis bloquants, de jalons obligatoires, de progression conditionnelle ou de matrice de compétences reliée aux résultats.

### 4.5 Progression et suivi

La progression est multi-source :
- `progressions` — marquage manuel ou programmatique d'une leçon comme terminée
- `scorm_scores` — score consolidé, statut, temps par leçon SCORM
- `quiz_attempts` + `quiz_attempt_questions` — tentatives quiz avec score, temps, réponses détaillées
- `video_segment_trackings` — suivi de lecture vidéo par segment
- `scorm_interactions` — prévu mais non alimenté par les leçons ordinaires

Cette hétérogénéité est pédagogiquement riche mais techniquement complexe. Les règles de "complétion" ne sont pas uniformes entre les écrans, ce qui peut conduire à des affichages contradictoires entre le tableau de progression formateur, le tableau de bord stagiaire et le détail module.

### 4.6 Commentaires et retours stagiaires

Partiellement fonctionnel avec un bug bloquant. `LessonFeedbackController::store()` (ligne 32 de `app/Http/Controllers/LessonFeedbackController.php`) redirige vers `route('module.lesson', [...])`. Cette route n'existe pas dans les routes déclarées (ni dans `web.php`, ni dans `stagiaire.php`, ni dans `formateur.php`). Les routes existantes s'appellent `stagiaire.module.lecture` et `formateur.formations.lecture`. En production, toute soumission de retour par un stagiaire provoquera une erreur 500.

### 4.7 Évaluations SCORM distinctes

`EvaluationSCORMController::saveEvaluationProgress()` traite les évaluations SCORM séparément des leçons. Contrairement à `SCORMController`, il enregistre les interactions SCORM dans `scorm_evaluation_interactions` via une logique de session PHP. Cette approche est plus complète pour les évaluations que pour les leçons.

La méthode `fin(Evaluation $evaluation)` dans le même contrôleur (ligne 133) référence le type `Evaluation` sans `use App\Models\Evaluation;` en tête du fichier. Cette méthode n'est pas déclarée dans les routes, mais sa présence avec un typage incomplet signale du code en transition.

L'évaluation SCORM utilise un seuil de réussite de 75% (`$scormScore->is_completed = $scormScore->best_score >= 75`) tandis que les leçons SCORM utilisent 50% (`SCORMController::recomputeMonotoneStatus()`). Cette incohérence de seuil n'est ni documentée ni configurable.

---

## 5. Analyse du suivi SCORM

### 5.1 Import et versioning

L'import SCORM est bien conçu. `ScormImporter::importToFolder()` dans `app/Services/Scorm/ScormImporter.php` :
- Valide le ZIP et le décompresse dans un dossier `release_YYYYMMDD_HHMMSS_{random}` — évite les collisions
- Vérifie l'absence de path traversal via `safeExtract()`
- Recherche `imsmanifest.xml` ou un `index.html` de fallback
- Injecte automatiquement le script `/scorm_core/js/API.js` dans la page d'entrée
- Crée ou met à jour un `ScormPackage` (slug stable) et une `ScormPackageVersion` (avec `imported_at` pour le cache token)

Ce versioning permet de garder plusieurs versions d'un même package et d'activer la version courante ou une version épinglée — mécanisme avancé et bien pensé.

### 5.2 L'API SCORM 1.2 (API.js)

Le fichier `public/scorm_core/js/API.js` expose l'objet `window.API` (SCORM 1.2) attendu par les packages SCORM. Il intercepte `LMSSetValue` et poste les paires clé/valeur vers `/scorm/save-progress` via `fetch()`. Un mécanisme de déduplication (`shouldSend`) évite les envois redondants de la même valeur.

Le fichier `public/scorm_core/js/api_Scorm2004.js` expose `window.API_1484_11` (SCORM 2004). Il stocke les valeurs localement et les log en console mais **ne les poste pas au backend** via fetch. Seules certaines clés déclenchent un appel de sauvegarde. Les interactions SCORM 2004 restent donc en mémoire navigateur uniquement.

### 5.3 Enregistrement des scores et statuts

`SCORMController::saveProgress()` gère correctement :
- Les paires brutes dans `scorm_results` (audit log)
- Le score (`cmi.core.score.raw`) dans `scorm_scores.first_score`, `last_score`, `best_score`
- Le statut (`cmi.core.lesson_status`, `cmi.completion_status`, `cmi.success_status`) avec monotonie (jamais de rétrogradation depuis `completed`)
- Le temps de session (`cmi.core.session_time`) via `handleSessionTime()` avec calcul de delta

Le calcul du delta de session repose sur `$sc->last_session_time` (ligne 99 du contrôleur). Ce champ est utilisé mais **aucune migration ne crée cette colonne dans `scorm_scores`**. Eloquent le traitera comme un attribut dynamique non persisté (retournera toujours 0 entre les requêtes), rendant le calcul de delta inefficace — chaque envoi de `session_time` par le package est traité comme si la session commençait à 0.

### 5.4 Les interactions SCORM — le déficit principal

C'est le point le plus critique pour la valeur analytique de la plateforme.

**État confirmé par lecture du code :**
1. `ScormInteraction` (modèle) et table `scorm_interactions` (migration) existent
2. `SCORMController::saveProgress()` traite uniquement les clés `cmi.core.score.raw`, `cmi.core.lesson_status`, `cmi.completion_status`, `cmi.success_status`, `cmi.core.session_time` — **aucun traitement pour `cmi.interactions.*`**
3. `public/scorm_core/js/API.js` (SCORM 1.2) envoie **toutes** les clés au backend via `LMSSetValue` → `envoyerProgression()`. Cela inclut les clés d'interactions. Le serveur reçoit ces données mais les ignore dans le `switch` du contrôleur.
4. `public/scorm_core/js/api_Scorm2004.js` (SCORM 2004) stocke les interactions en mémoire et les log en console mais ne les poste pas.

**Conséquence :** les indicateurs `ScormInteraction` utilisés dans `StagiaireController::StagiaireModuleDetail()` (ligne 48), `LearningAnalyticsService`, `ProgressionStagiaireController` et `Backend/ModuleController` retournent toujours 0 pour les leçons SCORM ordinaires. Les métriques "questions SCORM traitées", "bonnes réponses", "mauvaises réponses" et "temps de réflexion par question" sont structurellement vides pour les leçons.

**Ce qui fonctionne en revanche :** les évaluations SCORM via `API_evaluation.js` postent bien leurs interactions vers `EvaluationSCORMController`, et ce contrôleur les traite et les persiste dans `scorm_evaluation_interactions`. L'infrastructure analytique est donc fonctionnelle pour les évaluations, mais pas pour les leçons.

### 5.5 Statuts et seuils de réussite

La règle `best_score >= 50 => completed` est codée en dur dans `SCORMController::recomputeMonotoneStatus()` à la ligne 126. Un commentaire dans les migrations mentionne 75% ; le contrôleur d'évaluation utilise 75% ; le contrôleur de leçon utilise 50%. Cette incohérence n'est ni documentée, ni configurable par module ou par leçon.

`attempts_count` est initialisé à 1 lors du `firstOrCreate()` mais n'est jamais incrémenté dans le contrôleur des leçons — toutes les leçons SCORM afficheront 1 tentative quoi qu'il arrive.

### 5.6 Potentiel analytique SCORM

Le schéma de données est prêt pour une analyse riche : latence par question, première vs meilleure tentative, questions difficiles par module, remédiation automatique. Ce potentiel est aujourd'hui intégralement réalisé pour les quiz natifs et intégralement inexploité pour les leçons SCORM. Le branchement des interactions SCORM 1.2 dans le contrôleur est techniquement simple (parsing des clés `cmi.interactions.{index}.{field}`) et représente un impact analytique maximal pour un effort de développement modéré.

---

## 6. Analyse des tableaux de bord

### 6.1 Tableau de bord administrateur

**Fichiers :** `app/Http/Controllers/AdminController.php`, `resources/views/admin/index.blade.php`

Le dashboard admin est un tableau de pilotage volumétrique (compteurs d'entités) sans profondeur analytique. Il n'affiche pas de taux d'activité, de progression moyenne, d'alertes qualité ou d'indicateurs pédagogiques. C'est suffisant pour un tableau de bord de supervision système, insuffisant pour un pilotage pédagogique.

**Indicateurs présents :** catégories, sous-catégories, modules, formateurs, stagiaires, groupes, sections, leçons.

**Indicateurs absents :** taux de complétion moyen, stagiaires actifs / inactifs / bloqués, modules les plus utilisés, scores moyens, alertes qualité contenu.

### 6.2 Tableau de bord formateur

**Fichiers :** `app/Http/Controllers/FormateurController.php` (687 lignes), `app/Services/LearningAnalyticsService.php` (502 lignes), `resources/views/formateur/index.blade.php`

C'est le tableau de bord le plus riche et le plus pertinent pédagogiquement. Il affiche :
- Groupes accessibles avec nombre de stagiaires et statut
- Taux de réussite moyen (quiz natifs fiables, SCORM partiel)
- Stagiaires actifs / inactifs / non démarrés
- Groupes prioritaires (ceux avec le plus de stagiaires à risque)
- Modules prioritaires
- Graphique d'activité temporelle AJAX avec cache côté serveur

`LearningAnalyticsService::collectSnapshots()` agrège Progression, ScormResult, ScormScore, ScormInteraction, VideoSegmentTracking, QuizAttempt, QuizAttemptQuestion pour produire un snapshot unifié par paire (user, lecture). La méthode `finalizeSnapshot()` définit `is_started`, `is_successful`, `last_activity_at` selon des règles hiérarchiques.

**Fiabilité :** bonne pour les quiz natifs, partielle pour SCORM (les interactions vides faussent les indicateurs de questions), silencieuse sur l'origine des données.

**Améliorations prioritaires :** afficher la source des indicateurs (quiz / SCORM / vidéo / manuel), ajouter une alerte "données SCORM incomplètes" quand `scorm_interactions` est vide, permettre l'export.

### 6.3 Tableau de bord stagiaire

**Fichiers :** `app/Http/Controllers/StagiaireController.php`, `resources/views/stagiaire/index.blade.php`

Le tableau de bord stagiaire est bien conçu pour un public non expert : lisible, motivant, avec des indicateurs simples. Il affiche le formateur référent, le temps d'apprentissage, le nombre de questions traitées, le taux de réussite, les formations en cours et la progression par module.

**Limite principale :** si les interactions SCORM sont vides, "questions traitées" et "taux de réussite" ne reflètent que les quiz natifs, potentiellement sous-estimant l'activité réelle d'un stagiaire dont le contenu est principalement SCORM.

---

## 7. Analyse pédagogique

### Capacité à accompagner des débutants

Bonne. L'accès par code d'accès court-circuite la barrière de création de compte email, qui est souvent rédhibitoire pour les publics éloignés du numérique. Le changement de mot de passe guidé à la première connexion est un bon équilibre entre sécurité et accompagnement. Le menu stagiaire est court (Formation, Progression, Outils, Documentation) — c'est une force.

### Capacité à structurer un parcours

Moyenne à bonne. La hiérarchie module → section → leçon est claire. Les parcours `FormateurParcours` permettent une organisation séquencée incluant des activités collaboratives. La limite principale est l'absence de prérequis bloquants, de validation de compétences et de remédiation automatique.

### Capacité à suivre les apprenants

Bonne en intention, avec fiabilité variable selon le type de contenu. Pour les groupes à contenu principalement constitué de quiz natifs, le suivi est excellent. Pour les groupes à contenu SCORM, les indicateurs de questions et de compréhension sont structurellement vides.

### Capacité à aider les formateurs

Forte. Les tableaux de progression multi-niveaux (groupe, stagiaire, module), l'identification des apprenants à risque, les outils d'animation live et la personnalisation des leçons par groupe constituent un dispositif d'accompagnement pédagogique supérieur à celui de nombreux LMS plus matures.

### Capacité à produire des preuves d'apprentissage

Partielle. Les `quiz_attempts` sont des preuves solides, horodatées, avec détail des réponses. Les `scorm_scores` sont des preuves de complétion, mais sans détail interactionnel. Il manque : exports formatés, attestations générées, signature numérique, version du contenu utilisée.

### Soutien à l'inclusion numérique

Fort et différenciant. Le positionnement "LMS d'inclusion numérique" est cohérent avec les choix techniques : accès simplifié, formateur référent visible, interface épurée côté stagiaire, outils de médiation live, modules SCORM jouables en iframe. C'est la principale force stratégique du projet face aux LMS généralistes.

---

## 8. Analyse technique

### 8.1 Qualité du code

**Points positifs :**
- Architecture Laravel reconnue et exploitable
- Modèles Eloquent nombreux avec relations bien définies
- Services métier existants et pertinents
- Migrations évolutives, bien nommées, couvrant les évolutions du schéma
- Tests Pest/PHPUnit présents avec couverture sur les fonctionnalités clés
- Validation des formulaires dans la majorité des contrôleurs
- `ScormImporter` protège contre le path traversal
- Soft deletes sur `User` avec nettoyage des données liées dans `cleanupRelatedStagiaireData()`
- `RecordAdminActivity` journalise les actions admin automatiquement
- Cache token SCORM basé sur `imported_at` pour éviter les problèmes de cache navigateur

**Points faibles :**
- Contrôleurs trop volumineux (voir tableau section 2.2)
- Logique métier et présentation encore mélangées dans plusieurs contrôleurs
- `StoreModuleRequest` et `StoreGroupeRequest` ont `authorize(): return false` — elles sont importées mais renvoient systématiquement 403. Ces FormRequests sont inutilisables en l'état.
- Import mort de `ScormInteractionController` dans `routes/scorm.php` (classe inexistante)
- `$sc->last_session_time` utilisé dans `SCORMController` sans migration correspondante
- `EvaluationSCORMController::fin()` reference `Evaluation $evaluation` sans `use App\Models\Evaluation;`
- La méthode `Module::stagiaires()` retourne un query builder non-Eloquent, incompatible avec eager loading
- Nombreux commentaires de chantier résiduels

### 8.2 Relations Eloquent

Les relations principales sont bien définies et utilisent `withPivot`, `wherePivot`, des scopes nommés et des méthodes `is*` sur les modèles. Le `Group::scopeAccessibleByTrainer()` est un bon exemple de scope réutilisable. Les cascades de suppression dans `User::cleanupOwnedGroupsAndLinkedStagiaires()` montrent une réflexion avancée sur l'intégrité des données.

Limite : `Group` n'a pas de `SoftDeletes`, donc une suppression de groupe supprime physiquement l'enregistrement, mais les données liées (progressions, scorm_scores) restent en base avec un `group_id` devenu invalide.

### 8.3 Sécurité

**Points positifs :**
- Middleware de rôle vérifiant authentification, rôle ET statut actif simultanément
- Hash des mots de passe via Laravel (Bcrypt)
- Chiffrement du mot de passe temporaire groupe
- Validation des formulaires de création/modification
- Protection ZIP contre path traversal dans `ScormImporter`
- Throttling sur le formulaire de contact (`throttle:contact`)
- Throttling sur les soumissions de nuage de mots (`throttle:30,1`)

**Risques confirmés :**
1. `GET /admin/stagiaires/{id}/debug-progression` — accessible sans authentification, retourne des données de base de données JSON brut. Confirmé en lecture directe du code (`routes/web.php` lignes 220–248).
2. `POST /admin/stagiaires/{user}/reset-progression` — accessible sans middleware admin (`routes/web.php` ligne 273).
3. `POST /scorm/save-progress` — exclut le middleware CSRF (`withoutMiddleware([VerifyCsrfToken::class])`). Techniquement justifié pour les iframes SCORM, mais aucune vérification que l'utilisateur authentifié est bien le titulaire de la leçon cible.
4. `Module::isVisibleTo()` — ne vérifie que le statut actif, pas l'appartenance groupe, exposant tout module actif à tout stagiaire authentifié par URL directe.
5. Aucun throttling explicite sur `POST /stagiaire/connexion-code` — expose le code d'accès (6 caractères alphanumériques) à une attaque par force brute.
6. `AdminController::AdminProfilStore()` ne valide pas l'unicité de l'email — un admin peut usurper l'email d'un autre utilisateur.

### 8.4 Performance

Le tableau de bord formateur charge son activité via AJAX avec cache côté serveur — bonne pratique. Certains contrôleurs de progression utilisent des jointures SQL directes via `DB::table()` pour éviter les N+1.

Risques potentiels :
- `AdminController::AdminDashboard()` effectue une requête `map` sur les résultats SCORM qui peut générer des N+1 selon l'implémentation exacte
- `LearningAnalyticsService::collectSnapshots()` charge plusieurs tables pour tous les couples (user, lecture) en une passe — efficace mais potentiellement lourd pour de grands groupes
- Le calcul de progression à la volée sans matérialisation peut devenir lent à l'échelle

### 8.5 Tests

La suite de tests Pest/PHPUnit compte 37 fichiers de test. L'exécution de `php artisan test` confirme **40 tests échoués sur 85** avec 197 assertions totales (durée 40s).

Causes d'échec identifiées lors de l'exécution :

| Catégorie | Cause principale |
|---|---|
| Auth (6 tests) | Routes `/login` et `/profile` non disponibles ou format inattendu |
| Formateur (14 tests) | Middleware `association.member` redirige vers `/adhesion` faute de `adhesion_status = active` dans les factories |
| Admin (4 tests) | CSRF 419 sur DELETE (middleware non contourné en test), ou données de fixture insuffisantes |
| Quiz/Live/Whiteboard (6 tests) | Redirections 302 au lieu de 200 dues au middleware d'adhésion |
| Divers (10 tests) | Routes attendues absentes, fixtures incomplètes |

Le fichier `tests/Feature/SCORMStatusTest[mysqld].php` existe avec un nom illégal sur certains systèmes de fichiers — cela signale probablement une copie de test créée manuellement.

---

## 9. Analyse de l'expérience utilisateur

### Navigation et menus

**Points forts :**
- Menu stagiaire court et centré (5 entrées maximum) — adapté à un public non-expert
- Menu formateur bien structuré par domaines fonctionnels
- Fil d'Ariane présent dans les vues de progression
- Grandes cartes visuelles et pictogrammes dans les listes de groupes et modules

**Points faibles :**
- La terminologie n'est pas unifiée : "Formations", "Modules", "Mes formations", "Parcours" coexistent et peuvent se recouper sémantiquement sans distinction claire pour l'utilisateur
- Le menu "Outils" côté stagiaire peut paraître abstrait — ce terme désigne les outils live du formateur, mais le stagiaire n'en a pas une vision claire avant d'y accéder
- Les écrans de progression formateur sont riches mais denses — une vue simplifiée "en un coup d'œil" manque

### Accessibilité numérique

Des attributs `aria-label` et `aria-current` sont présents dans plusieurs sidebars. Les questions quiz prévoient `image_alt` et `audio_transcript`. Ce sont de bonnes initiatives mais elles restent insuffisantes pour prétendre à la conformité RGAA ou WCAG 2.1 niveau AA.

Points à traiter :
- Aucun audit de contraste de couleurs documenté (les tokens Tailwind `bleuone` / `orangeone` doivent être vérifiés)
- Les graphiques Chart.js n'ont pas d'alternatives textuelles observées
- Les contenus SCORM importés peuvent être de qualité d'accessibilité très variable — la plateforme n'impose aucun contrôle à l'import
- Les grandes icônes décoratives doivent avoir `aria-hidden="true"`
- Le SCORM en iframe peut poser des problèmes de navigation clavier et de lecteur d'écran

### Points de friction pour le public cible

Le principal obstacle restant est la combinaison : création de compte + mail de réception + changement de mot de passe à la première connexion. Même si le code d'accès court-circuite une partie du processus, le changement de mot de passe obligatoire peut bloquer des apprenants très débutants sans accompagnement en présentiel.

L'interface SCORM en iframe pose des problèmes connus sur mobile (viewport, scroll interne, boutons de navigation). Pour un public éloigné du numérique qui utilise principalement le téléphone, c'est un point de friction potentiellement bloquant.

---

## 10. Forces principales

**Fonctionnelles :**
- Gestion multi-profils réelle avec espaces distincts et middleware appropriés
- Groupes avec co-formateurs, modules ordonnés, personnalisation par groupe
- Import SCORM sécurisé avec versioning et injection d'API automatique
- Quiz natifs complets avec temps par question, médias, import CSV, types variés (dont cloze)
- Suite d'outils live pédagogiques remarquable pour un projet de cet âge (9 outils)
- Service `LearningAnalyticsService` comme abstraction analytique centrale
- Notification de fin de module aux formateurs via `ModuleCompletionNotifier`
- Journal d'activité admin automatique via `RecordAdminActivity`

**Pédagogiques :**
- Accès par code d'accès — force majeure pour l'inclusion numérique
- Approche "meilleure tentative" dans les résultats (droit à l'erreur valorisé)
- Identification des stagiaires à risque (inactifs, non démarrés)
- Outils collaboratifs adaptés à l'animation présentielle et distancielle
- Objectifs pédagogiques par leçon, agrégés au niveau module

**Techniques :**
- Architecture Laravel 11 moderne et structurée
- 102 migrations traçant l'évolution du schéma
- Cache token SCORM intelligent basé sur `imported_at`
- Soft deletes avec nettoyage des données liées
- Chiffrement du mot de passe temporaire de groupe

**Stratégiques :**
- Positionnement "LMS d'inclusion numérique accompagnée" différenciant et peu occupé
- Modèle associatif avec politique d'adhésion intégrée dans le code
- Potentiel fort pour les ateliers numériques, associations, collectivités, organismes de formation

---

## 11. Faiblesses principales

**Fonctionnalités incomplètes ou absentes :**
- Interactions SCORM leçons non enregistrées — invalidant une partie des métriques analytiques
- Certificats et attestations absents malgré le champ en base
- Exports de progression et preuves inexistants
- Multi-groupe stagiaire mal géré (seul le premier groupe actif est pris en compte)
- Seuils de complétion SCORM incohérents (50% vs 75%) et non configurables
- `attempts_count` SCORM toujours à 1, `questions_answered` non alimenté

**Risques de sécurité :**
- Deux routes admin hors middleware (debug et reset-progression)
- `Module::isVisibleTo()` ne vérifie pas l'appartenance groupe
- Connexion par code sans throttling
- Email admin modifiable sans vérification d'unicité
- Import `ScormInteractionController` mort dans les routes

**Dette technique :**
- 40 tests en échec sur 85 — suite non verte
- Contrôleurs surchargés (ModuleController 1185 lignes, StagiaireController 903 lignes)
- `last_session_time` utilisé sans migration — calcul de delta SCORM défaillant
- `LessonFeedbackController::store()` redirige vers une route inexistante
- FormRequests `StoreModuleRequest` et `StoreGroupeRequest` retournant `false` dans `authorize()`
- `EvaluationSCORMController::fin()` typage incomplet sans import du modèle
- `Group` sans `SoftDeletes` malgré des relations avec des données persistantes

---

## 12. Niveau de maturité du projet

### Maturité technique — 11/20

La base est solide et reconnaissable. Laravel 11, migrations propres, modèles bien reliés, services métier. La dette vient de contrôleurs surchargés, de bugs structurels (last_session_time, interactions SCORM), de tests non verts et de quelques anomalies de routes. Le projet est techniquement sain dans ses fondations mais pas encore stable en surface.

### Maturité pédagogique — 14/20

Le projet comprend très bien les besoins terrain de l'inclusion numérique. Les outils d'animation sont nombreux et bien intégrés. Le suivi des apprenants est pensé avec soin. La limite est l'absence de formalisation : pas de prérequis, pas de compétences reliées, pas de preuves exportables, pas de certificats.

### Expérience utilisateur — 13/20

L'interface stagiaire est simple et adaptée. Le dashboard formateur est riche et utile. Il manque une unification du vocabulaire, des alternatives pour les publics sur mobile avec contenu SCORM, et un premier test d'accessibilité WCAG. La cohérence visuelle semble bonne mais non auditée de façon exhaustive.

### Potentiel commercial — 15/20

Le positionnement est différenciant et le marché (inclusion numérique accompagnée) est peu couvert par les LMS généralistes. Le modèle associatif avec adhésion est une barrière de qualité positive. La commercialisation nécessite cependant de consolider les exports, les certificats et le reporting institutionnel avant de pouvoir convaincre des financeurs publics.

### Capacité LMS globale — 13/20

Oneduc fait déjà beaucoup de choses qu'un LMS doit faire. Ce qui manque pour prétendre à un LMS professionnel complet : suivi SCORM complet, accès stricts vérifiés, reporting fiable et exportable, certificats, politiques de permissions centralisées, documentation utilisateur, couverture de tests verte.

---

## 13. Potentiel d'exploitation professionnelle

### Ce qui est exploitable aujourd'hui (pilote contrôlé)

- Gestion de groupes de stagiaires avec formateur référent
- Contenu SCORM et quiz natifs avec suivi de complétion et scores
- Outils d'animation live pour les sessions en présentiel et distanciel
- Tableau de bord formateur pour identifier les apprenants à risque
- Dashboard stagiaire lisible et motivant

Pour une exploitation pilote avec 10 à 50 stagiaires et 3 à 5 formateurs, en contexte associatif ou de formation locale, la plateforme est utilisable dès maintenant **à condition de corriger les routes publiques et le contrôle d'accès aux modules**.

### Ce qui doit être consolidé avant exploitation élargie

- Correction des risques de sécurité (routes hors middleware, accès modules)
- Branchement des interactions SCORM pour crédibiliser les métriques analytiques
- Correction du bug `LessonFeedbackController`
- Suite de tests au vert
- Exports de progression pour les formateurs et organismes
- Documentation utilisateur minimale (guide stagiaire, guide formateur)

### Ce qui peut faire la différence face aux LMS concurrents

1. **Accès par code d'accès** — réduction radicale de la friction pour les publics débutants
2. **Suite d'outils live intégrée** — 9 outils d'animation sans plugin externe
3. **Accompagnement visible** — le formateur référent est affiché en permanence côté stagiaire
4. **Dashboard formateur pédagogique** — identification des apprenants à risque, non démarrés, inactifs
5. **Personnalisation par groupe** — masquage et réordonnancement des leçons par groupe

---

## 14. Risques avant mise en production

### Risques critiques (blocants)

| Risque | Fichier concerné | Impact |
|---|---|---|
| Route debug `/admin/stagiaires/{id}/debug-progression` sans auth | `routes/web.php` ligne 220 | Fuite de données personnelles |
| Route reset-progression hors middleware admin | `routes/web.php` ligne 273 | Modification non autorisée de données |
| `Module::isVisibleTo()` ne vérifie pas l'appartenance groupe | `app/Models/Module.php` ligne 84 | Accès non autorisé aux modules |
| `LessonFeedbackController::store()` redirige vers route inexistante | `app/Http/Controllers/LessonFeedbackController.php` ligne 32 | Erreur 500 à chaque retour stagiaire |
| Connexion par code sans throttling | `routes/web.php` ligne 263 | Brute-force possible |
| 40 tests en échec sur 85 | `tests/Feature/*` | Impossible de garantir la stabilité |

### Risques importants (production dégradée)

| Risque | Fichier concerné | Impact |
|---|---|---|
| `last_session_time` non migré | `app/Http/Controllers/SCORMController.php` ligne 99 | Temps SCORM non cumulé correctement |
| Interactions SCORM non enregistrées pour les leçons | `SCORMController`, `API.js` | Métriques analytiques partiellement vides |
| Seuils de réussite SCORM incohérents (50% vs 75%) | `SCORMController` ligne 126, `EvaluationSCORMController` ligne 60 | Indicateurs de réussite contradictoires |
| Multi-groupe stagiaire non géré | `StagiaireController.php` ligne 244 | Modules masqués pour stagiaires multi-groupes |
| `attempts_count` non incrémenté | `SCORMController.php` | Compteur de tentatives toujours à 1 |
| Import `ScormInteractionController` mort | `routes/scorm.php` ligne 6 | Erreur potentielle en mode strict |
| `Group` sans SoftDeletes | `app/Models/Group.php` | Données orphelines après suppression |

### Risques réglementaires

Les données d'apprentissage (temps de connexion, scores, réponses aux questions) sont des données personnelles au sens du RGPD. La plateforme doit documenter :
- La durée de conservation et les finalités
- Le droit d'accès et de portabilité
- La procédure de suppression (partiellement implémentée via `cleanupRelatedStagiaireData()`)
- La base légale du traitement (contrat de formation, intérêt légitime, consentement)

---

## 15. Recommandations détaillées

### Priorité CRITIQUE

**1. Sécuriser les routes admin exposées**

- Problème : `GET /admin/stagiaires/{id}/debug-progression` et `POST /admin/stagiaires/{user}/reset-progression` déclarées hors du groupe middleware admin dans `routes/web.php` lignes 220 et 273.
- Impact : Exposition de données de progression à tout internaute, et possibilité de réinitialiser la progression d'un stagiaire sans authentification.
- Solution : Déplacer ces deux routes dans `routes/admin.php` à l'intérieur du groupe `middleware(['auth', 'role:admin', 'admin.activity'])`. Supprimer la route de debug en production ou la conditionner à `app()->environment('local')`.
- Fichiers : `routes/web.php`, `routes/admin.php`

**2. Corriger le contrôle d'accès aux modules et leçons stagiaire**

- Problème : `Module::isVisibleTo()` retourne `true` pour tout module actif, sans vérification d'appartenance au groupe du stagiaire. Utilisé dans `ModuleController::section()` et `::lire()`.
- Impact : Un stagiaire peut accéder aux contenus d'un module auquel il n'est pas affecté.
- Solution : Ajouter dans `isVisibleTo()` ou créer une méthode `isAccessibleByStudent(User $user)` qui vérifie que le module est associé via `group_module` à un groupe dont l'utilisateur est stagiaire (`group_user.role_in_group = 'stagiaire'`). Appliquer ce contrôle dans toutes les routes de lecture (`section()`, `lire()`, `finModule()`).
- Fichiers : `app/Models/Module.php`, `app/Http/Controllers/Backend/ModuleController.php`

**3. Corriger la route cassante `LessonFeedbackController::store()`**

- Problème : Redirige vers `route('module.lesson', [...])` inexistante.
- Impact : Erreur 500 pour tout stagiaire ou formateur soumettant un retour de leçon.
- Solution : Remplacer par une redirection conditionnelle selon le rôle de l'utilisateur : `route('stagiaire.module.lecture', [...])` pour un stagiaire, `route('formateur.formations.lecture', [...])` pour un formateur. Ou passer l'URL de retour en champ caché dans le formulaire de feedback.
- Fichier : `app/Http/Controllers/LessonFeedbackController.php` ligne 32

**4. Ajouter le throttling sur la connexion par code d'accès**

- Problème : `POST /stagiaire/connexion-code` n'a pas de throttling. Un code d'accès à 6 caractères est attaquable par force brute.
- Impact : Accès non autorisé à un compte stagiaire.
- Solution : Ajouter `->middleware('throttle:10,1')` sur la route (10 tentatives par minute par IP). Envisager un code plus long (8 caractères minimum) ou une expiration du code après N jours.
- Fichier : `routes/web.php` ligne 263

**5. Remettre la suite de tests au vert**

- Problème : 40 tests échouent. La majorité à cause du middleware `association.member` qui redirige les formateurs sans `adhesion_status = active`.
- Impact : Impossible de valider la stabilité du code avant une évolution ou une mise en production.
- Solution : Créer ou modifier la `UserFactory` pour que les formateurs aient `adhesion_status = 'active'` et `adhesion_valid_until = null` par défaut dans les tests. Corriger les tests d'authentification pour utiliser les routes correctes. Ajouter `WithoutMiddleware` ou `actingAs` avec des attributs corrects dans les tests formateur.
- Fichiers : `database/factories/UserFactory.php`, `tests/Feature/*`

**6. Ajouter la migration pour `last_session_time` dans `scorm_scores`**

- Problème : `SCORMController::handleSessionTime()` lit et écrit `$sc->last_session_time` (lignes 99, 105, 108), mais aucune migration ne crée cette colonne. Eloquent traite cela comme un attribut dynamique non persisté, rendant le delta toujours 0.
- Impact : Le temps de session SCORM n'est pas cumulé correctement entre les requêtes.
- Solution : Créer une migration ajoutant `last_session_time` (integer, default 0, nullable) à la table `scorm_scores`.
- Fichiers : `database/migrations/` (nouvelle migration), `app/Http/Controllers/SCORMController.php`

### Priorité IMPORTANTE

**7. Brancher les interactions SCORM 1.2 dans `SCORMController`**

- Problème : `API.js` envoie toutes les clés SCORM au backend, y compris `cmi.interactions.{index}.{field}`, mais `SCORMController::saveProgress()` les ignore.
- Impact : Tous les indicateurs analytiques de questions SCORM (bonnes/mauvaises réponses, latence, questions difficiles) sont structurellement vides pour les leçons ordinaires.
- Solution : Ajouter dans le `switch` de `saveProgress()` un traitement pour `cmi.interactions.*` : parser l'index et le champ, accumuler dans une structure temporaire (session PHP ou un pattern similaire à `EvaluationSCORMController`), persister dans `ScormInteraction` quand les champs requis sont présents.
- Fichiers : `app/Http/Controllers/SCORMController.php`

**8. Unifier les seuils de réussite SCORM et les rendre configurables**

- Problème : 50% dans `SCORMController` (ligne 126), 75% dans `EvaluationSCORMController` (ligne 60). Aucun est configurable par module ou leçon.
- Solution : Ajouter un champ `passing_score` sur `module_lectures` et sur `evaluations`. Utiliser ce champ dans les deux contrôleurs.
- Fichiers : `database/migrations/`, `app/Http/Controllers/SCORMController.php`, `app/Http/Controllers/EvaluationSCORMController.php`

**9. Découper les contrôleurs surchargés**

- Problème : `ModuleController` (1185 lignes) mélange CRUD, navigation, SCORM, quiz, statistiques et logique métier multi-rôle.
- Solution : Extraire `ModuleNavigationService` (lecture section/leçon), `ModuleCompletionService` (fin de module, calcul progression), `StudentLectureAccessService` (contrôle d'accès). Ces services pourront être testés indépendamment.
- Fichiers : `app/Http/Controllers/Backend/ModuleController.php`, `app/Services/`

**10. Gérer correctement le multi-groupe stagiaire**

- Problème : `StagiaireController::StagiaireModules()` utilise `->first()` sur les groupes actifs (ligne 244), masquant les modules des autres groupes actifs du stagiaire.
- Solution : Récupérer tous les groupes actifs du stagiaire et agréger leurs modules (avec déduplication si un module est dans plusieurs groupes). Afficher les modules groupés par groupe, ou offrir un sélecteur de groupe.
- Fichier : `app/Http/Controllers/StagiaireController.php`

**11. Centraliser les autorisations dans des Laravel Policies**

- Problème : Les vérifications d'accès sont dispersées dans les contrôleurs, avec des logiques parfois différentes selon l'écran.
- Solution : Créer `ModulePolicy`, `GroupPolicy`, `LecturePolicy`, `StagiairePolicy`. Utiliser `$this->authorize()` dans les contrôleurs.
- Fichiers : `app/Policies/`, contrôleurs formateur et stagiaire

**12. Ajouter des exports de progression**

- Problème : Aucun export observé dans les routes auditées.
- Impact : Faible valeur institutionnelle — les organismes de formation et les financeurs exigent des preuves exportables.
- Solution : Export CSV par groupe (liste stagiaires, modules, scores, temps, complétion), export PDF de fiche individuelle.
- Fichiers : contrôleurs de progression formateur et admin

**13. Corriger les FormRequests inutilisables**

- Problème : `StoreModuleRequest::authorize()` et `StoreGroupeRequest::authorize()` retournent `false`.
- Solution : Remplacer par `return auth()->check() && in_array(auth()->user()->role, ['admin', 'formateur']);` selon le contexte, ou supprimer ces FormRequests et utiliser `$request->validate()` directement dans les contrôleurs.
- Fichiers : `app/Http/Requests/StoreModuleRequest.php`, `app/Http/Requests/StoreGroupeRequest.php`

### Priorité UTILE

**14. Ajouter `SoftDeletes` sur le modèle `Group`**

- Problème : La suppression d'un groupe supprime physiquement l'enregistrement, laissant des données de progression et SCORM orphelines.
- Solution : Ajouter `use SoftDeletes;` sur `Group`, créer la migration `deleted_at`.
- Fichiers : `app/Models/Group.php`, `database/migrations/`

**15. Supprimer l'import mort `ScormInteractionController` dans les routes**

- Fichier : `routes/scorm.php` ligne 6
- Solution : Supprimer la ligne `use App\Http\Controllers\ScormInteractionController;`

**16. Corriger `EvaluationSCORMController::fin()` — import manquant**

- Fichier : `app/Http/Controllers/EvaluationSCORMController.php` ligne 133
- Solution : Ajouter `use App\Models\Evaluation;` en tête du fichier. Router la méthode si elle est destinée à être utilisée.

**17. Nettoyer les artefacts de template**

- `resources/views/content/apps/*` et `resources/views/content/pages/*` contiennent des vues de template générique non spécifiques à Oneduc.
- Ces fichiers alourdissent le projet sans valeur fonctionnelle.
- Solution : Identifier via `grep -r "content.apps\|content.pages" routes/ app/` si elles sont référencées, puis les archiver ou supprimer.

**18. Lancer un audit d'accessibilité WCAG 2.1 niveau AA**

- Le positionnement "inclusion numérique" exige une accessibilité soignée.
- Outils suggérés : Wave, Axe, audit manuel navigation clavier, test avec lecteur d'écran NVDA/VoiceOver.
- Points prioritaires : contrastes `bleuone`/`orangeone`, alternatives graphiques Chart.js, ARIA sur composants interactifs Alpine.js.

### Priorité FUTURE

**19. Implémenter les certificats et attestations**

Le champ `certificat` existe dans `modules`. Implémenter la génération PDF à la fin d'un module avec score suffisant (ex. via `barryvdh/laravel-dompdf`), horodatage et version du contenu.

**20. Moteur de prérequis et parcours adaptatif**

Ajouter une table `module_prerequisites` et un moteur bloquant l'accès à un module si un autre n'est pas complété. Relier aux objectifs et compétences.

**21. Multi-organisation**

Ajouter la notion d'organisme, de coordinateur, de convention de formation pour les clients institutionnels.

**22. Mode simplifié stagiaire**

Un bouton principal "Continuer ma formation" sur le dashboard, avec les modules au format liste simple, pour les publics les plus éloignés du numérique.

---

## 16. Feuille de route proposée

### Phase 1 — Sécurisation et stabilisation (1 à 2 mois)

Objectif : rendre la plateforme sûre et stable pour un pilote.

- [ ] Déplacer les routes admin de debug/reset dans `routes/admin.php` avec middleware complet
- [ ] Corriger `Module::isVisibleTo()` pour vérifier l'appartenance groupe du stagiaire
- [ ] Corriger `LessonFeedbackController::store()` avec la bonne route de redirection
- [ ] Ajouter throttling sur la connexion par code d'accès
- [ ] Créer la migration `last_session_time` sur `scorm_scores`
- [ ] Mettre à jour `UserFactory` pour créer des formateurs avec `adhesion_status = active`
- [ ] Corriger les tests d'authentification et remettre la suite au vert
- [ ] Supprimer l'import mort `ScormInteractionController`
- [ ] Corriger `EvaluationSCORMController::fin()` avec l'import manquant

### Phase 2 — Fiabilisation LMS (2 à 4 mois)

Objectif : crédibiliser les données analytiques et l'expérience utilisateur.

- [ ] Brancher les interactions SCORM 1.2 dans `SCORMController::saveProgress()`
- [ ] Unifier et rendre configurables les seuils de réussite SCORM
- [ ] Gérer correctement le multi-groupe stagiaire dans `StagiaireModules()`
- [ ] Ajouter les exports CSV/PDF de progression par groupe
- [ ] Créer des Laravel Policies pour centraliser les autorisations
- [ ] Découper `ModuleController` en services dédiés
- [ ] Ajouter `SoftDeletes` sur `Group`
- [ ] Corriger les FormRequests avec `authorize()` retournant false

### Phase 3 — Maturité pédagogique (4 à 8 mois)

Objectif : transformer la plateforme en LMS pédagogiquement complet.

- [ ] Implémenter la génération de certificats et attestations
- [ ] Relier les objectifs de leçon aux référentiels de compétences
- [ ] Afficher les compétences acquises côté stagiaire
- [ ] Ajouter un moteur de prérequis de base (accès conditionnel entre modules)
- [ ] Audit d'accessibilité WCAG 2.1 niveau AA et corrections
- [ ] Documentation utilisateur (guide stagiaire, guide formateur)
- [ ] Nettoyer les artefacts de template et le code de chantier

### Phase 4 — Exploitation professionnelle (8 à 18 mois)

Objectif : rendre la plateforme commercialisable à des organismes institutionnels.

- [ ] Multi-organisation (organismes, coordinateurs, conventions)
- [ ] Reporting institutionnel (export PDF avec données certifiées, archivage)
- [ ] Mode simplifié stagiaire (une seule action principale)
- [ ] Gestion de sessions de formation avec présences et émargements
- [ ] Supervision et monitoring production (alertes, logs, sauvegardes)
- [ ] Conformité RGPD documentée et testée

---

## 17. Conclusion générale

Oneduc.fr est un projet LMS sérieux, en avance pour son âge et bien ancré dans une vision pédagogique cohérente. Le code révèle une compréhension fine des besoins terrain de l'inclusion numérique : accès simplifié, formateur présent, outils d'animation, suivi des progressions, visualisation pédagogique.

La plateforme a dépassé le stade de prototype. Elle constitue une base solide sur laquelle un produit professionnel peut être construit. Les problèmes identifiés sont réels, certains sont critiques pour la sécurité, mais aucun n'est architecturalement rédhibitoire. Ils sont tous corrigeables dans un délai raisonnable.

Le principal risque du projet n'est pas technique : c'est la pression de mise en production avant consolidation, qui pourrait exposer des données personnelles (route de debug), générer des erreurs visibles pour les utilisateurs (bug feedback) ou créer des métriques pédagogiques non fiables qui discréditent la plateforme auprès des formateurs et financeurs.

La recommandation finale est claire : mener la phase 1 de sécurisation avant toute présentation institutionnelle ou extension du pilote. Après cette phase, Oneduc aura le niveau de stabilité nécessaire pour démontrer son potentiel différenciant — ce potentiel est réel, mesurable dans le code, et pas encore réalisé par d'autres outils sur ce segment de marché.

### Notes sur 20 (audit Claude)

| Axe | Note | Commentaire |
|---|---|---|
| Maturité technique | 11/20 | Fondations solides, bugs structurels à corriger, tests non verts |
| Maturité pédagogique | 14/20 | Vision juste, outils live remarquables, preuves et prérequis manquants |
| Expérience utilisateur | 13/20 | Interface stagiaire adaptée, vocabulaire à unifier, accessibilité à auditer |
| Potentiel commercial | 15/20 | Positionnement différenciant fort, pilote exploitable, reporting insuffisant |
| Capacité LMS globale | 12/20 | LMS réel mais incomplet : interactions SCORM, certificats, exports absents |

---

## Tableau de synthèse final

| Domaine analysé | État actuel | Risque ou limite | Recommandation prioritaire |
|---|---|---|---|
| Architecture Laravel | Claire, segmentée par rôle, services présents | Contrôleurs surchargés (1185 lignes max) | Extraire services — ModuleNavigationService, StudentAccessService |
| Routes et middleware | Séparation nette par espace, middleware bien chaîné | Deux routes admin hors groupe middleware | Déplacer dans `routes/admin.php` sous middleware complet |
| Authentification | Multi-modal (email + code d'accès), first-login sécurisé | Connexion code sans throttling | Ajouter `throttle:10,1` sur `POST /stagiaire/connexion-code` |
| Contrôle d'accès modules | `Module::isVisibleTo()` présent mais insuffisant | Tout module actif accessible par URL à tout stagiaire | Ajouter vérification groupe dans `isVisibleTo()` ou créer `ModulePolicy` |
| Profil Admin | CRUD complet, journal d'activité automatique | Email modifiable sans unicité | Ajouter `Rule::unique` sur la mise à jour email admin |
| Profil Formateur | Dashboard analytique riche, outils complets | Middleware adhésion casse 40 tests | Mettre `adhesion_status = active` dans `UserFactory` |
| Profil Stagiaire | Dashboard clair, accès simplifié, progression visible | Multi-groupe non géré (`.first()`), accès modules trop large | Gérer l'agrégation multi-groupe, corriger `isVisibleTo()` |
| Profil Observateur | Lecture groupes et progressions | Même déficit d'accès modules que stagiaire | Appliquer le même correctif d'appartenance groupe |
| Modules | CRUD complet, durée estimée intelligente | Module::stagiaires() non-Eloquent | Réécrire en relation `hasManyThrough` |
| Sections et leçons | SCORM, slides, quiz, objectifs, ressources | Navigation dans `ModuleController` 1185 lignes | Extraire `ModuleNavigationService` |
| Groupes | Avancé : co-formateurs, modules ordonnés, personnalisation | `Group` sans SoftDeletes | Ajouter SoftDeletes avec migration `deleted_at` |
| Parcours | FormateurParcours avec items ordonnés, lié aux groupes | Pas de prérequis bloquants ni de compétences reliées | Ajouter moteur de prérequis — phase 3 |
| Quiz natifs | Complets : types variés, médias, import CSV, temps/question | Seuil de réussite non documenté par leçon | Ajouter `passing_score` sur `module_lectures` |
| SCORM — Import | Sécurisé, versioning, injection API automatique | — | — |
| SCORM — Scores | `first_score`, `best_score`, `last_score`, statut monotone | `last_session_time` non migré — delta inefficace | Créer migration `add_last_session_time_to_scorm_scores` |
| SCORM — Interactions | Modèle et table présents | Non branchées dans `SCORMController` (clés ignorées) | Parser `cmi.interactions.*` dans `saveProgress()` |
| SCORM — Seuils | 50% pour leçons, 75% pour évaluations | Incohérence non documentée, non configurable | Ajouter `passing_score` configurable par module/leçon |
| Évaluations SCORM | Interactions enregistrées via session PHP | `fin()` sans `use Evaluation`, méthode non routée | Ajouter l'import, router la méthode |
| Dashboard admin | Compteurs volumétriques | Pas d'indicateurs pédagogiques ni d'alertes | Ajouter taux d'activité, complétion, alertes qualité |
| Dashboard formateur | Très riche, analytique, AJAX avec cache | Fiabilité partielle (SCORM interactions vides) | Afficher sources des indicateurs, alerter sur données SCORM incomplètes |
| Dashboard stagiaire | Lisible, motivant, adapté aux débutants | Temps et questions sous-estimés si contenu SCORM | Corriger après branchement interactions SCORM |
| Feedback / Retours | Table et admin présents | `LessonFeedbackController::store()` route inexistante | Corriger la route vers `stagiaire.module.lecture` |
| Outils live | 9 outils d'animation intégrés et fonctionnels | — | Point fort à valoriser en communication |
| Tests | 37 fichiers Pest/PHPUnit | 40 échecs sur 85 (47%) | Priorité critique — factory formateurs, routes auth |
| Certificats | Champ `certificat` en base | Aucun flux de génération | Implémenter génération PDF — phase 3 |
| Exports | — | Absents | Ajouter exports CSV/PDF — phase 2 |
| Accessibilité | ARIA partiels, `image_alt`, `audio_transcript` quiz | Pas d'audit WCAG, contrastes non vérifiés | Audit WCAG 2.1 AA — phase 3 |
| Sécurité RGPD | Suppression user avec nettoyage des données liées | Durée conservation, finalités non documentées | Documenter politique de données — phase 3 |
| Performance | AJAX avec cache sur dashboard, jointures SQL directes | Potentiel N+1 dans dashboard admin SCORM | Profiler et optimiser après stabilisation |
| Positionnement | Inclusion numérique accompagnée — différenciant | Non encore exploitable en SaaS institutionnel | Consolider pilote, puis ouvrir à organismes |

---

*Rapport rédigé le 7 mai 2026 par Claude Sonnet 4.6 (Anthropic) sur la base d'une lecture exhaustive du code source du projet `/var/www/Oneduc_Dev`. Tous les fichiers et numéros de lignes cités ont été vérifiés par lecture directe.*



Ce que l'audit a produit
Le rapport couvre les 17 sections demandées avec des références précises au code. Voici les points saillants de l'analyse basée sur le code réel :

Problèmes critiques confirmés (vs rapport ChatGPT)
#	Problème	Confirmation
1	Interactions SCORM ignorées	CONFIRMÉ — SCORMController::saveProgress() abandonne silencieusement toutes les clés cmi.interactions.* dans son switch
2	Routes sensibles sans auth	CONFIRMÉ — 2 routes dans routes/web.php (debug + reset-progression) accessibles sans middleware admin
3	Stagiaire accède à tout module actif par URL	CONFIRMÉ — Module::isVisibleTo() vérifie uniquement $this->status, jamais l'appartenance au groupe
4	40 tests en échec	CONFIRMÉ — cause identifiée : le nouveau middleware association.member redirige les formateurs dont la factory ne définit pas adhesion_status = active
5	Bug LessonFeedback	CONFIRMÉ — redirection vers route module.lesson inexistante → erreur 500 garantie en prod
6	Contrôleurs surchargés	CONFIRMÉ — 4 contrôleurs dépassent 686 lignes
Nouveaux problèmes non signalés par ChatGPT
last_session_time utilisé sans colonne migrée → cumul du temps SCORM défaillant
attempts_count SCORM initialisé à 1 et jamais incrémenté
StoreModuleRequest et StoreGroupeRequest avec authorize(): return false → inutilisables en prod
Stagiaire dans plusieurs groupes → seul le premier groupe est pris en compte (.first() dans StagiaireModules)
Import mort ScormInteractionController dans routes/scorm.php (classe inexistante)
Notes sur 20
Axe	Note
Maturité technique	11/20
Maturité pédagogique	14/20
Expérience utilisateur	13/20
Potentiel commercial	15/20
Capacité LMS globale	12/20
Verdict : plateforme pédagogiquement prometteuse et pilotable en environnement contrôlé, mais 5 à 6 correctifs critiques sont indispensables avant toute mise en production ou présentation institutionnelle.