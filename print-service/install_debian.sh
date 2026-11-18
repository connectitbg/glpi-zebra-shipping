#!/bin/sh
set -eu

if [ "$(id -u)" -ne 0 ]; then
    echo "Run this installer as root." >&2
    exit 1
fi

SOURCE_DIR=$(CDPATH= cd -- "$(dirname -- "$0")" && pwd)
INSTALL_DIR=/opt/zebrashipping/print-service
CONFIG_DIR=/etc/zebrashipping
STATE_DIR=/var/lib/zebrashipping
TOKEN_FILE=$CONFIG_DIR/print-service.env

if ! command -v python3 >/dev/null 2>&1; then
    echo "Python 3 is required. Install it with your operating system package manager first." >&2
    exit 1
fi
python3 -c 'import sys; sys.exit(0 if sys.version_info >= (3, 11) else 1)' || {
    echo "Python 3.11 or newer is required." >&2
    exit 1
}
if ! getent group zebrashipping >/dev/null 2>&1; then
    groupadd --system zebrashipping
fi
if ! id zebrashipping >/dev/null 2>&1; then
    useradd --system --gid zebrashipping --home-dir /var/lib/zebrashipping --shell /usr/sbin/nologin zebrashipping
fi

install -d -o root -g root -m 0755 /opt/zebrashipping "$INSTALL_DIR"
install -d -o root -g zebrashipping -m 0770 "$CONFIG_DIR"
install -d -o zebrashipping -g zebrashipping -m 0750 "$STATE_DIR"
install -m 0644 "$SOURCE_DIR/app.py" "$SOURCE_DIR/requirements.txt" "$INSTALL_DIR/"
python3 -m venv /opt/zebrashipping/venv
/opt/zebrashipping/venv/bin/pip install --disable-pip-version-check -r "$INSTALL_DIR/requirements.txt"

if [ ! -f "$TOKEN_FILE" ]; then
    TOKEN_VALUE=$(/opt/zebrashipping/venv/bin/python -c 'import secrets; print(secrets.token_urlsafe(48))')
    printf 'ZEBRASHIPPING_API_TOKEN=%s\nZEBRASHIPPING_LICENSE_PUBLIC_KEY=\n' "$TOKEN_VALUE" > "$TOKEN_FILE"
    chmod 0640 "$TOKEN_FILE"
    chown root:zebrashipping "$TOKEN_FILE"
    unset TOKEN_VALUE
fi

if [ ! -f "$CONFIG_DIR/printers.json" ]; then
    printf '{"printers": []}\n' > "$CONFIG_DIR/printers.json"
    chmod 0640 "$CONFIG_DIR/printers.json"
    chown root:zebrashipping "$CONFIG_DIR/printers.json"
fi

install -m 0644 "$SOURCE_DIR/zebrashipping.service" /etc/systemd/system/zebrashipping.service
systemctl daemon-reload
systemctl enable --now zebrashipping.service
printf '%s\n' "Print Service installed and running on loopback port 8765. Configure Apache's environment using the secure token from $TOKEN_FILE."
