# Contribuer à Oneduc

Merci de votre intérêt pour Oneduc. Ce document précise les règles de contribution au projet.

Oneduc est un projet porté par l'Association Oneduc (loi 1901). Le projet est distribué sous licence GNU Affero General Public License v3.0 ou toute version ultérieure (AGPL-3.0-or-later).

## Principes de contribution

Les contributions doivent respecter quatre principes :

1. Améliorer l'accessibilité, la sécurité ou la qualité pédagogique de la plateforme.
2. Respecter l'architecture Laravel existante.
3. Éviter toute introduction de données personnelles, secrets techniques ou contenus sous droits.
4. Rester compatible avec la licence AGPL v3 du projet.

## Accord de contribution

Toute contribution significative nécessite l'acceptation du Contributor License Agreement d'Oneduc, disponible dans `CLA.md`.

Le projet peut utiliser CLA Assistant pour demander cette acceptation automatiquement lors d'une Pull Request.

Le CLA ne transfère pas vos droits d'auteur à l'Association Oneduc. Il accorde à l'association les droits nécessaires pour intégrer, maintenir, distribuer, sublicencier et éventuellement relicencier la contribution dans le cadre du projet.

## Préparer l'environnement local

```bash
git clone https://github.com/LaurentSta/Oneduc_Dev.git
cd Oneduc_Dev
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm run dev
```

Selon votre environnement, vous pouvez aussi utiliser :

```bash
composer dev
```

## Avant de proposer une modification

Vérifier au minimum :

```bash
php artisan test
php artisan pint
php artisan route:list
```

Pour les changements touchant au SCORM, aux groupes, aux tableaux de bord ou aux permissions, ajouter ou mettre à jour les tests correspondants.

## Règles Laravel attendues

- Utiliser les contrôleurs existants uniquement si la modification reste limitée.
- Extraire en service les logiques longues ou réutilisables.
- Préférer les FormRequest pour les validations complexes.
- Centraliser progressivement les autorisations dans des Policies Laravel.
- Ne jamais contourner les middlewares `auth`, `role`, `association.member`, `track.time` ou `force.password.change` sans justification documentée.
- Ne pas désactiver CSRF sauf cas technique explicitement justifié, comme certains flux SCORM.

## Sécurité

Ne pas ouvrir de Pull Request publique pour une faille de sécurité exploitable.

Signaler les failles à : contact@oneduc.fr

Voir `SECURITY.md` pour la procédure complète.

## Accessibilité et RGPD

Chaque contribution doit éviter de dégrader :

- la navigation clavier ;
- les libellés de formulaire ;
- les contrastes ;
- les alternatives textuelles ;
- la minimisation des données personnelles ;
- le respect des finalités pédagogiques déclarées.

Les nouvelles fonctionnalités collectant des données d'apprentissage doivent préciser :

- la finalité ;
- les données enregistrées ;
- la durée de conservation envisagée ;
- les droits d'accès ou de suppression applicables.

## Créer une Pull Request

Une Pull Request doit contenir :

- un titre clair ;
- le problème résolu ;
- les fichiers principaux modifiés ;
- les tests effectués ;
- les impacts éventuels sur la sécurité, l'accessibilité ou le RGPD ;
- des captures d'écran si l'interface est modifiée.

Exemple de résumé :

```markdown
## Objectif
Corriger l'accès direct aux leçons non affectées au stagiaire.

## Fichiers modifiés
- app/Models/Module.php
- app/Http/Controllers/Backend/ModuleController.php
- tests/Feature/StudentModuleAccessTest.php

## Tests
- php artisan test --filter StudentModuleAccessTest
- php artisan pint

## Points de vigilance
Impact sécurité positif : ajout d'une vérification d'appartenance groupe/module.
```

## Ce qui ne doit pas être ajouté au dépôt

Ne jamais ajouter :

- `.env`, `.env.local`, `.env.production` ;
- clés privées, jetons, mots de passe, fichiers `.pem`, `.key` ;
- dumps SQL ;
- exports de production ;
- données réelles de stagiaires ou formateurs ;
- modules SCORM sous droits ;
- fichiers volumineux générés ;
- dossiers `vendor/`, `node_modules/`, `public/build/`, `public/modules/`.

## Gouvernance

L'Association Oneduc conserve le pilotage du projet via ses instances statutaires. Les mainteneurs peuvent refuser une contribution si elle ne correspond pas à l'orientation pédagogique, technique, juridique ou associative du projet.
