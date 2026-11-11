#!/bin/bash
# ------------------------------------------------------------------
# Deploy TechVerse 2027 on an AWS EC2 instance (Amazon Linux 2023).
#
# Two ways to use it:
#  1) Paste this whole file into "Advanced details -> User data" when
#     launching the instance (set the password on the line below).
#  2) Or run it on the instance:  sudo bash aws-ec2.sh 'YourPassword'
#
# Running it again later updates the site to the latest code on GitHub.
# Registrations are kept in the "techfest-data" Docker volume.
# ------------------------------------------------------------------
set -euo pipefail

ADMIN_PASSWORD="${1:-${ADMIN_PASSWORD:-ChangeMe-Admin-2027}}"   # <- change this
REPO_URL="https://github.com/Mohamed-Ali-4/WTassignment.git"
APP_DIR=/opt/techfest

echo "==> Installing Docker and Git"
dnf install -y docker git
systemctl enable --now docker

echo "==> Fetching the latest code"
if [ -d "$APP_DIR/.git" ]; then
  git -C "$APP_DIR" pull --ff-only
else
  git clone --depth 1 "$REPO_URL" "$APP_DIR"
fi

echo "==> Building the app image"
docker build -t techfest "$APP_DIR"

echo "==> Starting the app on port 80"
docker rm -f techfest >/dev/null 2>&1 || true
docker run -d --name techfest --restart unless-stopped -p 80:80 \
  -e ADMIN_PASSWORD="$ADMIN_PASSWORD" \
  -v techfest-data:/var/www/html/data \
  techfest

TOKEN=$(curl -s -X PUT http://169.254.169.254/latest/api/token -H "X-aws-ec2-metadata-token-ttl-seconds: 60" || true)
IP=$(curl -s -H "X-aws-ec2-metadata-token: $TOKEN" http://169.254.169.254/latest/meta-data/public-ipv4 || true)
echo "==> Done. Form: http://${IP:-<public-ip>}/   Admin: http://${IP:-<public-ip>}/admin/"
