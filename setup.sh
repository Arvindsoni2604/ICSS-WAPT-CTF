#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")"
if [ ! -f .env ]; then
  cp .env.example .env
  echo "Created .env. Edit it now and set unique database passwords:"
  echo "  nano .env"
  exit 0
fi
sudo docker compose up --build -d
sudo docker compose ps
echo "Open http://127.0.0.1:8080"
