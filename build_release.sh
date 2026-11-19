#!/bin/sh
set -eu

ROOT=$(CDPATH= cd -- "$(dirname -- "$0")" && pwd)
DIST=$ROOT/dist/release-0.2.5
PLUGIN_VERSION=0.2.5
SERVICE_VERSION=0.2.3

if [ -e "$DIST" ]; then
    echo "Refusing to overwrite existing dist directory: $DIST" >&2
    exit 1
fi
mkdir -p "$DIST/plugin/zebrashipping" "$DIST/print-service/zebrashipping-print-service"

tar -C "$ROOT/plugin" \
    --exclude='.env' --exclude='.env.*' --exclude='*.key' --exclude='*.pem' \
    --exclude='*.db' --exclude='*.sqlite' --exclude='*.sqlite3' --exclude='*.sql' \
    --exclude='*.log' --exclude='vendor' --exclude='.git' \
    -cf - . | tar -C "$DIST/plugin/zebrashipping" -xf -
cp "$ROOT/print-service/app.py" "$ROOT/print-service/requirements.txt" \
   "$ROOT/print-service/config.example.json" "$ROOT/print-service/zebrashipping.service" \
   "$ROOT/print-service/install_debian.sh" "$DIST/print-service/zebrashipping-print-service/"
cp "$ROOT/docs/INSTALLATION.md" "$DIST/INSTALLATION.md"
cp "$ROOT/LICENSE" "$DIST/plugin/zebrashipping/LICENSE"
cp "$ROOT/LICENSE" "$DIST/print-service/zebrashipping-print-service/LICENSE"

cd "$DIST/plugin"
zip -qr "$DIST/glpi-zebrashipping-plugin-$PLUGIN_VERSION.zip" zebrashipping
cd "$DIST/print-service"
zip -qr "$DIST/zebrashipping-print-service-$SERVICE_VERSION.zip" zebrashipping-print-service
printf 'Release archives created under %s\n' "$DIST"
