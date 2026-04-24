# HedgeDoc Auto-heberge (Option 2)

Ce dossier permet de lancer HedgeDoc en local via Docker Compose.

## 1) Preparation

```bash
cd /var/www/Oneduc_Dev/deployment/hedgedoc
cp .env.example .env
```

Editez `.env` et changez au minimum:

- `POSTGRES_PASSWORD`
- `HEDGEDOC_DOMAIN` (ex: `192.168.1.18` ou votre nom de domaine)
- `HEDGEDOC_PORT` (par defaut `13000`)

## 2) Demarrage

```bash
cd /var/www/Oneduc_Dev/deployment/hedgedoc
docker compose --env-file .env up -d
```

Verifier:

```bash
docker compose --env-file .env ps
docker compose --env-file .env logs -f app
```

L'instance sera accessible sur:

- `http://<HEDGEDOC_DOMAIN>:<HEDGEDOC_PORT>`

Exemple:

- `http://192.168.1.18:13000`

## 3) Arret / redemarrage

```bash
docker compose --env-file .env stop
docker compose --env-file .env start
```

## 4) Mise a jour image

```bash
docker compose --env-file .env pull
docker compose --env-file .env up -d
```

## 5) Sauvegarde minimale

- Base Postgres: volume `hedgedoc_database`
- Uploads: volume `hedgedoc_uploads`

Dump SQL exemple:

```bash
docker compose --env-file .env exec -T database \
  pg_dump -U hedgedoc hedgedoc > hedgedoc_backup.sql
```
