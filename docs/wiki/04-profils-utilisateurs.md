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

L'administrateur pilote la plateforme. Il gère le catalogue de modules, les comptes utilisateurs, les groupes, les catégories, les référentiels de compétences, les badges et les évaluations. Ses actions d'écriture réussies sont journalisées automatiquement.

### Ce qu'il peut faire

**Utilisateurs** : consulter un répertoire dense des formateurs et stagiaires, le filtrer par rôle, statut ou rattachement à un groupe, le rechercher et le paginer ; créer, modifier, activer ou désactiver ces comptes. Le rôle choisi à la création est ensuite immuable. Pour un formateur, l'administrateur gère aussi l'entreprise et l'adhésion. Pour un stagiaire, il choisit le formateur principal et les groupes ; un code d'accès unique de six caractères est généré si le champ reste vide. Les observateurs conservent leur interface d'administration séparée.

**Groupes** : créer ou modifier un groupe en choisissant obligatoirement un formateur principal et, si nécessaire, des stagiaires existants ; consulter le nombre de membres et supprimer un groupe après confirmation.

**Progression stagiaire** : remettre à zéro, dans une transaction unique, les quiz, progressions, temps de connexion, suivis vidéo et données SCORM classiques, d'évaluation et de blocs modernes.

**Contenu pédagogique** : créer les formations officielles du catalogue Oneduc avec leurs chapitres et leçons, importer des packages SCORM et des slides, gérer les médias, l'audio, la banque de questions quiz (avec import CSV et génération IA), les évaluations SCORM et les catégories. Le nouveau constructeur admin reprend l'éditeur de plan et l'éditeur de blocs du formateur. Une création manuelle commence volontairement par un seul « Chapitre 1 » vide, sans leçon préremplie.

**Cycle de publication** : préparer une formation en brouillon, la relire dans l'aperçu, puis la publier explicitement. Une version publiée est immuable : toute évolution part d'une nouvelle version brouillon. La publication d'une nouvelle version n'impose aucune mise à jour aux groupes existants ; l'administrateur choisit les groupes à basculer, tandis que les autres restent rattachés à leur version précédente. Une version archivée disparaît du catalogue disponible, mais reste accessible aux groupes qui l'utilisent encore.

**Origine et référent** : les formations officielles sont attribuées au catalogue Oneduc. Un formateur référent peut être associé facultativement pour le suivi pédagogique, sans devenir propriétaire du master officiel. Il peut l'utiliser ou en créer une copie personnelle indépendante pour l'adapter.

**Créations formateur** : consulter les créations personnelles des formateurs en lecture seule et en dupliquer une dans le catalogue. La duplication crée une formation officielle indépendante ; elle ne modifie jamais l'original du formateur.

**Modèles de parcours** : préparer, publier et archiver des modèles globaux combinant des formations officielles et la configuration pédagogique des outils activés. Un formateur duplique un modèle publié dans « Mes parcours » avant de l'adapter. Voir [Modèles globaux de parcours](modeles-parcours.md).

**Pilotage** : un module interne de suivi de projets (Kanban, tâches, journal, notifications), les référentiels de compétences, la consultation des retours stagiaires.

**Tableau de bord** : indicateurs volumétriques (nombre de modules, formateurs, stagiaires, groupes, leçons...).

### Ce qu'il ne peut pas encore faire

Exporter des données en CSV ou PDF, générer des certificats, gérer plusieurs organisations. Les étapes génériques d'outil enregistrées dans les modèles globaux de parcours ne créent pas encore automatiquement leur session d'exécution ; ce raccord doit être réalisé outil par outil.

> **État d'intégration :** le nouveau constructeur de formations admin est disponible sur ses routes dédiées, mais reste en phase d'intégration et de vérification. L'ancien éditeur admin n'est pas encore remplacé ; aucun basculement d'interface définitif n'est annoncé à ce stade.

### Détails techniques

Routes principales : `routes/admin.php` — middleware `auth` + `role:admin` + `admin.activity`. Le constructeur moderne est isolé dans `routes/admin-constructeur-formations.php` sous `admin.formations.constructeur.*`, et les modèles globaux dans `routes/admin-modeles-parcours.php` sous `admin.modeles-parcours.*`.
Le contrôleur `UtilisateurController` et les routes `admin.utilisateurs.*` gèrent uniquement les rôles `formateur` et `stagiaire`. La création impose un email unique et un mot de passe confirmé d'au moins 12 caractères. La modification vérifie aussi l'unicité de l'email en excluant le compte courant ; le profil de l'administrateur applique désormais la même règle.

Pour un stagiaire, les rattachements sélectionnés sont synchronisés dans `group_user` avec `role_in_group = 'stagiaire'`. Si aucun `formateur_id` n'est fourni mais qu'un groupe est sélectionné, le formateur principal du premier groupe devient le formateur référent. Pour un formateur, les statuts d'adhésion acceptés sont `pending`, `active` et `expired` ; une adhésion activée sans date reçoit par défaut une validité d'un an.

Le middleware `RecordAdminActivity` journalise les actions POST/PUT/PATCH/DELETE réussies dans `activity_journal_entries`. Les nouveaux formulaires utilisateurs n'y versent pas les noms, coordonnées, identifiants, mots de passe ni codes d'accès ; les informations opérationnelles non nominatives, comme le rôle ou le statut, peuvent rester dans le contexte. La remise à zéro passe par `admin.stagiaires.reset`, protégée par la chaîne complète et refusée si le compte ciblé n'est pas stagiaire.

> **Avertissement — suppressions destructives :** le trait `SoftDeletes` du compte ne rend pas toutes les données récupérables. Supprimer un stagiaire efface immédiatement de nombreuses données pédagogiques liées. Supprimer un formateur supprime physiquement ses groupes (purge RGPD volontaire) et peut aussi supprimer les stagiaires qui ne disposent d'aucun autre rattachement. La suppression directe d'un groupe (admin ou formateur) est en revanche réversible depuis le 14 juillet 2026 (`Group` utilise `SoftDeletes`, voir [10-securite-rgpd.md](10-securite-rgpd.md)). Ces actions doivent rester exceptionnelles et être confirmées après vérification des rattachements.

---

## Formateur

Le formateur est le rôle central côté terrain. Il crée ses groupes, y ajoute des stagiaires, leur affecte des modules, anime des séances et suit les progressions.

Son accès dépend de son adhésion à l'association : adhésion active, ou compte de moins d'un mois en attente d'adhésion. Au-delà, il est redirigé vers la page d'adhésion.

### Ce qu'il peut faire

**Groupes** : créer, modifier, activer ou désactiver un groupe ; générer un code d'accès pour les stagiaires ; ajouter des stagiaires existants ou en créer avec invitation par mail ; inviter des co-formateurs ; affecter des modules dans un ordre choisi ; masquer ou réordonner des leçons pour un groupe précis.

**Suivi** : vue par groupe (taux de réussite, stagiaires actifs, inactifs, non démarrés), vue par stagiaire leçon par leçon, vue par module, repérage des apprenants à risque.

**Parcours** : assembler des modules, nuages de mots et sondages dans un parcours ordonné, puis l'associer à un groupe. Dans l'interface, ce sont « Mes parcours ». Il peut aussi parcourir le catalogue des modèles globaux publiés par l'administration et en créer une copie personnelle avant adaptation.

**Modules personnels** : créer ses propres modules dans un éditeur de plan continu (chapitres et leçons, réordonnancement au clavier ou à la souris, duplication de leçon), éditer chaque leçon en blocs de contenu (texte, image, citation, séparateur), téléverser ses images, ou dupliquer un module du catalogue pour l'adapter. Cette copie est indépendante du master officiel : les changements ultérieurs de l'un n'altèrent pas l'autre. Les modules personnels n'apparaissent pas dans le catalogue public.

Être désigné comme référent d'une formation officielle n'autorise pas le formateur à modifier le master publié. Toute personnalisation passe par sa copie personnelle.

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
