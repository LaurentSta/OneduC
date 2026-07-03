# 01 — Présentation du projet Oneduc

## Qu'est-ce qu'Oneduc ?

Oneduc est une plateforme de gestion de l'apprentissage (LMS) développée en Laravel, conçue spécifiquement pour accompagner les publics éloignés du numérique. Elle s'adresse aux formateurs qui animent des sessions d'inclusion numérique dans les associations, ateliers numériques, collectivités et organismes de formation.

Le positionnement d'Oneduc se distingue des LMS généralistes (Moodle, 360Learning, Canvas) sur deux axes :

1. **Simplification maximale de l'accès apprenant** — un code court suffit pour rejoindre une formation, sans création de compte email ni mot de passe initial
2. **Accompagnement visible du formateur** — le formateur référent est affiché en permanence côté stagiaire, les outils d'animation live sont intégrés nativement sans plugin externe

---

## Contexte et origine

Le projet est porté par l'**association Oneduc** (loi 1901), dont l'objet est de favoriser l'inclusion numérique par la formation accompagnée. La plateforme est développée pour les formateurs et stagiaires du réseau Oneduc.fr.

Il répond à un besoin terrain identifié : les LMS généralistes créent trop de friction pour des publics peu à l'aise avec le numérique. L'association Oneduc pilote le développement et détient les droits sur le logiciel.

Le modèle de gouvernance est associatif : les formateurs accèdent à la plateforme via un système d'adhésion intégré directement dans le code (`adhesion_status`, `adhesion_valid_until`).

---

## Ce que la plateforme fait

| Domaine | Capacités |
|---------|-----------|
| **Gestion des utilisateurs** | 4 profils distincts (Admin, Formateur, Stagiaire, Observateur) avec espaces séparés |
| **Contenu pédagogique** | Modules → Sections → Leçons (SCORM, slides, quiz natifs, blocs texte/image/liste/citation, vidéo, ressources) |
| **Création formateur** | Builder de modules personnels avec plan continu, duplication de modules catalogue, édition de leçons en blocs, images et affectation aux groupes |
| **Groupes de formation** | Création, co-formateurs, observateurs, personnalisation des leçons par groupe |
| **Suivi de progression** | Multi-sources (quiz, SCORM, vidéo, temps de connexion) |
| **Animation live** | Quiz live, Nuage de mots, Sondage, Échelle, Mur de questions, Roue aléatoire, Tableau blanc, Minuteur, pages collaboratives HedgeDoc |
| **Tableaux de bord** | Analytique formateur avec identification des apprenants à risque |
| **Accès simplifié** | Connexion par code court pour les stagiaires |

---

## Ce que la plateforme ne fait pas encore

- Génération de certificats (le champ `certificat` existe en base mais le flux n'est pas implémenté)
- Export de données de progression (CSV, PDF)
- Multi-organisation (un seul tenant)
- Prérequis bloquants entre modules
- Interactions SCORM enregistrées au niveau des leçons (uniquement pour les évaluations)
- Throttling sur la connexion stagiaire par code (`POST /stagiaire/connexion-code`)

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

## Volume du projet (3 juillet 2026)

| Élément | Volume |
|---------|--------|
| Contrôleurs PHP | 86 fichiers |
| Modèles Eloquent | 59 modèles |
| Services métier | 6 fichiers |
| Vues Blade | 287 fichiers |
| Tables dans le schéma MySQL baseline | 72 tables |
| Migrations versionnées | 3 migrations post-baseline |
| Routes déclarées | 405 routes |
| Fichiers de test | 43 fichiers PHP |
| Suite de tests | Dernière suite complète documentée : 103 tests passés, 523 assertions |

---

## Niveau de maturité (analyse juillet 2026)

| Axe | État | Commentaire |
|-----|------|-------------|
| Maturité technique | En consolidation avancée | Dernière suite complète documentée au vert et build Vite réussi ; plusieurs contrôleurs restent volumineux |
| Maturité pédagogique | Solide pour un pilote | Modules, quiz, SCORM, parcours, outils live et suivi formateur sont opérationnels |
| Expérience utilisateur | Bonne base terrain | Accès stagiaire simplifié, interfaces par rôle, convention vocabulaire définie dans le glossaire et à appliquer dans les menus |
| Potentiel de publication | Proche, sous réserve de vérifications | Licence, README, templates GitHub et wiki présents ; historique Git et quelques risques doivent être revus |
| Capacité LMS globale | Réelle mais incomplète | Il manque certificats, exports, prérequis bloquants et contrôle d'accès centralisé par policy |

La plateforme est utilisable dès aujourd'hui en **pilote contrôlé** (10 à 50 stagiaires, 3 à 5 formateurs, contexte associatif). Avant une publication publique large, il faut surtout corriger les points listés dans [Sécurité & RGPD](10-securite-rgpd.md) et [Checklist GitHub](13-publication-github.md).

---

## Liens utiles

- [Architecture technique](03-architecture.md)
- [Profils utilisateurs](04-profils-utilisateurs.md)
- [Roadmap](11-roadmap.md)
- [Retour au wiki](README.md)
