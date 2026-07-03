# 11 — Roadmap & dette technique

## État actuel (3 juillet 2026)

La plateforme est **utilisable en pilote contrôlé** (10 à 50 stagiaires, 3 à 5 formateurs, contexte associatif ou ateliers numériques). La suite automatisée est au vert (`102 tests passés`) et le build Vite réussit, mais quelques corrections restent nécessaires avant publication publique large.

---

## Corrections prioritaires avant publication publique

| # | Bug | Fichier | Impact immédiat |
|---|-----|---------|-----------------|
| B1 | Connexion par code sans throttling | `routes/web.php` | Brute-force sur code 6 caractères |
| B2 | `LessonFeedbackController::store()` redirige vers `module.lesson`, route inexistante | `app/Http/Controllers/LessonFeedbackController.php` | Erreur 500 sur retour de leçon |
| B3 | `Module::isVisibleTo()` ne vérifie pas l'appartenance groupe | `app/Models/Module.php` | Risque d'accès direct à un module actif par URL |
| B4 | `SCORMController` écrit `last_session_time`, colonne absente | `app/Http/Controllers/SCORMController.php` | Erreur SQL possible sur `cmi.core.session_time` |
| B5 | `POST /scorm/save-progress` ne vérifie pas l'appartenance à la leçon | `routes/scorm.php`, `SCORMController` | Soumission de score non autorisée possible |
| B6 | `StoreModuleRequest` / `StoreGroupeRequest` retournent `authorize() = false` | `app/Http/Requests/` | FormRequests inutilisables |

---

## Dette technique connue

| Élément | Localisation | Gravité |
|---------|-------------|---------|
| `last_session_time` utilisé sans colonne migrée | `SCORMController.php:99` | Haute — cumul temps SCORM défaillant |
| `attempts_count` SCORM jamais incrémenté | `SCORMController.php` | Moyenne |
| `scorm_interactions` non alimenté pour les leçons | `SCORMController::saveProgress()` | Haute — métriques analytiques vides |
| `StoreModuleRequest::authorize()` retourne `false` | `app/Http/Requests/` | Haute — FormRequest inutilisable |
| `StoreGroupeRequest::authorize()` retourne `false` | `app/Http/Requests/` | Haute — FormRequest inutilisable |
| Import mort `ScormInteractionController` | `routes/scorm.php:6` | Basse |
| `Group` sans `SoftDeletes` | `app/Models/Group.php` | Moyenne — données orphelines |
| Stagiaire multi-groupe — `.first()` uniquement | `StagiaireController.php:244` | Haute — modules masqués |
| `ModuleController` 1185 lignes | `Backend/ModuleController.php` | Maintenance difficile |
| `StagiaireController` 916 lignes | `StagiaireController.php` | Maintenance difficile |

---

## Phase 1 — Sécurisation et stabilisation

**Objectif : rendre la plateforme sûre et stable pour un pilote.**  
**État : partiellement réalisée.**

- [x] Déplacer `reset-progression` dans `routes/admin.php` avec middleware complet
- [x] Supprimer ou retirer la route debug admin non authentifiée
- [x] Mettre à jour `UserFactory` : formateurs avec `adhesion_status = active` par défaut en test
- [x] Remettre la suite de tests au vert (`102 tests passés`)
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
- [ ] Unifier le vocabulaire dans les menus (voir [Glossaire](12-glossaire.md))

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

---

## Résumé de maturité (analyse juillet 2026)

| Axe | État actuel | Cible proche |
|-----|-------------|--------------|
| Maturité technique | Tests et build au vert, architecture encore concentrée dans gros contrôleurs | Policies, FormRequests corrigées, contrôleurs découpés |
| Maturité pédagogique | Modules, quiz, SCORM, outils live et parcours déjà exploitables | Certificats, exports, prérequis et preuves SCORM complètes |
| Expérience utilisateur | Espaces par rôle complets, accès stagiaire simplifié | Vocabulaire unifié et multi-groupe stagiaire corrigé |
| Publication GitHub | Base légale/documentaire en place | Historique Git vérifié, checklist sécurité résolue |

---

[Retour au wiki](README.md)
