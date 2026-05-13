# Rapport d'audit LMS - Oneduc.fr

Date de l'audit : 7 mai 2026  
Perimetre : code source local du projet `/var/www/Oneduc_Dev`  
Stack observee : Laravel, Blade, Eloquent, Tailwind/Alpine/Chart.js, modules SCORM JavaScript, Pest/PHPUnit

## 1. Synthese generale

Oneduc.fr est deja beaucoup plus qu'une simple vitrine de formations. Le code contient une plateforme Laravel structurante avec des espaces distincts pour administrateur, formateur, stagiaire et observateur, une gestion de groupes, une affectation de modules, des parcours formateur, des lecons, des contenus SCORM, des quiz natifs, des evaluations SCORM, des outils d'animation pedagogique, des tableaux de progression et plusieurs briques d'accompagnement.

La plateforme possede donc une base LMS reelle. Elle permet de creer des contenus, d'organiser des stagiaires en groupes, d'affecter des modules, de suivre une activite d'apprentissage et de fournir au formateur des indicateurs de progression. Elle est particulierement interessante pour l'inclusion numerique, car elle combine des modules guides, un acces stagiaire simplifie par code, des activites collectives, des tableaux de bord et des outils pedagogiques comme quiz en direct, nuage de mots, mur de questions, sondages, echelle, roue aleatoire, tableau blanc et minuteur.

En revanche, le projet n'est pas encore au niveau d'un LMS professionnel robuste pret a etre exploite sans consolidation. Les principales limites observees sont :

- le suivi SCORM est partiel : les scores, statuts et temps sont consolides, mais les interactions SCORM des lecons ne sont pas effectivement enregistrees par le controleur principal alors que les dashboards les exploitent ;
- plusieurs routes sensibles ou de debug sont hors middleware admin explicite ;
- les routes de lecture stagiaire ne verifient pas assez strictement que le stagiaire a bien ete affecte au module par son groupe ;
- les tableaux de bord sont ambitieux mais reposent parfois sur des donnees incompletes ou fragiles ;
- l'application contient encore beaucoup d'artefacts de template, de code historique, de commentaires de chantier et de doublons ;
- certains flux sont incoherents ou cassants, par exemple `LessonFeedbackController::store()` redirige vers une route `module.lesson` introuvable dans `routes/web.php`, et le controleur admin des groupes ne correspond pas parfaitement aux routes declarees ;
- la suite de tests existe, ce qui est un point fort, mais elle echoue actuellement : `php artisan test` donne 45 tests passes et 40 echoues.

Conclusion courte : Oneduc.fr est un LMS pedagogiquement prometteur et deja exploitable en environnement pilote controle, mais il doit etre securise, fiabilise et nettoye avant une presentation institutionnelle ou une mise en production large.

## 2. Description globale de la plateforme

### Architecture generale Laravel

Le projet suit une organisation Laravel classique :

- routes principales dans `routes/web.php`, avec inclusion de `routes/admin.php`, `routes/formateur.php`, `routes/observateur.php`, `routes/stagiaire.php`, `routes/scorm.php` et `routes/api.php` ;
- controleurs generaux dans `app/Http/Controllers` ;
- controleurs par domaine dans `app/Http/Controllers/Backend`, `app/Http/Controllers/Formateur`, `app/Http/Controllers/Stagiaire`, `app/Http/Controllers/Frontend`, `app/Http/Controllers/Observateur` ;
- modeles Eloquent dans `app/Models` ;
- vues Blade separees par espace dans `resources/views/admin`, `resources/views/formateur`, `resources/views/stagiaire`, `resources/views/observateur`, `resources/views/frontend` ;
- migrations nombreuses dans `database/migrations` ;
- services metier dans `app/Services`, notamment `LearningAnalyticsService`, `QuizService`, `ModuleCompletionNotifier` et `Services/Scorm/ScormImporter.php`.

Le volume observe est significatif :

- environ 184 fichiers dans `app` ;
- environ 468 vues Blade ;
- environ 103 migrations ;
- 376 routes declarees par `php artisan route:list`.

Cette densite traduit une plateforme avancee fonctionnellement, mais aussi une complexite importante pour la maintenance.

### Separation des espaces

La separation des espaces est nette dans les routes :

- administrateur : `routes/admin.php`, prefixe `/admin`, middleware `auth`, `role:admin`, `admin.activity` ;
- formateur : `routes/formateur.php`, prefixe `/formateur`, middleware `auth`, `role:formateur`, `association.member` ;
- stagiaire : `routes/stagiaire.php`, prefixe `/stagiaire`, middleware `auth`, `role:stagiaire`, `track.time`, puis `force.password.change` sur le coeur fonctionnel ;
- observateur : `routes/observateur.php`, role ajoute plus tard par `database/migrations/2026_03_22_120000_add_observateur_role_support.php`.

Cette separation est positive. Elle rend la logique generale lisible. Cependant, des routes importantes restent declarees dans `routes/web.php` hors groupe role, par exemple :

- `/admin/stagiaires/{id}/debug-progression` ;
- `POST /admin/stagiaires/{user}/reset-progression` ;
- plusieurs routes SCORM sans CSRF dans `routes/scorm.php`.

Ces exceptions doivent etre revues avant production.

### Logique MVC

La logique MVC existe mais elle est parfois surchargee :

- `app/Http/Controllers/Backend/ModuleController.php` fait 1185 lignes et gere a la fois CRUD module, lecture, affichage de sections/lecons, fin de module, statistiques et logique de navigation ;
- `app/Http/Controllers/StagiaireController.php` fait 903 lignes et concentre dashboard, resultats, modules, outils, progression et calculs pedagogiques ;
- `app/Http/Controllers/FormateurController.php` fait 687 lignes et embarque un dashboard analytique avance ;
- `app/Http/Controllers/Formateur/GroupeController.php` fait 686 lignes et gere creation, edition, co-formateurs, stagiaires, modules, invitations et validations.

La presence de `LearningAnalyticsService` et `QuizService` est un bon signe : une partie de la logique a ete extraite. Mais le projet gagnerait a poursuivre cette extraction pour isoler :

- acces et autorisations ;
- calcul de progression ;
- suivi SCORM ;
- gestion des groupes ;
- presentation dashboard.

### Artefacts et redondances

Le depot contient de nombreuses vues et ressources de template generique, par exemple `resources/views/content/apps/*`, `resources/views/content/pages/*`, `resources/assets/js/app-ecommerce-*`, `resources/assets/js/app-logistics-*`. Ces fichiers ne sont pas necessairement utilises par Oneduc. Ils alourdissent le projet, compliquent l'audit et peuvent induire des confusions.

Plusieurs commentaires de chantier sont encore presents dans le code, par exemple dans `app/Http/Controllers/Stagiaire/QuizController.php` : "Copier le contenu de votre methode result() existante ici", "Reste des methodes privees inchangees". Ce n'est pas bloquant fonctionnellement, mais cela donne une impression de code en transition.

## 3. Analyse par profil utilisateur

### Administrateur

#### Droits et acces

L'administrateur accede a l'espace `/admin` via `routes/admin.php`. Le groupe de routes est protege par :

- `auth` ;
- `role:admin` ;
- `admin.activity`.

Le middleware `Role` dans `app/Http/Middleware/Role.php` verifie que l'utilisateur est connecte, que son role fait partie des roles autorises et que son compte est actif (`status`). Si la verification echoue, il deconnecte l'utilisateur et le redirige vers `/connexion`.

#### Fonctionnalites observees

Fonctionnel :

- tableau de bord admin avec compteurs de categories, sous-categories, modules, formateurs, stagiaires, groupes, sections et lecons dans `AdminController::AdminDashboard()` et `resources/views/admin/index.blade.php` ;
- gestion des formateurs : liste, activation/desactivation, suppression soft delete, statut d'adhesion (`AdminController`) ;
- gestion des stagiaires : liste, suppression, reset progression via `Backend/StagiaireController` ;
- gestion des observateurs via `Backend/ObservateurController` ;
- gestion des categories et sous-categories via `Backend/CategoryController` ;
- gestion des groupes via `Backend/GroupeController` ;
- gestion des modules via `Backend/ModuleController` ;
- gestion des sections via `Backend/ModuleSectionController` ;
- gestion des lecons via `Backend/ModuleLectureController` ;
- import SCORM pour une lecon via `Backend/ScormLibraryController::importForLecture()` ;
- import slides via `ModuleLectureController::importSlidesForLecture()` ;
- gestion des evaluations SCORM via `Backend/EvaluationController` ;
- gestion des questions de quiz natifs via `Backend/QuizQuestionController` ;
- gestion des competences, referentiels et badges via `SkillReferentialController`, `SkillDomainController`, `SkillController`, `CompetencyController`, `BadgeController` ;
- gestion des retours stagiaires via `LessonFeedbackController::adminIndex()` ;
- module de pilotage interne via `Backend/PilotageController` ;
- gestion d'un outil "nuage de mot" admin via `Backend/WordCloudController`.

Partiellement fonctionnel ou fragile :

- le tableau "Cours suivis" de `resources/views/admin/index.blade.php` affiche une table `datatables-academy-course`, mais les donnees `scormSummaries` calculees dans `AdminController::AdminDashboard()` ne sont pas rendues explicitement dans la vue. L'indicateur SCORM admin semble donc incomplet ou dependant d'un JavaScript de template ;
- `Backend/GroupeController` contient des methodes qui ne correspondent pas parfaitement aux routes admin : `routes/admin.php` declare `DeleteGroupe`, mais le controleur visible expose `destroy($id)` et pas `DeleteGroupe($id)` ; `UpdateGroupe(Request $request)` attend un champ `id` alors que la route fournit `{id}` ;
- plusieurs redirections dans le controleur admin des groupes utilisent des noms de route non prefixes ou formateur (`route('groupes')`, `route('formateur.groupes.index')`) alors que les routes admin sont prefixees `admin.*`.

Absent ou insuffisant :

- pas de gestion fine de roles/permissions au-dela du champ `role` ;
- pas de gestion multi-tenant claire pour organisme de formation ;
- pas d'audit pedagogique complet admin par groupe/module/stagiaire comparable a un LMS mature ;
- pas de generation de certificats observable malgre le champ `certificat` dans `modules` ;
- pas de reporting exportable robuste visible dans les routes auditees.

### Formateur

#### Droits et acces

Les routes formateur sont dans `routes/formateur.php`, protegees par :

- `auth` ;
- `role:formateur` ;
- `association.member`.

Le middleware `EnsureAssociationMembership` impose une politique d'adhesion : un formateur peut utiliser la plateforme si son adhesion est active ou s'il est en periode de grace (`adhesion_status = pending`, creation inferieure a un mois). Sinon il est redirige vers `/adhesion`.

Cette logique correspond au modele associatif/commercial recent du projet. Elle est pertinente strategiquement, mais elle a un impact fort : plusieurs tests formateur echouent aujourd'hui avec redirection `/adhesion`, ce qui montre que la politique n'est pas encore integree partout dans les tests et peut surprendre certains flux.

#### Fonctionnalites observees

Fonctionnel :

- dashboard formateur dans `FormateurController::FormateurDashboard()` et `resources/views/formateur/index.blade.php` ;
- visualisation des groupes accessibles au formateur et aux co-formateurs via `Group::scopeAccessibleByTrainer()` ;
- creation/edition/suppression de groupes via `Formateur/GroupeController` ;
- ajout ou reutilisation de stagiaires, creation de codes d'acces, rattachement aux groupes, invitation mail via `StagiaireGroupInvitation` ;
- gestion de co-formateurs sur un groupe, avec notifications internes ;
- affectation de modules actifs a un groupe avec ordre (`group_module.position`) ;
- personnalisation des lecons par groupe via `Formateur/GroupeModuleLessonController` et table `group_module_lectures` ;
- acces aux modules utilises ou crees via `FormateurModuleController` ;
- preview de module ;
- creation de "mes formations" / parcours formateur via `Formateur/MesFormationsController` ;
- parcours de formation formateur via `Formateur/ParcoursController` ;
- suivi de progression par groupes, stagiaires, modules et detail stagiaire via les controleurs `Progression*` ;
- outils numeriques : quiz en direct, nuage de mots, sondage, echelle, roue aleatoire, mur de questions, tableau blanc collaboratif, minuteur ;
- ressources de lecon visibles ou non aux stagiaires via `Formateur/LessonResourceController` ;
- profil, parametres, securite, suppression de compte via `FormateurProfileController`.

Partiellement fonctionnel :

- les tableaux de progression melangent SCORM, quiz natifs, video et progression manuelle, ce qui est pedagogiquement interessant, mais le canal SCORM interactionnel est incomplet ;
- les "formations" creees par formateur (`formateur_parcours`) structurent des modules, wordclouds et polls, mais elles semblent surtout servir a l'organisation de parcours et non encore a un moteur LMS complet de prerequis, certificats, objectifs atteints ou preuves ;
- le formateur peut visualiser des quiz comme un stagiaire, via `Stagiaire/QuizController`, ce qui evite la duplication mais brouille les responsabilites techniques ;
- le dashboard AJAX d'activite est avance mais depend de `LearningAnalyticsService`, lequel depend lui-meme de donnees partiellement alimentees.

Limites :

- un formateur ne semble pas creer directement des modules LMS complets dans l'espace formateur classique ; la creation de modules reste admin via `Backend/ModuleController`, tandis que "Mes formations" assemble des modules existants et activites ;
- les droits exacts entre formateur proprietaire, co-formateur et observateur sont partiellement traites mais pas generalises par policies Laravel ;
- l'adhesion formateur est recente et doit etre mieux testee pour eviter les redirections inattendues.

### Stagiaire

#### Droits et acces

Les routes stagiaire sont dans `routes/stagiaire.php`, protegees par :

- `auth` ;
- `role:stagiaire` ;
- `track.time` ;
- puis `force.password.change` sauf pour la premiere connexion.

Le stagiaire peut se connecter classiquement ou par code d'acces via `UserController::loginByCode()`. Le code est un champ `users.code_acces`, ajoute par `database/migrations/2025_05_11_152452_add_code_acces_to_users_table.php`. Ce choix est tres pertinent pour un public eloigne du numerique.

Le middleware `ForcePasswordChange` impose un changement de mot de passe si `password_changed_at` est nul. C'est bien pour la securite, meme si l'exigence de mot de passe reste seulement `min:8` dans `FirstLoginController`.

#### Fonctionnalites observees

Fonctionnel :

- dashboard stagiaire dans `StagiaireController::StagiaireDashboard()` et `resources/views/stagiaire/index.blade.php` ;
- liste des modules rattaches au premier groupe actif du stagiaire via `StagiaireController::StagiaireModules()` ;
- detail module avec progression par section/lecon dans `StagiaireController::StagiaireModuleDetail()` ;
- lecture de sections et lecons via `Backend/ModuleController::section()` et `::lire()` ;
- lancement et passage de quiz natifs via `Stagiaire/QuizController` ;
- resultats detailles via `StagiaireController::StagiaireResultats()` ;
- fin de module via `Backend/ModuleController::finModule()` ;
- participation aux live quiz, tableau blanc, minuteur, wordcloud de parcours ;
- profil, parametres, securite, suppression de compte via `UserController`.

Partiellement fonctionnel :

- la progression detaillee depend de plusieurs sources : `progressions`, `scorm_scores`, `scorm_results`, `quiz_attempts`, `quiz_attempt_questions`, `video_segment_trackings`. Cette richesse est interessante, mais les regles ne sont pas uniformes selon les ecrans ;
- `StagiaireModuleDetail()` utilise `ScormInteraction` pour compter des reponses SCORM ; comme les interactions SCORM de lecons ne sont pas enregistrees par `SCORMController`, une lecon SCORM peut apparaitre mal evaluee ou "non commencee" selon l'ecran ;
- `StagiaireModules()` ne prend que le premier groupe actif (`first()`). Si un stagiaire appartient a plusieurs groupes actifs, l'experience peut masquer des modules.

Risque important :

- les routes `stagiaire/modules/{module}`, `stagiaire/modules/{module}/sections/{section}` et `stagiaire/modules/{module}/sections/{section}/lessons/{lecture}` verifient surtout que le module est actif et coherent, mais pas suffisamment que le module appartient bien a un groupe du stagiaire. Dans `Backend/ModuleController::section()` et `::lire()`, `Module::isVisibleTo()` autorise tout module actif. Cela expose potentiellement tous les modules actifs a tout stagiaire authentifie connaissant l'URL.

## 4. Analyse des fonctionnalites LMS

### Gestion des modules

Etat : fonctionnel, admin-centre.

Les modules sont representes par `App\Models\Module` et la table `modules`. Le modele gere :

- categorie et sous-categorie ;
- formateur associe ;
- images module/header ;
- titre, nom, slug, description, objectifs ;
- video module ;
- duree, ressources, certificat, prerequis ;
- flags marketing (`bestseller`, `vedette`, `surevalue`) ;
- statut actif/inactif ;
- evaluation associee ;
- duree estimee calculee a partir des lecons et questions.

Fichiers principaux :

- `app/Models/Module.php` ;
- `database/migrations/2025_03_16_195920_create_modules_table.php` ;
- `app/Http/Controllers/Backend/ModuleController.php` ;
- `resources/views/admin/backend/modules/*`.

Capacite reelle :

- creation, edition, suppression, activation ;
- association a categories, sous-categories et formateur ;
- association a groupes via `group_module` ;
- calcul d'une duree pedagogique approximative via `getTotalSecondsAttribute()` et `getEstimatedSecondsForUser()`.

Limites :

- le champ `certificat` existe mais aucun flux de generation/remise de certificat n'a ete observe ;
- la creation module est admin, pas vraiment decentralisee pour formateurs independants ;
- le modele contient des attributs marketing herites d'un template ou d'une logique catalogue qui ne sont pas forcement utiles a un LMS inclusion numerique ;
- l'acces aux modules actifs doit etre mieux controle cote stagiaire.

### Categories et sous-categories

Etat : fonctionnel.

Fichiers :

- `app/Models/Category.php` ;
- `app/Models/SubCategory.php` ;
- `app/Http/Controllers/Backend/CategoryController.php` ;
- `database/migrations/2025_03_01_152713_create_categories_table.php` ;
- `database/migrations/2025_03_09_143822_create_sub_categories_table.php`.

La plateforme expose aussi les categories publiquement via `routes/web.php` :

- `/formations` ;
- `/categorie/{id}/modules` ;
- `/categorie/{id}/sous-categories`.

Limite : la taxonomie est utile pour le catalogue, mais elle n'est pas encore reliee a une logique de competences, prerequis ou parcours adaptatif.

### Sections et lecons

Etat : fonctionnel mais controleur tres charge.

Fichiers :

- `app/Models/ModuleSection.php` ;
- `app/Models/ModuleLecture.php` ;
- `app/Http/Controllers/Backend/ModuleSectionController.php` ;
- `app/Http/Controllers/Backend/ModuleLectureController.php` ;
- `database/migrations/2025_03_28_053345_create_module_sections_table.php` ;
- `database/migrations/2025_03_28_053401_create_module_lectures_table.php`.

Les lecons peuvent contenir :

- contenu SCORM (`scorm_path`, package/version) ;
- contenu slides (`content_type`, `slides_status`, `slides_path`) ;
- duree estimee ;
- nombre de slides ;
- nombre de questions ;
- quiz natif active ou non ;
- live quiz active ou non ;
- objectifs pedagogiques (`LectureObjective`) ;
- ressources de lecon (`LessonResource`).

Limites :

- la logique de lecture est mutualisee dans `Backend/ModuleController`, ce qui rend le fichier difficile a maintenir ;
- les modes `groupe`, `officiel`, `anonymous`, `include_hidden` sont puissants mais complexes ;
- les controles d'appartenance groupe/module doivent etre renforces pour le stagiaire.

### Groupes et affectations

Etat : tres avance, mais a consolider.

Fichiers :

- `app/Models/Group.php` ;
- `database/migrations/2025_03_10_214601_create_groups_table.php` ;
- `database/migrations/2025_03_10_214611_create_group_user_table.php` ;
- `database/migrations/2025_04_08_193327_create_group_module_table.php` ;
- `database/migrations/2026_01_24_000000_create_group_module_lectures_table.php` ;
- `app/Http/Controllers/Formateur/GroupeController.php` ;
- `app/Http/Controllers/Formateur/GroupeModuleLessonController.php`.

Capacites :

- un groupe a un formateur principal (`instructor_id`) ;
- des stagiaires sont rattaches via `group_user` avec `role_in_group = stagiaire` ;
- des co-formateurs sont rattaches via `role_in_group = formateur` ;
- des observateurs peuvent etre rattaches via migration role observateur ;
- des modules sont rattaches via `group_module`, avec ordre ;
- des lecons peuvent etre masquees/reordonnees pour un groupe via `group_module_lectures`.

Points forts :

- logique co-formateur claire avec `Group::scopeAccessibleByTrainer()` ;
- possibilite de parcours personnalises par groupe ;
- code d'acces temporaire chiffre (`temporary_password` caste `encrypted`) ;
- invitation mail aux stagiaires.

Limites :

- le controleur admin des groupes est moins mature que le controleur formateur ;
- pas de notion explicite de cohorte institutionnelle, centre, organisme ou client ;
- pas de regles d'inscription/archivage avancees ;
- multi-groupes stagiaire encore mal expose dans l'interface stagiaire.

### Parcours de formation

Etat : partiellement fonctionnel.

Deux notions coexistent :

- le parcours de formation du formateur lui-meme, via `app/Data/ParcoursFormateur.php` et `Formateur/ParcoursController.php` ;
- les formations/parcours crees par un formateur, via `FormateurParcours`, `FormateurParcoursItem` et `Formateur/MesFormationsController.php`.

Les parcours formateur peuvent contenir des modules et des activites comme wordcloud ou poll. La table `groups` peut pointer vers `formateur_parcours_id`.

Capacite reelle :

- assembler une sequence de modules et activites ;
- rattacher un parcours a un groupe ;
- afficher le parcours cote stagiaire dans `StagiaireModules()`.

Limites :

- pas encore un moteur de parcours complet avec prerequis bloquants, jalons obligatoires, remediation automatique, certificats et preuves ;
- validation des acquis surtout issue des quiz/SCORM, pas d'une matrice de competences finalisee ;
- les parcours semblent recents et encore en structuration.

### Progression stagiaire

Etat : avance mais heterogene.

Sources de progression :

- table `progressions` pour validation manuelle de lecon ;
- table `scorm_results` pour traces SCORM K/V ;
- table `scorm_scores` pour score/statut/temps ;
- table `quiz_attempts` et `quiz_attempt_questions` pour quiz natifs ;
- table `video_segment_trackings` pour suivi video ;
- `LearningAnalyticsService` pour consolider ces donnees.

Points forts :

- le service `LearningAnalyticsService` est une bonne abstraction ;
- les dashboards formateur et progression detaillee prennent en compte quiz, video, SCORM et activite recente ;
- l'approche "droit a l'erreur" dans `StagiaireResultats()` et `ProgressionStagiaireController` valorise la progression finale plutot que seulement le premier echec.

Limites :

- les regles de completion varient selon les ecrans ;
- le SCORM interactionnel n'etant pas rempli, certains indicateurs de questions/reponses SCORM sont trompeurs ;
- une progression manuelle peut marquer une lecon terminee sans preuve pedagogique forte ;
- absence de schema unique "attempt" pour les lecons SCORM, ce qui limite l'analyse de tentatives.

### Commentaires et retours stagiaires

Etat : partiellement fonctionnel.

Fichiers :

- `app/Models/LessonFeedback.php` ;
- `app/Http/Controllers/LessonFeedbackController.php` ;
- migrations `2025_04_26_201930_create_lesson_feedbacks_table.php` et suivantes.

Capacites :

- un utilisateur connecte peut envoyer un commentaire avec type, note, urgence ;
- l'admin peut consulter et supprimer les retours.

Probleme observe :

- `LessonFeedbackController::store()` redirige vers `route('module.lesson')`, route non trouvee dans `routes/web.php`. Les routes existantes sont plutot `stagiaire.module.lecture`, `formateur.formations.lecture` ou `lecture.show`. Ce flux risque donc de casser en production.

### Evaluations

Etat : partiellement fonctionnel.

Il existe deux dispositifs :

- evaluations SCORM rattachees aux modules (`Evaluation`, `ScormEvaluation*`) ;
- quiz natifs par lecon (`QuizQuestion`, `QuizOption`, `QuizAttempt`, `QuizAttemptQuestion`).

Les quiz natifs sont plus solides que les evaluations SCORM en termes d'analyse : ils enregistrent les questions, reponses, temps par question, score, pourcentage et statut de reussite.

Les evaluations SCORM enregistrent des traces et interactions, mais leur controleur contient une methode `fin(Evaluation $evaluation)` avec une reference a `Evaluation` sans `use App\Models\Evaluation;` dans `EvaluationSCORMController.php`. Cette methode n'est pas routee dans `routes/scorm.php`, mais sa presence signale un code incomplet ou duplique avec `Backend/EvaluationController::fin()`.

### Suivi pedagogique

Etat : prometteur.

Les controleurs `ProgressionGroupesController`, `ProgressionStagiairesController`, `ProgressionModulesController`, `ProgressionStagiaireController`, `FormateurController` et le service `LearningAnalyticsService` montrent une vraie intention de suivi pedagogique :

- taux de reussite ;
- activite recente ;
- stagiaires non demarres ;
- stagiaires inactifs ;
- questions les plus echouees ;
- temps d'apprentissage ;
- temps de reflexion ;
- timeline d'activite.

Mais la fiabilite depend de la qualite des donnees. Pour devenir un outil de suivi institutionnel, il faut verrouiller les definitions : "commence", "termine", "reussi", "actif", "temps d'apprentissage", "temps de connexion".

## 5. Analyse du suivi SCORM

### Integration SCORM

Fichiers principaux :

- `routes/scorm.php` ;
- `app/Http/Controllers/SCORMController.php` ;
- `app/Http/Controllers/EvaluationSCORMController.php` ;
- `app/Http/Controllers/Backend/ScormLibraryController.php` ;
- `app/Services/Scorm/ScormImporter.php` ;
- `public/scorm_core/js/API.js` ;
- `public/scorm_core/js/api_Scorm2004.js` ;
- modeles `ScormResult`, `ScormScore`, `ScormInteraction`, `ScormPackage`, `ScormPackageVersion`, `ScormEvaluationResult`, `ScormEvaluationScore`, `ScormEvaluationInteraction`.

L'import SCORM est bien pense sur plusieurs points :

- upload ZIP valide par `ScormImportRequest`, taille max 500 Mo ;
- extraction dans un dossier par lecon via `LearningAssetPath::lessonImportFolder()` ;
- protection contre path traversal dans `ScormImporter::safeExtract()` ;
- detection du `imsmanifest.xml` et du point d'entree ;
- injection de `/scorm_core/js/API.js` dans l'index ;
- version active via `scorm_packages` et `scorm_package_versions` ;
- cache token base sur `imported_at`.

### Routes SCORM

Dans `routes/scorm.php` :

- `POST /scorm/save-progress` vers `SCORMController::saveProgress`, sans middleware CSRF ;
- `POST /scorm/progress` vers le meme controleur, avec `auth` ;
- `GET /lecture/{id}/scorm` vers `Frontend\LectureController::showScorm` ;
- `GET /lecture/{id}/slides` vers `Frontend\LectureController::showSlides` ;
- `POST /scorm/evaluation-progress` vers `EvaluationSCORMController::saveEvaluationProgress`, sans CSRF.

Risque :

- `save-progress` est sans CSRF et sans `auth` explicite, meme si le controleur exige `Auth::id()` et retournera 400 sans session. Pour une API appelee par iframe SCORM, l'absence de CSRF peut se comprendre, mais il faut au minimum verifier que l'utilisateur authentifie a droit a la lecon cible.

### Tables SCORM

Pour les lecons :

- `scorm_results` : trace brute par `user_id`, `lecture_id`, `scorm_key`, `scorm_value` ;
- `scorm_scores` : score consolide par `user_id`, `lecture_id`, avec `first_score`, `best_score`, `last_score`, `attempts_count`, `questions_answered`, `lesson_status`, `is_completed`, `session_time`, `last_attempt_at` ;
- `scorm_interactions` : modele prevu pour questions/reponses SCORM, avec `interaction_id`, `interaction_key`, `interaction_type`, `result`, `response`, `correct_response`, `latency`, `time`.

Pour les evaluations :

- `scorm_evaluation_results` ;
- `scorm_evaluation_scores` ;
- `scorm_evaluation_interactions`.

### Enregistrement des scores et statuts

`SCORMController::saveProgress()` enregistre :

- toutes les paires K/V dans `ScormResult::updateOrCreate()` ;
- `cmi.core.score.raw` dans `ScormScore.last_score`, `first_score`, `best_score` ;
- `cmi.core.lesson_status`, `cmi.completion_status`, `cmi.success_status` dans `lesson_status` et `is_completed` ;
- `cmi.core.session_time` dans `session_time`.

Il applique ensuite une regle metier : score >= 50 => completion.

Limites :

- le seuil est code en dur a 50 dans `SCORMController::recomputeMonotoneStatus()` ;
- la migration initiale indique un commentaire "termine si score >= 75", mais le controleur utilise 50 : incoherence ;
- `attempts_count` est cree avec 1 mais n'est pas incremente dans `SCORMController` ;
- `questions_answered` n'est pas alimente ;
- `last_session_time` est utilise dans le controleur, mais aucune migration ne cree cette colonne. Eloquent peut porter un attribut dynamique non persiste, donc le calcul de delta de session risque de ne pas fonctionner comme prevu entre requetes.

### Interactions SCORM

Point critique : les interactions SCORM des lecons sont modelisees mais non branchees.

Observation :

- `ScormInteraction` existe ;
- la table `scorm_interactions` existe ;
- les dashboards et services la consultent massivement ;
- `public/scorm_core/js/API.js` envoie toutes les cles SCORM vers `/scorm/save-progress` ;
- `SCORMController::saveProgress()` ne traite pas les cles `cmi.interactions.*` ;
- `public/scorm_core/js/api_Scorm2004.js` stocke les interactions en memoire et les affiche en console, mais ne les poste pas au backend.

Consequence :

- les indicateurs "questions traitees", "bonnes reponses", "mauvaises reponses", "latence", "questions difficiles" ne sont fiables que pour les quiz natifs, pas pour les modules SCORM ;
- plusieurs ecrans utilisent pourtant `ScormInteraction`, par exemple `StagiaireController`, `ProgressionStagiaireController`, `LearningAnalyticsService`, `Backend/ModuleController` ;
- le potentiel d'analyse pedagogique SCORM est present dans le schema, mais pas realise dans le flux principal.

### Temps passe

Le temps SCORM est stocke dans `scorm_scores.session_time`, mais :

- le calcul de delta repose sur `last_session_time`, non migre ;
- SCORM 2004 utilise souvent `cmi.session_time` au format ISO 8601, alors que le controleur principal ne gere que `cmi.core.session_time` au format `HH:MM:SS` ;
- `EvaluationSCORMController` additionne directement chaque `session_time`, ce qui peut surestimer le temps si le package renvoie un cumul et non un delta.

Le suivi du temps est donc utile en tendance, mais pas encore fiable comme preuve institutionnelle.

### Statut de lecon

Le statut est monotone dans `SCORMController` : une fois `completed` ou `passed`, il ne retrograde pas. C'est positif pour eviter qu'un package SCORM envoie ensuite `incomplete`.

Limite : la completion SCORM ne cree pas de ligne dans `progressions`. Les ecrans doivent donc tous penser a regarder `scorm_scores`. Certains le font, d'autres utilisent `ScormInteraction` ou `Progression`.

### Potentiel d'analyse pedagogique

Fort potentiel si les interactions sont branchees :

- questions echouees par module ;
- erreurs recurrentes par stagiaire ;
- temps de reflexion par question ;
- distinction premiere tentative / meilleure tentative ;
- remediation automatique ;
- preuves d'apprentissage exportables.

Aujourd'hui, ce potentiel est surtout realise pour les quiz natifs.

## 6. Analyse des tableaux de bord

### Tableau de bord administrateur

Fichiers :

- `app/Http/Controllers/AdminController.php` ;
- `resources/views/admin/index.blade.php`.

Indicateurs affiches :

- categories ;
- sous-categories ;
- modules ;
- formateurs ;
- stagiaires ;
- groupes ;
- sections ;
- lecons.

Origine :

- `Module::count()`, `Category::count()`, `SubCategory::count()`, `Group::count()`, `User::where(role)`, `ModuleSection::count()`, `ModuleLecture::count()`.

Pertinence :

- bon tableau de pilotage administratif ;
- faible valeur pedagogique directe ;
- utile pour mesurer le volume de contenus et d'utilisateurs.

Limites :

- pas de taux d'activite, completions, resultats moyens ou alertes globales ;
- `scormSummaries` est calcule mais pas vraiment exploite dans la vue ;
- pas d'indicateur qualite contenu, accessibilite, progression par organisme.

### Tableau de bord formateur

Fichiers :

- `app/Http/Controllers/FormateurController.php` ;
- `app/Services/LearningAnalyticsService.php` ;
- `resources/views/formateur/index.blade.php`.

Indicateurs affiches :

- groupes crees/accessibles ;
- modules utilises ;
- total stagiaires ;
- taux de reussite moyen ;
- stagiaires actifs, inactifs, non demarres ;
- groupes prioritaires ;
- modules prioritaires ;
- graphique AJAX d'activite des groupes par jour/semaine/mois/annee.

Origine :

- relations groupes/modules/stagiaires ;
- snapshots de `LearningAnalyticsService` ;
- `progressions`, `scorm_results`, `scorm_scores`, `scorm_interactions`, `video_segment_trackings`, `quiz_attempts`, `quiz_attempt_questions`.

Pertinence pedagogique :

- tres bonne intention : le formateur voit rapidement ou intervenir ;
- l'identification des groupes/modules prioritaires peut aider au suivi ;
- la prise en compte de l'activite recente est pertinente pour l'accompagnement.

Fiabilite :

- correcte pour les quiz natifs ;
- partielle pour SCORM ;
- dependante de la definition de "started" et "successful" dans `LearningAnalyticsService::finalizeSnapshot()`.

Manques :

- export ;
- filtres par periode et groupe sur tous les blocs ;
- affichage des objectifs/competences non acquis ;
- alerte explicite "donnees SCORM incompletes" ;
- distinction score formatif/sommatif.

### Tableau de bord stagiaire

Fichiers :

- `app/Http/Controllers/StagiaireController.php` ;
- `resources/views/stagiaire/index.blade.php`.

Indicateurs affiches :

- formateur referent ;
- temps d'apprentissage ;
- questions traitees ;
- taux de reussite ;
- formations en cours ;
- progression par module ;
- graphique de reussite ;
- temps moyen de reflexion.

Origine :

- `VideoSegmentTracking` ;
- `ScormScore.session_time` ;
- `quiz_attempts.total_time_seconds` ;
- `ScormInteraction` ;
- `quiz_attempt_questions` ;
- groupes et modules du stagiaire.

Pertinence pedagogique :

- tres bonne lisibilite pour l'apprenant ;
- donne un retour motivant ;
- peut soutenir l'autonomie.

Fiabilite :

- bonne pour les quiz natifs ;
- partielle pour SCORM ;
- le temps d'apprentissage peut sous-estimer ou surestimer selon les formats SCORM/video ;
- si plusieurs groupes actifs existent, seul le premier groupe semble utilise dans `StagiaireModules()`.

## 7. Analyse pedagogique

Oneduc.fr est pedagogiquement pertinent pour l'inclusion numerique pour plusieurs raisons :

- l'acces par code reduit la barriere de connexion ;
- les modules peuvent etre structures en sections et lecons ;
- les formateurs peuvent organiser des groupes et suivre des stagiaires ;
- les quiz natifs permettent un feedback clair ;
- les activites live soutiennent l'animation presentielle ou distancielle ;
- le tableau blanc, le minuteur et les outils collaboratifs favorisent la mediation ;
- le suivi de progression donne au formateur des signaux d'accompagnement.

Capacite a accompagner des debutants : bonne, sous reserve de simplifier encore l'interface stagiaire et les parcours. Les menus stagiaire sont courts : Formation, Progression, Outils, Documentation. C'est positif.

Capacite a structurer un parcours : moyenne a bonne. Les modules, sections, lecons et parcours existent. Il manque encore une logique claire de prerequis, objectifs valides, competences acquises et certificat.

Capacite a suivre les apprenants : bonne en intention, moyenne en fiabilite. Les quiz natifs sont exploitables, mais le SCORM doit etre consolide.

Capacite a aider les formateurs : bonne. Les dashboards, groupes, stagiaires, co-formateurs et outils d'animation sont des atouts forts.

Capacite a produire des preuves d'apprentissage : partielle. Les tentatives quiz et scores SCORM sont des preuves, mais il manque une formalisation : exports, horodatage robuste, definition de reussite, version de contenu, certificat, journal d'activite pedagogique.

Capacite a soutenir l'inclusion numerique : forte. C'est le positionnement le plus coherent du projet. Oneduc peut se differencier d'un LMS classique en etant plus accompagne, plus simple et plus centre formateur/stagiaire debutant.

## 8. Analyse technique

### Qualite du code

Points positifs :

- architecture Laravel reconnaissable ;
- modeles Eloquent nombreux et relations utiles ;
- services metier existants (`LearningAnalyticsService`, `QuizService`, `ScormImporter`) ;
- migrations evolutives ;
- tests existants ;
- validation presente dans de nombreux controleurs ;
- protections d'import ZIP SCORM ;
- middleware roles et statut ;
- soft delete utilisateurs et nettoyage de donnees liees.

Points faibles :

- plusieurs controleurs sont trop longs ;
- logique metier et presentation encore melangees dans les controleurs ;
- noms de routes parfois incoherents ou historiques ;
- code de template non utilise encore present ;
- FormRequest `StoreModuleRequest` et `StoreGroupeRequest` ont `authorize(): false`, ce qui les rend inutilisables tels quels ;
- beaucoup de commentaires de chantier et de traces "nouveau", "correction", etc. ;
- certaines methodes ou imports semblent morts ou incomplets (`ScormInteractionController` importe dans `routes/scorm.php`, non present ; `EvaluationSCORMController::fin()` non routee et typage incomplet).

### Relations Eloquent

Bonnes relations :

- `User` vers groupes stagiaire/formateur/observateur ;
- `Group` vers instructor, students, modules, coFormateurs, observers ;
- `Module` vers category, subCategory, formateur, sections, lectures, groups, evaluation ;
- `ModuleLecture` vers section, module, progressions, feedbacks, quizQuestions, scormPackageVersion ;
- quiz attempts et questions bien relies.

Limites :

- certaines relations retournent des query builders non conventionnels, par exemple `Module::stagiaires()` construit une requete `User::where(...)` au lieu d'une relation Eloquent classique ;
- les scopes et policies d'acces devraient etre centralises pour eviter les controles disperses.

### Securite

Points positifs :

- middleware par role ;
- statut actif obligatoire ;
- password hashing ;
- code d'acces genere par service ;
- premiere connexion stagiaire avec changement de mot de passe ;
- validation de nombreux formulaires ;
- protection import ZIP contre path traversal ;
- nettoyage des donnees utilisateur a la suppression.

Risques :

- route `/admin/stagiaires/{id}/debug-progression` publique dans `routes/web.php` ;
- route `POST /admin/stagiaires/{user}/reset-progression` declaree hors groupe admin ;
- `POST /scorm/save-progress` et `POST /scorm/evaluation-progress` sans CSRF ;
- absence de verification d'appartenance module/groupe dans la lecture stagiaire ;
- route publique `/lecture/{id}` peut afficher une lecon frontend sans auth ;
- login par code sans throttling explicite sur `POST /stagiaire/connexion-code` ;
- mise a jour de certains emails sans regle `unique` stricte dans profil ;
- suppression de comptes formateur peut supprimer des groupes/stagiaires par cascade applicative : puissant mais a manipuler avec confirmation et audit.

### Performance

Points positifs :

- le dashboard formateur charge son activite en AJAX avec cache ;
- certains controleurs ont ete optimises pour eviter des N+1 ;
- `LearningAnalyticsService` regroupe des calculs.

Risques :

- `AdminController::AdminDashboard()` fait des requetes par ligne SCORM dans un `map`, source potentielle de N+1 ;
- gros controleurs difficiles a profiler ;
- beaucoup de vues et assets inutiles peuvent alourdir le build et la maintenance ;
- calculs de progression parfois faits a la volee sans materialisation.

### Tests

La suite de tests est un atout. Elle couvre :

- auth ;
- import SCORM ;
- quiz ;
- live quiz ;
- groupes/co-formateurs ;
- progression formateur ;
- tableau blanc ;
- observateur ;
- medias quiz ;
- parcours formateur.

Commande executee : `php artisan test`  
Resultat observe : 45 tests passes, 40 tests echoues, 197 assertions, duree 39,59 s.

Types d'echecs :

- nombreuses erreurs 419 sur POST/DELETE en test ;
- routes attendues absentes (`dashboard`, `/profile`) ;
- redirections formateur vers `/adhesion` liees au middleware `association.member` ;
- certains flux ne creent pas les ressources attendues (live quiz, roue aleatoire, ressources lecon) dans les conditions de test ;
- certains ecrans formateur renvoient 302 ou 403 au lieu de 200.

Ces echecs ne prouvent pas que tout est casse en production, mais ils indiquent que la base de tests n'est pas actuellement verte et que les evolutions recentes n'ont pas ete totalement reintegrees.

## 9. Analyse de l'experience utilisateur

### Navigation

Points forts :

- menus separes par role ;
- menu stagiaire simple ;
- menu formateur clair : Groupes, Stagiaires, Formations, Outils, Progression ;
- fil d'Ariane present dans plusieurs vues ;
- grandes cartes visuelles, pictogrammes, sections lisibles.

Points faibles :

- l'interface utilise beaucoup de grandes cartes arrondies et d'illustrations, ce qui peut etre agreable mais parfois moins dense pour des formateurs experts ;
- de nombreux ecrans contiennent beaucoup d'informations et de controles ;
- certains libelles et routes historiques peuvent creer des incoherences : modules/formations/parcours/mes formations ;
- le dashboard admin n'exploite pas toutes ses donnees.

### Accessibilite numerique

Points positifs :

- certains champs quiz prevoient `image_alt` et `audio_transcript` ;
- menus avec `aria-label` et `aria-current` dans plusieurs sidebars ;
- le parcours stagiaire est relativement simple ;
- code d'acces pratique.

Limites :

- il n'y a pas de preuve d'audit RGAA/WCAG ;
- les couleurs et contrastes doivent etre testes systematiquement ;
- plusieurs composants interactifs reposent sur JavaScript custom ;
- les carrousels et graphiques doivent avoir des alternatives textuelles ;
- les contenus SCORM importes peuvent etre tres variables en accessibilite ;
- les grandes icones decoratives doivent rester ignorees par lecteur d'ecran quand elles ne portent pas d'information.

### Points de friction pour un public eloigne du numerique

- creation de compte + changement de mot de passe peut rester difficile malgre le code ;
- interface dashboard riche mais peut impressionner ;
- modules multiples par groupe non evidents ;
- "Outils" peut etre abstrait pour un stagiaire ;
- SCORM en iframe peut poser des problemes mobile/accessibilite.

## 10. Forces principales

Fonctionnelles :

- vraie gestion multi-profils ;
- groupes, stagiaires, formateurs, co-formateurs ;
- modules, sections, lecons ;
- SCORM et quiz natifs ;
- tableaux de progression ;
- outils d'animation live ;
- parcours formateur et parcours de formation ;
- ressources de lecon ;
- profil, securite, suppression compte ;
- observateur.

Pedagogiques :

- suivi du droit a l'erreur ;
- quiz natifs avec temps par question ;
- indicateurs d'activite recente ;
- outils collaboratifs adaptes a l'animation ;
- acces simplifie stagiaire ;
- orientation inclusion numerique tres coherente.

Techniques :

- Laravel structure ;
- migrations riches ;
- tests presents ;
- services metier ;
- import SCORM securise contre path traversal ;
- versioning SCORM ;
- soft deletes et nettoyage de donnees.

Ergonomiques :

- menus par role ;
- visuel identifiable ;
- tableaux de bord accessibles dans l'intention ;
- parcours stagiaire court.

Strategiques :

- positionnement inclusion numerique differenciant ;
- logique d'adhesion association ;
- potentiel de solution pour organismes, collectivites, associations, ateliers numeriques.

## 11. Faiblesses principales

Fonctionnalites incompletes :

- suivi interactions SCORM non branche ;
- certificats absents malgre champ ;
- exports/reporting absents ;
- parcours sans moteur de prerequis/acquis complet ;
- competences/badges presents mais pas encore pleinement relies aux resultats stagiaire ;
- evaluation SCORM separee et moins robuste que les quiz natifs.

Risques techniques :

- routes admin de debug/reset hors middleware ;
- acces stagiaire aux modules actifs trop large ;
- controleurs trop volumineux ;
- incoherences de routes ;
- tests non verts ;
- code historique/template important ;
- certains champs utilises sans migration observee (`last_session_time`).

Dette technique :

- duplication entre role formateur/stagiaire pour quiz et lecture ;
- logique de progression dispersee ;
- commentaires de chantier ;
- FormRequests inutilisables ;
- N+1 potentiels.

Bloquants production :

- securisation des routes ;
- controle d'acces par affectation groupe/module ;
- fiabilisation SCORM ;
- correction des routes cassantes ;
- suite de tests verte ;
- clarification RGPD et donnees d'apprentissage ;
- sauvegarde/export des preuves.

## 12. Niveau de maturite du projet

Maturite fonctionnelle : intermediaire avancee. Les briques existent et beaucoup sont connectees.

Maturite technique : intermediaire. La base est solide mais la dette technique et les tests echoues empechent une qualification production sereine.

Maturite pedagogique : prometteuse. Le produit comprend bien les besoins d'accompagnement et d'inclusion, mais doit mieux formaliser competences, preuves et remediation.

Maturite UX : correcte pour un pilote, a simplifier pour large public. Le stagiaire a un menu simple, mais certaines interfaces restent riches.

Maturite LMS professionnelle : partielle. Oneduc fait deja beaucoup de choses d'un LMS, mais il manque les garanties d'un LMS professionnel : acces stricts, reporting fiable, suivi SCORM complet, exports, certifications, roles/permissions fins, documentation.

## 13. Potentiel d'exploitation professionnelle

### Formateurs independants

Potentiel : bon.

Deja exploitable :

- creation de groupes ;
- rattachement de stagiaires ;
- modules disponibles ;
- suivi de progression ;
- outils d'animation ;
- code d'acces ;
- dashboard formateur.

A consolider :

- permettre aux formateurs de creer/modifier leurs propres modules sans passer par l'admin, ou clarifier que le catalogue est central ;
- modele d'adhesion clair ;
- exports PDF/CSV ;
- tutoriels formateur ;
- tests du flux d'inscription/adhesion.

### Organismes de formation

Potentiel : moyen a bon, mais non mature.

Manques :

- multi-organisation ;
- roles gestionnaire, coordinateur, financeur ;
- conventions, sessions, presences ;
- exports administratifs ;
- attestations/certificats ;
- archivage et audit.

### Outil d'inclusion numerique

Potentiel : tres fort.

Oneduc peut se differencier par :

- simplicite d'acces ;
- accompagnement par formateur ;
- visualisation de progression ;
- outils collectifs ;
- contenus centres numerique ;
- langage clair et pedagogique.

### Outil de suivi pedagogique

Potentiel : bon si les donnees sont fiabilisees.

Il faut prioriser :

- SCORM interactions ;
- definitions d'indicateurs ;
- export de preuves ;
- alertes pedagogiques exploitables ;
- competences liees aux objectifs et quiz.

### Plateforme commercialisable

Potentiel : reel, mais pas immediatement comme SaaS LMS generaliste.

Positionnement recommande :

- "LMS d'inclusion numerique accompagnee" ;
- solution pour ateliers numeriques, associations, collectivites, organismes de formation debutants ;
- moins concurrente de Moodle/360Learning sur tout le spectre, plus differenciante sur la mediation et la simplicite.

## 14. Risques avant mise en production

Critiques :

- acces non autorise possible a des modules actifs par URL pour des stagiaires ;
- routes admin/debug hors middleware ;
- SCORM incomplet et donnees dashboard potentiellement trompeuses ;
- tests non verts ;
- route de feedback cassante ;
- incoherences controleur/routes admin groupes.

Importants :

- donnees de temps d'apprentissage peu fiables ;
- absence d'exports de preuve ;
- absence de policies Laravel centralisees ;
- code de template non nettoye ;
- documentation utilisateur et admin insuffisante ;
- accessibilite non certifiee.

Reglementaires :

- donnees d'apprentissage, temps de connexion et resultats sont des donnees personnelles ;
- il faut clarifier duree de conservation, finalite, droit d'acces/suppression, traces, exports ;
- suppression utilisateur nettoie beaucoup de donnees, mais il faut documenter ce comportement.

## 15. Recommandations detaillees

### Priorites critiques

1. Renforcer les autorisations d'acces aux modules et lecons stagiaire.

Probleme : `Backend/ModuleController::section()` et `::lire()` autorisent un module actif sans verifier strictement que le stagiaire appartient a un groupe ayant ce module.  
Impact : un stagiaire authentifie peut potentiellement acceder a un module actif par URL.  
Solution : ajouter une policy `ModulePolicy::viewAsStudent()` ou un service d'autorisation qui verifie `group_user` + `group_module` + groupe actif + lecon active selon `group_module_lectures`.  
Zones : `app/Http/Controllers/Backend/ModuleController.php`, `app/Policies`, `routes/stagiaire.php`.

2. Securiser les routes admin hors middleware.

Probleme : routes debug/reset declarees dans `routes/web.php` hors groupe admin.  
Impact : fuite ou modification de donnees de progression.  
Solution : deplacer dans `routes/admin.php` sous middleware `auth`, `role:admin`, supprimer la route debug en production ou la proteger par environnement local.  
Zones : `routes/web.php`, `routes/admin.php`, `app/Http/Controllers/Backend/StagiaireController.php`.

3. Brancher les interactions SCORM.

Probleme : `scorm_interactions` est utilisee par les dashboards mais non alimentee par `SCORMController`.  
Impact : indicateurs de questions, bonnes/mauvaises reponses, temps de reflexion SCORM non fiables.  
Solution : parser `cmi.interactions.{index}.{field}` dans `SCORMController::saveProgress()`, creer ou mettre a jour `ScormInteraction`, gerer SCORM 1.2 et 2004 (`student_response` vs `learner_response`, `correct_responses.0.pattern`, `latency`, `time/timestamp`, `result`).  
Zones : `app/Http/Controllers/SCORMController.php`, `public/scorm_core/js/API.js`, `public/scorm_core/js/api_Scorm2004.js`, migrations si besoin.

4. Corriger le suivi du temps SCORM.

Probleme : `last_session_time` est utilise mais non migre ; SCORM 2004 `cmi.session_time` n'est pas gere dans le controleur principal.  
Impact : temps d'apprentissage incertain.  
Solution : ajouter colonne `last_session_time`, parser ISO 8601 SCORM 2004, definir si `session_time` stocke un cumul ou un delta.  
Zones : `database/migrations`, `SCORMController`, `EvaluationSCORMController`.

5. Corriger les routes cassantes.

Probleme : `LessonFeedbackController::store()` redirige vers `module.lesson`, route absente.  
Impact : retour stagiaire peut echouer apres enregistrement.  
Solution : rediriger selon route courante ou stocker `redirect_to`, utiliser `stagiaire.module.lecture` / `formateur.formations.lecture`.  
Zones : `app/Http/Controllers/LessonFeedbackController.php`, vues de lecon.

6. Remettre la suite de tests au vert.

Probleme : `php artisan test` echoue a 40 tests.  
Impact : impossible de garantir la stabilite avant financeurs/production.  
Solution : corriger configuration CSRF tests, adapter les factories formateurs a `adhesion_status = active` ou tester explicitement l'adhesion, restaurer routes attendues ou mettre a jour les tests.  
Zones : `tests/*`, middleware, routes.

### Priorites importantes

7. Decouper les gros controleurs.

Probleme : `ModuleController`, `StagiaireController`, `FormateurController`, `Formateur/GroupeController` sont trop longs.  
Impact : maintenance difficile, regressions probables.  
Solution : extraire services et actions : `ModuleNavigationService`, `StudentAccessService`, `ProgressionCalculator`, `ScormTrackingService`, `GroupManagementService`.  
Zones : controleurs cites, `app/Services`.

8. Centraliser les droits dans des policies.

Probleme : checks disperses dans les controleurs.  
Impact : incoherences d'acces.  
Solution : `GroupPolicy`, `ModulePolicy`, `LecturePolicy`, `StagiairePolicy`, `LessonResourcePolicy`.  
Zones : `app/Policies`, controleurs formateur/stagiaire/observateur.

9. Clarifier les indicateurs dashboards.

Probleme : "taux de reussite", "progression", "actif", "temps" varient selon source.  
Impact : incomprehension financeurs/formateurs, donnees contestables.  
Solution : documenter les definitions, afficher la source, ajouter badges "quiz natif", "SCORM", "video", "manuel".  
Zones : `LearningAnalyticsService`, vues dashboards/progression.

10. Finaliser competences et badges.

Probleme : competences/badges existent mais ne semblent pas attribues automatiquement aux stagiaires.  
Impact : potentiel pedagogique sous-exploite.  
Solution : lier objectifs de lecon aux competences, definir regles d'acquisition, afficher competences acquises/non acquises.  
Zones : `LectureObjective`, `Competency`, `Badge`, quiz/SCORM/progression.

11. Ajouter exports et preuves.

Probleme : pas d'exports observes.  
Impact : faible valeur pour organismes/financeurs.  
Solution : exports CSV/PDF par groupe, stagiaire, module ; attestation de suivi ; journal horodate.  
Zones : progressions formateur/admin.

### Ameliorations utiles

12. Nettoyer les artefacts de template.

Probleme : nombreuses vues/assets generiques non Oneduc.  
Impact : complexite, risque de confusion, dette.  
Solution : identifier routes/vues utilisees, archiver ou supprimer le reste.  
Zones : `resources/views/content`, `resources/assets/js`, `public/frontend/assets/js`.

13. Ameliorer l'accessibilite.

Probleme : pas de preuve RGAA/WCAG.  
Impact : contradiction possible avec l'objectif d'inclusion.  
Solution : audit contrastes, navigation clavier, alternatives graphiques, ARIA, tests lecteur d'ecran, SCORM accessible.  
Zones : vues stagiaire/formateur, composants Blade, quiz, dashboards.

14. Simplifier le vocabulaire.

Probleme : modules, formations, parcours, mes formations, outils peuvent se chevaucher.  
Impact : confusion utilisateurs.  
Solution : definir un lexique produit : Formation = parcours, Module = unite, Chapitre = section, Lecon = activite.  
Zones : vues et menus.

15. Renforcer la securite du login par code.

Probleme : pas de throttle explicite sur `loginByCode`.  
Impact : brute force possible sur codes courts.  
Solution : ajouter throttle, code plus long ou expiration, journalisation, tentative max.  
Zones : `routes/web.php`, `UserController::loginByCode()`, `CodeGeneratorService`.

### Evolutions futures

16. Multi-organisation.

Ajouter organismes, equipes, financeurs, coordinateurs, perimetres de donnees.

17. Certificats et attestations.

Generer des attestations basees sur completions, temps, scores, version de contenu.

18. Remediation pedagogique.

Proposer automatiquement des reprises de lecons ou ressources selon questions echouees.

19. Bibliotheque SCORM centralisee.

Permettre versioning, rollback, affectation multi-lecons et analyse par version.

20. Mode tres simplifie stagiaire.

Pour inclusion numerique : un bouton principal "Continuer ma formation", affichage minimal, audio/aide contextuelle.

## 16. Feuille de route proposee

### Phase 1 - Securisation et stabilisation

- deplacer/proteger routes admin debug/reset ;
- ajouter controle d'appartenance stagiaire aux modules/lecons ;
- corriger route feedback ;
- corriger admin groupes ;
- remettre tests critiques auth/admin/formateur au vert ;
- documenter les indicateurs existants.

### Phase 2 - Fiabilisation LMS

- brancher interactions SCORM ;
- corriger temps SCORM ;
- unifier calcul progression ;
- ajouter exports de progression ;
- ajouter policies Laravel ;
- nettoyer controleurs les plus gros.

### Phase 3 - Maturite pedagogique

- relier objectifs, competences et quiz ;
- afficher competences acquises ;
- definir regles de badge ;
- ajouter remediation ;
- ameliorer feedback stagiaire/formateur.

### Phase 4 - Exploitation professionnelle

- multi-organisation ;
- roles coordinateur/financeur ;
- certificats/attestations ;
- reporting institutionnel ;
- documentation utilisateur ;
- audit RGAA/WCAG ;
- supervision et sauvegarde production.

## 17. Conclusion generale

Oneduc.fr dispose d'une base tres prometteuse pour devenir un LMS specialise dans l'inclusion numerique. Le projet montre une comprehension fine des besoins terrain : acces simple, groupes, accompagnement formateur, outils collectifs, progression, quiz, SCORM et tableaux de bord.

Son principal enjeu n'est plus de prouver qu'il peut devenir un LMS : le code montre deja cette direction. L'enjeu est maintenant de transformer une plateforme riche et en evolution rapide en produit fiable, securise, lisible et defendable devant des partenaires institutionnels.

La priorite absolue est de fiabiliser les droits d'acces, le suivi SCORM, les routes sensibles et la suite de tests. Ensuite, Oneduc pourra capitaliser sur sa vraie force : une approche LMS moins froide qu'un outil generaliste, plus proche de l'accompagnement humain et adaptee aux publics eloignes du numerique.

## Notes indicatives sur 20

- Maturite technique : 11/20
- Maturite pedagogique : 14/20
- Experience utilisateur : 13/20
- Potentiel commercial : 15/20
- Capacite LMS globale : 13/20

Ces notes tiennent compte du potentiel visible dans le code, mais aussi des risques actuels avant production.

## Tableau de synthese

| Domaine analyse | Etat actuel | Risque ou limite | Recommandation prioritaire |
|---|---|---|---|
| Architecture Laravel | Structure claire par routes, modeles, vues et controleurs | Controleurs tres volumineux, logique dispersee | Extraire services et policies |
| Profils utilisateurs | Admin, formateur, stagiaire, observateur presents | Droits geres par role simple, pas de permissions fines | Centraliser les autorisations |
| Admin | CRUD et pilotage larges | Routes debug/reset hors groupe admin, dashboard SCORM peu exploite | Securiser routes et enrichir reporting |
| Formateur | Gestion groupes, stagiaires, modules, outils, progression | Middleware adhesion recent provoque des redirections/tests casses | Stabiliser politique adhesion et tests |
| Stagiaire | Acces modules, dashboard, resultats, quiz, outils | Acces direct possible a modules actifs non affectes | Verifier appartenance groupe/module |
| Modules | CRUD admin, images, video, statut, duree estimee | Certificat non implemente, creation formateur limitee | Clarifier catalogue et certification |
| Sections/lecons | SCORM, slides, quiz, objectifs, ressources | Logique de navigation complexe dans `ModuleController` | Extraire service de navigation |
| Groupes | Affectations, co-formateurs, ordre modules | Admin groupe incoherent avec routes | Corriger controleur admin groupes |
| Parcours | Parcours formateur et parcours crees existent | Pas de moteur complet de prerequis/acquis | Formaliser parcours pedagogiques |
| Quiz natifs | Tres utile, scores et temps par question | Seuils et regles a documenter | Lier aux competences/objectifs |
| SCORM | Import/versioning et scores presents | Interactions non enregistrees, temps fragile | Brancher `cmi.interactions.*` et temps SCORM |
| Evaluations SCORM | Tables et controleur dedies | Flux moins robuste, duplication de fin | Harmoniser avec quiz/progression |
| Dashboards | Riches et pedagogiques | Fiabilite variable selon donnees SCORM | Afficher sources et definitions |
| Feedback stagiaire | Table et admin presents | Redirection vers route inexistante | Corriger `LessonFeedbackController` |
| Accessibilite | Quelques attributs et ARIA presents | Pas d'audit RGAA/WCAG, SCORM variable | Lancer audit accessibilite |
| Securite | Middleware role/statut, hash, soft delete | CSRF SCORM, routes publiques, acces URL | Durcir routes et policies |
| Tests | Couverture existante importante | 40 tests echoues sur 85 | Remettre la suite au vert |
| Commercialisation | Fort potentiel inclusion numerique | Pas encore SaaS/organisme mature | Positionner pilote puis consolider reporting |
