Parcours formateur - emplacement des SCORM

Ce dossier est reserve aux SCORM du parcours formateur.

Pourquoi ce dossier existe :
- separer completement les contenus SCORM du parcours formateur des modules standards de la plateforme
- garder une arborescence lisible, stable et facile a raccorder ensuite dans les vues

Convention retenue :
- `module_X_nom_du_module`
- `chapitre_X_nom_du_chapitre`
- `lecon_X_Y_nom_de_la_lecon`

Exemple :
- `public/modules/parcours_formateur/module_2_organiser_ses_parcours/chapitre_1_preparer_lenvironnement_de_formation/lecon_1_1_les_composants_indispensables/`

Quand un SCORM sera pret :
1. dezipper le package dans le dossier de la lecon cible
2. conserver a la racine du dossier les fichiers d entree du package
3. ne pas melanger plusieurs SCORM dans le meme dossier de lecon

Contenu attendu le plus souvent :
- `index_lms.html`
- `imsmanifest.xml`
- `story_content/`
- `mobile/`
- `html5/`
- `lms/`

Important :
- ce dossier est distinct de `public/modules/00_Lecons`
- il est reserve uniquement au parcours formateur
- on pourra ensuite brancher chaque lecon vers son dossier SCORM sans toucher au systeme des modules standards
