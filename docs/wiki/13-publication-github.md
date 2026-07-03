# 13 — Checklist : prêt à publier sur GitHub

Cette checklist doit être complétée avant de rendre le dépôt public sur GitHub. Elle couvre quatre axes : sécurité du code, nettoyage de l'historique git, conformité légale, et configuration GitHub.

---

## Axe 1 — Sécurité du code (bloquant)

Ces points doivent être corrigés AVANT de publier. Publier avec ces failles expose vos utilisateurs et donne une carte d'attaque publique.

- [x] **S1** — Route `/admin/stagiaires/{id}/debug-progression` supprimée ou absente
  Vérifié le 3 juillet 2026 : aucune route `debug-progression` dans `php artisan route:list --json`

- [x] **S2** — `POST /admin/stagiaires/{user}/reset-progression` protégé côté admin
  Vérifié : route `admin.stagiaires.reset` dans `routes/admin.php`, sous `auth`, `role:admin`, `admin.activity`

- [ ] **S3** — Corriger `Module::isVisibleTo()` pour vérifier l'appartenance groupe du stagiaire  
  `app/Models/Module.php`

- [ ] **S4** — Ajouter throttling sur `/stagiaire/connexion-code`  
  `routes/web.php` — ajouter `->middleware('throttle:10,1')`

- [ ] **S5** — Corriger `LessonFeedbackController::store()` (route inexistante → erreur 500)  
  `app/Http/Controllers/LessonFeedbackController.php` redirige vers `module.lesson`, route absente

- [ ] **S6** — Corriger le cumul temps SCORM
  `SCORMController::handleSessionTime()` écrit `last_session_time`, absent de `scorm_scores`

- [ ] **S7** — Ajouter la vérification d'appartenance à la leçon dans `POST /scorm/save-progress`
  Le CSRF est volontairement désactivé pour l'iframe SCORM, il faut compenser côté contrôleur

---

## Axe 2 — Nettoyage de l'historique git (bloquant)

### Secrets et fichiers sensibles

Vérifier que ces fichiers ne sont pas dans l'historique git (même supprimés d'un commit récent, ils restent accessibles via `git log`) :

```bash
# Vérifier si .env a jamais été commité
git log --all --full-history -- .env
git log --all --full-history -- .env.local
git log --all --full-history -- .env.production

# Vérifier les clés et credentials
git log --all --full-history -- "*.pem" "*.key" "*credentials*"

# Chercher des secrets dans l'historique complet
git grep -i "password\|secret\|api_key\|token" $(git rev-list --all) 2>/dev/null | grep -v "\.example\|test\|dummy\|placeholder" | head -20
```

Si un secret est trouvé dans l'historique :
1. Révoquez immédiatement le secret concerné (mot de passe, clé API, etc.)
2. Utilisez `git filter-repo` (ou BFG Repo Cleaner) pour le supprimer de l'historique
3. Faites un force-push après nettoyage (attention : coordonner avec les autres contributeurs)

### Données personnelles dans les seeders/fixtures

```bash
# Chercher des emails réels dans les seeders
grep -r "@" database/seeders/ database/factories/
grep -r "@" tests/

# Vérifier qu'il n'y a pas de dumps de base de données
find . -name "*.sql" -not -path "./.git/*"
find . -name "*.sqlite" -not -path "./.git/*"
```

Tout email ou nom réel doit être remplacé par des données fictives (`test@example.com`, `jean.dupont@exemple.fr`).

État local partiel au 3 juillet 2026 : `git log --all --full-history -- .env` ne retourne rien. Le scan complet des secrets reste à faire avant passage public.

### Vérification du .gitignore

```bash
# Ces éléments doivent être ignorés
cat .gitignore | grep -E "\.env$|storage/|public/build/|public/modules/|vendor/|node_modules/"
```

Vérifier que `.gitignore` contient au minimum :
```
.env
.env.*
!.env.example
/vendor/
/node_modules/
/public/build/
/storage/*.key
/storage/app/public/
/public/modules/
/public/upload/
```

### Packages SCORM

```bash
# Vérifier qu'aucun package SCORM n'est dans le dépôt
find public/modules -name "*.zip" 2>/dev/null
find storage -name "imsmanifest.xml" 2>/dev/null | head -5
```

Les packages SCORM importés ne doivent pas être dans le dépôt git (ils peuvent contenir des contenus sous droits).

---

## Axe 3 — Conformité légale

- [ ] Le fichier `LICENSE` contient le texte intégral de l'AGPL v3  
  → État local : le texte AGPL v3 complet est présent

- [ ] Le fichier `NOTICE` mentionne `© Association Oneduc` avec les années correctes

- [ ] Le fichier `CLA.md` est présent et référencé dans `CONTRIBUTING.md`

- [ ] Vérification avec votre école/organisme de certification que votre projet de fin d'année peut être publié sous AGPL au nom de l'association (vérifier la convention de formation ou le règlement intérieur)

- [ ] Les statuts de l'association Oneduc couvrent bien le développement de logiciels numériques (sinon : délibération du CA actant la titularité des droits)

- [ ] Les emails de contact dans `SECURITY.md` et `CODE_OF_CONDUCT.md` sont opérationnels (`contact@oneduc.fr`)

---

## Axe 4 — Configuration GitHub

### Paramètres du dépôt

- [ ] Le dépôt est mis en **Public** (ou restera privé temporairement le temps des corrections)
- [ ] Description du dépôt renseignée : "LMS pour l'inclusion numérique — Laravel 11, SCORM, quiz natifs, outils d'animation"
- [ ] Topics GitHub ajoutés : `lms`, `laravel`, `scorm`, `inclusion-numerique`, `formation`, `php`, `tailwind`
- [ ] Site web renseigné : `https://oneduc.fr`

### Fonctionnalités GitHub à activer

- [ ] **Issues** activées (pour les rapports de bugs et demandes de fonctionnalités)
- [ ] **Discussions** activées (pour les questions et la communauté)
- [ ] **Wiki** GitHub activé et alimenté avec le contenu de `docs/wiki/`
- [ ] **Security advisories** activées (pour les signalements de failles privés)

### Fichiers GitHub spéciaux

- [ ] `.github/ISSUE_TEMPLATE/bug_report.md` — template de rapport de bug
- [ ] `.github/ISSUE_TEMPLATE/feature_request.md` — template de demande de fonctionnalité
- [ ] `.github/PULL_REQUEST_TEMPLATE.md` — template de Pull Request (inclure rappel du CLA)

État local : ces trois fichiers existent dans `.github/`.

### Branches et protection

- [ ] La branche `main` est protégée (au moins : interdire les push directs, exiger une PR)
- [ ] Au moins un reviewer requis pour les PRs (vous-même suffit pour commencer)

---

## Axe 5 — README principal

Le `README.md` à la racine est la première chose que les visiteurs GitHub voient. À vérifier :

- [ ] Badge de licence AGPL v3 présent  
  ```markdown
  [![License: AGPL v3](https://img.shields.io/badge/License-AGPL_v3-blue.svg)](https://www.gnu.org/licenses/agpl-3.0)
  ```
- [ ] Description courte et claire du projet
- [ ] Lien vers le wiki (`docs/wiki/README.md`)
- [ ] Lien vers `CONTRIBUTING.md`
- [ ] Section "Licence & gouvernance" mentionnant Association Oneduc + AGPL v3
- [ ] Contact (`contact@oneduc.fr`)

---

## Récapitulatif de priorité

| Priorité | Action | Bloquant ? |
|----------|--------|------------|
| 1 | Corriger les points sécurité restants (S3 à S7) | Oui |
| 2 | Vérifier et nettoyer l'historique git (secrets, données personnelles) | Oui |
| 3 | Synchroniser `SECURITY.md` avec l'état réel de la checklist | Recommandé |
| 4 | Vérifier convention formation / statuts association | Recommandé |
| 5 | Configurer GitHub (topics, protections de branche, templates) | Non |
| 6 | Mettre à jour le README principal avec badge et sections | Non |

---

[Retour au wiki](README.md)
