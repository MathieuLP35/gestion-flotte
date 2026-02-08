#!/usr/bin/env bash
# ---------------------------------------------------------------------------
# Deploy sur le VPS — à lancer sur le serveur (ou via SSH)
#
# Usage sur le serveur:
#   /var/www/gestion-flotte/scripts/vps/deploy.sh
#   # ou
#   cd /var/www/gestion-flotte && ./scripts/vps/deploy.sh
#
# Variables (optionnel):
#   DEPLOY_PATH=/var/www/gestion-flotte
#   COMPOSE_FILE=compose.yml
# ---------------------------------------------------------------------------

set -e

# Répertoire du script (bash vs sh)
if [ -n "${BASH_SOURCE[0]:-}" ]; then
  SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
else
  SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
fi

COMPOSE_FILE="${COMPOSE_FILE:-compose.yml}"

# DEPLOY_PATH : si défini, on l'utilise ; sinon on déduit du script ou /var/www/gestion-flotte
if [ -n "${DEPLOY_PATH:-}" ]; then
  :
elif [ -f "$SCRIPT_DIR/../../compose.yml" ]; then
  DEPLOY_PATH="$(cd "$SCRIPT_DIR/../.." && pwd)"
else
  DEPLOY_PATH="/var/www/gestion-flotte"
fi

cd "$DEPLOY_PATH"

if [ ! -f "$COMPOSE_FILE" ]; then
  # Dernier recours : /var/www/gestion-flotte
  if [ "$DEPLOY_PATH" != "/var/www/gestion-flotte" ] && [ -f "/var/www/gestion-flotte/$COMPOSE_FILE" ]; then
    DEPLOY_PATH="/var/www/gestion-flotte"
    cd "$DEPLOY_PATH"
  else
    echo "Erreur: $COMPOSE_FILE introuvable dans $DEPLOY_PATH" >&2
    echo "        (vérifiez DEPLOY_PATH ou lancez depuis /var/www/gestion-flotte/scripts/vps/deploy.sh)" >&2
    exit 1
  fi
fi

if [ ! -f .env ]; then
  echo "Erreur: .env introuvable. Lancez d'abord scripts/vps/setup-vps.sh et éditez .env." >&2
  exit 1
fi

echo ">>> Deploy Gestion Flotte — $DEPLOY_PATH (build local, image gestion-flotte-app:latest)"
echo ""

# Mise à jour des sources si dépôt git
if [ -d .git ]; then
  echo ">>> Git pull..."
  git fetch --all
  git pull -q --rebase 2>/dev/null || true
  echo ""
fi

echo ">>> docker compose build..."
docker compose -f "$COMPOSE_FILE" --env-file .env build

echo ">>> docker compose up -d..."
docker compose -f "$COMPOSE_FILE" --env-file .env up -d

echo ">>> Attente démarrage app (5s)..."
sleep 5

echo ">>> php artisan migrate --force..."
docker compose -f "$COMPOSE_FILE" --env-file .env exec -T app php artisan migrate --force || true

echo ""
echo ">>> Deploy terminé."
docker compose -f "$COMPOSE_FILE" --env-file .env ps
