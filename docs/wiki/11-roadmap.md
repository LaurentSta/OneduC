# 11 — Roadmap & dette technique

## État actuel (mai 2026)

La plateforme est **utilisable en pilote contrôlé** (10 à 50 stagiaires, 3 à 5 formateurs, contexte associatif ou ateliers numériques). Elle n'est pas encore prête pour une mise en production large ni pour une présentation à des financeurs institutionnels sans les corrections de la Phase 1.

---

## Bugs critiques à corriger avant tout pilote

| # | Bug | Fichier | Impact immédiat |
|---|-----|---------|-----------------|
| B1 | Route debug admin accessible sans auth | `routes/web.php:220` | Fuite de données personnelles |
| B2 | Route reset-progression hors middleware admin | `routes/web.php:273` | Modification non autorisée |
| B3 | `Module::isVisibleTo()` ne vérifie pas le groupe | `app/Models/Module.php:84` | Accès à tout module actif par URL |
| B4 | `LessonFeedbackController::store()` route inexistante | `app/Http/Controllers/LessonFeedbackController.php:32` | Erreur 500 sur tout retour de leçon |
| B5 | Connexion par code sans throttling | `routes/web.php` | Brute-force sur code 6 caractères |
| B6 | 40 tests en échec sur 85 | `tests/Feature/*` | Impossible de garantir la stabilité |

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
| `EvaluationSCORMController::fin()` sans `use Evaluation` | `EvaluationSCORMController.php` | Basse |
| `Group` sans `SoftDeletes` | `app/Models/Group.php` | Moyenne — données orphelines |
| Stagiaire multi-groupe — `.first()` uniquement | `StagiaireController.php:244` | Haute — modules masqués |
| `ModuleController` 1185 lignes | `Backend/ModuleController.php` | Maintenance difficile |
| `StagiaireController` 903 lignes | `StagiaireController.php` | Maintenance difficile |

---

## Phase 1 — Sécurisation et stabilisation

**Objectif : rendre la plateforme sûre et stable pour un pilote.**  
**Durée estimée : 1 à 2 mois.**

- [ ] Déplacer les routes debug/reset dans `routes/admin.php` avec middleware complet
- [ ] Corriger `Module::isVisibleTo()` pour vérifier l'appartenance au groupe du stagiaire
- [ ] Corriger `LessonFeedbackController::store()` avec la bonne route de redirection
- [ ] Ajouter `throttle:10,1` sur la route de connexion par code d'accès
- [ ] Créer la migration `add_last_session_time_to_scorm_scores`
- [ ] Mettre à jour `UserFactory` : formateurs avec `adhesion_status = 'active'` par défaut en test
- [ ] Corriger les tests d'authentification et remettre la suite au vert
- [ ] Supprimer l'import mort `ScormInteractionController` dans `routes/scorm.php`
- [ ] Corriger `EvaluationSCORMController::fin()` avec `use App\Models\Evaluation`
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

## Résumé des notes de maturité (audit Claude, mai 2026)

| Axe | Note | Cible après Phase 1 |
|-----|------|---------------------|
| Maturité technique | 11/20 | 14/20 |
| Maturité pédagogique | 14/20 | 15/20 |
| Expérience utilisateur | 13/20 | 14/20 |
| Potentiel commercial | 15/20 | 16/20 |
| Capacité LMS globale | 12/20 | 15/20 |

---

[Retour au wiki](README.md)
