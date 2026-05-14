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
| **Contenu pédagogique** | Modules → Sections → Leçons (SCORM, slides, quiz natifs, vidéo, ressources) |
| **Groupes de formation** | Création, co-formateurs, personnalisation des leçons par groupe |
| **Suivi de progression** | Multi-sources (quiz, SCORM, vidéo, temps de connexion) |
| **Animation live** | 9 outils intégrés : Quiz live, Nuage de mots, Sondage, Échelle, Mur de questions, Roue aléatoire, Tableau blanc, Minuteur |
| **Tableaux de bord** | Analytique formateur avec identification des apprenants à risque |
| **Accès simplifié** | Connexion par code court pour les stagiaires |

---

## Ce que la plateforme ne fait pas encore

- Génération de certificats (le champ `certificat` existe en base mais le flux n'est pas implémenté)
- Export de données de progression (CSV, PDF)
- Multi-organisation (un seul tenant)
- Prérequis bloquants entre modules
- Interactions SCORM enregistrées au niveau des leçons (uniquement pour les évaluations)

---

## Stack technique

| Composant | Technologie |
|-----------|-------------|
| Backend | Laravel 11 / PHP 8.2+ |
| Frontend | Tailwind CSS v4 + Vite + Alpine.js |
| Base de données | MySQL / MariaDB |
| Standard e-learning | SCORM 1.2 et 2004 (API.js natif) |
| Tests | Pest / PHPUnit |
| Formatage PHP | Laravel Pint |
| Interactivité temps réel | Polling AJAX (pas de WebSockets) |

---

## Volume du projet (mai 2026)

| Élément | Volume |
|---------|--------|
| Contrôleurs PHP | 85 fichiers |
| Modèles Eloquent | 58 modèles |
| Vues Blade | 468 fichiers |
| Migrations | 102 fichiers |
| Routes déclarées | ~376 routes |
| Fichiers de test | 37 fichiers Pest |

---

## Niveau de maturité (audit mai 2026)

| Axe | Note | Commentaire |
|-----|------|-------------|
| Maturité technique | 11/20 | Fondations solides, bugs structurels à corriger, tests à stabiliser |
| Maturité pédagogique | 14/20 | Vision juste, outils live remarquables, preuves et prérequis manquants |
| Expérience utilisateur | 13/20 | Interface stagiaire adaptée, vocabulaire à unifier |
| Potentiel commercial | 15/20 | Positionnement différenciant, pilote exploitable |
| Capacité LMS globale | 12/20 | LMS réel mais incomplet sur SCORM, certificats, exports |

La plateforme est utilisable dès aujourd'hui en **pilote contrôlé** (10 à 50 stagiaires, 3 à 5 formateurs, contexte associatif). Elle nécessite une consolidation technique avant mise en production large ou présentation institutionnelle.

---

## Liens utiles

- [Architecture technique](03-architecture.md)
- [Profils utilisateurs](04-profils-utilisateurs.md)
- [Roadmap](11-roadmap.md)
- [Retour au wiki](README.md)
