# Wiki Oneduc — Documentation du projet

Bienvenue dans la documentation complète de la plateforme Oneduc, LMS dédié à l'inclusion numérique et à la formation accompagnée.

---

## Table des matières

| # | Page | Résumé |
|---|------|--------|
| 01 | [Présentation du projet](01-presentation.md) | Vision, positionnement, stack technique, contexte |
| 02 | [Installation & configuration](02-installation.md) | Prérequis, mise en route dev, variables d'environnement |
| 03 | [Architecture technique](03-architecture.md) | Structure Laravel, routing multi-rôle, services, schéma SQL baseline |
| 04 | [Profils utilisateurs](04-profils-utilisateurs.md) | Admin, Formateur, Stagiaire, Observateur — droits et espaces |
| 05 | [Modules, SCORM & Quiz](05-modules-scorm-quiz.md) | Contenu pédagogique, import SCORM, quiz natifs, progression |
| 06 | [Groupes & Parcours](06-groupes-parcours.md) | Groupes de formation, co-formateurs, parcours ordonnés |
| 07 | [Outils d'animation pédagogique](07-outils-animation.md) | Activités live intégrées, tableau blanc, minuteur, pages collaboratives |
| 08 | [Tableaux de bord](08-tableaux-de-bord.md) | Analytics Admin, Formateur, Stagiaire — sources de données |
| 09 | [Design system](09-design-system.md) | Tokens Tailwind, composants, typographie, accessibilité |
| 10 | [Sécurité & RGPD](10-securite-rgpd.md) | Middleware, authentification, données personnelles |
| 11 | [Roadmap](11-roadmap.md) | Feuille de route en 4 phases, bugs connus, dette technique |
| 12 | [Glossaire](12-glossaire.md) | Vocabulaire unifié du projet |
| 13 | [Prêt à publier sur GitHub](13-publication-github.md) | Checklist avant publication — sécurité, licence, nettoyage |

---

## Licence & gouvernance

Oneduc est distribué sous **licence GNU Affero General Public License v3 (AGPL v3)**.

**Ce que cela signifie :**
- Tout le monde peut utiliser, modifier et redistribuer Oneduc librement
- Toute modification redistribuée (y compris en mode SaaS hébergé) doit être publiée sous la même licence
- L'usage commercial est autorisé sous conditions de réciprocité — contactez l'association pour une licence commerciale

**Titulaire des droits :** Association Oneduc (loi 1901)  
**Contributions :** soumises au [Contributor License Agreement](../../CLA.md)  
**Gouvernance :** le projet est piloté par l'Association Oneduc via ses instances statutaires

Fichiers légaux à la racine du projet :

| Fichier | Rôle |
|---------|------|
| [LICENSE](../../LICENSE) | Texte de la licence AGPL v3 |
| [NOTICE](../../NOTICE) | Copyright et crédits des dépendances |
| [CONTRIBUTING.md](../../CONTRIBUTING.md) | Guide de contribution |
| [CLA.md](../../CLA.md) | Contributor License Agreement |
| [SECURITY.md](../../SECURITY.md) | Politique de signalement des failles |
| [CODE_OF_CONDUCT.md](../../CODE_OF_CONDUCT.md) | Code de conduite de la communauté |

---

## Autres ressources du dossier `docs/`

- [Parcours formateur Oneduc](../parcours-formateur.md) — Structure de l'onboarding formateur (5 modules)
- [Idées d'outils formateurs](../idees-outils-formateurs.md) — 8 pistes produit (OF-001 à OF-008)

---

## À propos du wiki

Ce wiki est destiné à quatre publics :

- **Développeurs** — comprendre l'architecture, contribuer au code, éviter les pièges connus
- **Formateurs** — comprendre les fonctionnalités disponibles, les outils d'animation, le parcours d'onboarding
- **Partenaires & financeurs** — vision du projet, maturité, positionnement, roadmap
- **Administrateurs système** — installation, configuration, sécurité, variables d'environnement

---

## État vérifié du dépôt

Analyse réalisée le **3 juillet 2026** depuis `/var/www/Oneduc_Dev`.

| Vérification | Résultat |
|--------------|----------|
| Routes Laravel | 402 routes déclarées (`php artisan route:list --json`) |
| Tests automatisés | 102 tests passés, 501 assertions |
| Build frontend | `npm run build` réussi, avec avertissements de taille de chunks |
| Schéma base de données | Baseline MySQL dans `database/schema/mysql-schema.sql` + 3 migrations post-baseline |
| Point d'attention publication | Connexion stagiaire par code sans throttling, route de feedback leçon à corriger, historique Git à vérifier |

---

*Dernière mise à jour : juillet 2026 — Version actuelle : Laravel 11, PHP 8.2+, Tailwind CSS v4, React 19 pour certains écrans riches*
