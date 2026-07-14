# 10 — Sécurité & RGPD

*Public : développeurs et administrateurs système.*

## Authentification

### Mode standard
Email + mot de passe (bcrypt via Laravel). Le middleware `role` vérifie simultanément :
- que l'utilisateur est authentifié
- que son `role` correspond à l'espace demandé
- que son compte est actif (`users.status`)

`LoginRequest` ajoute également `status = true` aux identifiants transmis à `Auth::attempt()`. Un compte dont `users.status = false` ne peut donc pas ouvrir de session par email, même avec le bon mot de passe. Il reçoit l'erreur générique d'authentification et reste déconnecté.

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

`RecordAdminActivity` (`app/Http/Middleware/RecordAdminActivity.php`) journalise automatiquement toutes les actions d'écriture (POST/PUT/PATCH/DELETE) des routes `admin.*` dans `activity_journal_entries` :

- Action horodatée avec l'ID de l'admin
- Uniquement les réponses réussies, dont le code HTTP est inférieur à 400
- Données de la requête sanitisées (champs sensibles exclus, textes longs tronqués)
- Consultable dans l'interface de pilotage admin

Pour les nouveaux formulaires de gestion des formateurs et stagiaires, le contexte exclut les champs nominatifs et de contact (`prenom`, `name`, `username`, `email`, téléphone, adresse, société), les mots de passe et le code d'accès. Les champs nécessaires au suivi opérationnel, par exemple `role`, `status`, `formateur_id` ou les identifiants de groupes, peuvent encore être enregistrés.

---

## Gestion des mots de passe et données sensibles

| Donnée | Protection |
|--------|-----------|
| Mots de passe utilisateurs | Hash bcrypt via Laravel (irréversible) |
| Code d'accès temporaire groupe | Chiffrement via cast Laravel `encrypted` (réversible pour affichage) |
| Codes d'accès stagiaires | Générés aléatoirement par `CodeGeneratorService` (`users.code_acces`) |
| Codes d'accès outils live | Générés par les contrôleurs d'outils (`access_code` sur les sessions) |

---

## Risques de sécurité identifiés (analyse mise à jour le 14 juillet 2026)

### Points corrigés depuis l'ancien audit

| Point | État vérifié |
|-------|--------------|
| Route `/admin/stagiaires/{id}/debug-progression` | Aucune route correspondante trouvée dans `php artisan route:list --json` |
| `POST /admin/stagiaires/{user}/reset-progression` | Route présente dans `routes/admin.php`, protégée par `auth`, `role:admin`, `admin.activity` |
| Tests liés au middleware `association.member` | La suite du 5 juillet passe sur ces scénarios |
| `/inscription`, connexion par code, `LessonFeedbackController::store()`, `Module::isVisibleTo()`, SCORM (`save-progress`/`save-block-progress`/`evaluation-progress`), `last_session_time`, contrat d'upload image du builder | Corrigés le 5 juillet 2026 (voir [Checklist de publication](13-publication-github.md), Axe 1, S3 à S9). Suite de tests complète verte (124 tests). |
| Connexion email d'un compte inactif | `LoginRequest` exige désormais `status = true` pendant l'authentification |
| Unicité de l'email du profil administrateur | `AdminController::AdminProfilStore()` utilise `Rule::unique(...)->ignore($user->id)` |
| Données personnelles des nouveaux formulaires utilisateurs dans le journal admin | Les champs nominatifs, coordonnées, mots de passe et codes d'accès sont exclus du contexte journalisé |
| Remise à zéro partielle de la progression | L'opération est transactionnelle et couvre désormais quiz, progression, vidéo, SCORM classique, évaluations et blocs SCORM modernes |

### Gap identifié lors du correctif S3 (à traiter)

`Module::isVisibleTo()` est désormais le point de vérité pour la visibilité d'un module, mais deux points d'accès ne l'appellent pas et n'ont **aucune** vérification d'appartenance groupe :
- `StagiaireController::StagiaireModuleDetail()` (route `stagiaire.module.detail`) — ne vérifie que `Module::active()`
- `Frontend\LectureController` (`show`, `showScorm`, `showScormBlock`, `showSlides`) — aucune vérification ; `showScorm` (route `lecture.scorm`) n'est même pas derrière le middleware `auth`

Un stagiaire authentifié (ou, pour `showScorm`, un visiteur non authentifié) peut donc encore accéder au détail ou au contenu d'une leçon dont le module n'est pas affecté à son groupe, en devinant/énumérant les identifiants. À corriger avant publication, ou au minimum documenter le risque.

### Points restants (hors périmètre du correctif du 5 juillet 2026)

| # | Risque | Localisation | Impact |
|---|--------|--------------|--------|
| 8 | `StoreModuleRequest::authorize()` et `StoreGroupeRequest::authorize()` retournent `false` | `app/Http/Requests/` | FormRequests inutilisables tant qu'ils ne sont pas corrigés |
| 10 | Import mort `ScormInteractionController` | `routes/scorm.php` | Dette technique faible, à nettoyer |

---

## Remise à zéro de la progression stagiaire

La route `POST /admin/stagiaires/{user}/reset-progression` (`admin.stagiaires.reset`) refuse toute cible dont le rôle n'est pas `stagiaire`. L'effacement est exécuté dans une transaction : réponses et tentatives de quiz, progressions, suivi vidéo, notifications de fin de module, temps total de connexion, ainsi que les résultats, scores et interactions SCORM classiques et d'évaluation sont supprimés. Les tables modernes `content_block_scorm_scores` et `content_block_scorm_results` sont également couvertes.

Le contrôleur vérifie l'existence des tables optionnelles avant de les utiliser. Si une exception survient, la transaction est annulée et l'administrateur reçoit un message d'échec ; aucune réussite partielle ne doit être présentée comme une remise à zéro complète.

---

## Suppression de compte et données liées

> **Avertissement — opération irréversible :** les comptes `User` utilisent `SoftDeletes`, mais leurs événements de suppression exécutent aussi des purges physiques. Restaurer uniquement la ligne `users` ne restaure donc pas les groupes, progressions, scores ou réponses déjà effacés.

La suppression d'un compte stagiaire déclenche immédiatement `cleanupRelatedStagiaireData()` dans le modèle `User`. Cette méthode supprime notamment :
- Les progressions
- Les scores SCORM
- Les tentatives quiz
- Les réponses aux outils

La suppression d'un compte formateur via `cleanupOwnedGroupsAndLinkedStagiaires()` supprime physiquement tous les groupes dont il est `instructor_id`. Pour chaque stagiaire lié directement au formateur ou à l'un de ces groupes, le code recherche un autre formateur ou un autre groupe principal : il réaffecte le stagiaire lorsque c'est possible, sinon il supprime aussi son compte et déclenche sa purge de données liées.

La suppression directe d'un groupe admin est également physique, car le modèle `Group` n'utilise pas `SoftDeletes`. Ces trois opérations nécessitent une confirmation explicite, une vérification préalable des rattachements et une trace dans le journal admin. Il n'existe actuellement ni corbeille de groupes ni restauration complète des données pédagogiques purgées.

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

- Soft delete de la ligne utilisateur, avec avertissement nécessaire car les données liées peuvent être purgées immédiatement par les événements du modèle
- Nettoyage automatique des données liées à la suppression de compte, destructif et non intégralement réversible
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
