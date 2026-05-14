# 12 — Glossaire

Ce glossaire unifie le vocabulaire du projet. L'une des faiblesses identifiées dans les audits est la coexistence de termes proches (Modules, Formations, Parcours, Mes formations) qui peuvent désorienter les utilisateurs. Ce glossaire sert de référence pour les développeurs, les formateurs et les communications externes.

---

## Vocabulaire pédagogique

### Module
**Définition** : Unité d'apprentissage thématique. Un module contient des chapitres, eux-mêmes composés de leçons.  
**Modèle** : `Module`  
**Table** : `modules`  
**Synonymes à éviter** : "cours", "formation" (dans ce contexte)  
**Exemple** : "Module Bureautique — Traitement de texte"

### Chapitre (Section)
**Définition** : Subdivision d'un module. Regroupe plusieurs leçons autour d'un objectif pédagogique commun.  
**Modèle** : `ModuleSection`  
**Table** : `module_sections`  
**Terme dans l'interface** : "Section" ou "Chapitre" selon le contexte  
**Exemple** : "Chapitre 1 — Mise en forme"

### Leçon
**Définition** : Unité d'apprentissage élémentaire. Contient le contenu à proprement parler (SCORM, slides, quiz, vidéo).  
**Modèle** : `ModuleLecture`  
**Table** : `module_lectures`  
**Synonymes à éviter** : "cours", "activité" (réservé aux outils live)  
**Exemple** : "Leçon 1.2 — Les styles de paragraphe"

### Parcours de formation
**Définition** : Séquence ordonnée de modules et d'activités créée par un formateur pour un groupe de stagiaires. Un parcours peut combiner des modules, des nuages de mots et des sondages.  
**Modèle** : `FormateurParcours` + `FormateurParcoursItem`  
**Table** : `formateur_parcours`, `formateur_parcours_items`  
**Terme dans le code** : "MesFormations" (historique) → à renommer en "Parcours" dans l'interface  
**Exemple** : "Parcours Inclusion Numérique Niveau 1"

### Parcours formateur
**Définition** : Programme d'onboarding de la plateforme Oneduc elle-même, destiné aux formateurs. Ce n'est pas un parcours pour les stagiaires.  
**Modèle** : Codé en dur dans `app/Data/ParcoursFormateur.php`  
**URL** : `/formateur/parcours-formateur`  
**À distinguer de** : "Parcours de formation" (ci-dessus)

### Objectif pédagogique
**Définition** : Résultat d'apprentissage attendu pour une leçon. Associé à un référentiel de compétences (en cours d'implémentation).  
**Modèle** : `LectureObjective`  
**Table** : `lecture_objectives`

### Activité
**Définition** : Exercice interactif lancé pendant ou après une leçon. Peut être un outil live (quiz, nuage de mots, sondage) ou un exercice SCORM.  
**Utilisé dans** : `FormateurParcoursItem` (type `activity`), parcours formateur

### Bilan
**Définition** : Moment de synthèse à la fin d'un module dans le parcours formateur. Permet de faire le point avant de passer au module suivant.  
**Utilisé dans** : Parcours formateur Oneduc (onboarding)

---

## Vocabulaire utilisateurs

### Admin (Administrateur)
**Définition** : Rôle avec accès complet à la plateforme. Gère les utilisateurs, le catalogue de modules, les catégories, la configuration générale.  
**Champ** : `users.role = 'admin'`  
**Espace** : `/admin`

### Formateur
**Définition** : Animateur de formation. Crée et gère des groupes de stagiaires, leur affecte des modules, anime des sessions live, suit la progression.  
**Champ** : `users.role = 'formateur'`  
**Espace** : `/formateur`  
**Condition d'accès** : adhésion active ou période de grâce

### Co-formateur
**Définition** : Formateur rattaché à un groupe dont il n'est pas le créateur. Il a les mêmes droits d'animation qu'un formateur principal sur ce groupe.  
**Champ** : `group_user.role_in_group = 'formateur'`

### Stagiaire (Apprenant)
**Définition** : Personne en formation. Accède aux modules de son groupe, participe aux outils live, consulte sa progression.  
**Champ** : `users.role = 'stagiaire'`  
**Espace** : `/stagiaire`  
**Mode d'accès** : email/mot de passe ou code d'accès court

### Observateur
**Définition** : Rôle en lecture seule. Peut consulter les groupes et les progressions sans intervenir dans la formation.  
**Champ** : `users.role = 'observateur'`  
**Espace** : `/observateur`

### Formateur principal
**Définition** : Formateur créateur d'un groupe. Référencé par `groups.instructor_id`.  
**À distinguer de** : Co-formateur

---

## Vocabulaire technique

### Groupe
**Définition** : Unité organisationnelle qui relie un formateur principal, des stagiaires, des modules et éventuellement des co-formateurs et un parcours de formation.  
**Modèle** : `Group`  
**Table** : `groups`

### Code d'accès
**Définition** : Code alphanumérique à 6 caractères généré automatiquement par `CodeGeneratorService`. Permet à un stagiaire de rejoindre une formation sans connaître l'URL.  
**Champ** : `users.code_acces`  
**Utilisé pour** : connexion stagiaire simplifiée

### Code temporaire de groupe
**Définition** : Mot de passe provisoire associé à un groupe, chiffré en base de données. Permet aux stagiaires de s'identifier au groupe lors de leur première connexion.  
**Champ** : `groups.temporary_password` (cast `encrypted`)  
**À distinguer de** : Code d'accès (ci-dessus)

### SCORM
**Définition** : Standard e-learning (Sharable Content Object Reference Model) qui définit comment un package de formation communique avec un LMS. Oneduc supporte SCORM 1.2 et SCORM 2004.  
**Fichiers clés** : `public/scorm_core/js/API.js` (SCORM 1.2), `public/scorm_core/js/api_Scorm2004.js` (SCORM 2004)

### Snapshot (analytique)
**Définition** : Représentation unifiée de l'état d'un apprenant sur une leçon à un instant T, calculée par `LearningAnalyticsService`. Agrège toutes les sources de progression.  
**Méthode** : `LearningAnalyticsService::collectSnapshots()`

### Adhésion
**Définition** : Statut d'adhésion d'un formateur à l'association Oneduc.  
**Champs** : `users.adhesion_status` (`active`, `pending`, `expired`), `users.adhesion_valid_until`  
**Middleware associé** : `EnsureAssociationMembership`

---

## Vocabulaire à unifier dans l'interface

Les termes suivants coexistent dans l'interface et créent parfois de la confusion. Ce tableau propose une normalisation :

| Terme actuel | Recommandation | Explication |
|-------------|----------------|-------------|
| "Formations" (menu formateur) | "Parcours" | Désigne les `FormateurParcours` |
| "Mes formations" | "Mes parcours" | Idem |
| "Modules" | "Modules" | OK — à conserver |
| "Parcours" | "Parcours formateur" | À clarifier pour éviter la confusion avec les parcours pédagogiques |
| "Outils" (menu stagiaire) | "Activités de groupe" | Plus clair pour un public non-expert |
| "Documentation" (menu stagiaire) | "Ressources" | Plus neutre et plus clair |

---

[Retour au wiki](README.md)
