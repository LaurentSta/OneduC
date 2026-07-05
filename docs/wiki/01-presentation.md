# 01 — Présentation du projet Oneduc

## Qu'est-ce qu'Oneduc ?

Oneduc est une plateforme web de formation accompagnée, développée en Laravel, qui réunit dans un même outil la diffusion de contenus pédagogiques, l'animation de groupes, le suivi de progression et des activités interactives en direct.

On peut la décrire comme un LMS, mais l'étiquette est un peu courte : Oneduc mise autant sur la **gestion de parcours** que sur la **présence pédagogique du formateur**. La plateforme sert à créer des modules, organiser des groupes, accompagner des apprenants, animer une séance et suivre ce qui se passe réellement pendant et après la formation.

Son origine est liée à l'inclusion numérique, mais elle ne s'y limite pas. Une association, un organisme de formation, une collectivité, une école, un tiers-lieu, une entreprise ou un formateur indépendant peuvent s'en servir pour proposer des apprentissages guidés sans imposer un environnement technique lourd.

Elle se distingue des LMS généralistes (Moodle, 360Learning, Canvas) sur quatre points :

1. **Accès apprenant simplifié** — un code court suffit pour rejoindre un espace de formation, sans compte email ni mot de passe initial dans les parcours les plus simples.
2. **Accompagnement humain visible** — le formateur référent, les groupes et les interactions de séance restent au centre de l'expérience.
3. **Création progressive** — un formateur peut partir d'un module catalogue, le dupliquer, le personnaliser, créer ses propres chapitres et leçons, puis affecter le tout à ses groupes.
4. **Animation intégrée** — quiz live, sondages, nuages de mots, tableaux blancs, minuteurs et autres outils de séance vivent dans le même environnement, sans jongler entre plusieurs services.

---

## Contexte et origine

Le projet est porté par l'**association Oneduc** (loi 1901), dont l'objet est de favoriser la formation accompagnée et l'accès aux compétences. Le terrain de départ est celui de l'inclusion numérique : des publics parfois peu à l'aise avec les outils, des formateurs qui doivent garder une relation humaine forte, et des séances où la simplicité d'accès compte autant que la richesse pédagogique.

Ce point de départ explique plusieurs choix : connexion par code pour les stagiaires, interfaces séparées par rôle, formateur référent clairement visible, outils d'animation directement disponibles, suivi des progressions sans exiger une autonomie numérique complète.

Le besoin couvert dépasse pourtant l'inclusion numérique : beaucoup de formations ont besoin d'un outil qui relie **contenu**, **groupe**, **présence du formateur**, **activité en séance** et **progression mesurable**. Oneduc est pensé comme une base réutilisable pour différents contextes pédagogiques.

Le modèle de gouvernance est associatif : l'association pilote le développement, détient les droits sur le logiciel et organise l'accès formateur autour d'un système d'adhésion intégré (`adhesion_status`, `adhesion_valid_until`).

---

## À qui peut servir Oneduc ?

- **Associations et médiation numérique** : ateliers d'inclusion, accompagnement de publics débutants, parcours d'autonomie numérique.
- **Organismes de formation** : modules structurés, groupes de stagiaires, suivi pédagogique, évaluations et outils d'animation.
- **Collectivités et tiers-lieux** : formations locales, ateliers citoyens, accompagnement administratif ou numérique.
- **Écoles, centres de formation et dispositifs hybrides** : complément entre présentiel, activités en ligne et suivi individuel.
- **Formateurs indépendants** : création de modules, gestion de petits groupes, partage de ressources et animation en direct.
- **Structures internes d'entreprise** : parcours d'intégration, montée en compétences, sessions courtes, quiz et suivi de participation.

Elle est surtout pertinente quand la formation n'est pas un simple catalogue de vidéos, mais un accompagnement organisé autour de personnes, de groupes, de contenus et d'interactions.

---

## Bilan fonctionnel

### Gestion des utilisateurs et des espaces

Oneduc distingue quatre profils :

- **Administrateur** : pilote le catalogue, les utilisateurs, les catégories, les référentiels, les évaluations, les badges et certains outils.
- **Formateur** : crée ou adapte des modules, gère ses groupes, ajoute des stagiaires, suit les progressions et anime des séances.
- **Stagiaire** : accède à ses modules, suit ses leçons, répond aux quiz, participe aux outils live et consulte ses résultats.
- **Observateur** : consulte les groupes et les progressions dans un périmètre limité, sans rôle de création ou d'animation.

Cette séparation évite d'exposer la même complexité à tout le monde : vision système pour l'administrateur, espace de conception et d'animation pour le formateur, espace centré sur son parcours pour le stagiaire, lecture contrôlée des données pour l'observateur.

### Contenu pédagogique

Le contenu suit une hiérarchie simple :

```text
Module
└── Chapitre / Section
    └── Leçon
```

Une leçon peut mélanger plusieurs types de contenu : texte riche, images, vidéos internes ou externes, ressources téléchargeables, quiz natifs, contenus SCORM, slides, ou des blocs SCORM intégrés dans une leçon mixte.

Oneduc ne se limite donc pas à déposer des documents : la plateforme permet de construire des supports structurés, de les relier à des objectifs, de les présenter dans un ordre défini, puis de suivre leur consultation et leur réussite.

### Création de modules par les formateurs

Le formateur dispose d'un builder dédié pour créer ses propres modules. Il peut créer un module personnel, partir d'une structure d'exemple, créer/renommer/déplacer/réordonner chapitres et leçons, dupliquer une leçon, transformer une leçon vide en chapitre, éditer une leçon en blocs de contenu, téléverser des images, vidéos ou packages SCORM, dupliquer un module catalogue pour l'adapter à son contexte, puis affecter son module à un ou plusieurs groupes.

Oneduc n'impose donc pas seulement un catalogue descendant administré par une équipe centrale : il donne aussi au formateur la capacité de produire, adapter et contextualiser ses propres contenus.

### Groupes, parcours et co-animation

Les groupes sont au cœur de l'organisation pédagogique : ils relient des stagiaires, un formateur principal, d'éventuels co-formateurs, des observateurs et des modules.

Le formateur peut créer un groupe, y ajouter des stagiaires, inviter ou rattacher des co-formateurs, affecter des modules, puis adapter l'ordre ou la visibilité de certaines leçons pour ce groupe précis. Cela rend possible une formation différenciée : le même module de base peut servir différemment selon le public, le rythme, le niveau ou les contraintes de séance.

Le wiki distingue par ailleurs deux notions de parcours :

- le **parcours formateur Oneduc**, qui guide les formateurs dans la prise en main de la plateforme ;
- les **parcours créés par le formateur**, qui assemblent des modules ou ressources pour ses propres groupes.

### Animation pédagogique en direct

Oneduc intègre des outils d'animation pour éviter de disperser la séance entre plusieurs services externes : quiz live, nuage de mots, sondage, échelle de positionnement, mur de questions, roue aléatoire, tableau blanc collaboratif, minuteur, pages collaboratives HedgeDoc.

Ces outils couvrent les besoins habituels d'une séance : recueillir une réponse rapide, faire émerger des idées, vérifier une compréhension, rythmer un atelier, ouvrir une activité collective, rendre visibles les questions d'un groupe.

### Suivi, progression et tableaux de bord

La plateforme agrège plusieurs sources de progression : validation manuelle d'une leçon, scores et statuts SCORM, tentatives de quiz natifs, réponses aux questions, lecture vidéo, temps de connexion, activité récente.

Le formateur dispose d'un tableau de bord pour suivre ses groupes, repérer les stagiaires actifs, inactifs ou à risque, et consulter des vues par groupe, par stagiaire ou par module. Le stagiaire voit sa progression et ses résultats dans un espace simplifié. L'administrateur dispose d'indicateurs système et d'outils de pilotage.

### Sécurité, gouvernance et données

Oneduc a déjà plusieurs garde-fous en place : authentification Laravel classique, rôles séparés, middleware d'adhésion formateur, changement de mot de passe forcé à la première connexion stagiaire, journalisation de certaines actions administrateur, suppression de compte avec nettoyage des données liées, politique documentaire AGPL/CLA/sécurité/contribution.

Le wiki signale aussi ce qui doit être sécurisé avant une publication publique plus large : throttling de la connexion par code, vérification centralisée d'accès aux modules et leçons, garde SCORM, et correction de quelques routes ou contrats techniques.

---

## Ce que la plateforme propose aujourd'hui

Au 5 juillet 2026, Oneduc a déjà une base fonctionnelle solide : un espace public (présentation, catalogue, pages légales, contact, connexions), un espace administrateur (utilisateurs, modules catalogue, catégories, groupes, évaluations, badges, référentiels, retours, pilotage), un espace formateur (groupes, stagiaires, modules, parcours, outils numériques, progressions), un espace stagiaire (modules, quiz, résultats, outils, activités), un espace observateur (groupes et progressions autorisés), un système de modules compatible SCORM/quiz natifs/vidéos/ressources/slides/blocs éditoriaux, un builder formateur avec plan continu et édition de leçons, des outils d'animation live, des tableaux de bord, et la base documentaire nécessaire à une publication open source (licence AGPL, contribution, sécurité, CLA, wiki).

Ce n'est plus une maquette : c'est une application Laravel complète, avec 411 routes, plus de 290 vues Blade, une soixantaine de modèles, des tests automatisés, et une architecture qui commence à se structurer en domaines internes.

---

## Ce que la plateforme proposera bientôt

La roadmap vise à transformer cette base en plateforme de formation plus complète, plus sûre et plus exploitable à grande échelle. Les évolutions prioritaires :

- **Sécurisation des accès** : throttling de la connexion par code, policies Laravel pour centraliser l'accès aux modules/groupes/leçons, vérification plus stricte des écritures SCORM.
- **Correction des points publics bloquants** : page `/inscription`, contrat d'upload image du builder, route de feedback leçon, suite de tests entièrement au vert.
- **Exports de progression** : CSV/PDF pour les formateurs, groupes ou fiches individuelles.
- **Certificats** : génération d'attestations en fin de module, en s'appuyant sur les champs déjà présents.
- **Prérequis et accès conditionnels** : débloquer certains modules ou leçons selon une progression, un score ou un parcours.
- **SCORM enrichi** : meilleure collecte des interactions SCORM pour les leçons, afin que les tableaux de bord reflètent mieux les réponses et scores détaillés.
- **Multi-groupe et multi-organisation** : meilleure prise en compte des stagiaires présents dans plusieurs groupes, puis évolution vers des organismes ou espaces distincts.
- **Analytics pédagogiques avancées** : indicateurs de risque, activité, réussite, temps d'apprentissage et qualité des parcours plus lisibles.
- **Nouveaux blocs de leçon** : carrousel/processus, flashcards, tri, bouton « Continuer » et autres blocs interactifs inspirés d'outils auteurs modernes.
- **Exploitation professionnelle** : conformité RGPD documentée, sauvegardes, supervision, exports institutionnels, rôles de coordination ou financeurs.

L'idée n'est pas seulement d'ajouter des fonctionnalités, mais de consolider Oneduc comme un environnement complet de formation accompagnée — simple pour l'apprenant, plus puissant pour les formateurs, administrateurs et organisations.

---

## Stack technique

| Composant | Technologie |
|-----------|-------------|
| Backend | Laravel 11 / PHP 8.2+ |
| Frontend | Blade, Tailwind CSS v4, Vite, Alpine.js |
| Écrans riches | React 19, Excalidraw, Tiptap, XYFlow |
| Base de données | MySQL / MariaDB |
| Standard e-learning | SCORM 1.2 et 2004 (API.js natif) |
| Tests | Pest / PHPUnit |
| Formatage PHP | Laravel Pint |
| Interactivité temps réel | Polling AJAX (pas de WebSockets) |

---

## Volume du projet (5 juillet 2026)

| Élément | Volume |
|---------|--------|
| Contrôleurs PHP | 85 fichiers |
| Modèles Eloquent | 61 modèles |
| Services métier | 6 fichiers |
| Domaines internes | 26 fichiers dans `app/Domains` |
| Vues Blade | 291 fichiers |
| Tables dans le schéma MySQL baseline | 72 tables |
| Migrations versionnées | 5 migrations post-baseline |
| Routes déclarées | 411 routes |
| Fichiers de test | 43 fichiers PHP |
| Suite de tests | Audit du 5 juillet : 103 tests passés, 1 échec, 505 assertions |

---

## Niveau de maturité (analyse juillet 2026)

| Axe | État | Commentaire |
|-----|------|-------------|
| Maturité technique | En consolidation avancée | Build Vite réussi ; suite de tests presque verte avec 1 échec sur le contrat d'upload image du builder ; plusieurs contrôleurs restent volumineux |
| Maturité pédagogique | Solide pour un pilote | Modules, quiz, SCORM, parcours, outils live et suivi formateur sont opérationnels |
| Expérience utilisateur | Bonne base terrain | Accès stagiaire simplifié, interfaces par rôle, convention vocabulaire définie dans le glossaire et à appliquer dans les menus |
| Potentiel de publication | Proche, sous réserve de corrections | Licence, README, templates GitHub et wiki présents ; `/inscription`, sécurité d'accès, historique Git et quelques risques doivent être revus |
| Capacité LMS globale | Réelle mais incomplète | Il manque certificats, exports, prérequis bloquants et contrôle d'accès centralisé par policy |

La plateforme est utilisable dès aujourd'hui en **pilote contrôlé** (10 à 50 stagiaires, 3 à 5 formateurs, contexte associatif). Avant une publication publique large, il faut surtout traiter les points listés dans [Sécurité & RGPD](10-securite-rgpd.md), [Checklist GitHub](13-publication-github.md) et [Audit site du 5 juillet 2026](14-audit-site-2026-07-05.md).

---

## Liens utiles

- [Architecture technique](03-architecture.md)
- [Profils utilisateurs](04-profils-utilisateurs.md)
- [Roadmap](11-roadmap.md)
- [Retour au wiki](README.md)
