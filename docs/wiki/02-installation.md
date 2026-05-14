# 02 — Installation & Configuration

## Prérequis

| Outil | Version minimale |
|-------|-----------------|
| PHP | 8.2+ |
| Composer | 2.x |
| Node.js | 18+ |
| NPM | 9+ |
| MySQL / MariaDB | 8.0+ / 10.4+ |

---

## Installation (environnement de développement)

### 1. Cloner le dépôt

```bash
git clone https://github.com/LaurentSta/Oneduc_Dev.git
cd Oneduc_Dev
```

### 2. Installer les dépendances PHP et JavaScript

```bash
composer install
npm install
```

### 3. Configurer l'environnement

```bash
cp .env.example .env
php artisan key:generate
```

Éditer `.env` — voir la section [Variables d'environnement](#variables-denvironnement) ci-dessous.

### 4. Créer la base de données et migrer

```bash
# Créer la base MySQL au préalable
# Dans .env : DB_CONNECTION=mysql, DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD

php artisan migrate
php artisan db:seed   # optionnel — données de démonstration
```

### 5. Lancer l'environnement de développement

```bash
# Recommandé — lance tout en parallèle (serveur, queue, logs, Vite)
composer dev

# Ou individuellement :
php artisan serve      # Backend Laravel
npm run dev            # Frontend Vite (hot reload)
php artisan queue:listen  # Queue de travail
```

---

## Configuration du HMR Vite (développement)

Le fichier `vite.config.js` contient une adresse IP en dur pour le Hot Module Replacement :

```js
hmr: {
    host: '192.168.x.x' // <- remplacer par l'IP de votre machine de dev
}
```

Mettre à jour cette valeur avec l'IP locale de la machine de développement pour que le rechargement automatique fonctionne correctement.

---

## Variables d'environnement

### Variables standard Laravel

| Variable | Description | Valeur dev typique |
|----------|-------------|-------------------|
| `APP_NAME` | Nom de l'application | `Oneduc` |
| `APP_ENV` | Environnement | `local` / `production` |
| `APP_URL` | URL de base | `http://localhost:8000` |
| `APP_DEBUG` | Mode debug | `true` (dev), `false` (prod) |
| `DB_CONNECTION` | Driver BDD | `mysql` |
| `DB_HOST` | Hôte BDD | `127.0.0.1` |
| `DB_PORT` | Port BDD | `3306` |
| `DB_DATABASE` | Nom de la base | `oneduc` |
| `DB_USERNAME` | Utilisateur BDD | — |
| `DB_PASSWORD` | Mot de passe BDD | — |
| `MAIL_MAILER` | Driver mail | `smtp` / `log` (dev) |
| `SESSION_DRIVER` | Driver session | `database` |
| `QUEUE_CONNECTION` | Driver queue | `database` |
| `CACHE_STORE` | Driver cache | `database` |

### Variables spécifiques au projet

| Variable | Description | Requis |
|----------|-------------|--------|
| `VITE_APP_NAME` | Nom exposé au frontend via Vite | Oui |
| `HEDGEDOC_BASE_URL` | URL de l'instance HedgeDoc (pages collaboratives) | Non |
| `HEDGEDOC_NEW_PATH` | Chemin de création de nouvelle page HedgeDoc | Non (défaut : `/new`) |
| `DISCORD_SUPPORT_WEBHOOK_URL` | Webhook Discord pour les notifications support | Non |
| `DISCORD_SUPPORT_INVITE_URL` | URL d'invitation Discord affichée aux utilisateurs | Non |
| `DISCORD_SERVER_ID` | ID du serveur Discord | Non |

---

## Dépendances principales

### PHP (Composer)

| Package | Usage |
|---------|-------|
| `laravel/framework` ^11 | Framework principal |
| `anhskohbo/no-captcha` | Captcha sur formulaires publics |
| `spatie/laravel-cookie-consent` | Bandeau RGPD cookies |
| `laravel-lang/common` | Traductions Laravel |

### Dev PHP

| Package | Usage |
|---------|-------|
| `laravel/pint` | Formatage du code PHP |
| `pestphp/pest` | Framework de tests |
| `laravel/pail` | Viewer de logs en temps réel |
| `laravel/sail` | Docker pour dev local (optionnel) |

---

## Commandes utiles

```bash
# Tests
php artisan test                                    # Tous les tests
./vendor/bin/pest tests/Feature/SomeTest.php        # Fichier spécifique
php artisan test --filter NomDuTest                 # Filtrer par nom

# Formatage
php artisan pint                                    # Formatter le PHP

# Base de données
php artisan migrate                                 # Migrations en attente
php artisan migrate:fresh --seed                    # Réinitialiser la BDD

# Débogage
php artisan route:list                              # Lister les routes
php artisan tinker                                  # Console interactive
```

---

## Stockage des assets SCORM

Les packages SCORM importés ne sont **pas versionnés dans git**. Ils sont stockés dans `storage/` et `public/modules/` selon la configuration dans `config/learning_assets.php`.

En production, ces dossiers doivent être sauvegardés séparément du code source.

---

## Notes pour la production

- Désactiver `APP_DEBUG=false` obligatoirement
- Configurer un vrai driver mail (SMTP)
- Configurer `QUEUE_CONNECTION=database` et lancer un worker queue en continu (`php artisan queue:work`)
- Configurer le scheduler Laravel dans cron (`php artisan schedule:run`)
- Vérifier les routes exposées publiquement (voir [Sécurité](10-securite-rgpd.md))

---

[Retour au wiki](README.md)
