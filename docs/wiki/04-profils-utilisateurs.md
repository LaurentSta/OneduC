# 04 — Profils utilisateurs

*Public : tous les profils. Les détails techniques (routes, middleware) sont regroupés sous chaque rôle et en fin de page.*

La plateforme distingue quatre rôles. Chacun a son espace, son interface et ses droits. Un stagiaire ne voit jamais un écran de gestion ; un observateur ne peut rien modifier.

| Rôle | Espace | Peut créer du contenu |
|------|--------|----------------------|
| Admin | `/admin` | Oui (tout) |
| Formateur | `/formateur` | Oui (groupes, parcours, modules) |
| Stagiaire | `/stagiaire` | Non |
| Observateur | `/observateur` | Non |

---

## Administrateur

L'administrateur pilote la plateforme. Il gère le catalogue de modules, les comptes utilisateurs, les catégories, les référentiels de compétences, les badges et les évaluations. Toutes ses actions destructives sont journalisées automatiquement.

### Ce qu'il peut faire

**Utilisateurs** : créer, modifier, activer ou désactiver les formateurs (avec gestion du statut d'adhésion), gérer les stagiaires (dont la remise à zéro de progression) et les observateurs. La suppression d'un compte nettoie les données liées.

**Contenu pédagogique** : créer les modules du catalogue avec leurs sections et leçons, importer des packages SCORM et des slides, gérer la banque de questions quiz (avec import CSV), les évaluations SCORM et les catégories.

**Pilotage** : un module interne de suivi de projets (Kanban, tâches, journal, notifications), les référentiels de compétences, la consultation des retours stagiaires.

**Tableau de bord** : indicateurs volumétriques (nombre de modules, formateurs, stagiaires, groupes, leçons...).

### Ce qu'il ne peut pas encore faire

Exporter des données en CSV ou PDF, générer des certificats, gérer plusieurs organisations. La mise à jour d'email admin ne valide pas encore l'unicité.

### Détails techniques

Routes : `routes/admin.php` — middleware `auth` + `role:admin` + `admin.activity`.
Le middleware `RecordAdminActivity` journalise les actions POST/PUT/PATCH/DELETE dans `activity_journal_entries`, données sensibles sanitisées. La remise à zéro de progression passe par `admin.stagiaires.reset`, protégée par la chaîne complète. Le nettoyage à la suppression de compte est porté par `cleanupRelatedStagiaireData()`.

---

## Formateur

Le formateur est le rôle central côté terrain. Il crée ses groupes, y ajoute des stagiaires, leur affecte des modules, anime des séances et suit les progressions.

Son accès dépend de son adhésion à l'association : adhésion active, ou compte de moins d'un mois en attente d'adhésion. Au-delà, il est redirigé vers la page d'adhésion.

### Ce qu'il peut faire

**Groupes** : créer, modifier, activer ou désactiver un groupe ; générer un code d'accès pour les stagiaires ; ajouter des stagiaires existants ou en créer avec invitation par mail ; inviter des co-formateurs ; affecter des modules dans un ordre choisi ; masquer ou réordonner des leçons pour un groupe précis.

**Suivi** : vue par groupe (taux de réussite, stagiaires actifs, inactifs, non démarrés), vue par stagiaire leçon par leçon, vue par module, repérage des apprenants à risque.

**Parcours** : assembler des modules, nuages de mots et sondages dans un parcours ordonné, puis l'associer à un groupe. Dans l'interface, ce sont "Mes parcours".

**Modules personnels** : créer ses propres modules dans un éditeur de plan continu (chapitres et leçons, réordonnancement au clavier ou à la souris, duplication de leçon), éditer chaque leçon en blocs de contenu (texte, image, citation, séparateur), téléverser ses images, ou dupliquer un module du catalogue pour l'adapter. Les modules personnels n'apparaissent pas dans le catalogue public.

Limite à connaître : les leçons SCORM ou slides copiées depuis le catalogue restent dans la copie, mais leur contenu importé n'est pas modifiable — seul le titre peut être renommé.

**Animation** : tous les outils live (voir [Outils d'animation](07-outils-animation.md)).

**Ressources** : ajouter des liens ou fichiers à une leçon, avec visibilité stagiaire réglable par ressource.

### Ce qu'il ne peut pas encore faire

Importer lui-même un package SCORM complet (l'import reste côté admin), exporter les données de son groupe, générer des badges ou certificats en fin de parcours.

### Détails techniques

Routes : `routes/formateur.php` — middleware `auth` + `role:formateur` + `association.member`.
Politique d'adhésion (`EnsureAssociationMembership`) : accès si `adhesion_status = active` avec `adhesion_valid_until` non dépassée, ou `adhesion_status = pending` avec `created_at + 30 jours > aujourd'hui`.
Un formateur accède à un groupe soit comme `instructor_id`, soit comme co-formateur via `group_user.role_in_group = 'formateur'`. Le scope `Group::scopeAccessibleByTrainer()` couvre les deux cas — à utiliser systématiquement dans les contrôleurs formateur.
La personnalisation des leçons par groupe passe par `group_module_lectures`. Les modules personnels sont marqués `is_trainer_authored = true` et leurs images stockées dans `modules_formateur/module_{id}/images`. Les blocs de leçon acceptés : `text`, `image`, `list`, `quote`, `divider`.

---

## Stagiaire

Le stagiaire accède à sa formation, suit ses leçons, répond aux quiz et participe aux activités lancées par le formateur. Son interface est volontairement épurée.

### Deux façons de se connecter

Le mode classique : email et mot de passe. Le mode code d'accès : un code alphanumérique de 6 caractères, sans email ni mot de passe. C'est le mode privilégié pour les publics éloignés du numérique.

À la première connexion, le stagiaire doit définir un nouveau mot de passe avant d'accéder à son espace.

### Ce qu'il voit

**Tableau de bord** : son formateur référent (affiché en permanence), son temps d'apprentissage, son taux de réussite, sa progression par module.

**Formation** : "Ma formation" et le programme de son groupe, la navigation chapitre → leçon, la lecture des leçons SCORM, slides, vidéos et quiz. La progression se marque automatiquement.

**Résultats** : l'historique de ses tentatives de quiz, question par question, et ses scores SCORM.

**Outils** : les activités live lancées par le formateur.

**Profil** : modification de ses informations, de son mot de passe, et suppression de son compte.

### Limites connues

Un stagiaire présent dans plusieurs groupes actifs ne voit que les modules du premier groupe. C'est un bug identifié, à corriger en phase 2.

### Détails techniques

Routes : `routes/stagiaire.php` — middleware `auth` + `role:stagiaire` + `track.time` + `force.password.change` (hors routes de première connexion).
Connexion par code : `POST /stagiaire/connexion-code` (`UserController::loginByCode()`), sur `users.code_acces`. Le middleware `ForcePasswordChange` bloque tant que `users.password_changed_at` est nul (validation `min:8`).
Le bug multi-groupe vient du `.first()` dans `StagiaireController::StagiaireModules()`. L'accès direct aux leçons repose encore surtout sur `Module::isVisibleTo()` ; la vérification d'appartenance doit être centralisée via policies avant publication large.

---

## Observateur

L'observateur consulte sans intervenir. Il voit les groupes auxquels il est rattaché, la progression des stagiaires et les leçons de ces groupes, en lecture seule. Le rôle a été ajouté en mars 2026 — typiquement pour un coordinateur, un financeur ou un tuteur.

### Détails techniques

Routes : `routes/observateur.php` — middleware `auth` + `role:observateur`.
Rattachement aux groupes via `group_user.role_in_group = 'observateur'`.

---

## Diagramme simplifié des droits

```
Admin
  └── Tout faire (CRUD contenu, utilisateurs, configuration)

Formateur (avec adhésion active)
  └── Gérer ses groupes
      └── Affecter modules (du catalogue admin)
      └── Gérer ses stagiaires
      └── Animer des sessions live
      └── Consulter la progression

Stagiaire
  └── Accéder aux modules de son groupe
  └── Passer des quiz
  └── Participer aux outils live
  └── Consulter sa progression

Observateur
  └── Lire les progressions de ses groupes (lecture seule)
```

---

[Retour au wiki](README.md)
