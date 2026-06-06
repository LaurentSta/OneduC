# Resume des activites du module 2 - Organiser ses parcours

Date d'audit : 2026-06-05  
Perimetre analyse : `public/modules/parcours_formateur/module_2_organiser_ses_parcours` et configuration applicative Laravel associee.

## Synthese courte

Le module 2 est structure comme un parcours formateur hybride : des contenus Storyline/SCORM servent de capsules de demonstration, tandis que les validations reelles du module sont principalement portees par des activites natives Oneduc et des simulateurs Laravel/JS.

Le module contient :

- 1 introduction SCORM.
- 3 chapitres.
- 7 lecons pedagogiques suivies dans la completion du module.
- 20 paquets SCORM exportes Storyline 360 au total, introduction comprise.
- 7 activites ou validations suivies dans `trainer_path_activity_attempts`.
- 1 questionnaire final d'utilisabilite, enregistre separement dans `trainer_module_questionnaire_submissions`.

Conclusion importante : les SCORM du module 2 n'ont pas de quiz note interne. Ils sont configures en suivi de consultation de diapositives (`type: view`) avec `passPercent: 80`, mais sans `scoreRefs`, sans question notee, sans ponderation pedagogique et sans score chiffre exploite pour la validation du module. La validation finale Oneduc repose sur les activites natives reussies.

## Architecture du module

Module : `organiser-ses-parcours`  
Titre : `Mettre en place un environnement de formation`  
Duree affichee : `48 a 51 min`  
Objectif specifique : creer un environnement de formation pret a utiliser.

Chapitres :

| Chapitre | Titre | Duree | Objectif |
|---|---:|---:|---|
| 2.1 | Preparer avant de creer | 11 min | Verifier que les informations indispensables sont pretes avant d'ouvrir un groupe |
| 2.2 | Creer et organiser | 16 min | Creer un groupe puis construire un parcours coherent |
| 2.3 | Ajuster et securiser | 21 a 24 min | Ajuster un groupe, securiser les acces et modifier le contenu |

## Activites suivies pour la validation du module

Il y a 7 validations attendues pour que le module soit considere comme termine. Elles sont calculees par `ParcoursFormateur::moduleCompletionRequirements()`.

| # | Chapitre / lecon | Activite suivie | Type | Condition de validation | Enregistrement |
|---:|---|---|---|---|---|
| 1 | 2.1.1 Identifier les elements essentiels | `classer-les-elements` | `sorting` | Les 9 cartes doivent etre dans la bonne zone | Insertion d'une tentative a chaque validation |
| 2 | 2.1.2 Preparer les informations utiles | `preparer-informations-utiles` | `essential_sorting` | Les 8 cartes doivent etre dans la bonne colonne | Insertion d'une tentative a chaque validation |
| 3 | 2.2.1 Creer un groupe de formation | `creation-groupe-finalisee` | `guided_group_creation` | Modules attendus 101, 102, 103 selectionnes dans cet ordre | `updateOrInsert` sur la tentative de l'utilisateur |
| 4 | 2.2.2 Creer un parcours | `creation-parcours-finalisee` | `guided_group_creation` | Arriver sur la page de finalisation du simulateur | `updateOrInsert` automatique a l'affichage de la partie finale |
| 5 | 2.3.1 Ajuster le groupe | `ajustement-groupe-finalise` | `guided_group_creation` | Arriver sur la partie de finalisation apres creation du stagiaire | `updateOrInsert` automatique |
| 6 | 2.3.2 Traiter les cas particuliers | `cas-particuliers-finalises` | `guided_group_creation` | Arriver sur la finalisation de modification de contenu | `updateOrInsert` automatique |
| 7 | 2.3.3 Bilan module 2 | `bilan-module-2-finalise` | `guided_group_creation` | Afficher la vue native de bilan final | `updateOrInsert` automatique |

Le bilan final lit ces 7 validations, calcule `x/7`, un pourcentage d'avancement, les durees indicatives couvertes et le detail par chapitre.

## Detail des activites natives

### 1. Classer les elements de preparation

Lecon : 2.1.1 `Identifier les elements essentiels`  
Activite : `classer-les-elements`  
Consigne : faire glisser chaque etiquette dans la bonne etape de preparation.

Zones :

- Informations : titre du groupe, date de debut, date de fin.
- Stagiaires : Pierre Dupont, Aurelie Martin, email du stagiaire.
- Modules : Word debutant, Excel avance, PowerPoint.

Evaluation :

- 9 elements a classer.
- Reussite seulement si les 9 sont corrects.
- Pas de score chiffre affiche.
- Pas de ponderation explicite ; chaque element compte implicitement pour 1 dans `correct_items`.
- Pas de seuil partiel : si un element est faux ou absent, `is_success = false`.
- Les mauvaises cartes retournent un feedback item par item avec la zone attendue.

Essais :

- L'interface affiche un compteur `3 essai(s) restant(s)`.
- Le compteur est cote front uniquement, dans l'etat Alpine de la page.
- Le serveur ne bloque pas au 3e essai et enregistre chaque tentative.
- Le bouton `Refaire l'exercice` remet l'activite a zero cote interface.

Popups et feedback :

- Modal de consigne.
- Modal/resultat de verification.
- Modal de fin avec variantes A/B/C selon le nombre d'echecs avant reussite.
- Actions proposees : revoir la lecon, recommencer, revoir la consigne, lecon suivante.

### 2. Obligatoire ou ajoutable plus tard ?

Lecon : 2.1.2 `Preparer les informations utiles`  
Activite : `preparer-informations-utiles`  
Type : `essential_sorting`

Consigne : distinguer ce qui bloque la creation du groupe de ce qui peut etre ajoute ensuite.

Zones :

- Obligatoire pour creer le groupe.
- Peut etre ajoute plus tard.

Elements obligatoires :

- Intitule : `Hygiene alimentaire 2026 - Promo 1`.
- Stagiaire : `Marie Dupont`.
- Module : `Securite alimentaire 2026`.

Elements ajoutables plus tard :

- Description.
- Dates.
- Ressource.
- Statut.
- Coformateur.

Evaluation :

- 8 elements a classer.
- Reussite seulement si les 8 sont corrects.
- Pas de score chiffre affiche.
- Pas de ponderation explicite ; chaque element compte pour 1 dans `correct_items`.
- Feedbacks A/B/C personnalises :
  - A : reussite franche, repere des trois indispensables.
  - B : reussite avec hesitation.
  - C : reprise de la consigne et rappel des indispensables.

Essais et popups :

- Meme logique que l'activite precedente : 3 essais affiches cote front, pas de limitation serveur.
- Feedback modal avec detail des erreurs, zone attendue et message par element.

### 3. Creation guidee d'un groupe

Lecon : 2.2.1 `Creer un groupe de formation`  
Activite suivie : `creation-groupe-finalisee`

Structure :

- SCORM `introduction` : fiche de groupe.
- Formulaire `informations`.
- Formulaire `stagiaires`.
- Formulaire `modules`.
- SCORM + recapitulatif `finalisation`.

Consignes et blocs de developpement :

- Informations : nom obligatoire, description facultative, statut actif, dates, coformateur.
- Stagiaires : prenom, nom, email, code d'acces provisoire.
- Modules : selectionner les modules utiles et les organiser dans l'ordre.

Condition de validation :

- Le serveur attend exactement les modules `[101, 102, 103]`.
- Si le nombre est mauvais : message `Selectionnez exactement les modules attendus`.
- Si les modules ne correspondent pas : message `Les modules selectionnes ne correspondent pas tous au scenario`.
- Si les bons modules sont dans le mauvais ordre : message `Les bons modules sont presents, mais ils ne sont pas dans le bon ordre`.

Evaluation :

- Pas de score chiffre affiche.
- Enregistrement force `total_items = 3`, `correct_items = 3`, `is_success = true` quand la validation serveur passe.
- Pas de ponderation.
- Pas de nombre d'essais limite cote serveur.

Stockage local et serveur :

- Les champs intermediaires sont conserves dans `localStorage` sous `oneduc_training_group_creation`.
- La validation finale est enregistree en base dans `trainer_path_activity_attempts`.
- `submitted_answer` contient notamment le nom du groupe, les stagiaires, les modules et la partie terminee.
- `expected_answer` contient la partie requise et les modules attendus.

### 4. Simulateur de creation de parcours

Lecon : 2.2.2 `Creer un parcours`  
Activite suivie : `creation-parcours-finalisee`

Structure :

- SCORM d'ouverture.
- Simulateur React de creation de parcours.
- Page de felicitation / preview.

Consigne implicite du simulateur :

- Creer un parcours compose exactement de 5 etapes.
- Etape 1 : nuage de mots.
- Etape 2 : module `Securite alimentaire 2026`.
- Etape 3 : module `Hygiene en cuisine professionnelle`.
- Etape 4 : module `Nettoyage et desinfection des espaces`.
- Etape 5 : sondage.
- Le titre du parcours est obligatoire.

Evaluation :

- Pas de score chiffre.
- Pas de ponderation.
- Validation front par structure attendue du parcours.
- Les erreurs produisent une popup `Ajustement necessaire`.

Essais :

- 3 essais geres cote React.
- Apres 3 essais, l'interface propose `Stopper l'activite`.
- L'etat est conserve dans `localStorage` sous `oneduc_training_path_creation`.
- Attention : l'arrivee sur la page de finalisation marque l'activite comme terminee cote serveur, meme si le payload local indique `stopped: true`.

Feedback :

- Popup avec le message d'erreur et le nombre d'essais restants.
- Messages possibles : titre manquant, nombre d'etapes incorrect, mauvaise premiere etape, mauvais module a une position donnee, sondage manquant en cinquieme position.

### 5. Ajuster le groupe

Lecon : 2.3.1 `Ajuster le groupe`  
Activite suivie : `ajustement-groupe-finalise`

Structure :

- SCORM d'introduction.
- Table de stagiaires simulee.
- Formulaire d'ajout de stagiaire.
- Table finalisee.

Consigne :

- Modifier un groupe vivant et ajouter un stagiaire.
- Le formulaire attend prenom, nom, email, groupe.
- Un mot de passe facultatif est affiche mais la simulation s'appuie surtout sur le groupe selectionne.

Evaluation :

- Pas de score chiffre.
- Pas de ponderation.
- Pas de verification serveur detaillee du formulaire dans cette vue ; le formulaire envoie en GET vers la finalisation.
- L'activite est marquee reussie automatiquement lorsque la partie `ajustement-groupe-finalisation` est affichee.

Enregistrement :

- `trainer_path_activity_attempts` avec `total_items = 1`, `correct_items = 1`, `is_success = true`.
- `submitted_answer` contient la partie terminee et la date de completion.

### 6. Traiter les cas particuliers

Lecon : 2.3.2 `Traiter les cas particuliers`  
Activite suivie : `cas-particuliers-finalises`

Structure :

- SCORM `cas-particulier` : Marc/Sofia, procedure a 5 niveaux.
- Table stagiaires Marc.
- Fiche profil Marc et volet message.
- Validation Marc.
- SCORM et simulateur de modification de contenu.
- Finalisation.

Cas Marc :

- La consigne demande d'ouvrir le volet message.
- Cocher `Inclure le lien et le code d'acces`.
- Cocher `Email`.
- Envoyer le message.
- Enregistrer les modifications.

Feedback Marc :

- Si lien/code ou email manquent : feedback `Options obligatoires`.
- Si le message n'est pas envoye : feedback `Message non envoye`.
- Si tout est fait : feedback `Message envoye`, puis acces a la validation.

Modification de contenu :

- Consigne : aller dans l'onglet Modules.
- Ajouter le module `Conservation des aliments et DLC`.
- Verifier qu'il apparait dans l'enchainement.
- Enregistrer.

Evaluation :

- Pas de score chiffre.
- Pas de ponderation.
- Pas de nombre d'essais limite.
- Validation finale automatique quand la partie `modifier-contenu-finalisation` est affichee.

### 7. Bilan module 2

Lecon : 2.3.3 `Bilan du module 2 et ouverture vers le module 3`  
Activite suivie : `bilan-module-2-finalise`

La partie `bilan` affiche un SCORM de synthese. La partie native `resultat-final` calcule :

- nombre d'activites validees sur 7 ;
- pourcentage d'avancement ;
- durees indicatives couvertes ;
- detail de validation par chapitre ;
- statut visuel `Parcours valide` ou `Parcours en cours`.

Evaluation :

- Pas de score pedagogique.
- Validation du bilan par affichage de la partie `resultat-final`.
- Le questionnaire final est propose ensuite, mais il n'est pas necessaire a la completion du module dans `moduleCompletionRequirements()`.

## Questionnaire final d'utilisabilite

Le module 2 contient un questionnaire final separe de la validation pedagogique.

Questionnaire : `utilisabilite-percue`, version 1  
Enregistrement : table `trainer_module_questionnaire_submissions`  
Email : envoye a `contact@oneduc.fr` via `ModuleQuestionnaireSubmitted`.

Structure :

- 17 items fermes.
- 2 questions ouvertes.
- 5 dimensions :
  - Contenu percu : 4 items.
  - Effort cognitif percu : 3 items.
  - Guidage visuel percu : 3 items.
  - Reperage dans le parcours : 3 items.
  - Activites et simulateurs : 4 items.
- Echelle : 1 a 5 + `NA`.
- Item 7 inverse : `Suivre le module m'a fatigue.`

Validation :

- Les 17 items fermes sont obligatoires.
- Les 2 questions ouvertes doivent etre presentes dans le payload, mais leur texte peut etre vide.
- Si un item ferme manque, la requete est rejetee et aucun email n'est envoye.
- Un `submission_uuid` evite les doublons : une deuxieme soumission avec le meme UUID ne cree pas de nouvelle ligne et ne renvoie pas l'email.

Score :

- Aucun score global n'est calcule dans le code.
- Les valeurs sont stockees telles quelles avec libelle de reponse.
- L'item inverse est seulement signale pour recodage lors de l'analyse.

## Paquets SCORM du module

Tous les SCORM sont des exports Storyline 360 version `3.105.35604.0`. Ils sont configures avec :

- `lmsPresent: true`.
- SCORM 1.2 via `lms/scormdriver.js`.
- scoring Storyline de type `view`.
- `passPercent: 80`.
- `passStatus: pass`.
- `failStatus: incomplete`.
- `scoreRefs: []`, donc aucune question notee rattachee au scoring.

| Partie SCORM | Diapos suivies | Seuil Storyline | Titre LMS | Diapositives principales |
|---|---:|---:|---|---|
| Introduction | 1 | 1 vue | `IntroductionModule2` | Diapositive d'introduction |
| 2.1.1 Les composants indispensables | 5 | 5 vues | `IdentifieLesElementsEssentiel` | Introduction, Avant de commencer, Avant d'ouvrir une formation, Reperer les etapes, Place a la pratique |
| 2.1.2 Preparer les informations utiles | 7 | 7 vues | `PreparerLesInformationsUtiles` | Introduction, Preparer avant de creer, Nommer le groupe, Modalites, Stagiaires, Parcours, L'essentiel |
| 2.2.1 Entete creation groupe | 2 | 2 vues | `EnteteCreerUnGroupeDeFormation` | Introduction, Ma fiche de groupe |
| 2.2.1 Informations | 1 | 1 vue | `CreerUnGroupeDeFormation_information` | Remplir les Informations |
| 2.2.1 Stagiaires | 1 | 1 vue | `CreerUnGroupeDeFormation_stagiaires` | Ajouter les Stagiaires |
| 2.2.1 Modules | 1 | 1 vue | `CreerUnGroupeDeFormation_Modules` | Organiser les modules |
| 2.2.1 Finalisation | 1 | 1 vue | `CreerUnGroupeDeFormation_Finalisation` | Visualiser le groupe |
| 2.2.2 Creation parcours | 3 | 3 vues | `CreerUnParcours` | Introduction, Module ou parcours, Deux portes pour construire |
| 2.2.2 Remplir formulaire | 1 | 1 vue | `02_remplirFormulaire` | Remplir le formulaire |
| 2.2.2 Finalisation parcours | 1 | 1 vue | `CreerUnParcoursFinalisation` | Visualiser le groupe |
| 2.3.1 Ajuster le groupe | 3 | 3 vues | `01_AjusterLeGroupe` | Introduction, Un groupe vivant, Deux portes pour les stagiaires |
| 2.3.1 Liste stagiaires | 1 | 1 vue | `FormulaireStagiaires` | Ajouter un stagiaire |
| 2.3.1 Formulaire stagiaire | 1 | 1 vue | `03_FormulaireStagiaires` | Formulaire stagiaire |
| 2.3.1 Finalisation | 1 | 1 vue | `04_FormulaireStagiaire_Finalisation` | Un groupe vivant |
| 2.3.2 Cas particulier | 4 | 4 vues | `CasParticulier` | Introduction, Quand l'acces se bloque, Procedure a 5 niveaux, A vous d'agir |
| 2.3.2 Validation Marc | 1 | 1 vue | `SituationParticuliereMarc_Finalisation` | Marc est enregistre |
| 2.3.2 Modifier contenu | 1 | 1 vue | `ModifierLeContenu` | Modifier le contenu |
| 2.3.2 Finalisation contenu | 1 | 1 vue | `04_ModifierContenuFinalisation` | Modifiez le contenu |
| 2.3.3 Bilan | 2 | 2 vues | `Bilan` | Introduction, Ce que vous savez faire maintenant |

## Score, tests, ponderation, essais

### Tests / quiz

- Aucun quiz Storyline note n'a ete trouve dans les SCORM du module 2.
- Les exports contiennent des variables generiques Storyline comme `questionCorrect`, mais pas de scoring par question.
- Les `scoreRefs` des scorings Storyline sont vides.
- Les tests pedagogiques reels sont donc les 2 tris natifs, les 5 validations de simulateur et le questionnaire final d'utilisabilite.

### Score chiffre

- Activites natives de tri : pas de score affiche, mais `correct_items / total_items` est enregistre.
- Simulateurs : pas de score affiche ; ils enregistrent une reussite booleenne.
- Questionnaire final : pas de score global calcule.
- SCORM : scoring de consultation Storyline, mais non exploite comme score pedagogique du module.

### Ponderation

- Aucune ponderation pedagogique explicite n'est configuree.
- Dans les tris, chaque carte vaut implicitement une unite.
- Dans la creation de groupe, les 3 modules attendus sont traites comme 3 items.
- Dans les autres simulateurs, la validation est binaire.

### Nombre d'essais

- Tris natifs : 3 essais affiches cote front, mais pas de blocage serveur.
- Simulateur creation de parcours : 3 essais cote front, puis possibilite de stopper.
- Creation de groupe, ajustement, Marc, modification contenu, bilan : pas de limite d'essais identifiee.
- SCORM : pas d'essais pedagogiques notes.

## Enregistrement et releve final

### Activites du module

Table : `trainer_path_activity_attempts`

Colonnes principales :

- `user_id`
- `module_key`
- `chapter_key`
- `lesson_key`
- `activity_key`
- `activity_type`
- `total_items`
- `correct_items`
- `is_success`
- `submitted_answer`
- `expected_answer`
- `wrong_items`
- `submitted_at`

Pour les activites de tri :

- Chaque tentative est inseree.
- Les mauvaises reponses sont conservees dans `wrong_items`.
- La reussite est determinee par absence d'erreur.
- Une tentative reussie est reutilisee pour pre-remplir l'activite lors d'une revisite.

Pour les simulateurs :

- `updateOrInsert` cree ou met a jour une seule ligne par activite utilisateur.
- La completion est souvent declenchee par l'affichage de la partie finale.
- La creation de groupe est le cas le plus strict : elle valide le payload et les modules attendus avant enregistrement.

### SCORM

Le parent de lecon charge `public/scorm_core/js/API.js`, mais dans le parcours formateur le `SCORM_CONTEXT` fixe `lecture_id: 0`. Or l'API Oneduc n'envoie pas la progression SCORM si `lecture_id` est absent ou egal a 0.

Effet concret :

- Les SCORM du module 2 peuvent afficher le bouton suivant quand Storyline envoie `completed`/`passed` ou appelle `LMSFinish`.
- Mais les progres SCORM du module 2 ne sont normalement pas enregistres dans `scorm_results` / `scorm_scores`, car `lecture_id` vaut 0.
- La completion durable du module 2 vient donc de `trainer_path_activity_attempts`, pas des tables SCORM classiques.

### Bilan final

La page `module-2-final-results` relit les activites reussies du module et calcule :

- activites validees sur activites attendues ;
- pourcentage du parcours valide ;
- durees indicatives couvertes ;
- detail chapitre par chapitre.

Le module est considere valide lorsque les 7 cles attendues par `moduleCompletionRequirements()` ont une tentative reussie du bon type.

### Questionnaire final

Table : `trainer_module_questionnaire_submissions`

Le questionnaire est releve separement :

- stockage JSON complet dans `responses`;
- date `submitted_at`;
- date `emailed_at` apres envoi a Oneduc;
- dedoublonnage par `submission_uuid`;
- consultable cote admin via les soumissions du module.

## Points d'attention

- Les intitulés de certains dossiers Storyline ne correspondent pas toujours au code pedagogique affiche dans Laravel : il faut se fier a `app/Data/ParcoursFormateur.php` pour la structure officielle du module.
- Les SCORM ont un scoring de consultation, mais la validation Oneduc n'utilise pas directement ces scores.
- Les compteurs d'essais des tris sont visuels/front et ne limitent pas les tentatives cote serveur.
- Le simulateur de creation de parcours peut etre `stopped` apres 3 essais dans le localStorage, mais l'affichage de la finalisation declenche tout de meme l'enregistrement de completion.
- Plusieurs validations de simulateur sont automatiques a l'ouverture de la derniere partie, sans recalcul detaille serveur du geste utilisateur.

## Sources principales

- `app/Data/ParcoursFormateur.php`
- `app/Http/Controllers/Formateur/ParcoursController.php`
- `resources/views/formateur/parcours/activity.blade.php`
- `resources/views/formateur/parcours/lesson.blade.php`
- `resources/views/formateur/parcours/partials/lessons/group-creation-form.blade.php`
- `resources/views/formateur/parcours/partials/lessons/path-creation-form.blade.php`
- `resources/js/formateur-parcours-builder.jsx`
- `resources/views/formateur/parcours/partials/lessons/module-2-final-results.blade.php`
- `resources/views/formateur/parcours/partials/lessons/module-2-usability-questionnaire.blade.php`
- `database/migrations/2026_04_09_120000_create_trainer_path_activity_attempts_table.php`
- `database/migrations/2026_06_02_120000_create_trainer_module_questionnaire_submissions_table.php`
- `public/scorm_core/js/API.js`
- `public/modules/parcours_formateur/module_2_organiser_ses_parcours/**/html5/data/js/data.js`
- `public/modules/parcours_formateur/module_2_organiser_ses_parcours/**/imsmanifest.xml`
