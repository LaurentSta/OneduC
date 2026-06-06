# Recette - Resultats et questionnaire final du module 2

Date d'audit : 2026-06-05  
Perimetre : bilan final du module 2, progression affichee, questionnaire d'evaluation final, stockage et email.

## Pages a recetter

Module : `organiser-ses-parcours`  
Lecon : `bilan-module-2`  
Parties concernees :

- `bilan` : SCORM de synthese.
- `resultat-final` : page native de resultats du module 2.
- `questionnaire` : questionnaire d'evaluation / utilisabilite percue.

Routes importantes :

- Resultats : `formateur.parcours.lessons.part` avec `part=resultat-final`.
- Questionnaire : `formateur.parcours.lessons.part` avec `part=questionnaire`.
- Soumission questionnaire : `formateur.parcours.questionnaire.submit`, POST `/modules/{module}/questionnaire` dans le groupe parcours formateur.

## 1. Page de resultats / bilan final

### Objectif fonctionnel

La page `resultat-final` reprend les validations enregistrees pendant le module 2. Elle ne mesure pas le temps reel de connexion et ne calcule pas un score pedagogique : elle affiche une synthese des activites validees et des durees indicatives couvertes.

### Donnees utilisees

Source principale : `activityStatusMap`, chargee depuis `trainer_path_activity_attempts`.

Une activite est consideree validee si :

- une ligne existe pour l'utilisateur connecte ;
- `module_key = organiser-ses-parcours` ;
- `is_success = true` ;
- la cle `chapter.lesson.activity` correspond a une activite attendue ;
- le `activity_type` correspond au type attendu.

Les activites attendues sont celles renvoyees par `ParcoursFormateur::moduleCompletionRequirements('organiser-ses-parcours')`.

### Les 7 validations attendues

| # | Cle de validation | Type attendu | Libelle visible |
|---:|---|---|---|
| 1 | `preparer-les-contenus.retrouver-les-espaces-de-preparation.classer-les-elements` | `sorting` | Identifier les elements essentiels |
| 2 | `preparer-les-contenus.distinguer-contenu-ressource-et-structure.preparer-informations-utiles` | `essential_sorting` | Preparer les informations utiles |
| 3 | `structurer-la-progression.creation-groupe-de-formation.creation-groupe-finalisee` | `guided_group_creation` | Creer un groupe de formation |
| 4 | `structurer-la-progression.creation-parcours.creation-parcours-finalisee` | `guided_group_creation` | Creer un parcours |
| 5 | `mettre-en-place-un-parcours-coherent.associer-le-bon-parcours-au-bon-contexte.ajustement-groupe-finalise` | `guided_group_creation` | Ajuster le groupe |
| 6 | `mettre-en-place-un-parcours-coherent.traiter-les-cas-particuliers.cas-particuliers-finalises` | `guided_group_creation` | Traiter les cas particuliers |
| 7 | `mettre-en-place-un-parcours-coherent.bilan-module-2.bilan-module-2-finalise` | `guided_group_creation` | Bilan du module 2 |

### Calculs affiches

La page calcule :

- `Activites` : `totalCompleted / totalTracked`.
- `Avancement` : `round(totalCompleted / totalTracked * 100)`.
- `Duree indicative` : somme des durees des lecons validees.
- `Durees indicatives couvertes` : `round(completedEstimatedMinutes / totalEstimatedMinutes * 100)`.
- Detail par chapitre : nombre valide / nombre suivi + barre de progression.
- Statut visuel : `Parcours valide` si l'avancement est a 100 %, sinon `Parcours en cours`.

### Durees indicatives attendues

Le code extrait les nombres contenus dans les libelles de duree :

- Si la duree contient un seul nombre, il prend ce nombre.
- Si la duree contient deux nombres, il prend la moyenne.

Total attendu pour les 7 etapes suivies :

- Chapitre 2.1 : 5 + 6 = 11 min.
- Chapitre 2.2 : 8 + 8 = 16 min.
- Chapitre 2.3 : moyenne 8 a 10 = 9 min, puis 10 min, puis moyenne 3 a 4 = 3,5 min.
- Total indicatif suivi : 40,5 min.

La page peut donc afficher une duree totale de reference differente du libelle general `48 a 51 min`, car elle additionne seulement les etapes suivies dans la completion.

### Etats a recetter

#### Etat 0 / aucun avancement

Precondition : aucune tentative reussie dans `trainer_path_activity_attempts` pour l'utilisateur.

Attendus :

- Activites : `0/7`.
- Avancement : `0%`.
- Duree indicative : `0 min couvertes sur 40,5 min prevues`.
- Chaque chapitre affiche `0/x validees`.
- Chaque lecon suivie affiche `A terminer`, sauf le bilan qui affiche `Bilan a terminer`.
- Badge de droite : `Parcours en cours`.
- Bouton `Repondre au questionnaire` visible malgre l'absence de completion complete.

#### Etat partiel

Precondition exemple : seules les 2 activites du chapitre 2.1 sont reussies.

Attendus :

- Activites : `2/7`.
- Avancement : `29%` environ, car `round(2/7*100) = 29`.
- Chapitre 2.1 : `2/2 validees`, barre verte.
- Chapitre 2.2 : `0/2 validees`, barre orange.
- Chapitre 2.3 : `0/3 validees`, barre orange.
- Durees couvertes : `11 min couvertes sur 40,5 min prevues`.
- Durees indicatives couvertes : environ `27%`.
- Badge : `Parcours en cours`.

#### Etat complet

Precondition : les 7 validations existent avec `is_success = true` et le bon `activity_type`.

Attendus :

- Activites : `7/7`.
- Avancement : `100%`.
- Duree indicative : `40,5 min couvertes sur 40,5 min prevues`.
- Tous les chapitres sont a 100 %.
- Toutes les lignes affichent `Validee`.
- Badge de droite : `Parcours valide`.
- Le bouton `Aller au module 3` est visible.
- Le bouton `Repondre au questionnaire` est visible.

### Navigation visible

La page de resultats propose :

- `Revoir le module 2`.
- `Retour au tableau de bord`.
- `Repondre au questionnaire`.
- `Aller au module 3`.

Point de recette : verifier que les URLs ne valent pas `#`, sauf configuration incomplete.

## 2. Questionnaire final

### Objectif fonctionnel

Le questionnaire recueille l'avis du formateur sur l'utilisabilite percue du module 2. Il ne valide pas le module pedagogiquement : c'est une collecte de retour d'experience separee du score/progres module.

### Structure attendue

Questionnaire :

- Module : numero `2`.
- Module key : `organiser-ses-parcours`.
- Questionnaire key : `utilisabilite-percue`.
- Version : `1`.

Contenu :

- 17 items fermes obligatoires.
- 2 questions ouvertes.
- Echelle : `1`, `2`, `3`, `4`, `5`, `NA`.
- 1 item inverse : item 7.

Dimensions et items :

| Dimension | Items | Libelles |
|---|---:|---|
| Contenu percu | 1 a 4 | clarte des textes, comprehension du contenu, utilite metier, attentes |
| Effort cognitif percu | 5 a 7 | apprentissage rapide, suivi sans effort, fatigue |
| Guidage visuel percu | 8 a 10 | couleurs, elements mis en evidence, illustrations |
| Reperage dans le parcours | 11 a 13 | position dans le parcours, passage d'ecran, logique d'enchainement |
| Activites et simulateurs | 14 a 17 | consignes, attentes, realisme des simulateurs, retour apres activite |

Questions ouvertes :

- 18 : `Qu'est-ce qui vous a le plus aide dans ce module ?`
- 19 : `Qu'est-ce qui meriterait d'etre clarifie ou ameliore ?`

### Interface attendue

La page doit afficher :

- Titre : `Votre avis sur le module 2`.
- Introduction mentionnant le module `Mettre en place un environnement de formation`.
- Bloc `Echelle de reponse`.
- Legende 1 a 5 + NA.
- Mention des items inverses avec asterisque.
- Un slider par item ferme.
- Un bouton `NA - Non applicable` par item ferme.
- Deux zones de texte pour les questions ouvertes.
- Bouton `Retour au bilan`.
- Bouton `Envoyer mes reponses`.

### Comportement des sliders

Point subtil a recetter :

- Le slider HTML a une valeur visuelle initiale `3`.
- Mais l'etat Alpine `score` demarre a `null`.
- Tant que l'utilisateur n'a pas touche le slider ou clique `NA`, le champ hidden reste vide.
- Donc tous les items fermes doivent etre explicitement renseignes par l'utilisateur.

### Validation front

Au submit :

- Le JS lit tous les champs `item_1` a `item_17`.
- Les valeurs autorisees sont `1`, `2`, `3`, `4`, `5`, `NA`.
- Si un item manque :
  - affichage du message global `Repondez a chaque affirmation avant d'envoyer le questionnaire.`
  - affichage du message item `Selectionnez une note ou choisissez NA.`
  - focus sur le premier item incomplet.
  - aucun POST n'est envoye.

### Payload envoye

Le front envoie :

- `submission_uuid` : UUID genere au chargement de page.
- `module` : code et titre informatifs.
- `submitted_at` : date ISO cote navigateur, informative.
- `closed_items` : les 17 items avec numero, dimension, libelle, indicateur `reversed`, valeur.
- `open_questions` : les 2 questions avec texte trimme.

Le serveur ne fait pas confiance aux libelles front pour l'email : il reconstruit les textes et dimensions depuis `ParcoursFormateur::moduleTwoUsabilityQuestionnaire()`.

### Validation serveur

Regles serveur :

- `submission_uuid` obligatoire et format UUID.
- `closed_items` obligatoire, tableau de taille 17.
- Chaque `closed_items.*.item_number` :
  - entier ;
  - dans la liste 1 a 17 ;
  - distinct.
- Chaque `closed_items.*.value` :
  - obligatoire ;
  - valeur dans `[1, 2, 3, 4, 5, 'NA']`.
- `open_questions` obligatoire, tableau de taille 2.
- Chaque `open_questions.*.item_number` :
  - entier ;
  - dans `[18, 19]` ;
  - distinct.
- Chaque `open_questions.*.text` :
  - nullable ;
  - string ;
  - maximum 5000 caracteres.

### Enregistrement en base

Table : `trainer_module_questionnaire_submissions`.

Colonnes utiles :

- `submission_uuid`, unique.
- `user_id`.
- `module_number = 2`.
- `module_key = organiser-ses-parcours`.
- `questionnaire_key = utilisabilite-percue`.
- `questionnaire_version = 1`.
- `responses`, JSON complet.
- `submitted_at`.
- `emailed_at`, rempli apres envoi email.

Payload stocke dans `responses` :

- `module`.
- `submitted_at` formate cote serveur : `d/m/Y a H:i:s`.
- `trainer` :
  - `id`;
  - `full_name`;
  - `username`;
  - `email`.
- `closed_items` :
  - `item_number`;
  - `dimension`;
  - `dimension_label`;
  - `label`;
  - `reversed`;
  - `value`;
  - `answer_label`.
- `open_questions` :
  - `item_number`;
  - `label`;
  - `text`.

### Email envoye

Destinataire : `contact@oneduc.fr`.

Sujet :

- `Questionnaire d'evaluation Oneduc - Module 2`.

Reply-To :

- email du formateur, si valide.

Contenu :

- module ;
- date d'envoi ;
- nom du formateur ;
- identifiant ;
- email ;
- tableaux regroupes par dimension ;
- reponses fermees sous forme de libelles ;
- mention `item inverse a recoder lors de l'analyse` pour l'item 7 ;
- questions ouvertes, ou `Aucune reponse.` si vide.

### Confirmation utilisateur

Apres POST OK :

- le formulaire est masque ;
- le bloc de confirmation est affiche ;
- titre : `Merci pour votre retour`;
- texte : `Vos reponses ont bien ete enregistrees et envoyees a l'equipe Oneduc.`;
- liens visibles :
  - `Retour au tableau de bord`;
  - `Aller au module 3`.

### Cas d'erreur utilisateur

#### Item ferme non renseigne

Action : ne pas toucher un ou plusieurs sliders et ne pas choisir NA.

Attendus :

- pas d'appel serveur ;
- message global d'erreur visible ;
- message d'erreur sous les items incomplets ;
- bouton toujours disponible.

#### Echec serveur / reseau

Action : simuler une reponse non 2xx ou couper la route.

Attendus :

- message `L'envoi n'a pas abouti. Verifiez votre connexion puis reessayez.`
- bouton redevient actif ;
- texte du bouton redevient `Envoyer mes reponses`.

#### Payload incomplet cote serveur

Action : poster moins de 17 items fermes.

Attendus :

- HTTP 422.
- Erreur de validation `closed_items`.
- Aucune ligne en base.
- Aucun email envoye.

#### Texte ouvert trop long

Action : envoyer plus de 5000 caracteres sur une question ouverte.

Attendus :

- HTTP 422.
- Pas d'email.
- Pas de nouvelle soumission valide.

#### Doublon de `submission_uuid`

Action : renvoyer deux fois le meme payload avec le meme UUID.

Attendus :

- 1 seule ligne en base.
- 1 seul email.
- Deuxieme POST OK mais sans nouvel envoi email.

#### UUID appartenant a un autre utilisateur ou autre module

Action : reutiliser un UUID deja cree par un autre utilisateur ou contexte incompatible.

Attendus :

- HTTP 409 via `abort_unless`.
- Pas d'email supplementaire.

## 3. Releve cote admin

La liste admin des formateurs charge les soumissions depuis `trainer_module_questionnaire_submissions`.

Regle :

- les soumissions sont regroupees par `user_id`;
- le module est marque comme ayant recu un questionnaire via `module_number`;
- il n'y a pas dans cette vue de detail des reponses, seulement l'information que le questionnaire du module a ete recu.

Point de recette :

- Apres soumission du questionnaire module 2, verifier que le formateur est associe au module numero 2 dans `$questionnaireSubmissionModules`.

## 4. Cas de recette recommandes

### Resultats

1. Ouvrir le bilan avec 0 activite validee.
2. Ouvrir le bilan avec seulement les 2 activites du chapitre 2.1 validees.
3. Ouvrir le bilan avec les 7 activites validees.
4. Verifier les badges par lecon : `Validee`, `A terminer`, `Bilan a terminer`.
5. Verifier que `Parcours valide` apparait uniquement a 100 %.
6. Verifier que les durees sont indicatives et non du temps reel.
7. Verifier que le bouton `Repondre au questionnaire` pointe vers la partie `questionnaire`.

### Questionnaire

1. Charger la page et verifier les 17 sliders + 17 boutons NA.
2. Verifier les 5 dimensions et les 2 questions ouvertes.
3. Envoyer sans repondre : erreur front, pas de POST.
4. Repondre aux 17 items avec des valeurs 1 a 5 : POST OK.
5. Utiliser `NA` sur un item : `answer_label = Non applicable`.
6. Verifier que l'item 7 est marque `reversed = true`.
7. Laisser les questions ouvertes vides : POST OK, email affiche `Aucune reponse.`
8. Renseigner les questions ouvertes : textes stockes et envoyes.
9. Reposter le meme UUID : pas de doublon, pas de deuxieme email.
10. Envoyer un payload incomplet via requete directe : HTTP 422, aucun email.

## 5. Points d'attention / risques

- Le questionnaire peut etre ouvert meme si le module n'est pas complet ; le bouton est visible sur la page de bilan.
- La completion pedagogique du module ne depend pas du questionnaire.
- La page de resultats depend de `trainer_path_activity_attempts`, pas des tables SCORM.
- Les sliders affichent visuellement 3 mais ne comptent pas comme reponse tant que l'utilisateur n'interagit pas.
- L'email est envoye immediatement, pas via file d'attente explicite dans ce code.
- Le dedoublonnage est par `submission_uuid`; un nouveau chargement de page genere normalement un nouvel UUID.

## Sources verifiees

- `resources/views/formateur/parcours/partials/lessons/module-2-final-results.blade.php`
- `resources/views/formateur/parcours/partials/lessons/module-2-usability-questionnaire.blade.php`
- `app/Http/Controllers/Formateur/ParcoursController.php`
- `app/Data/ParcoursFormateur.php`
- `database/migrations/2026_06_02_120000_create_trainer_module_questionnaire_submissions_table.php`
- `app/Mail/ModuleQuestionnaireSubmitted.php`
- `resources/views/emails/module-questionnaire-submitted.blade.php`
- `app/Http/Controllers/AdminController.php`
- `tests/Feature/Formateur/ParcoursSidebarCompletionTest.php`
