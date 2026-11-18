"""Create an Ed25519 license key pair for the product owner, never for customers."""

import argparse
import base64
import getpass
import os
from pathlib import Path

from cryptography.hazmat.primitives import serialization
from cryptography.hazmat.primitives.asymmetric.ed25519 import Ed25519PrivateKey


def main() -> None:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--private-key", required=True, help="Private key output path; keep offline and backed up")
    args = parser.parse_args()
    private_path = Path(args.private_key)
    if private_path.exists():
        raise SystemExit("Refusing to overwrite an existing private key")
    private_path.parent.mkdir(parents=True, exist_ok=True)
    passphrase = getpass.getpass("Set a passphrase for the license signing key: ").encode("utf-8")
    if len(passphrase) < 12:
        raise SystemExit("Use a passphrase of at least 12 characters")
    if passphrase != getpass.getpass("Confirm passphrase: ").encode("utf-8"):
        raise SystemExit("Passphrases do not match")
    private_key = Ed25519PrivateKey.generate()
    private_bytes = private_key.private_bytes(
        encoding=serialization.Encoding.PEM,
        format=serialization.PrivateFormat.PKCS8,
        encryption_algorithm=serialization.BestAvailableEncryption(passphrase),
    )
    fd = os.open(private_path, os.O_WRONLY | os.O_CREAT | os.O_EXCL, 0o600)
    with os.fdopen(fd, "wb") as private_file:
        private_file.write(private_bytes)
    public = private_key.public_key().public_bytes(
        encoding=serialization.Encoding.Raw,
        format=serialization.PublicFormat.Raw,
    )
    print("Public key (base64), safe to configure in the Print Service:")
    print(base64.b64encode(public).decode("ascii"))
    print("Private key created with owner-only permissions. Keep it out of Git and customer packages.")


if __name__ == "__main__":
    main()
