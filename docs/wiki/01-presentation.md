# 01 — Présentation du projet Oneduc

## Qu'est-ce qu'Oneduc ?

Oneduc est une plateforme web de formation accompagnée. Elle réunit dans un même outil la diffusion de contenus pédagogiques, l'animation de groupes en séance et le suivi de progression.

On peut la décrire comme un LMS, mais l'étiquette est un peu courte. La plupart des LMS sont construits autour du contenu ; Oneduc est construit autour du groupe et du formateur qui l'accompagne. Le contenu est là, structuré et suivi, mais il n'est pas le centre de gravité.

Le nom est un acronyme : **O**utils **N**umériques **ÉDUC**atifs.

Ce qui la distingue de Moodle, Canvas ou 360Learning tient en quatre points :

1. Un stagiaire rejoint sa formation avec un code court. Pas de compte email, pas de mot de passe à retenir dans les parcours simples.
2. Le formateur référent reste visible partout. L'apprenant sait qui l'accompagne.
3. Un formateur peut partir d'un module du catalogue, le dupliquer, le modifier, puis l'affecter à ses groupes. Il n'est pas obligé de tout créer de zéro, ni de se contenter du catalogue.
4. Les outils d'animation de séance (quiz live, sondages, nuage de mots, tableau blanc...) sont développés en natif, pour éviter de jongler avec Wooclap ou Klaxoon à côté. Au 5 juillet 2026, ils ne sont pas encore activés en environnement de production (voir [Outils d'animation](07-outils-animation.md)).

---

## D'où vient le projet

Oneduc est porté par l'**association Oneduc** (loi 1901). Le terrain de départ est l'inclusion numérique : des publics peu à l'aise avec les outils, et des formateurs pour qui la relation humaine passe avant la richesse fonctionnelle.

Ce contexte explique des choix qui, ailleurs, sembleraient des limitations. La connexion par code existe parce que demander une adresse email à quelqu'un qui n'en a pas encore est un mur. Les interfaces sont séparées par rôle parce qu'un stagiaire débutant n'a pas à voir la complexité d'un back-office.

Le besoin dépasse pourtant l'inclusion numérique. Beaucoup de formations ont besoin de relier un contenu, un groupe et un formateur dans le même espace, avec une progression mesurable. C'est ce qu'Oneduc cherche à faire, quel que soit le public.

L'association pilote le développement et détient les droits sur le logiciel. L'accès formateur passe par un système d'adhésion intégré.

---

## À qui peut servir Oneduc ?

Associations de médiation numérique, organismes de formation, collectivités, tiers-lieux, écoles, formateurs indépendants, services formation en entreprise. La liste est large parce que le besoin l'est.

Le vrai critère n'est pas le type de structure. Oneduc est pertinent quand la formation est un accompagnement de personnes, pas un catalogue de vidéos en libre-service. Si vos apprenants avancent seuls sans jamais croiser un formateur, d'autres outils feront aussi bien.

---

## Ce que la plateforme permet de faire

### Quatre profils, quatre espaces

- L'**administrateur** pilote le catalogue, les utilisateurs, les référentiels et les indicateurs système.
- Le **formateur** crée ou adapte des modules, gère ses groupes, anime des séances et suit les progressions.
- Le **stagiaire** accède à ses modules, suit ses leçons, répond aux quiz et consulte ses résultats.
- L'**observateur** consulte les progressions d'un périmètre défini, sans pouvoir créer ni animer.

Chacun ne voit que ce qui le concerne. Un stagiaire débutant n'est jamais confronté à un écran de gestion.

### Le contenu pédagogique

La hiérarchie est simple :

```text
Module
└── Chapitre / Section
    └── Leçon
```

Une leçon peut mélanger du texte, des images, des vidéos, des ressources à télécharger, des quiz natifs ou des contenus SCORM. On peut donc construire de vrais supports structurés, pas seulement déposer des PDF.

### La création de modules par les formateurs

Le formateur dispose d'un builder pour créer ses propres modules : créer des chapitres et des leçons, les réordonner, dupliquer une leçon, éditer le contenu en blocs, téléverser des médias ou des packages SCORM.

Il peut aussi dupliquer un module du catalogue pour l'adapter à son public. C'est souvent le chemin le plus rapide : partir d'une base qui existe, puis modifier ce qui doit l'être.

### Les groupes

Le groupe est l'unité centrale d'Oneduc. Il relie des stagiaires, un formateur principal, d'éventuels co-formateurs, des observateurs et des modules.

Le formateur peut adapter l'ordre ou la visibilité de certaines leçons pour un groupe précis. Le même module de base peut donc servir différemment selon le public ou le rythme de la session.

Le wiki distingue deux notions de parcours : le parcours formateur Oneduc (la prise en main de la plateforme par les formateurs) et les parcours créés par le formateur pour ses propres groupes.

### L'animation en séance

Quiz live, nuage de mots, sondage, échelle de positionnement, mur de questions, roue aléatoire, tableau blanc collaboratif, minuteur, pages collaboratives HedgeDoc. Prévus pour vivre dans le même environnement que les modules et les groupes, avec l'objectif qu'une séance n'ait pas besoin de trois onglets et deux comptes externes pour fonctionner.

**Statut au 5 juillet 2026** : ces outils sont développés côté code mais pas encore activés en environnement de production (détail dans [Outils d'animation](07-outils-animation.md)).

### Le suivi

La progression est agrégée depuis plusieurs sources : validation manuelle des leçons, scores SCORM, tentatives de quiz, lecture vidéo, temps de connexion.

Le formateur voit ses groupes, repère les stagiaires actifs, inactifs ou à risque, et peut descendre au niveau d'un stagiaire ou d'un module. Le stagiaire voit sa propre progression dans un espace simplifié.

---

## Où en est le projet aujourd'hui

Au 5 juillet 2026, Oneduc est une application Laravel complète : 411 routes, plus de 290 vues, une soixantaine de modèles, des tests automatisés. Espaces public, administrateur, formateur, stagiaire et observateur sont opérationnels, ainsi que le builder et les tableaux de bord. Les outils d'animation sont développés mais pas encore activés en production.

La plateforme est utilisable dès maintenant en pilote contrôlé : 10 à 50 stagiaires, 3 à 5 formateurs, dans un contexte associatif. Les correctifs de sécurité S3 à S9 ont été appliqués le 5 juillet 2026 (throttling, contrôle d'appartenance SCORM, `Module::isVisibleTo()`, etc.) ; un gap reste identifié sur deux contrôleurs qui ne vérifient pas encore l'appartenance groupe (détail dans [Sécurité & RGPD](https://github.com/LaurentSta/Oneduc/wiki/10-securite-rgpd) et la [Checklist GitHub](https://github.com/LaurentSta/Oneduc/wiki/13-publication-github)).

## Et ensuite

Les évolutions prioritaires de la roadmap :

- Policies Laravel centralisées pour les autorisations (Phase 2), et fermeture du gap `StagiaireModuleDetail`/`LectureController` identifié lors du correctif `isVisibleTo()`.
- Exports de progression en CSV et PDF pour les formateurs.
- Certificats de fin de module.
- Prérequis : débloquer un module ou une leçon selon une progression ou un score.
- Meilleure collecte des interactions SCORM, pour des tableaux de bord plus fins.
- Multi-groupe, puis multi-organisation.
- Nouveaux blocs de leçon : flashcards, tri, carrousel.
- Le nécessaire pour une exploitation professionnelle : conformité RGPD documentée, sauvegardes, supervision, exports institutionnels.

---

## Partie technique

Cette section s'adresse aux développeurs et administrateurs système. Les formateurs peuvent s'arrêter ici.

### Stack

| Composant | Technologie |
|-----------|-------------|
| Backend | Laravel 13 / PHP 8.4+ |
| Frontend | Blade, Tailwind CSS v4, Vite, Alpine.js |
| Écrans riches | React 19, Excalidraw, Tiptap, XYFlow |
| Base de données | MySQL / MariaDB |
| Standard e-learning | SCORM 1.2 et 2004 (API.js natif) |
| Tests | Pest / PHPUnit |
| Formatage PHP | Laravel Pint |
| Interactivité temps réel | Polling AJAX (pas de WebSockets) |

### Volume (5 juillet 2026)

| Élément | Volume |
|---------|--------|
| Contrôleurs PHP | 85 fichiers |
| Modèles Eloquent | 61 modèles |
| Services métier | 6 fichiers |
| Domaines internes | 26 fichiers dans `app/Domains` |
| Vues Blade | 291 fichiers |
| Tables (schéma MySQL baseline) | 72 tables |
| Migrations post-baseline | 5 migrations |
| Routes déclarées | 411 routes |
| Fichiers de test | 43 fichiers PHP |
| Suite de tests | 124 tests passés, 0 échec, 580 assertions (correctifs sécurité du 5 juillet) |

### Maturité

| Axe | État | Commentaire |
|-----|------|-------------|
| Technique | En consolidation | Build Vite OK ; suite de tests verte (124 tests) ; plusieurs contrôleurs restent volumineux |
| Pédagogique | Solide pour un pilote | Modules, quiz, SCORM, parcours et suivi formateur opérationnels ; outils live développés mais pas encore activés en production |
| Expérience utilisateur | Bonne base terrain | Accès stagiaire simplifié ; convention de vocabulaire définie dans le glossaire, à appliquer dans les menus |
| Publication | Proche | Licence, README, templates GitHub et wiki présents ; checklist sécurité S3-S9 résolue, historique Git et gap `isVisibleTo()` à revoir avant |
| Capacité LMS globale | Réelle mais incomplète | Manquent : certificats, exports, prérequis bloquants, policies d'accès centralisées |

---

## Liens utiles

- [Architecture technique](https://github.com/LaurentSta/Oneduc/wiki/03-architecture)
- [Profils utilisateurs](https://github.com/LaurentSta/Oneduc/wiki/04-profils-utilisateurs)
- [Roadmap](https://github.com/LaurentSta/Oneduc/wiki/11-roadmap)
- [Retour au wiki](https://github.com/LaurentSta/Oneduc/wiki)
