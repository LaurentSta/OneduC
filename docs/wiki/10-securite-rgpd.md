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

**Point de vigilance** : cette route n'a pas de throttling. Un code à 6 caractères est attaquable par force brute. Voir [Roadmap Phase 1](11-roadmap.md).

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
| Tests liés au middleware `association.member` | La suite du 5 juillet passe sur ces scénarios ; l'unique échec concerne le contrat d'upload image du builder |

### Points à corriger avant publication publique

| # | Risque | Localisation | Impact |
|---|--------|--------------|--------|
| 1 | `/inscription` retourne une erreur 500 | `routes/web.php` appelle `UserController::Register()` absent | Page publique cassée, à corriger avant publication |
| 2 | Connexion par code sans throttling | `POST /stagiaire/connexion-code` (`web` uniquement) | Brute-force possible sur les codes à 6 caractères |
| 3 | `LessonFeedbackController::store()` redirige vers `module.lesson`, route inexistante | `app/Http/Controllers/LessonFeedbackController.php` | Erreur 500 probable à la soumission d'un retour de leçon |
| 4 | `Module::isVisibleTo()` autorise tout module actif pour les non-admins | `app/Models/Module.php` | Risque d'accès direct à un module actif hors affectation groupe selon la route |
| 5 | `POST /scorm/save-progress` sans CSRF et sans vérification d'appartenance à la leçon | `routes/scorm.php`, `SCORMController::saveProgress()` | Soumission de progression possible pour une leçon non affectée si l'utilisateur est authentifié |
| 6 | `SCORMController` écrit `last_session_time`, absent du schéma `scorm_scores` | `SCORMController::handleSessionTime()` | Erreur SQL possible lors de la réception de `cmi.core.session_time` sur SCORM legacy |
| 7 | Contrat d'upload image du builder instable | `ModuleBuilderController::uploadImage()` retourne `media_id`/`url`, le test attend `path` | Suite de tests rouge, risque de rupture éditeur de blocs |
| 8 | `StoreModuleRequest::authorize()` et `StoreGroupeRequest::authorize()` retournent `false` | `app/Http/Requests/` | FormRequests inutilisables tant qu'ils ne sont pas corrigés |
| 9 | `AdminController::AdminProfilStore()` ne valide pas l'unicité email avec exclusion de l'utilisateur courant | `AdminController` | Collision possible avec l'email d'un autre compte |
| 10 | Import mort `ScormInteractionController` | `routes/scorm.php` | Dette technique faible, à nettoyer |

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

La compensation de sécurité consiste à vérifier que `Auth::id()` existe dans le contrôleur. **Il manque** la vérification que l'utilisateur authentifié est bien autorisé à écrire la progression de la leçon en cours. Attention aussi à la distinction entre les deux stockages : `content_block_scorm_scores` contient bien `last_session_time`, mais `scorm_scores` ne le contient pas dans la baseline actuelle.

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
