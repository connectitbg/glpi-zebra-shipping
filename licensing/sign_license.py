"""Operator-only Ed25519 license signer. Keep the private key off customer systems."""

import argparse
import base64
import getpass
import json
import os
from datetime import datetime, timezone
from pathlib import Path

from cryptography.hazmat.primitives import serialization
from cryptography.hazmat.primitives.asymmetric.ed25519 import Ed25519PrivateKey


def main() -> None:
    parser = argparse.ArgumentParser()
    parser.add_argument("--private-key", required=True, help="PEM Ed25519 private key file")
    parser.add_argument("--installation-id", required=True)
    parser.add_argument("--edition", choices=["professional"], default="professional")
    parser.add_argument("--output", required=True)
    args = parser.parse_args()
    passphrase = getpass.getpass("License signing key passphrase: ").encode("utf-8")
    key = serialization.load_pem_private_key(Path(args.private_key).read_bytes(), password=passphrase)
    if not isinstance(key, Ed25519PrivateKey):
        raise SystemExit("Private key must be Ed25519")
    payload = {"product": "glpi-zebra-shipping", "edition": args.edition,
               "installation_id": args.installation_id, "major_version": 1,
               "issued_at": datetime.now(timezone.utc).isoformat()}
    message = json.dumps(payload, sort_keys=True, separators=(",", ":")).encode()
    envelope = {"payload": payload, "signature": base64.b64encode(key.sign(message)).decode()}
    encoded = base64.urlsafe_b64encode(json.dumps(envelope, separators=(",", ":")).encode()).decode().rstrip("=")
    output_path = Path(args.output)
    fd = os.open(output_path, os.O_WRONLY | os.O_CREAT | os.O_EXCL, 0o600)
    with os.fdopen(fd, "w", encoding="ascii") as output_file:
        output_file.write(encoded + "\n")


if __name__ == "__main__":
    main()
