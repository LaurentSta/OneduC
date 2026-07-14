# 11 — Roadmap & dette technique

*Public : tous les profils. Les tableaux de bugs et de dette technique s'adressent aux développeurs.*

## État actuel (5 juillet 2026)

La plateforme est **utilisable en pilote contrôlé** (10 à 50 stagiaires, 3 à 5 formateurs, contexte associatif ou ateliers numériques). Le build Vite réussit. Les corrections de sécurité et de santé applicative S3 à S9 (voir tableau ci-dessous, B0-B5 et B7) ont été appliquées le 5 juillet 2026 : suite de tests complète verte (124 tests). Un gap est resté ouvert lors du correctif de `Module::isVisibleTo()` : `StagiaireController::StagiaireModuleDetail()` et `Frontend\LectureController` ne vérifient pas l'appartenance groupe (voir [Sécurité & RGPD](10-securite-rgpd.md)).

Depuis l'audit initial, le module builder formateur a évolué vers un éditeur de plan continu :
- plan chapitres/leçons en React + Tiptap ;
- page séparée pour éditer le contenu riche d'une leçon ;
- renommage autosauvegardé, déplacement inter-chapitres et promotion d'une leçon vide en chapitre ;
- duplication de modules catalogue avec conservation des leçons SCORM/slides verrouillées côté contenu.

La segmentation interne a démarré avec `app/Domains/ModulesFormateur` et `app/Domains/Learners` : le module builder garde l'orchestration HTTP, tandis que les actions métier (noms de classes en français : `CreerModule`, `CreerChapitre`, `CreerLecon`, `ModifierLecon`, `ReordonnerChapitres`, `ReordonnerLecons`, `DeplacerLecon`, `PromouvoirLeconEnChapitre`, `TeleverserImageModule`, `TeleverserVideoModule`, `TeleverserScormModule`, `ModifierOptionsModule`, `AssignerGroupesModule`, `DupliquerModuleCatalogue`) portent la création, duplication, réordonnancement, déplacement, promotion, médias, options et assignation aux groupes. Le contrôleur stagiaire délègue désormais le calcul de progression des modules à `LearnerModuleProgress`.

---

## Corrections prioritaires avant publication publique

| # | Bug | Fichier | Impact immédiat |
|---|-----|---------|-----------------|
| B0 | ✅ Corrigé 5/07/2026 — `/inscription` appelait `UserController::Register()` absent | `routes/web.php` | Redirection 301 vers `/inscription-formateur` |
| B1 | ✅ Corrigé 5/07/2026 — Connexion par code sans throttling | `routes/web.php`, `AppServiceProvider` | Rate limiter `connexion-code` (10/min/IP) |
| B2 | ✅ Corrigé 5/07/2026 — `LessonFeedbackController::store()` redirigeait vers `module.lesson`, route inexistante | `app/Http/Controllers/LessonFeedbackController.php` | `redirect()->back()` |
| B3 | ✅ Corrigé 5/07/2026 — `Module::isVisibleTo()` ne vérifiait pas l'appartenance groupe | `app/Models/Module.php` | Vérification via `User::aAccesAuModule()` / `Group::scopeAccessibleByTrainer()`. **Gap restant** : `StagiaireModuleDetail()` et `Frontend\LectureController` n'appellent pas cette méthode |
| B4 | ✅ Corrigé 5/07/2026 — `SCORMController` écrit `last_session_time`, colonne absente de `scorm_scores` | `app/Http/Controllers/SCORMController.php` | Migration ajoutant la colonne |
| B5 | ✅ Corrigé 5/07/2026 — `POST /scorm/save-progress` ne vérifiait pas l'appartenance à la leçon | `routes/scorm.php`, `SCORMController` | `aAccesAuModule()` + middleware `auth`, même garde sur `save-block-progress` et `evaluation-progress` |
| B6 | `StoreModuleRequest` / `StoreGroupeRequest` retournent `authorize() = false` | `app/Http/Requests/` | FormRequests inutilisables |
| B7 | ✅ Corrigé 5/07/2026 — Upload image builder : le test attendait `path`, le contrôleur retourne `media_id`/`url` | `ModuleBuilderController`, `ModuleBuilderTest` | Test aligné sur le contrat Media Library réellement utilisé par le frontend |

---

## Dette technique connue

| Élément | Localisation | Gravité |
|---------|-------------|---------|
| `attempts_count` SCORM jamais incrémenté | `SCORMController.php` | Moyenne |
| `scorm_interactions` non alimenté pour les leçons | `SCORMController::saveProgress()` | Haute — métriques analytiques vides |
| `StoreModuleRequest::authorize()` retourne `false` | `app/Http/Requests/` | Haute — FormRequest inutilisable |
| `StoreGroupeRequest::authorize()` retourne `false` | `app/Http/Requests/` | Haute — FormRequest inutilisable |
| Import mort `ScormInteractionController` | `routes/scorm.php:6` | Basse |
| `Group` sans `SoftDeletes` | `app/Models/Group.php` | Moyenne — données orphelines |
| Stagiaire multi-groupe — `.first()` uniquement | `StagiaireController.php:244` | Haute — modules masqués |
| `ModuleController` 1185 lignes | `Backend/ModuleController.php` | Maintenance difficile |
| `StagiaireController` 794 lignes | `StagiaireController.php` | Encore volumineux, mais progression module extraite |

---

## Documentation du wiki à compléter (audit du 5 juillet 2026)

Une comparaison du code sur `main` (85 contrôleurs, 61 modèles, 411 routes) avec le contenu réel du wiki a fait apparaître onze fonctionnalités déjà présentes dans le code mais absentes ou sous-documentées dans le wiki. Suivi aussi comme tâches dans le Kanban de pilotage interne (projet « Wiki — combler les trous de documentation », colonne À faire).

- [ ] Documenter les référentiels de compétences (`SkillReferential` → `SkillDomain` → `Skill`, CRUD complet sous `/admin/referentiels/*`) — corriger au passage le glossaire, qui dit à tort « en cours d'implémentation »
- [ ] Documenter le système de badges (`Badge` relié à `Competency` via `badge_competency`) — préciser que le catalogue existe déjà, seule l'attribution automatique à un utilisateur manque
- [ ] Documenter la messagerie formateur → stagiaire (`FormateurStagiaireController`, modèle `FormateurMessage`, notification et/ou email)
- [ ] Documenter le pilotage interne (Kanban admin : `PilotProject`, `PilotTask`, commentaires, abonnements, notifications)
- [ ] Documenter le dashboard qualité parcours formateur module 2 (`TrainerPathQualityController`, `TrainerPathActivityAttempt`)
- [ ] Documenter la participation publique anonyme aux outils live (`/oneduc/mot`, `/sondage`, `/echelle`, `/questions`, `/roue` — sans compte ni connexion) et croiser avec la page Sécurité pour vérifier le throttling
- [ ] Documenter le vote sur le mur de questions (`QuestionWallVote`)
- [ ] Documenter les questionnaires de fin de module et les activités notées du parcours formateur (`TrainerModuleQuestionnaireSubmission`, mail `ModuleQuestionnaireSubmitted`)
- [ ] Documenter le système de notifications et les endpoints `notification-status` (Live Quiz, Mur de questions, Tableau blanc côté stagiaire)
- [ ] Documenter le formulaire de contact (`ContactController`, mails `ContactConfirmation`/`ContactMessage`)
- [ ] Documenter les pages publiques/marketing (`/association`, `/le-projet-oneduc-fr`, `/chartegraphique`, `/formations`, `/adhesion`, `Frontend/MFormationsController`)

---

## Phase 1 — Sécurisation et stabilisation

**Objectif : rendre la plateforme sûre et stable pour un pilote.**  
**État : partiellement réalisée.**

- [x] Déplacer `reset-progression` dans `routes/admin.php` avec middleware complet
- [x] Supprimer ou retirer la route debug admin non authentifiée
- [x] Mettre à jour `UserFactory` : formateurs avec `adhesion_status = active` par défaut en test
- [x] Remettre la suite de tests au vert lors de l'audit initial (`102 tests passés`)
- [x] Livrer l'éditeur de plan continu du module builder formateur
- [x] Extraire la logique métier du module builder dans `app/Domains/ModulesFormateur` (classes en français)
- [x] Extraire le calcul de progression des modules stagiaire dans `app/Domains/Learners`
- [x] Corriger `/inscription` ou rediriger explicitement vers `/inscription-formateur`
- [x] Stabiliser le contrat d'upload image du builder et remettre `ModuleBuilderTest` au vert
- [x] Corriger `Module::isVisibleTo()` pour vérifier l'appartenance au groupe (policies complètes reportées en Phase 2 — voir gap `StagiaireModuleDetail`/`LectureController` dans [Sécurité & RGPD](10-securite-rgpd.md))
- [x] Corriger `LessonFeedbackController::store()` avec la bonne route de redirection selon le rôle/contexte
- [x] Ajouter `throttle:10,1` ou rate limiter dédié sur la route de connexion par code d'accès
- [x] Ajouter la colonne `last_session_time` à `scorm_scores` ou modifier le cumul de temps SCORM
- [x] Refaire le socle d'administration avec un tableau de bord dense, une gestion unifiée des formateurs et stagiaires et un CRUD groupes fiabilisé (14 juillet 2026)
- [ ] Incrémenter correctement `attempts_count` SCORM
- [ ] Supprimer l'import mort `ScormInteractionController` dans `routes/scorm.php`
- [ ] Corriger `StoreModuleRequest::authorize()` et `StoreGroupeRequest::authorize()`

---

## Phase 2 — Fiabilisation LMS

**Objectif : crédibiliser les données analytiques et l'expérience utilisateur.**  
**Durée estimée : 2 à 4 mois après Phase 1.**

- [ ] Brancher les interactions SCORM 1.2 dans `SCORMController::saveProgress()` (parser `cmi.interactions.*`)
- [ ] Unifier et rendre configurables les seuils de réussite SCORM (50% leçons / 75% évaluations → champ `passing_score` par leçon)
- [ ] Corriger le multi-groupe stagiaire dans `StagiaireModules()` (agréger tous les groupes actifs)
- [ ] Ajouter les exports CSV/PDF de progression par groupe
- [ ] Créer des Laravel Policies (`ModulePolicy`, `GroupPolicy`, `LecturePolicy`) pour centraliser les autorisations
- [ ] Découper `ModuleController` : extraire `ModuleNavigationService`, `ModuleCompletionService`, `StudentLectureAccessService`
- [ ] Continuer la segmentation en domaines internes : Groupes, Progression/Analytics, SCORM/Quiz, Outils d'animation
- [ ] Ajouter `SoftDeletes` sur `Group` avec migration `deleted_at`
- [x] Valider l'unicité de l'email dans `AdminController::AdminProfilStore()`
- [ ] Séparer la désactivation, l'archivage réversible et la purge définitive des comptes formateur et stagiaire
- [x] Ajouter la vérification d'appartenance à la leçon dans `POST /scorm/save-progress` (et `save-block-progress`, `evaluation-progress`)
- [ ] Découper `StagiaireController` : dashboard, modules, résultats, outils
- [ ] Mettre en place une arborescence d'objectifs (objectif pédagogique général au niveau de la formation → objectifs opérationnels au niveau de chaque leçon, `LectureObjective`), construite soit au moment de la génération par IA, soit au moment de la création manuelle par le formateur

---

## Phase 3 — Maturité pédagogique

**Objectif : transformer la plateforme en LMS pédagogiquement complet.**  
**Durée estimée : 4 à 8 mois après Phase 1.**

- [ ] Implémenter la génération de certificats PDF à la fin d'un module (ex. via `barryvdh/laravel-dompdf`)
- [ ] Relier les `LectureObjective` aux référentiels de compétences (`Competency`, `SkillDomain`)
- [ ] Afficher les compétences acquises et non acquises côté stagiaire
- [ ] Ajouter des règles d'acquisition de badges automatiques
- [ ] Implémenter un moteur de prérequis minimal (accès conditionnel entre modules)
- [ ] Audit d'accessibilité WCAG 2.1 niveau AA et corrections (contrastes, ARIA, navigation clavier)
- [ ] Documentation utilisateur : guide stagiaire, guide formateur (PDF + vidéo)
- [ ] Nettoyer les vues de template génériques (`resources/views/content/apps/*`)
- [x] Appliquer dans les menus la convention du glossaire : "Ma formation" côté stagiaire, "Catalogue" / "Créations" / "Parcours" côté formateur (2026, voir [12-glossaire.md](12-glossaire.md))

---

## Phase 4 — Exploitation professionnelle

**Objectif : rendre la plateforme commercialisable à des organismes institutionnels.**  
**Durée estimée : 8 à 18 mois après Phase 1.**

- [ ] Multi-organisation (organismes, coordinateurs, conventions de formation)
- [ ] Rôle coordinateur/financeur avec périmètre de données restreint
- [ ] Reporting institutionnel (export PDF certifié, archivage des preuves d'apprentissage)
- [ ] Gestion de sessions de formation avec présences et émargements
- [ ] Mode très simplifié stagiaire (un bouton "Continuer ma formation", affichage minimal)
- [ ] Conformité RGPD documentée et testée (durée de conservation, base légale, export individuel)
- [ ] Supervision et monitoring production (alertes, logs centralisés, sauvegardes automatisées)

---

## Idées d'outils futurs

Voir [docs/idees-outils-formateurs.md](../idees-outils-formateurs.md) pour le détail des 8 pistes identifiées :

| ID | Outil | Priorité |
|----|-------|----------|
| OF-001 | Cockpit de séance formateur | Haute |
| OF-002 | Ticket de sortie | Haute |
| OF-003 | Mur de questions anonyme | Haute |
| OF-004 | Émargement QR code | Moyenne |
| OF-005 | Générateur d'activités IA | Moyenne |
| OF-006 | Groupes intelligents | Moyenne |
| OF-007 | Débrief / rétrospective | Moyenne |
| OF-008 | Analytics pédagogiques avancées | Moyenne |
| OF-009 | Import de cours par IA (PowerPoint, PDF) | Moyenne |
| OF-010 | Clic sur zones d'image | Moyenne |
| OF-011 | File d'attente de parole | Moyenne |
| OF-012 | Vrai / Faux express | Moyenne |
| OF-013 | Roue aléatoire (améliorée) | Moyenne |

---

## Nouveaux blocs de contenu de leçon (inspiration Articulate Rise)

Le bloc SCORM (juillet 2026, voir [docs/wiki/05-modules-scorm-quiz.md](05-modules-scorm-quiz.md)) a établi le pattern pour ajouter un nouveau type de bloc dans l'éditeur de leçon : composant JS dans `resources/js/formateur-module-builder-editor.jsx`, validation dans `NettoyeurBlocsModule::sanitizeBlocks()`, rendu lecture dans `resources/views/shared/lecture_blocks.blade.php`. Les blocs suivants, inspirés des blocs interactifs d'Articulate Rise, sont identifiés comme prochaines pistes :

| ID | Bloc | Description | Priorité |
|----|------|--------------|----------|
| BC-001 | Carrousel / Processus | Suite d'étapes numérotées navigables (précédent/suivant), chaque étape avec titre + texte + image optionnelle. Utile pour une chronologie, un processus ou les étapes d'une procédure | Moyenne |
| BC-002 | Flashcards | Cartes recto/verso (question/réponse ou terme/définition) que le stagiaire retourne au clic. Simple à créer pour du vocabulaire ou de la mémorisation | Moyenne |
| BC-003 | Tri (sorting) | Cartes à glisser-déposer dans 2 catégories nommées par le formateur (ex. Vrai/Faux, Avant/Après, Bonne pratique/Mauvaise pratique) | Moyenne |
| BC-004 | Bouton "Continuer" en fin de leçon | Bouton qui se débloque une fois la leçon parcourue en entier (scroll jusqu'en bas et/ou blocs interactifs complétés), pour marquer explicitement la progression avant de passer à la suite | Moyenne |

**BC-001 — Carrousel / Processus** : nouveau type de bloc `carousel` avec une liste d'étapes (`{title, text, image_id}`) ; contenu passif, pas de suivi de progression nécessaire.

**BC-002 — Flashcards** : bloc `flashcard` avec une liste de cartes (`{front, back}`) et un retournement visuel au clic/tap ; peut être décliné en grille ou en défilement une carte à la fois.

**BC-003 — Tri (sorting)** : bloc `sorting` avec deux catégories nommées et une liste d'éléments à classer (`{label, category}`). Peut réutiliser le pattern de drag-and-drop déjà en place pour réordonner les leçons dans le plan du module (`resources/js/outline-editor/lesson-item-node.js`), plus une validation visuelle bonne/mauvaise réponse après dépôt.

**BC-004 — Bouton "Continuer" en fin de leçon** : aujourd'hui, seuls le SCORM (`afficherBoutonSuivantDepuisIframe()` dans `public/scorm_core/js/API.js`) et les quiz pilotent la navigation vers la leçon suivante. Une leçon en blocs (texte/image/vidéo/carrousel/flashcards/tri) n'a aucun mécanisme de progression explicite : ce bouton se déclenche en bas de la page de leçon, cohérent avec le comportement SCORM existant, et pourrait aussi servir de brique pour le mode stagiaire très simplifié envisagé en Phase 4 (bouton "Continuer ma formation").

---

## Résumé de maturité (analyse juillet 2026)

| Axe | État actuel | Cible proche |
|-----|-------------|--------------|
| Maturité technique | Build au vert, tests verts (124), architecture encore concentrée dans gros contrôleurs | Policies, FormRequests corrigées, contrôleurs découpés |
| Maturité pédagogique | Modules, quiz, SCORM, outils live et parcours déjà exploitables | Certificats, exports, prérequis et preuves SCORM complètes |
| Expérience utilisateur | Espaces par rôle complets, accès stagiaire simplifié, menus alignés sur "Ma formation" / "Catalogue" / "Créations" / "Parcours" | Multi-groupe stagiaire corrigé |
| Publication GitHub | Base légale/documentaire en place, checklist sécurité S3-S9 résolue (5 juillet 2026) | Historique Git vérifié, gap `isVisibleTo()` (StagiaireModuleDetail/LectureController) traité, crawl public vert |

---

[Retour au wiki](README.md)
