# Déploiement Docker + GitHub Actions + Dockge

Déploiement sur **IP** : **build Docker sur le VPS** (pas de registry), puis `docker compose up`. Compatible **Dockge** pour gérer le stack (logs, redémarrage, etc.). GitHub Actions : qualité → rsync → build → up → migrate.

---

## 1. Prérequis serveur (IP)

- **Docker** et **Docker Compose** v2
- Utilisateur avec accès `docker` (sans `sudo` si possible)
- Clé SSH pour le déploiement (celle utilisée dans `SSH_PRIVATE_KEY`)

### Option : script tout-en-un `install-all.sh`

Installe **Docker**, **Dockge**, le projet et prépare le `.env` :

```bash
curl -sSL https://raw.githubusercontent.com/MathieuLP35/gestion-flotte/main/scripts/vps/install-all.sh | sudo bash
# ou
scp scripts/vps/install-all.sh user@IP:/tmp/ && ssh user@IP 'sudo bash /tmp/install-all.sh'
```

Puis : **éditer `.env`** et lancer **`/var/www/gestion-flotte/scripts/vps/deploy.sh`**.

Autre option : **`scripts/vps/setup-vps.sh`** (sans Dockge). Voir **`scripts/vps/README.md`**.

Sans le script (Debian/Ubuntu) :

```bash
sudo apt update && sudo apt install -y docker.io docker-compose-plugin
sudo usermod -aG docker $USER
```

---

## 2. Préparer le dépôt GitHub

### Secrets (Settings → Secrets and variables → Actions)

| Secret            | Description                                  |
|-------------------|----------------------------------------------|
| `SSH_PRIVATE_KEY` | Clé privée SSH pour `SSH_USER@IP`   |
| `SSH_USER`        | Utilisateur SSH (ex. `root`, `deploy`)       |

Pas de registry : l’image est construite sur le VPS. Les **build args Vite** (`VITE_APP_NAME`, `REVERB_*`) viennent du **`.env`** sur le serveur.

### CI Qualité & Sécurité (`.github/workflows/ci.yml`)

Pipeline **Pint → Larastan → PEST → SonarCloud → Trivy** (push + PR) :

| Étape | Rôle |
|-------|------|
| **Laravel Pint** | Vérifie le format du code PHP (`--test`) |
| **Larastan** | Analyse statique (typage, bugs) |
| **PEST** | Tests PHP (`composer test`) + Vue (`npm run test:vue`) |
| **SonarCloud** | Sécurité & dette technique (optionnel) |
| **Trivy** | Vulnérabilités (CRITICAL/HIGH) dans les deps et la config |

**SonarCloud** : `sonar.organization` et `sonar.projectKey` sont dans `sonar-project.properties`. Il ne reste qu’à :

- **Secret** : `SONAR_TOKEN` (Settings → Secrets and variables → Actions → Secrets)  
  Token : [sonarcloud.io](https://sonarcloud.io) → My Account → Security → Generate Tokens

Le **déploiement** ne lance le build Docker que si **Pint, Larastan, PEST et Trivy** passent (job `quality-gate` dans `deploy.yml`).

---

## 3. Premier déploiement sur le serveur

### 3.1 Dossier de déploiement

```bash
ssh VOTRE_USER@IP
sudo mkdir -p /var/www/gestion-flotte
sudo chown $USER:$USER /var/www/gestion-flotte
```

### 3.2 Fichier `.env`

Créer `/var/www/gestion-flotte/.env` à partir de `.env.production.example` :

```bash
cd /var/www/gestion-flotte
# Coller le contenu adapté de .env.production.example
nano .env
```

À adapter au minimum :

- `APP_KEY` : `php artisan key:generate` (en local), puis copier la valeur
- `DB_PASSWORD` (utilisateur PostgreSQL)
- `REVERB_APP_KEY` et `REVERB_APP_SECRET` (générer des valeurs uniques)
- `APP_URL` : `http://IP` ou votre domaine
- `REVERB_HOST` / `REVERB_PORT` / `REVERB_SCHEME` en cohérence avec `APP_URL`
- `VITE_APP_NAME` : nom de l’app (ex. `Gestion Flotte`) — utilisé au **build** Docker pour Vite

### 3.3 Lancer le déploiement via la CI

Au **premier** déploiement, la CI va :

1. (Qualité : Pint, Larastan, PEST, Trivy)
2. **Rsync** du projet vers `DEPLOY_PATH` (`.env` exclu, conservé sur le serveur)
3. Sur le VPS : **`docker compose build`** (image `gestion-flotte-app:latest`), **`up -d`**, **`migrate --force`**

Le **premier** push sur `main` doit avoir lieu **après** la création de `/var/www/gestion-flotte` et du `.env`.

Déploiement manuel (sans CI) :

```bash
cd /var/www/gestion-flotte
./scripts/vps/deploy.sh
```

### 3.4 Dockge (optionnel)

[Dockge](https://github.com/louislam/dockge) permet de gérer les stacks (logs, redémarrage, etc.) via une interface web.

**Installer Dockge** (une fois) :

```bash
mkdir -p /opt/stacks /opt/dockge
curl -sSL https://raw.githubusercontent.com/louislam/dockge/master/compose.yaml -o /opt/dockge/compose.yaml
cd /opt/dockge && docker compose up -d
# Interface : http://IP:5001
```

**Ajouter le stack Gestion Flotte** dans Dockge :

- **Compose file path** : `/var/www/gestion-flotte/docker-compose.prod.yml`
- **Compose project name** : `gestion-flotte` (ou laisser par défaut)

La CI et `deploy.sh` lancent `docker compose` dans ce répertoire ; Dockge peut afficher le même stack (logs, Redéploy, etc.) si le chemin correspond.

**Important** : l'image `gestion-flotte-app` est **buildée sur le VPS** (pas de registry). Ne pas utiliser dans Dockge le bouton « Pull » / « Update and pull » — cela provoquerait « pull access denied ». Pour déployer : utiliser `./scripts/vps/deploy.sh` en SSH (build + up).

---

## 4. Déploiements suivants

À chaque **push sur `main`** (et sur `workflow_dispatch`) :

1. Qualité (Pint, Larastan, PEST, Trivy)
2. **Rsync** du projet vers le VPS (sans `.env`)
3. Sur le VPS : **`docker compose build`** → **`up -d`** → **`migrate --force`**

Rien à faire à la main si les secrets GitHub et le `.env` sur le serveur sont corrects.

---

## 5. Structure Docker

- **app** : Laravel (PHP-FPM) + assets Vite — image **`gestion-flotte-app:latest`** (build local)
- **nginx** : reverse proxy (HTTP 80, WebSocket `/app` → Reverb)
- **reverb** : `php artisan reverb:start` (même image que app)
- **queue** : `php artisan queue:work` (même image que app)
- **postgres** : PostgreSQL 16

Volumes : `storage`, `bootstrap/cache`, `postgres_data`.

---

## 6. Commandes utiles sur le serveur

```bash
cd /var/www/gestion-flotte

# Logs
docker compose -f docker-compose.prod.yml logs -f app
docker compose -f docker-compose.prod.yml logs -f queue
docker compose -f docker-compose.prod.yml logs -f reverb

# Redémarrer un service
docker compose -f docker-compose.prod.yml restart app

# Exec
docker compose -f docker-compose.prod.yml exec app php artisan tinker
docker compose -f docker-compose.prod.yml exec app php artisan queue:work --once
```

---

## 7. Domaine et HTTPS

Pour utiliser un **domaine** et du **HTTPS** :

1. Pointer le domaine vers `IP`
2. Mettre à jour `APP_URL`, `REVERB_HOST`, `REVERB_SCHEME` (et les variables GitHub `REVERB_*` pour le build Vite)
3. Ajouter un reverse proxy (Traefik, Caddy, ou nginx + Certbot) **devant** le compose, ou intégrer Certbot dans un service nginx dédié et adapter `docker/nginx` et les ports (80/443).

---

## 8. Dépannage

### « pull access denied for gestion-flotte-app »

L’image `gestion-flotte-app:latest` est **construite sur le VPS**, pas tirée d’un registry. Cette erreur apparaît si un `docker compose pull` est exécuté (par ex. via **Dockge** « Pull » / « Update »).

**À faire :**

1. **Ne jamais lancer `docker compose pull`** pour ce stack. Utiliser **`./scripts/vps/deploy.sh`** (build puis up).
2. **Avec Dockge** : ne pas utiliser le bouton « Pull » ou « Update and pull ». Pour déployer : lancer `./scripts/vps/deploy.sh` en SSH, ou dans Dockge uniquement « Start » après un premier déploiement réussi via `deploy.sh`. Si « Start » dans Dockge déclenche un `pull` avant `up`, privilégier `deploy.sh` pour les déploiements.
3. Mettre à jour les scripts sur le VPS (`git pull` ou rsync) pour avoir `deploy.sh` et `docker-compose.prod.yml` à jour (avec `pull_policy: never` sur app/reverb/queue).

### « DB_PASSWORD variable is not set »

Docker Compose doit être lancé **avec** `--env-file .env` dans le bon répertoire. Vérifier que :

- `/var/www/gestion-flotte/.env` existe et contient `DB_PASSWORD` (et `DB_CONNECTION=pgsql`, `DB_USERNAME`, `DB_DATABASE`) ;
- les commandes utilisent : `docker compose -f docker-compose.prod.yml --env-file .env ...`

`deploy.sh` le fait automatiquement. Avec Dockge, définir le **working directory** sur `/var/www/gestion-flotte` et que le stack utilise le `.env` (selon la config Dockge).

### Passage de MySQL à PostgreSQL

Si vous aviez une installation avec MySQL : les données ne sont pas migrées automatiquement. Soit repartez d'une base vide (`php artisan migrate:fresh` après avoir supprimé l'ancien volume `mysql_data` si besoin), soit effectuez un export/import manuel de vos données.

---

## 9. Changer l’IP ou le chemin de déploiement

- **IP** : modifier `SERVER_HOST` dans `.github/workflows/deploy.yml` ou le mettre dans un secret `SERVER_HOST` et l’utiliser dans le workflow.
- **Chemin** : modifier `DEPLOY_PATH` dans le même fichier (ou via variable d’environnement de la CI).
