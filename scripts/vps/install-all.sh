#!/usr/bin/env bash
# ---------------------------------------------------------------------------
# Installation complète : Docker, Dockge, projet Gestion Flotte, .env
#
# Usage:
#   curl -sSL https://raw.githubusercontent.com/MathieuLP35/gestion-flotte/main/scripts/vps/install-all.sh | sudo bash
#   # ou
#   scp scripts/vps/install-all.sh user@VPS:/tmp/ && ssh user@VPS 'sudo bash /tmp/install-all.sh'
#
# Options:
#   --no-dockge      Ne pas installer Dockge
#   --deploy         Lancer le premier déploiement à la fin (éditez .env avant si besoin)
#
# Variables:
#   DEPLOY_PATH=/var/www/gestion-flotte
#   REPO_URL=https://github.com/MathieuLP35/gestion-flotte
#   Pour dépôt privé : REPO_URL="https://TOKEN@github.com/OWNER/gestion-flotte"
# ---------------------------------------------------------------------------

set -e

DEPLOY_PATH="${DEPLOY_PATH:-/var/www/gestion-flotte}"
REPO_URL="${REPO_URL:-https://github.com/MathieuLP35/gestion-flotte}"
INSTALL_DOCKGE=1
RUN_DEPLOY=0

for arg in "$@"; do
  case "$arg" in
    --no-dockge) INSTALL_DOCKGE=0 ;;
    --deploy)    RUN_DEPLOY=1 ;;
  esac
done

# --- Vérifier root ---
if [ "$(id -u)" -ne 0 ]; then
  echo "Relancez avec : sudo $0 $*" >&2
  exit 1
fi

echo "=============================================="
echo "  Installation Gestion Flotte (Docker, Dockge, projet)"
echo "=============================================="
echo "  DEPLOY_PATH=$DEPLOY_PATH"
echo "  REPO_URL=${REPO_URL/\/\/[^@]*@/\/\/***@}"
echo "  Dockge: $([ "$INSTALL_DOCKGE" = 1 ] && echo 'oui' || echo 'non')"
echo "  Déployer à la fin: $([ "$RUN_DEPLOY" = 1 ] && echo 'oui' || echo 'non')"
echo ""

# ========== 1. Docker + Docker Compose ==========
echo ">>> [1/4] Docker"
if ! command -v docker &>/dev/null; then
  apt-get update -qq
  apt-get install -y ca-certificates curl
  install -m 0755 -d /etc/apt/keyrings
  curl -fsSL https://download.docker.com/linux/ubuntu/gpg -o /etc/apt/keyrings/docker.asc
  chmod a+r /etc/apt/keyrings/docker.asc
  echo "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.asc] https://download.docker.com/linux/ubuntu $(. /etc/os-release 2>/dev/null && echo "${VERSION_CODENAME:-jammy}") stable" \
    > /etc/apt/sources.list.d/docker.list
  apt-get update -qq
  apt-get install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin
  systemctl enable --now docker
  [ -n "${SUDO_USER:-}" ] && usermod -aG docker "$SUDO_USER"
  echo "    Docker installé."
else
  echo "    Docker déjà installé."
fi

if ! docker compose version &>/dev/null; then
  apt-get install -y docker-compose-plugin 2>/dev/null || true
fi

# ========== 2. Dockge ==========
if [ "$INSTALL_DOCKGE" = 1 ]; then
  echo ">>> [2/4] Dockge"
  mkdir -p /opt/stacks /opt/dockge
  if [ ! -f /opt/dockge/compose.yaml ]; then
    curl -sSL https://raw.githubusercontent.com/louislam/dockge/master/compose.yaml -o /opt/dockge/compose.yaml
    cd /opt/dockge && docker compose up -d
    cd - >/dev/null
    echo "    Dockge installé → http://$(hostname -I 2>/dev/null | awk '{print $1}'):5001"
  else
    cd /opt/dockge && docker compose up -d 2>/dev/null || true
    cd - >/dev/null
    echo "    Dockge déjà présent."
  fi
else
  echo ">>> [2/4] Dockge (ignoré)"
fi

# ========== 3. Projet Gestion Flotte ==========
echo ">>> [3/4] Projet $DEPLOY_PATH"
command -v git &>/dev/null || ( apt-get update -qq && apt-get install -y git )
mkdir -p "$DEPLOY_PATH"
cd "$DEPLOY_PATH"

if [ -d .git ]; then
  git fetch --all
  git pull -q 2>/dev/null || git checkout -B main origin/main 2>/dev/null || true
  echo "    Dépôt mis à jour."
elif [ -z "$(ls -A . 2>/dev/null)" ]; then
  git clone --depth 1 "$REPO_URL" .
  echo "    Dépôt cloné."
else
  git clone --depth 1 "$REPO_URL" /tmp/gf-clone-$$
  cp /tmp/gf-clone-$$/docker-compose.prod.yml . 2>/dev/null || true
  cp -r /tmp/gf-clone-$$/docker . 2>/dev/null || true
  cp /tmp/gf-clone-$$/.env.production.example . 2>/dev/null || true
  cp -r /tmp/gf-clone-$$/scripts . 2>/dev/null || true
  rm -rf /tmp/gf-clone-$$
  echo "    Fichiers (docker-compose, docker, scripts) copiés."
fi

# .env
if [ ! -f .env ]; then
  if [ -f .env.production.example ]; then
    cp .env.production.example .env
    echo "    .env créé depuis .env.production.example"
  else
    echo "    ATTENTION: .env.production.example introuvable. Créez .env à la main."
  fi
else
  echo "    .env existe déjà (conservé)."
fi

[ -f scripts/vps/deploy.sh ] && chmod +x scripts/vps/deploy.sh

# Permissions (si pas root au quotidien)
if [ -n "${SUDO_USER:-}" ]; then
  chown -R "$SUDO_USER:$SUDO_USER" "$DEPLOY_PATH" 2>/dev/null || true
fi

# ========== 4. Récap + option deploy ==========
echo ">>> [4/4] Récapitulatif"
echo ""
echo "----------------------------------------------"
echo "  À FAIRE :"
echo "  1. Éditer le .env :"
echo "     nano $DEPLOY_PATH/.env"
echo ""
echo "     À adapter : APP_KEY, DB_PASSWORD, MYSQL_ROOT_PASSWORD,"
echo "     REVERB_APP_KEY, REVERB_APP_SECRET, REVERB_HOST, REVERB_PORT, REVERB_SCHEME, VITE_APP_NAME"
echo ""
echo "  2. Déployer :"
echo "     $DEPLOY_PATH/scripts/vps/deploy.sh"
echo ""
echo "     Ou laisser la GitHub Action le faire au prochain push sur main."
echo "----------------------------------------------"
if [ "$INSTALL_DOCKGE" = 1 ]; then
  echo ""
  echo "  Dockge : ajoutez le stack avec"
  echo "  Compose path = $DEPLOY_PATH/docker-compose.prod.yml"
  echo "----------------------------------------------"
fi
echo ""

if [ "$RUN_DEPLOY" = 1 ]; then
  echo ">>> Lancement du déploiement (comme demandé avec --deploy)..."
  if [ -f "$DEPLOY_PATH/scripts/vps/deploy.sh" ]; then
    ( cd "$DEPLOY_PATH" && ./scripts/vps/deploy.sh ) || echo "    Échec (vérifiez .env)."
  else
    echo "    deploy.sh introuvable."
  fi
fi

echo ""
echo "Installation terminée."
