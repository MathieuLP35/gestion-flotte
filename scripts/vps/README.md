# Scripts VPS — déploiement sur le serveur

À utiliser sur le VPS (ex. **${{ secrets.SERVER_IP }}**) pour préparer l’environnement et déployer.

---

## 0. `install-all.sh` — tout en un

Installe **Docker**, **Dockge**, clone le projet, crée le `.env`.

```bash
curl -sSL https://raw.githubusercontent.com/MathieuLP35/gestion-flotte/main/scripts/vps/install-all.sh | sudo bash
# ou
scp scripts/vps/install-all.sh user@VPS:/tmp/ && ssh user@VPS 'sudo bash /tmp/install-all.sh'
```

**Options :** `--no-dockge` (sans Dockge), `--deploy` (lancer le déploiement à la fin).

**Variables :** `DEPLOY_PATH`, `REPO_URL` (dépôt privé : `REPO_URL="https://TOKEN@github.com/OWNER/gestion-flotte"`).

Puis : **éditer `.env`** et lancer **`scripts/vps/deploy.sh`** (ou laisser la CI faire au push sur `main`).

---

## 1. `setup-vps.sh` (une fois)

Installe Docker, crée le répertoire de déploiement, récupère `docker-compose.prod.yml`, `docker/`, `scripts/` et un `.env` à éditer.

### Depuis ta machine (SSH)

```bash
scp scripts/vps/setup-vps.sh user@${{ secrets.SERVER_IP }}:/tmp/
ssh user@${{ secrets.SERVER_IP }} 'sudo bash /tmp/setup-vps.sh'
```

### Directement sur le VPS

```bash
# Téléchargement du script (remplace OWNER/REPO par ton dépôt)
curl -sSL https://raw.githubusercontent.com/MathieuLP35/gestion-flotte/main/scripts/vps/setup-vps.sh -o /tmp/setup-vps.sh
sudo bash /tmp/setup-vps.sh
```

### Variables d’environnement (optionnel)

| Variable      | Défaut                          | Rôle                          |
|---------------|----------------------------------|-------------------------------|
| `DEPLOY_PATH` | `/var/www/gestion-flotte`        | Dossier de déploiement        |
| `REPO_URL`    | `https://github.com/MathieuLP35/gestion-flotte` | Dépôt Git          |

Pour un **dépôt privé** : `REPO_URL="https://TOKEN@github.com/OWNER/gestion-flotte"` (PAT avec `repo`) ou configurez une deploy key sur le serveur.

### Après le setup

1. Éditer `.env` :  
   `nano /var/www/gestion-flotte/.env`
2. Renseigner au minimum : `APP_KEY`, `DB_PASSWORD`, `MYSQL_ROOT_PASSWORD`, `REVERB_APP_KEY`, `REVERB_APP_SECRET`, `VITE_APP_NAME`, `REVERB_HOST`, `REVERB_PORT`, `REVERB_SCHEME` (les `REVERB_*` et `VITE_APP_NAME` servent aussi au **build** Docker).

---

## 2. `deploy.sh` (à chaque déploiement)

Lance **`docker compose build`**, **`up -d`** et **`php artisan migrate --force`**. L’image `gestion-flotte-app:latest` est construite localement sur le VPS (pas de registry).

### Sur le VPS

```bash
/var/www/gestion-flotte/scripts/vps/deploy.sh
```

ou :

```bash
cd /var/www/gestion-flotte && ./scripts/vps/deploy.sh
```

### Variables (optionnel)

| Variable        | Défaut                     | Rôle                               |
|-----------------|----------------------------|------------------------------------|
| `DEPLOY_PATH`   | répertoire du script/../.. | Dossier de déploiement             |
| `COMPOSE_FILE`  | `docker-compose.prod.yml`  | Fichier Compose à utiliser         |

---

## Déploiement manuel vs GitHub Actions

- **GitHub Actions** : au push sur `main`, qualité (Pint, Larastan, PEST, Trivy), puis **rsync** du projet, **build** et **compose up** sur le VPS.
- **Script `deploy.sh`** : à lancer à la main sur le VPS (après `git pull` si le code est en dépôt, ou après un rsync). Build local, `up -d`, migrate.
