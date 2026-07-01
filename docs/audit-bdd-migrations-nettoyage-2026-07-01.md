# Audit BDD, migrations et nettoyage du code

Date: 2026-07-01
Projet: Oneduc Dev
Mode: audit lecture seule initial, corrections non destructives de code/routes, puis baseline schema non destructive

## Corrections appliquees dans ce lot

- Route `GET /admin/stagiaires/{id}/debug-progression` supprimee de `routes/web.php`.
- Route `POST /admin/stagiaires/{user}/reset-progression` deplacee dans `routes/admin.php` sous `auth`, `role:admin`, `admin.activity`.
- Route GET `/contact` dupliquee retiree.
- Modele `App\Models\TrainerPathActivityAttempt` ajoute pour la table `trainer_path_activity_attempts`.
- Usages applicatifs principaux de `trainer_path_activity_attempts` refactorises depuis `DB::table(...)` vers le modele.
- Archive `database/migrations/migrations.zip` deplacee vers `docs/archives/migrations-legacy-2026-07-01.zip`.
- Baseline Laravel ajoutee dans `database/schema/mysql-schema.sql` via `php artisan schema:dump`, sans `--prune`.
- Baseline restauree avec succes dans une instance MariaDB temporaire isolee: 74 tables, 109 migrations marquees comme executees, puis `Nothing to migrate`.
- Migrations historiques pruned via `php artisan schema:dump --prune` apres merge et validation de la baseline.
- Dossier `database/migrations` conserve avec un README pour les futures migrations.
- Vues, layouts, partials, menus JSON et scripts Vite de demonstration du template retires apres verification d'absence de routes/references applicatives.
- Verification: `php artisan test` passe avec 101 tests.


## Synthese courte

La base locale est coherentement reliee au code applicatif: je n'ai pas trouve de table metier totalement orpheline, c'est-a-dire sans modele, sans reference directe et sans lien relationnel.

Le vrai sujet est une consolidation:

- 74 tables MariaDB, base `oneduc`, environ 4.27 MB.
- 109 migrations appliquees, toutes en batch 1.
- 60 tables couvertes par des modeles Eloquent.
- Aucune classe de modele ne pointe vers une table absente.
- Au depart, une table metier n'avait pas de modele: `trainer_path_activity_attempts`. Corrige dans ce lot.
- Beaucoup de tables metier sont vides en local, mais plusieurs correspondent a des fonctionnalites actives.
- Plusieurs migrations historiques se neutralisent ou representent des etapes intermediaires a consolider.
- Deux routes de diagnostic/action sur les progressions sont exposees hors middleware admin.

Conclusion: il ne faut pas commencer par supprimer des tables. Il faut d'abord corriger les risques de routes, consolider les migrations sur une base propre, puis nettoyer par lots.

## Commandes d'audit executees

- `git status -sb`
- `php artisan migrate:status`
- `php artisan db:show --counts --views`
- `php artisan route:list`
- `php artisan route:list -v --path=admin/stagiaires`
- `php artisan schema:dump`
- `php artisan schema:dump --prune`
- Restauration du schema dans une instance MariaDB temporaire locale, separee de `oneduc`.
- Verification des vues avec `php artisan view:cache`.
- Verification frontend avec `npm run build`.
- Recherches `rg` sur les migrations, modeles, routes, controleurs, vues et tests.
- Introspection Laravel en lecture seule via `php artisan tinker --execute` pour croiser tables, modeles, references de code et cles etrangeres.

## Baseline schema Laravel

Fichier ajoute:

- `database/schema/mysql-schema.sql`

Statut:

- Dump genere avec `php artisan schema:dump`.
- Anciennes migrations conservees: aucun `schema:dump --prune` n'a ete execute.
- Le fichier contient 74 definitions de tables et les 109 entrees de la table `migrations`.
- La restauration a ete testee dans une instance MariaDB 10.11.14 temporaire, lancee sur un port isole.
- Resultat Laravel sur base vide: `Loading stored database schemas`, puis `Nothing to migrate`.
- La base temporaire et ses fichiers ont ete supprimes apres validation.

Conclusion:

- On dispose maintenant d'un point de reference fiable pour une installation neuve.
- La prochaine etape peut etre une PR separee de pruning/consolidation, mais seulement apres decision explicite, car elle supprimera ou archivera des fichiers de migration historiques.

## Pruning des migrations historiques

Action appliquee apres merge de la baseline:

- `php artisan schema:dump --prune`
- Suppression des 109 fichiers de migrations historiques de `database/migrations`.
- Conservation du schema final dans `database/schema/mysql-schema.sql`.
- Ajout de `database/migrations/README.md` pour garder le dossier et orienter les prochaines migrations.

Impact:

- Aucune table ni donnee applicative n'est supprimee.
- Les nouvelles installations chargent le schema baseline avant les migrations futures.
- L'historique detaille reste disponible dans Git avant le commit de pruning.

## Nettoyage des vues et assets de demonstration

Action appliquee apres le pruning:

- Suppression de `resources/views/content`, qui contenait les pages de demonstration du template: ecommerce, chat, calendar, invoice, charts, tables, auth demos, etc.
- Suppression des partials de demonstration `resources/views/_partials`.
- Suppression des layouts de demonstration `layoutMaster`, `commonMaster`, `contentNavbarLayout`, `blankLayout`, `horizontalLayout`, `layoutFront` et de `resources/views/layouts/sections`.
- Suppression des menus JSON de demonstration `resources/menu`.
- Suppression des scripts Vite de demonstration `resources/assets/js` et de `resources/assets/css/demo.css`.

Verifications:

- Aucune route ne ciblait ces vues.
- Les layouts applicatifs conserves sont `layouts.app`, `layouts.guest` et `layouts.navigation`.
- `php artisan view:cache` passe.
- `php artisan route:list` passe avec 401 routes.
- `npm run build` passe.
- `php artisan test` passe avec 101 tests.

Impact:

- Nettoyage non destructif cote base de donnees.
- Les assets publics et les sources applicatives utilises par `resources/js/app.js` restent en place.
- Les vues metier Oneduc, admin, formateur, stagiaire et observateur sont conservees.

## Etat de la base locale

Base:

- SGBD: MariaDB 10.11.14
- Connexion: `mysql`
- Base: `oneduc`
- Tables: 74
- Taille totale: 4.27 MB

Tables avec donnees significatives dans cette base locale:

- `users`: 6
- `groups`: 1
- `group_user`: 5
- `group_module`: 9
- `categories`: 4
- `subcategories`: 6
- `modules`: 15
- `module_sections`: 43
- `module_lectures`: 86
- `lecture_objectives`: 168
- `quiz_questions`: 168
- `quiz_options`: 420
- `cache`: 1
- `sessions`: 1

La plupart des autres tables metier sont vides dans cette base, mais cela ne prouve pas qu'elles soient inutiles. Plusieurs sont des tables de fonctionnalites actives qui n'ont simplement pas encore de donnees locales.

## Cartographie par domaines

### Socle utilisateur et groupes

Tables principales:

- `users`
- `groups`
- `group_user`
- `group_module`
- `sessions`
- `password_reset_tokens`

Statut:

- A conserver.
- `users`, `groups`, `group_user` et `group_module` sont fortement utilises.
- `sessions` et `password_reset_tokens` sont des tables systeme Laravel.

Remarque:

- `groups` a plusieurs migrations d'ajout de colonnes (`temporary_password`, `is_active`, `start_date`, `end_date`, `formateur_parcours_id`, `is_sandbox`) qui doivent etre repliees dans une migration consolidee.

### Catalogue pedagogique

Tables principales:

- `categories`
- `subcategories`
- `modules`
- `module_sections`
- `module_lectures`
- `lesson_resources`
- `lecture_objectives`
- `competencies`
- `badges`
- `badge_competency`
- `lecture_objective_competency`

Statut:

- A conserver pour l'instant.
- Le domaine est actif dans les controleurs admin, formateur et stagiaire.
- `lesson_resources`, `competencies`, `badges` et pivots sont vides localement, mais ils ont des controleurs, routes ou vues.

Decision prise dans le lot objectifs legacy:

- `learning_objectives` n'est plus utilise par l'application.
- Le domaine actif des objectifs pedagogiques est `lecture_objectives`.
- Suppression du modele `App\Models\LearningObjective`.
- Ajout d'une migration de suppression de `learning_objectives` avec garde anti-perte de donnees si des lignes existent.

### SCORM, quiz et progression

Tables principales:

- `progressions`
- `quiz_questions`
- `quiz_options`
- `quiz_attempts`
- `quiz_attempt_questions`
- `scorm_results`
- `scorm_scores`
- `scorm_interactions`
- `scorm_evaluation_results`
- `scorm_evaluation_scores`
- `scorm_evaluation_interactions`
- `video_segment_trackings`
- `evaluations`
- `scorm_packages`
- `scorm_package_versions`

Statut:

- A conserver.
- Les tables de quiz sont peuplees (`quiz_questions`, `quiz_options`).
- Les tables SCORM et progression sont vides localement mais utilisees par le code et les tests.

Point a clarifier:

- Il y a deux familles SCORM: suivi de lecon (`scorm_*`) et suivi d'evaluation (`scorm_evaluation_*`).
- Elles peuvent rester separees si les parcours metier sont distincts.
- Un regroupement serait possible uniquement apres verification fonctionnelle, car cela toucherait les rapports, suppressions de stagiaires et endpoints SCORM.

### Outils live et animation

Tables principales:

- `word_clouds`
- `word_cloud_entries`
- `random_wheel_sessions`
- `question_walls`
- `question_wall_questions`
- `question_wall_votes`
- `poll_sessions`
- `poll_session_responses`
- `scale_sessions`
- `scale_session_responses`
- `group_timers`
- `group_whiteboards`
- `group_whiteboard_items`
- `live_quiz_sessions`
- `live_quiz_session_questions`
- `live_quiz_session_participants`

Statut:

- A conserver pour l'instant.
- Beaucoup de tables sont vides localement, mais les fonctionnalites ont routes, controleurs, modeles et vues.
- Les references directes par nom de table sont parfois faibles parce que le code utilise surtout Eloquent.

### Parcours formateur

Tables principales:

- `formateur_parcours`
- `formateur_parcours_items`
- `trainer_path_activity_attempts`
- `trainer_module_questionnaire_submissions`

Statut:

- Domaine actif.
- `trainer_path_activity_attempts` etait utilisee via `DB::table(...)`, sans modele Eloquent.

Correction conseillee:

- Correction appliquee: modele `TrainerPathActivityAttempt` ajoute.
- Les usages principaux dans `ParcoursController`, `TrainerPathQualityDashboardService` et `AdminController` passent maintenant par le modele.
- Les migrations et tests peuvent conserver `DB::table(...)`, car ils manipulent directement le schema ou des fixtures.

### Pilotage interne

Tables principales:

- `pilot_projects`
- `pilot_tasks`
- `pilot_task_comments`
- `pilot_subscriptions`
- `pilot_notification_preferences`
- `activity_journal_entries`
- `notifications`

Statut:

- A conserver si le module pilotage est voulu.
- Tables vides localement, mais routes admin, controleurs, notifications et middleware existent.

Point de vigilance:

- Si le module pilotage est experimental, le nettoyer doit etre decide comme un choix produit, pas seulement sur la base du nombre de lignes.

### Contact

Tables et code:

- `contacts`
- `App\Models\Contact`
- `database/migrations/2025_05_31_083505_create_contacts_table.php`
- `app/Http/Controllers/ContactController.php`

Constat:

- La table `contacts` est vide.
- Le modele `Contact` existe mais n'est pas utilise.
- Le formulaire de contact envoie des emails et une notification Discord, mais n'enregistre rien en base.

Decision prise dans le lot `contacts`:

- Conserver le support par email et Discord, sans historique applicatif en base.
- Supprimer le modele `Contact`.
- Ajouter une migration de suppression de `contacts`.
- Ajouter une garde anti-perte de donnees: si la table contient des lignes au moment du deploiement, la migration s'arrete et demande un export ou une migration explicite avant suppression.

## Risques et code inutile identifies

### Priorite haute: routes de progression non protegees

Dans `routes/web.php`, deux routes admin sont declarees au niveau global:

- `GET /admin/stagiaires/{id}/debug-progression`
- `POST /admin/stagiaires/{user}/reset-progression`

Verification:

- `php artisan route:list -v --path=admin/stagiaires` montre que ces deux routes n'ont que le middleware `web`.
- Les routes admin normales ont `web`, `auth`, `role:admin`, `admin.activity`.

Impact:

- `debug-progression` expose des compteurs de tables par utilisateur.
- `reset-progression` appelle une methode qui supprime des donnees de progression, quiz, SCORM et suivi video apres desactivation temporaire des contraintes SQL.

Recommandation:

- Supprimer la route de debug si elle n'est plus utile.
- Deplacer `reset-progression` dans `routes/admin.php`, dans le groupe admin protege.
- Ajouter un test de route/middleware ou un test d'acces admin.

### Route contact dupliquee

Dans `routes/web.php`:

- `Route::get('/contact', fn () => view(...))->name('contact.form');`
- `Route::get('/contact', [ContactController::class, 'index'])->name('contact');`

`route:list --path=contact` n'expose que:

- `POST contact` vers `ContactController@send`
- `GET contact` vers `ContactController@index`

Recommandation:

- Supprimer la closure `contact.form`, ou garder un seul nom de route clair.

### Archive dans le dossier migrations

Fichier initialement suivi:

- `database/migrations/migrations.zip`

Correction appliquee:

- Archive deplacee vers `docs/archives/migrations-legacy-2026-07-01.zip`.
- Le dossier `database/migrations` ne contient plus cette archive.
- Le contenu est conserve hors du chemin executable des migrations Laravel.

### Vues et assets de template

Plusieurs vues `resources/views/content/*`, layouts de template et assets `resources/assets/*` ressemblent a du contenu de demonstration/theme.

Exemples reperes:

- `resources/views/content/apps/*`
- `resources/views/content/front-pages/*`
- `resources/views/content/user-interface/*`
- `resources/views/content/wizard-example/*`
- `resources/views/content/laravel-example/*`

Statut:

- Ce n'est pas directement lie a la BDD.
- Plusieurs references sont des faux positifs d'audit, par exemple autour de `contacts`.

Recommandation:

- Faire un audit frontend separe avant suppression.
- Verifier si des routes, menus ou layouts utilisent encore ces vues.

## Migrations a consolider

Toutes les migrations sont appliquees en batch 1. Cela signifie que la base locale a ete reconstruite en appliquant toute l'histoire d'un coup. C'est un bon signal pour envisager une consolidation.

Attention: ne pas modifier ou supprimer les anciennes migrations sur une base existante sans strategie. Pour une application deja installee, il faut soit garder l'historique, soit produire un schema baseline controle.

### Migrations clairement historiques ou intermediaires

#### Evaluation sur `modules`

- `2025_05_18_053656_add_evaluation_id_to_modules_table.php`
  - `up()` ne fait rien.
  - `down()` supprime `evaluation_id`.
  - Cette migration est incoherente en rollback.
- `2025_07_13_000001_add_evaluation_fk_to_modules_table.php`
  - Ajoute vraiment `evaluation_id`.

Action:

- En consolidation, ne garder qu'une definition finale de `modules.evaluation_id`.

#### Parcours formateur: remplacement table modules par items

- `2026_04_21_100001_create_formateur_parcours_modules_table.php`
  - Cree `formateur_parcours_modules`.
- `2026_04_21_100002_replace_formateur_parcours_modules_with_items.php`
  - Supprime `formateur_parcours_modules`.
  - Cree `formateur_parcours_items`.

Action:

- En baseline, ne pas recreer `formateur_parcours_modules`.
- Garder seulement `formateur_parcours_items` dans sa forme finale.

#### Parcours formateur: evolution wordcloud/sondage

Migrations a replier dans la definition finale de `formateur_parcours_items`:

- `2026_04_21_100003_add_poll_columns_to_formateur_parcours_items.php`
- `2026_04_21_191700_add_wc_duration_to_formateur_parcours_items.php`
- `2026_04_21_192247_replace_wc_question_with_wc_questions_on_formateur_parcours_items.php`
- `2026_04_21_194532_replace_poll_fields_on_formateur_parcours_items.php`

Etat final attendu:

- `wc_questions` JSON
- `wc_duration`
- `poll_questions` JSON
- `poll_duration`
- pas de `wc_question`, `poll_question`, `poll_choices`

#### Module lectures

`module_lectures` est creee puis modifiee par de nombreuses migrations:

- chemin SCORM
- position
- structure de contenu
- quiz settings
- scorm package version
- duree
- slides
- live quiz
- html content
- content blocks

Action:

- Tres bon candidat a une migration consolidee.
- Ne garder dans la baseline que les colonnes finales.

#### Users

`users` recoit plusieurs ajouts:

- `formateur_id`
- `code_acces`
- `total_site_time`
- soft deletes
- `password_changed_at`
- champs adhesion
- support role observateur

Action:

- Replier dans une seule definition finale de `users`.

#### Groups

`groups` recoit plusieurs ajouts:

- `temporary_password`
- `is_active`
- `start_date`
- `end_date`
- `formateur_parcours_id`
- `is_sandbox`

Action:

- Replier dans une seule definition finale de `groups`.

### Timestamps de migrations dupliques

Plusieurs migrations ont exactement le meme timestamp:

- `2025_05_18_182626_create_scorm_evaluation_results_table.php`
- `2025_05_18_182626_create_scorm_evaluation_scores_table.php`
- `2026_01_04_113808_create_skill_domains_table.php`
- `2026_01_04_113808_create_skills_table.php`
- `2026_06_12_110207_add_attempt_number_to_trainer_path_activity_attempts_table.php`
- `2026_06_12_110207_add_is_sandbox_to_groups_table.php`

Ce n'est pas forcement bloquant, car Laravel trie aussi par nom complet, mais c'est moins lisible et plus fragile pour une histoire long terme.

Action:

- En consolidation, utiliser des noms ordonnes et non ambigus.

## Protocole recommande pour regrouper les migrations

### Phase 1: stabiliser sans toucher au schema

1. Corriger/proteger les routes sensibles.
2. Decider du sort de `contacts`. Fait.
3. Lancer les tests apres chaque lot.

Fait dans le lot courant:

- Modele/couche d'acces pour `trainer_path_activity_attempts`.
- Deplacement de `database/migrations/migrations.zip`.
- Tests Laravel complets.

### Phase 2: produire un schema de reference

Option recommandee Laravel:

1. Faire une sauvegarde de la base.
2. Generer un dump de schema avec `php artisan schema:dump`.
3. Ne pas utiliser `--prune` au premier passage.
4. Tester une installation fraiche: base vide, migrations, seeders, tests.
5. Comparer le schema obtenu avec la base actuelle.
6. Quand le schema est valide, envisager `schema:dump --prune` uniquement sur une branche dediee.

Fait dans le lot baseline:

- Dump `database/schema/mysql-schema.sql` genere sans pruning.
- Restauration testee sur base MariaDB temporaire.
- Statut valide: 74 tables et 109 migrations restaurees.
- Tests Laravel complets OK.

Fait dans le lot pruning:

- `schema:dump --prune` execute apres merge de la baseline.
- Migrations historiques retirees du dossier d'execution Laravel.
- Dossier `database/migrations` conserve pour les futures migrations.

Fait dans le lot vues/assets:

- Suppression des vues et scripts de demonstration du template non routes.
- Verification `view:cache`, `route:list`, `npm run build` et tests Laravel.

Fait dans le lot PHP/dependances:

- Suppression du controleur vide `HomeController`.
- Suppression du mailer scaffold inutilise `ContactFormMail`.
- Suppression des anciennes vues admin SCORM `library_*`, non routees et referenceant des routes inexistantes.
- Suppression des vues d'evaluation `old_*`, non referencees.
- Suppression d'une ancienne route `/login` doublonnee vers un `LoginController` inexistant; la route active reste `Auth\AuthenticatedSessionController`.
- Suppression de la dependance NPM inutilisee `@tailwindcss/aspect-ratio`.
- Conservation temporaire de `learning_objectives`: decision traitee dans le lot objectifs legacy.
- Conservation temporaire de `contacts`: decision traitee dans le lot suivant.

Fait dans le lot contacts:

- Decision de ne pas stocker les demandes de contact en base applicative.
- Suppression du modele `App\Models\Contact`.
- Ajout d'une migration `drop_contacts_table` avec garde si des donnees inattendues existent.

Fait dans le lot objectifs legacy:

- Decision de conserver `lecture_objectives` comme modele d'objectifs pedagogiques actif.
- Suppression du modele `App\Models\LearningObjective`.
- Retrait de `learning_objectives` de la purge manuelle stagiaire et du test associe.
- Ajout d'une migration `drop_learning_objectives_table` avec garde si des donnees historiques existent.

Fait dans le lot vues auth/admin:

- Suppression de l'ancien login admin `admin.admin_login`, non reference par une route.
- Suppression de l'ancien formulaire `admin.backend.groupes.assign_stagiaire`, non reference par une route et pointant vers une route inexistante.
- Suppression du footer admin `admin.body.footer`, remplace par le footer integre dans `admin.admin_dashboard`.
- Suppression du login Breeze `auth.login`, remplace par `frontend.contenu.login`.
- Suppression de l'ancien forgot-password stagiaire Vuexy, remplace par `auth.forgot-password`.

Fait dans le lot rafraichissement baseline:

- Regeneration de `database/schema/mysql-schema.sql` apres execution des migrations de suppression legacy.
- Retrait de `contacts` et `learning_objectives` du schema baseline pour les nouvelles installations.
- Conservation des migrations `drop_contacts_table` et `drop_learning_objectives_table` pour les environnements existants qui ne les auraient pas encore appliquees.

Fait dans le lot route/partials stagiaire:

- Suppression de la route `stagiaire.progression.detailmodule`, qui pointait vers une methode `ProgressionDetailModule` inexistante.
- Suppression de l'ancienne vue `stagiaire.progression_detailmodule`, plus alimentee par un controleur.
- Suppression de composants/partials non references: `components.auth-session-status`, `components.oneduc.legal-sidebar`, `formateur.body_dashboard.footer`.

Fait dans le lot assets backend demo JS:

- Suppression des scripts publics de pages demo Vuexy dans `public/backend/assets/js`.
- Conservation des assets vendor, images, CSS et bibliotheques publiques encore utiles ou a auditer separement.
- Validation par recherche de references applicatives avant suppression.

Fait dans le lot payloads publics de demo:

- Suppression des JSON de fake data Vuexy dans `public/backend/assets/json` et `public/frontend/assets/json`.
- Suppression de l'audio de demonstration `Water_Lily.mp3` cote backend et frontend.
- Suppression de `demo.css` cote backend et frontend.
- Suppression des scripts publics de pages demo Vuexy dans `public/frontend/assets/js`.
- Conservation des assets vendor, images, logos et fichiers references par les vues applicatives.
- Validation par recherche de references applicatives avant suppression.

Alternative plus explicite:

1. Creer une serie de migrations baseline par domaine:
   - `create_core_user_group_tables`
   - `create_learning_catalog_tables`
   - `create_progress_tracking_tables`
   - `create_scorm_tables`
   - `create_live_tools_tables`
   - `create_trainer_path_tables`
   - `create_pilotage_tables`
2. Archiver les anciennes migrations dans un dossier hors execution ou les supprimer apres validation.

### Phase 3: nettoyage par lots

Ordre conseille:

1. Routes et debug code. Fait.
2. `trainer_path_activity_attempts` modelisation. Fait.
3. Contact table/model decision. Fait.
4. `learning_objectives` legacy. Fait.
5. Vues auth/admin orphelines. Fait.
6. Migrations intermediaires consolidees. Fait.
7. Audit frontend des vues/assets de template. En cours par lots.
8. Suppressions de tables seulement apres sauvegarde et validation metier.

## Ce qu'il ne faut pas supprimer maintenant

Ne pas supprimer uniquement parce que la table est vide:

- tables d'outils live: sondage, echelle, mur de questions, roue, live quiz, tableau blanc;
- tables SCORM/progression;
- tables pilotage si le module est conserve;
- tables systeme Laravel: `cache`, `cache_locks`, `jobs`, `failed_jobs`, `job_batches`, `sessions`, `notifications`, `password_reset_tokens`, `migrations`.

## Recommandation finale

Le nettoyage est faisable, mais le chemin propre n'est pas "DROP les tables vides". Etat courant:

1. `contacts`: decision prise, suppression controlee par migration.
2. `learning_objectives`: decision prise, suppression controlee par migration.
3. Migrations consolidees via un schema baseline teste.
4. Audit vues/assets de template effectue par lots.
5. Vues auth/admin orphelines supprimees.
6. Baseline SQL rafraichi sans les tables legacy deja traitees.
7. Route stagiaire cassee et partials non references supprimes.
8. Scripts publics backend de demo non references supprimes.
9. Payloads publics de demo non references supprimes.
10. Supprimer des tables uniquement apres sauvegarde et validation metier.
