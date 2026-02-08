#!/usr/bin/env bash
# ---------------------------------------------------------------------------
# Setup VPS — à lancer une fois sur le serveur
# Préparation : Docker, répertoire, .env. Build Docker local (pas de registry).
#
# Usage:
#   curl -sSL https://raw.githubusercontent.com/OWNER/REPO/main/scripts/vps/setup-vps.sh | bash
#   # ou
#   scp scripts/vps/setup-vps.sh user@IP:/tmp/ && ssh user@IP 'bash /tmp/setup-vps.sh'
#
# Variables (optionnel) :
#   DEPLOY_PATH=/var/www/gestion-flotte   (défaut)
#   REPO_URL=https://github.com/OWNER/gestion-flotte
# ---------------------------------------------------------------------------

set -e

DEPLOY_PATH="${DEPLOY_PATH:-/var/www/gestion-flotte}"
REPO_URL="${REPO_URL:-https://github.com/MathieuLP35/gestion-flotte}"

echo "=== Setup VPS Gestion Flotte ==="
echo "  DEPLOY_PATH=$DEPLOY_PATH"
echo "  REPO_URL=$REPO_URL"
echo ""

# --- 1. Docker + Docker Compose ---
if ! command -v docker &>/dev/null; then
  echo ">>> Installation de Docker..."
  apt-get update -qq
  apt-get install -y ca-certificates curl
  install -m 0755 -d /etc/apt/keyrings
  curl -fsSL https://download.docker.com/linux/ubuntu/gpg -o /etc/apt/keyrings/docker.asc
  chmod a+r /etc/apt/keyrings/docker.asc
  echo "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.asc] https://download.docker.com/linux/ubuntu $(. /etc/os-release && echo "${VERSION_CODENAME:-jammy}") stable" \
    > /etc/apt/sources.list.d/docker.list
  apt-get update -qq
  apt-get install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin
  systemctl enable --now docker
  # Utilisateur courant dans le groupe docker (si pas root)
  [ -n "${SUDO_USER:-}" ] && usermod -aG docker "$SUDO_USER"
  echo "    Docker installé."
else
  echo ">>> Docker déjà installé."
fi

if ! docker compose version &>/dev/null; then
  apt-get update -qq
  apt-get install -y docker-compose-plugin || true
fi

# --- 2. Répertoire et dépôt (docker-compose + docker/nginx) ---
echo ">>> Répertoire $DEPLOY_PATH..."
mkdir -p "$DEPLOY_PATH"
cd "$DEPLOY_PATH"

if [ -d .git ]; then
  echo ">>> Git pull (docker-compose, docker/)..."
  git fetch --all
  git pull -q 2>/dev/null || git checkout -B main origin/main 2>/dev/null || true
elif [ -z "$(ls -A . 2>/dev/null)" ]; then
  echo ">>> Clone du dépôt (shallow) — répertoire vide..."
  git clone --depth 1 "$REPO_URL" .
else
  echo ">>> Récupération docker-compose, docker/, scripts/ depuis le dépôt..."
  git clone --depth 1 "$REPO_URL" /tmp/gf-clone-$$
  cp /tmp/gf-clone-$$/compose.yml . 2>/dev/null || true
  cp -r /tmp/gf-clone-$$/docker . 2>/dev/null || true
  cp /tmp/gf-clone-$$/.env.production.example . 2>/dev/null || true
  cp -r /tmp/gf-clone-$$/scripts . 2>/dev/null || true
  rm -rf /tmp/gf-clone-$$
fi

# --- 3. .env ---
if [ ! -f .env ]; then
  if [ -f .env.production.example ]; then
    cp .env.production.example .env
    echo ">>> Fichier .env créé depuis .env.production.example — éditez-le (APP_KEY, DB_*, REVERB_*, VITE_APP_NAME)."
  else
    echo ">>> Pas de .env.production.example trouvé. Créez .env à la main (voir .env.production.example dans le dépôt)."
  fi
else
  echo ">>> .env existe déjà."
fi

echo ""
echo "=== Suite ==="
echo "1. Éditez .env : nano $DEPLOY_PATH/.env"
echo "   — APP_KEY (php artisan key:generate en local)"
echo "   — DB_PASSWORD (PostgreSQL)"
echo "   — REVERB_APP_KEY, REVERB_APP_SECRET, REVERB_HOST, REVERB_PORT, REVERB_SCHEME"
echo "   — VITE_APP_NAME (pour le build Docker)"
if [ -f "$DEPLOY_PATH/scripts/vps/deploy.sh" ]; then
  chmod +x "$DEPLOY_PATH/scripts/vps/deploy.sh"
  echo "2. Déploiement : $DEPLOY_PATH/scripts/vps/deploy.sh  OU  depuis GitHub Actions (push main)"
else
  echo "2. Déploiement : copiez deploy.sh depuis scripts/vps/ ou utilisez GitHub Actions (push main)"
fi
echo ""
