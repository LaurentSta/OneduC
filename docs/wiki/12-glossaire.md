# 12 — Glossaire

Ce glossaire unifie le vocabulaire du projet. L'une des faiblesses identifiées dans les audits est la coexistence de termes proches (Modules, Formations, Parcours, Mes formations) qui peuvent désorienter les utilisateurs. Ce glossaire sert de référence pour les développeurs, les formateurs, les interfaces et les communications externes.

---

## Convention produit

Oneduc doit privilégier un vocabulaire simple côté stagiaire et plus précis côté formateur/admin.

| Niveau | Terme à afficher | Rôle |
|--------|------------------|------|
| Expérience globale | **Formation** | Ce que le stagiaire suit concrètement |
| Organisation pédagogique | **Parcours** | Ordre préparé par le formateur pour un groupe |
| Bloc de contenu réutilisable | **Module** | Élément du catalogue ou créé par un formateur |
| Sous-partie du module | **Chapitre** | Partie structurante d'un module |
| Page de contenu | **Leçon** | Contenu précis à lire, regarder ou réaliser |
| Interaction | **Activité** | Quiz live, sondage, nuage de mots, tableau blanc, exercice, etc. |

### Règle d'affichage par public

| Public | Vocabulaire recommandé |
|--------|------------------------|
| Stagiaire | **Ma formation**, **Continuer ma formation**, **Mon programme**, **Chapitre**, **Leçon**, **Activité** |
| Formateur | **Mes parcours**, **Mes modules**, **Catalogue de modules**, **Outils d'animation**, **Groupes** |
| Admin | **Catalogue**, **Modules**, **Sections/chapitres**, **Leçons**, **Catégories**, **Évaluations** |
| Code / base de données | Conserver les noms techniques existants (`Module`, `ModuleSection`, `ModuleLecture`, `FormateurParcours`) |

Côté stagiaire, le terme **module** doit être limité aux écrans où il aide vraiment à comprendre la structure. Le mot **formation** porte l'expérience principale : c'est le terme le plus naturel pour un public éloigné du numérique.

Côté formateur, le terme **parcours** désigne l'organisation pédagogique préparée pour un groupe. Le terme **module** désigne une brique pédagogique réutilisable, créée depuis `/formateur/mes-modules` ou issue du catalogue.

---

## Vocabulaire pédagogique

### Formation
**Définition** : Expérience globale suivie par un stagiaire. Dans l'interface stagiaire, "formation" désigne l'ensemble de ce qui lui est proposé, même si techniquement cela agrège un groupe, des modules et éventuellement un parcours.

**Terme recommandé côté stagiaire** : "Ma formation", "Continuer ma formation", "Mon programme"

**À éviter côté stagiaire** : multiplier les termes "module", "parcours" et "formation" sur le même écran.

### Module
**Définition** : Unité d'apprentissage thématique. Un module contient des chapitres, eux-mêmes composés de leçons.  
**Modèle** : `Module`  
**Table** : `modules`  
**Synonymes à éviter côté formateur/admin** : "cours", "formation"

**Usage recommandé** : brique de contenu réutilisable dans le catalogue ou dans les modules personnels du formateur.

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
**Types d'items actuels** : `module`, `wordcloud`, `poll`

**Terme recommandé côté formateur** : "Parcours" ou "Mes parcours"

**Terme dans le code** : `MesFormations` (historique) → à renommer progressivement en "Parcours" dans l'interface

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
**Définition** : Code alphanumérique à 6 caractères généré automatiquement par `CodeGeneratorService`. Permet à un stagiaire de se connecter sans passer par email/mot de passe.

**Champ** : `users.code_acces`  
**Utilisé pour** : connexion stagiaire simplifiée

### Code temporaire de groupe
**Définition** : Mot de passe provisoire associé à un groupe, chiffré en base de données. Sert dans les flux d'invitation/rattachement gérés par le formateur.

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

## Convention d'interface à appliquer

Les termes suivants coexistent dans l'interface et créent parfois de la confusion. La normalisation retenue est :

| Terme actuel | Recommandation | Explication |
|-------------|----------------|-------------|
| "Formations" (menu formateur) | "Parcours" | Désigne les `FormateurParcours` construits pour un groupe |
| "Mes formations" (formateur) | "Mes parcours" | Évite la confusion avec "Ma formation" côté stagiaire |
| "Ma formation" (stagiaire) | "Ma formation" | Terme principal à conserver côté apprenant |
| "Modules" | "Modules" | À conserver pour le catalogue et les briques pédagogiques |
| "Parcours formateur" | "Parcours formateur Oneduc" | Désigne l'onboarding du formateur dans la plateforme |
| "Outils" (stagiaire) | "Activités de groupe" | Plus clair pour un public non-expert |
| "Documentation" (stagiaire) | "Ressources" | Plus neutre et plus clair |
| Bouton de reprise stagiaire | "Continuer ma formation" | Action la plus lisible pour reprendre là où il s'est arrêté |

### Exemples de libellés recommandés

| Contexte | Libellé recommandé |
|----------|--------------------|
| Menu stagiaire principal | "Ma formation" |
| Bouton principal dashboard stagiaire | "Continuer ma formation" |
| Liste stagiaire | "Mon programme" ou "Étapes de la formation" |
| Menu formateur pour `FormateurParcours` | "Mes parcours" |
| Menu formateur pour `ModuleBuilderController` | "Mes modules" |
| Catalogue formateur/admin | "Catalogue de modules" |
| Outils live | "Outils d'animation" côté formateur, "Activités de groupe" côté stagiaire |

---

[Retour au wiki](README.md)
