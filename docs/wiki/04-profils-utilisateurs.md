# 04 — Profils utilisateurs

La plateforme distingue quatre rôles, chacun avec un espace dédié, un préfixe d'URL, une chaîne de middleware et une interface spécifique.

---

## Vue d'ensemble des rôles

| Rôle | Champ `users.role` | Espace URL | Peut créer du contenu |
|------|-------------------|------------|----------------------|
| Admin | `admin` | `/admin` | Oui (tout) |
| Formateur | `formateur` | `/formateur` | Oui (groupes, parcours) |
| Stagiaire | `stagiaire` | `/stagiaire` | Non |
| Observateur | `observateur` | `/observateur` | Non |

---

## Administrateur

### Accès et middleware

Routes : `routes/admin.php`  
Middleware : `auth` + `role:admin` + `admin.activity`

Le middleware `RecordAdminActivity` journalise automatiquement toutes les actions destructives (POST/PUT/PATCH/DELETE) dans la table `activity_journal_entries`. Les données sensibles y sont sanitisées.

### Fonctionnalités

**Gestion des utilisateurs**
- CRUD complet des formateurs (activation, désactivation, gestion du statut d'adhésion)
- CRUD des stagiaires avec reset de progression (`admin.stagiaires.reset`, route protégée par `auth`, `role:admin`, `admin.activity`)
- Gestion des observateurs
- Soft delete des utilisateurs avec nettoyage des données liées (`cleanupRelatedStagiaireData()`)

**Gestion du contenu pédagogique**
- CRUD modules, sections, leçons
- Import SCORM pour une leçon (`ScormLibraryController::importForLecture()`)
- Import de slides (`ModuleLectureController::importSlidesForLecture()`)
- Banque de questions quiz (CRUD, import CSV, médias)
- CRUD évaluations SCORM
- Catégories et sous-catégories

**Outils admin**
- Module de pilotage interne (projets Kanban, tâches, journal, notifications)
- Référentiels de compétences, domaines, badges
- Consultation et suppression des retours stagiaires
- Nuage de mots admin

**Tableau de bord**
Indicateurs volumétriques : nombre de catégories, sous-catégories, modules, formateurs, stagiaires, groupes, sections, leçons.

### Limites actuelles
- Pas d'export CSV/PDF des données
- Pas de génération de certificats
- Pas de gestion multi-organisation
- La mise à jour d'email admin ne valide pas l'unicité

---

## Formateur

### Accès et middleware

Routes : `routes/formateur.php`  
Middleware : `auth` + `role:formateur` + `association.member`

**Politique d'adhésion** (`EnsureAssociationMembership`) : un formateur accède à la plateforme si :
- `adhesion_status = active` avec `adhesion_valid_until` non dépassée, **ou**
- `adhesion_status = pending` et le compte a moins d'un mois (`created_at + 30 jours > aujourd'hui`)

Au-delà, le formateur est redirigé vers `/adhesion`.

### Fonctionnalités

**Gestion des groupes**
- Créer, éditer, supprimer, activer/désactiver des groupes
- Générer un code d'accès temporaire chiffré pour les stagiaires
- Ajouter des stagiaires existants ou en créer de nouveaux avec invitation mail
- Ajouter des co-formateurs avec notification interne
- Affecter des modules à un groupe avec ordre configurable
- Personnaliser les leçons par groupe (masquage, réordonnancement) via `group_module_lectures`

**Suivi de progression**
- Vue par groupe : taux de réussite, stagiaires actifs/inactifs/non démarrés
- Vue par stagiaire : détail leçon par leçon
- Vue par module : progression globale
- Identification des apprenants à risque

**Parcours de formation**
- Créer des parcours (`FormateurParcours`) en assemblant modules, nuages de mots, sondages
- Associer un parcours à un groupe

**Modules personnels**
- Créer des modules formateur via `/formateur/mes-modules`
- Dupliquer un module catalogue dans une copie éditable
- Créer/réordonner chapitres et leçons
- Éditer des leçons en blocs structurés (`text`, `image`, `list`, `quote`, `divider`)
- Uploader des images propres au module dans `modules_formateur/module_{id}/images`
- Affecter le module personnel aux groupes accessibles

Les modules formateur sont marqués `is_trainer_authored = true` et sont exclus du catalogue public.

**Outils d'animation live** (voir [Outils](07-outils-animation.md))

**Ressources de leçon**
- Ajouter des ressources (liens, fichiers) à une leçon
- Toggle de visibilité stagiaire par ressource

### Accès aux modules
Un formateur accède aux modules via deux voies :
1. Il est le `instructor_id` du groupe
2. Il est co-formateur via `group_user.role_in_group = 'formateur'`

Le scope Eloquent `Group::scopeAccessibleByTrainer()` couvre les deux cas.

### Limites actuelles
- Les modules créés par les formateurs supportent surtout le contenu en blocs ; l'import SCORM complet reste côté admin
- Pas d'export des données de groupe
- Pas de génération automatique de badges ou certificats en fin de parcours

---

## Stagiaire

### Accès et middleware

Routes : `routes/stagiaire.php`  
Middleware : `auth` + `role:stagiaire` + `track.time` + `force.password.change` (groupe principal)

### Modes de connexion

**Mode classique** : email + mot de passe  
**Mode code d'accès** : via `POST /stagiaire/connexion-code` — le stagiaire saisit uniquement un code alphanumérique à 6 caractères. C'est le mode privilégié pour les publics éloignés du numérique.

À la première connexion, le middleware `ForcePasswordChange` bloque l'accès jusqu'à la définition d'un nouveau mot de passe (validation `min:8`).

### Fonctionnalités

**Tableau de bord**
- Formateur référent affiché en permanence
- Temps d'apprentissage cumulé
- Taux de réussite
- Progression par module
- Temps moyen de réflexion

**Formation**
- Liste des modules du groupe actif
- Navigation section → leçon
- Lecture de leçons SCORM (iframe), slides, quiz natifs, contenu vidéo
- Marquage automatique de progression

**Résultats**
- Historique des tentatives quiz avec détail par question
- Scores SCORM par leçon

**Outils**
- Participation aux outils live lancés par le formateur (Quiz live, Nuage de mots, Sondage, etc.)

**Profil**
- Modification du profil et du mot de passe
- Suppression du compte

### Limites actuelles
- Un stagiaire dans plusieurs groupes actifs ne voit que les modules du premier groupe (`.first()` dans `StagiaireModules()`)
- L'accès direct aux routes de leçon s'appuie encore surtout sur `Module::isVisibleTo()` et le contexte de groupe ; il faut centraliser la vérification d'appartenance via policies avant publication large

---

## Observateur

### Accès et middleware

Routes : `routes/observateur.php`  
Middleware : `auth` + `role:observateur`

Rôle ajouté en mars 2026. L'observateur accède en lecture seule aux groupes et aux progressions.

### Fonctionnalités
- Consultation des groupes auxquels il est rattaché
- Lecture de la progression des stagiaires
- Accès en lecture aux leçons des groupes observés

### Rattachement
Les observateurs sont rattachés aux groupes via `group_user.role_in_group = 'observateur'`.

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
