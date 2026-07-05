# 11 — Roadmap & dette technique

## État actuel (5 juillet 2026)

La plateforme est **utilisable en pilote contrôlé** (10 à 50 stagiaires, 3 à 5 formateurs, contexte associatif ou ateliers numériques). Le build Vite réussit, mais la validation du 5 juillet signale 1 test rouge sur 104 et une page publique en erreur 500 (`/inscription`). Ces points doivent être corrigés avant publication publique large.

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
| B0 | `/inscription` appelle `UserController::Register()` absent | `routes/web.php` | Erreur 500 sur page publique |
| B1 | Connexion par code sans throttling | `routes/web.php` | Brute-force sur code 6 caractères |
| B2 | `LessonFeedbackController::store()` redirige vers `module.lesson`, route inexistante | `app/Http/Controllers/LessonFeedbackController.php` | Erreur 500 sur retour de leçon |
| B3 | `Module::isVisibleTo()` ne vérifie pas l'appartenance groupe | `app/Models/Module.php` | Risque d'accès direct à un module actif par URL |
| B4 | `SCORMController` écrit `last_session_time`, colonne absente de `scorm_scores` | `app/Http/Controllers/SCORMController.php` | Erreur SQL possible sur `cmi.core.session_time` legacy |
| B5 | `POST /scorm/save-progress` ne vérifie pas l'appartenance à la leçon | `routes/scorm.php`, `SCORMController` | Soumission de score non autorisée possible |
| B6 | `StoreModuleRequest` / `StoreGroupeRequest` retournent `authorize() = false` | `app/Http/Requests/` | FormRequests inutilisables |
| B7 | Upload image builder : test attend `path`, contrôleur retourne `media_id`/`url` | `ModuleBuilderController`, `ModuleBuilderTest` | Suite de tests rouge et contrat frontend à clarifier |

---

## Dette technique connue

| Élément | Localisation | Gravité |
|---------|-------------|---------|
| `/inscription` en erreur 500 | `routes/web.php:174` | Haute — page publique cassée |
| Contrat d'upload image builder instable | `ModuleBuilderController::uploadImage()` | Haute — test rouge et risque éditeur de blocs |
| `last_session_time` utilisé sans colonne migrée dans `scorm_scores` | `SCORMController.php:99` | Haute — cumul temps SCORM legacy défaillant |
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
- [ ] Corriger `/inscription` ou rediriger explicitement vers `/inscription-formateur`
- [ ] Stabiliser le contrat d'upload image du builder et remettre `ModuleBuilderTest` au vert
- [ ] Corriger `Module::isVisibleTo()` ou introduire des policies pour vérifier l'appartenance au groupe
- [ ] Corriger `LessonFeedbackController::store()` avec la bonne route de redirection selon le rôle/contexte
- [ ] Ajouter `throttle:10,1` ou rate limiter dédié sur la route de connexion par code d'accès
- [ ] Ajouter la colonne `last_session_time` à `scorm_scores` ou modifier le cumul de temps SCORM
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
- [ ] Valider l'unicité de l'email dans `AdminController::AdminProfilStore()`
- [ ] Ajouter la vérification d'appartenance à la leçon dans `POST /scorm/save-progress`
- [ ] Découper `StagiaireController` : dashboard, modules, résultats, outils

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
- [ ] Appliquer dans les menus la convention du glossaire : "Ma formation" côté stagiaire, "Mes parcours" et "Mes modules" côté formateur

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
| Maturité technique | Build au vert, 1 test rouge, architecture encore concentrée dans gros contrôleurs | Tests verts, policies, FormRequests corrigées, contrôleurs découpés |
| Maturité pédagogique | Modules, quiz, SCORM, outils live et parcours déjà exploitables | Certificats, exports, prérequis et preuves SCORM complètes |
| Expérience utilisateur | Espaces par rôle complets, accès stagiaire simplifié, convention vocabulaire documentée | Menus alignés sur "Ma formation", "Mes parcours", "Mes modules" et multi-groupe stagiaire corrigé |
| Publication GitHub | Base légale/documentaire en place, mais `/inscription` et un test doivent être corrigés | Historique Git vérifié, checklist sécurité résolue, crawl public vert |

---

[Retour au wiki](README.md)
