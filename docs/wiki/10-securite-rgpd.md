# 10 — Sécurité & RGPD

*Public : développeurs et administrateurs système.*

## Authentification

### Mode standard
Email + mot de passe (bcrypt via Laravel). Le middleware `role` vérifie simultanément :
- que l'utilisateur est authentifié
- que son `role` correspond à l'espace demandé
- que son compte est actif (`users.status`)

### Mode code d'accès (stagiaire)
Route : `POST /stagiaire/connexion-code`  
Contrôleur : `UserController::loginByCode()`  
Fonctionnement : le stagiaire saisit uniquement un code alphanumérique 6 caractères (`users.code_acces`). Aucun email ni mot de passe n'est requis.

Throttling : rate limiter nommé `connexion-code` (10 tentatives/minute par IP, 429 au-delà), défini dans `AppServiceProvider` et appliqué à la route depuis le 5 juillet 2026.

### Première connexion
Le middleware `ForcePasswordChange` bloque l'accès à l'espace stagiaire tant que `users.password_changed_at` est nul, sauf pour les routes `/stagiaire/premiere-connexion`. L'utilisateur est redirigé vers un formulaire de changement de mot de passe (validation `min:8`). Au 5 juillet 2026, ce middleware n'est pas appliqué à l'espace formateur.

---

## Politique d'adhésion formateur

Le middleware `EnsureAssociationMembership` (`app/Http/Middleware/EnsureAssociationMembership.php`) vérifie pour chaque requête formateur :

```
SI adhesion_status = 'active' ET adhesion_valid_until > aujourd'hui → Accès autorisé
SI adhesion_status = 'pending' ET created_at + 30 jours > aujourd'hui → Accès autorisé (grâce)
SINON → Redirection vers /adhesion
```

C'est ce mécanisme qui fait tenir le modèle associatif : l'accès formateur reste conditionné à une adhésion à jour.

---

## Middleware de journalisation admin

`RecordAdminActivity` (`app/Http/Middleware/RecordAdminActivity.php`) journalise automatiquement toutes les actions destructives (POST/PUT/PATCH/DELETE) des routes `admin.*` dans `activity_journal_entries` :

- Action horodatée avec l'ID de l'admin
- Données de la requête sanitisées (champs sensibles masqués, textes longs tronqués)
- Consultable dans l'interface de pilotage admin

---

## Gestion des mots de passe et données sensibles

| Donnée | Protection |
|--------|-----------|
| Mots de passe utilisateurs | Hash bcrypt via Laravel (irréversible) |
| Code d'accès temporaire groupe | Chiffrement via cast Laravel `encrypted` (réversible pour affichage) |
| Codes d'accès stagiaires | Générés aléatoirement par `CodeGeneratorService` (`users.code_acces`) |
| Codes d'accès outils live | Générés par les contrôleurs d'outils (`access_code` sur les sessions) |

---

## Risques de sécurité identifiés (analyse 5 juillet 2026)

### Points corrigés depuis l'ancien audit

| Point | État vérifié |
|-------|--------------|
| Route `/admin/stagiaires/{id}/debug-progression` | Aucune route correspondante trouvée dans `php artisan route:list --json` |
| `POST /admin/stagiaires/{user}/reset-progression` | Route présente dans `routes/admin.php`, protégée par `auth`, `role:admin`, `admin.activity` |
| Tests liés au middleware `association.member` | La suite du 5 juillet passe sur ces scénarios |
| `/inscription`, connexion par code, `LessonFeedbackController::store()`, `Module::isVisibleTo()`, SCORM (`save-progress`/`save-block-progress`/`evaluation-progress`), `last_session_time`, contrat d'upload image du builder | Corrigés le 5 juillet 2026 (voir [Checklist de publication](13-publication-github.md), Axe 1, S3 à S9). Suite de tests complète verte (124 tests). |

### Corrections du 7 juillet 2026

| Point | Correctif |
|-------|-----------|
| XSS stocké dans les blocs de contenu texte libre (builder de module) | `NettoyeurBlocsModule` filtre désormais le HTML par allowlist de balises/attributs avant stockage |
| Open redirect sur la validation de leçon | `Frontend\LectureController` valide que la cible de redirection reste un chemin interne |
| Zip slip / manifest hors-répertoire à l'import SCORM | `ScormImporter` rejette les entrées ZIP avec segments `..` et vérifie que le manifest reste dans le dossier extrait |
| IDOR sur `FormateurModuleController::updateQuizCount()` | Vérification d'appartenance du module au formateur (même contrôle que `preview()`) ajoutée avant modification |
| Upload SVG accepté comme photo de profil (XSS stocké potentiel) | `UserController::UserProfilStore()` et `FormateurProfileController::FormateurProfilStore()` restreignent désormais l'upload à `mimes:jpg,jpeg,png` |
| Upload SVG accepté comme image de catégorie/sous-catégorie | `CategoryController` retire `image/svg+xml` des types acceptés |

### Gap identifié lors du correctif S3 (à traiter)

`Module::isVisibleTo()` est désormais le point de vérité pour la visibilité d'un module, mais deux points d'accès ne l'appellent pas et n'ont **aucune** vérification d'appartenance groupe :
- `StagiaireController::StagiaireModuleDetail()` (route `stagiaire.module.detail`) — ne vérifie que `Module::active()`
- `Frontend\LectureController` (`show`, `showScorm`, `showScormBlock`, `showSlides`) — aucune vérification ; `showScorm` (route `lecture.scorm`) n'est même pas derrière le middleware `auth`

Un stagiaire authentifié (ou, pour `showScorm`, un visiteur non authentifié) peut donc encore accéder au détail ou au contenu d'une leçon dont le module n'est pas affecté à son groupe, en devinant/énumérant les identifiants. À corriger avant publication, ou au minimum documenter le risque.

### Points restants (hors périmètre du correctif du 5 juillet 2026)

| # | Risque | Localisation | Impact |
|---|--------|--------------|--------|
| 8 | `StoreModuleRequest::authorize()` et `StoreGroupeRequest::authorize()` retournent `false` | `app/Http/Requests/` | FormRequests inutilisables tant qu'ils ne sont pas corrigés |
| 9 | `AdminController::AdminProfilStore()` ne valide pas l'unicité email avec exclusion de l'utilisateur courant | `AdminController` | Collision possible avec l'email d'un autre compte |
| 10 | Import mort `ScormInteractionController` | `routes/scorm.php` | Dette technique faible, à nettoyer |
| 11 | `/register` (scaffold Breeze) reste public et crée des comptes `role => 'stagiaire'` sans code d'accès ni invitation | `routes/auth.php`, `Auth\RegisteredUserController::store` | Contourne le modèle d'accès par code documenté plus haut. **Décision produit à trancher** : désactiver la route ou assumer l'auto-inscription. Non modifié lors de l'audit du 7 juillet 2026. |
| 12 | Modèle d'autorisation incohérent entre les 7 outils interactifs : Nuage de mots et Roue aléatoire sont accessibles sans authentification, les 5 autres (Sondage, Mur de questions, Quiz live, Tableau blanc, Minuteur) exigent une authentification et/ou une appartenance de groupe | `routes/web.php` (routes de participation), contrôleurs `Formateur/*Controller` | Confidentialité effective variable d'un outil à l'autre pour un usage a priori similaire. **Décision produit à trancher** avant toute uniformisation. Non modifié lors de l'audit du 7 juillet 2026. |

---

## Suppression de compte et données liées

La suppression d'un compte stagiaire déclenche `cleanupRelatedStagiaireData()` dans le modèle `User`. Cette méthode supprime :
- Les progressions
- Les scores SCORM
- Les tentatives quiz
- Les réponses aux outils

La suppression d'un compte formateur via `cleanupOwnedGroupsAndLinkedStagiaires()` peut déclencher des suppressions en cascade sur les groupes et les stagiaires associés. C'est un mécanisme puissant qui nécessite une confirmation explicite et une trace dans le journal admin.

---

## SCORM et CSRF

Les routes `/scorm/save-progress`, `/scorm/save-block-progress` et `/scorm/evaluation-progress` désactivent le middleware CSRF (`VerifyCsrfToken::class`). C'est techniquement nécessaire car les packages SCORM s'exécutent dans un iframe et ne peuvent pas inclure le token CSRF Laravel.

La compensation de sécurité vérifie que `Auth::id()` existe (middleware `auth`, ajouté le 5 juillet 2026 sur les 3 routes) **et** que l'utilisateur authentifié est bien autorisé à écrire la progression de la leçon/évaluation en cours (`User::aAccesAuModule()` — stagiaire d'un groupe actif auquel le module est affecté), avec 403 sinon. `scorm_scores` contient désormais `last_session_time`, au même titre que `content_block_scorm_scores`.

---

## Considérations RGPD

Oneduc collecte des données d'apprentissage qui sont des **données personnelles** au sens du RGPD :

| Donnée collectée | Table | Finalité |
|-----------------|-------|----------|
| Temps de connexion | `video_segment_trackings`, `scorm_scores.session_time` | Suivi pédagogique |
| Scores et résultats | `quiz_attempts`, `scorm_scores` | Évaluation, suivi |
| Réponses aux questions | `quiz_attempt_questions` | Analyse pédagogique |
| Activité horodatée | `progressions`, `quiz_attempts` | Reporting |
| Adresse email | `users` | Authentification, communication |

### Points à documenter avant mise en production

1. **Durée de conservation** : combien de temps les données de formation sont-elles conservées ?
2. **Finalités** : formation initiale, attestation, amélioration du contenu ?
3. **Base légale** : contrat de formation (art. 6.1.b), intérêt légitime, consentement ?
4. **Droit d'accès** : procédure pour qu'un stagiaire obtienne ses données
5. **Droit à l'effacement** : la méthode `cleanupRelatedStagiaireData()` existe mais doit être documentée et exposée à l'utilisateur
6. **Portabilité** : export des données en format lisible (pas encore implémenté)
7. **Sous-traitants** : hébergeur, service mail, Discord (si utilisé)

### Ce qui est déjà en place

- Soft delete des utilisateurs (les données ne sont pas supprimées immédiatement)
- Nettoyage des données liées à la suppression de compte
- Aucun tiers tracker côté frontend identifié
- Cookie consent via `spatie/laravel-cookie-consent`

---

## Bonnes pratiques développeur

```bash
# Vérifier les routes exposées publiquement
php artisan route:list --path=admin | grep -v "auth"

# Vérifier les FormRequests avec authorize() retournant false
grep -r "return false" app/Http/Requests/

# Vérifier les imports morts
grep -r "use App\\Http\\Controllers\\Scorm" routes/
```

---

[Retour au wiki](README.md)
