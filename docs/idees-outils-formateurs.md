# Idees d'outils formateurs a developper

Derniere mise a jour : 2026-07-04

Cette note regroupe des idees d'outils numeriques pour aider les formateurs en presentiel, en distanciel ou en format hybride. Chaque idee est volontairement formulee comme une piste produit, avec un ID stable et le statut `A developper`.

## Priorites proposees

| ID | Statut | Proposition | Usage principal | Priorite |
| --- | --- | --- | --- | --- |
| OF-001 | A developper | Cockpit de seance formateur | Piloter une session live depuis un seul ecran | Haute |
| OF-002 | A developper | Ticket de sortie | Mesurer la comprehension en fin de sequence | Haute |
| OF-003 | A developper | Mur de questions anonyme | Recuperer et prioriser les questions des stagiaires | Haute |
| OF-004 | Partiellement developpe | Emargement par QR code | Suivre presence et participation | Moyenne |
| OF-005 | A developper | Generateur d'activites IA | Produire rapidement quiz, cas pratiques et animations | Moyenne |
| OF-006 | A developper | Groupes intelligents | Creer binomes, sous-groupes et roles automatiquement | Moyenne |
| OF-007 | A developper | Debrief / retrospective de session | Capitaliser les retours et points de blocage | Moyenne |
| OF-008 | A developper | Analytics pedagogiques | Detecter les contenus difficiles et les decrochages | Moyenne |
| OF-009 | A developper | Import de cours par IA (PowerPoint, PDF) | Transformer un support existant en lecon structuree | Moyenne |
| OF-010 | A developper | Clic sur zones d'image | Creer des activites interactives a partir d'une image | Moyenne |
| OF-011 | A developper | File d'attente de parole | Organiser la prise de parole en groupe ou en visio | Moyenne |
| OF-012 | A developper | Vrai / Faux express | Verifier rapidement la comprehension en quelques secondes | Moyenne |
| OF-013 | A developper | Roue aleatoire (amelioree) | Etendre l'outil existant avec ponderation et historique | Moyenne |

## OF-001 - Cockpit de seance formateur

Statut : `A developper`

Un ecran de pilotage pour le formateur pendant une seance. Il regroupe le timer, les etapes de la seance, les participants connectes, les QR codes, et les raccourcis pour lancer un quiz, un nuage de mots, une roue aleatoire, un tableau blanc ou une pause.

Interet :
- reduire la charge mentale du formateur pendant le live ;
- rendre les outils existants plus faciles a enchainer ;
- fonctionner aussi bien en salle qu'en visioconference.

Inspiration : FocusTide pour la logique timer/taches, PollN pour la presentation live avec QR code.

## OF-002 - Ticket de sortie

Statut : `A developper`

Un mini-formulaire de fin de sequence ou de fin de journee. Les stagiaires indiquent ce qu'ils ont compris, ce qui reste flou, leur niveau de confiance, et une question a reprendre.

Interet :
- donner au formateur un signal rapide sur la comprehension reelle ;
- alimenter le prochain demarrage de seance ;
- historiser les retours par groupe, module et parcours.

Inspiration : PollN et LimeSurvey pour les sondages, questions ouvertes et statistiques.

## OF-003 - Mur de questions anonyme

Statut : `A developper`

Un espace ou les stagiaires peuvent poser des questions pendant la formation, avec ou sans anonymat. Les autres participants peuvent voter pour les questions importantes. Le formateur peut marquer une question comme traitee, la transformer en activite, ou la garder pour plus tard.

Interet :
- aider les apprenants qui n'osent pas parler ;
- eviter de perdre les questions en distanciel ;
- faire ressortir les blocages collectifs.

Inspiration : outils de live polling comme PollN, et pratiques de retrospective type Parabol.

## OF-004 - Emargement par QR code

Statut : `Partiellement developpe`

La partie feuille de presence est developpee : par seance datee (matin/apres-midi/journee/soiree), avec signature graphique de chaque stagiaire, correction manuelle formateur, et export PDF pour audit Qualiopi/OPCO. Voir [docs/wiki/16-emargement.md](wiki/16-emargement.md) pour l'usage et le detail technique.

Reste a faire : le volet QR code lui-meme (le formateur affiche un QR de session, les stagiaires scannent pour rejoindre directement l'ecran de signature au lieu de naviguer depuis leur tableau de bord). Non bloquant pour l'usage actuel, simple gain d'ergonomie en presentiel.

Inspiration : Campus QR pour le check-in par QR code et la validation rapide des presences.

## OF-005 - Generateur d'activites IA

Statut : `A developper`

Depuis une lecon, un module ou un texte colle, le formateur demande a Oneduc de proposer des activites : quiz, cas pratique, debat, nuage de mots, flashcards, consigne de groupe, synthese ou plan d'animation.

Interet :
- accelerer la preparation des seances ;
- diversifier les formats pedagogiques ;
- transformer le contenu existant en activites interactives.

Inspiration : OpenMAIC pour la generation de cours, quiz, simulations et supports interactifs par IA.

## OF-006 - Groupes intelligents

Statut : `A developper`

Un outil pour creer automatiquement des binomes, sous-groupes et roles : rapporteur, secretaire, timekeeper, observateur. Les regles peuvent etre aleatoires ou equilibrees selon le niveau, la progression, la presence ou les contraintes du formateur.

Interet :
- gagner du temps en atelier ;
- eviter les groupes toujours identiques ;
- rendre les activites presenciales et distancielles plus fluides.

Inspiration : picker pour la selection aleatoire simple, et la roue aleatoire deja presente dans Oneduc.

## OF-007 - Debrief / retrospective de session

Statut : `A developper`

Un tableau collaboratif structure en colonnes : "Je retiens", "Encore flou", "A tester", "Besoin d'aide", "Suggestion". Le formateur peut regrouper les retours, repondre, exporter ou transformer certains elements en prochaines actions.

Interet :
- structurer les retours qualitatifs ;
- aider les groupes a verbaliser les apprentissages ;
- preparer les ameliorations de la prochaine session.

Inspiration : Parabol pour les retrospectives structurees et collaboratives.

## OF-008 - Analytics pedagogiques

Statut : `A developper`

Un tableau d'analyse pour detecter les contenus a ameliorer : lecons peu consultees, quiz souvent rates, progression lente, stagiaires inactifs, questions recurrentes, ecarts entre groupes.

Interet :
- aider le formateur a ajuster son parcours ;
- reperer les decrochages avant la fin ;
- prioriser les contenus a reprendre.

Inspiration : EdOptimize pour les tableaux de bord d'usage, de rythme et de performance pedagogique.

## OF-009 - Import de cours par IA (PowerPoint, PDF)

Statut : `A developper`

Le formateur importe un support existant (PowerPoint, PDF) dans un module. Oneduc lit le contenu (texte et diapositives) et propose automatiquement une lecon structuree en blocs (texte, images, listes) directement dans le module, que le formateur relit et valide avant publication.

Interet :
- reutiliser des supports de formation deja crees plutot que de tout ressaisir ;
- accelerer la creation de nouveaux modules a partir de contenus existants ;
- garder la main sur le contenu final grace a une relecture obligatoire avant publication.

Inspiration : OpenMAIC pour la generation de cours par IA.

## OF-010 - Clic sur zones d'image

Statut : `A developper`

Le formateur importe une image (schema, interface, plan, photo) et delimite des zones cliquables. Les stagiaires cliquent sur la zone correspondant a la bonne reponse, ou explorent l'image zone par zone pour decouvrir une legende, une definition ou une etape.

Interet :
- transformer une image statique en activite interactive ;
- utile pour identifier des elements sur un schema, une interface ou un plan ;
- fonctionne aussi bien en quiz (bonne/mauvaise zone) qu'en exploration libre.

Inspiration : H5P pour ses formats "Image Hotspots" et "Find the Hotspot".

## OF-011 - File d'attente de parole

Statut : `A developper`

Un outil live ou les stagiaires demandent la parole en un clic et sont places dans une file visible par le formateur. Le formateur appelle la personne suivante, passe son tour ou marque une intervention comme terminee.

Interet :
- organiser la prise de parole sans coupures ni participants qui monopolisent ;
- particulierement utile en visioconference ou en grand groupe ;
- suit le meme schema technique que les outils live existants (session + reponses, mise a jour par sondage).

## OF-012 - Vrai / Faux express

Statut : `A developper`

Le formateur affiche une affirmation, les stagiaires repondent instantanement Vrai ou Faux, et le resultat agrege s'affiche en direct. Pense pour des verifications rapides de comprehension, sans creer un quiz complet.

Interet :
- verifier la comprehension en quelques secondes, plusieurs fois par seance ;
- dynamiser une sequence (icebreaker, transition, energizer) ;
- plus leger a preparer qu'un quiz avec plusieurs questions.

Inspiration : ClassQuiz pour le format questions rapides en direct.

## OF-013 - Roue aleatoire (amelioree)

Statut : `A developper`

Etendre l'outil Roue aleatoire deja present dans Oneduc : ponderation des elements, exclusion automatique d'un element apres tirage, historique des tirages de la session, et plus de personnalisation visuelle (couleurs, images par segment).

Interet :
- rendre les tirages plus equitables sur une session complete (ex. distribuer la parole sans repeter toujours les memes stagiaires) ;
- eviter de reconfigurer la roue a chaque tirage ;
- capitaliser sur un outil deja utilise par les formateurs.

Inspiration : Picker, deja cite pour les groupes intelligents (OF-006), sur la logique de ponderation et d'exclusion apres tirage.

## Sources d'inspiration GitHub

- Excalidraw : https://github.com/excalidraw/excalidraw
- H5P : https://github.com/h5p
- PollN : https://github.com/bgtti/polln
- ClassQuiz : https://github.com/mawoka-myblock/ClassQuiz
- jovVix : https://github.com/Improwised/jovVix
- Campus QR : https://github.com/studoverse/campus-qr
- OpenMAIC : https://github.com/THU-MAIC/OpenMAIC
- Parabol : https://github.com/ParabolInc/parabol
- EdOptimize : https://github.com/PlaypowerLabs/EdOptimize
- LimeSurvey : https://github.com/LimeSurvey/LimeSurvey
- FocusTide : https://github.com/Hanziness/FocusTide
- Picker : https://github.com/koddsson/picker
