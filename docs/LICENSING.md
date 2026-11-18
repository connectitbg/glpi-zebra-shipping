# Offline license operations

The demo is automatic and starts at the timestamp recorded on first plugin installation. The Print Service persists that trial state independently. The Professional license is perpetual for major version 1 and is bound to the GLPI installation ID.

## Create the signing key once

Run the key generator in a protected operator environment that has the Print Service Python dependencies installed:

```bash
python licensing/generate_keys.py --private-key /secure/offline/zebrashipping-ed25519.pem
```

The utility prompts for a passphrase, encrypts the private key, sets owner-only file permissions, and prints the public key in base64. Back up the private key securely. Do not place it in the product repository or on a customer's GLPI server.

Set the public key in the Print Service environment as `ZEBRASHIPPING_LICENSE_PUBLIC_KEY`. The public key is not secret.

## Issue a Professional license

Obtain the GLPI installation ID from the GLPI variable directory's `plugins/zebrashipping/installation.json` file through an authorized admin. Then sign a license from the operator environment:

```bash
python licensing/sign_license.py \
  --private-key /secure/offline/zebrashipping-ed25519.pem \
  --installation-id INSTALLATION_ID \
  --output customer-license.lic
```

The utility prompts for the private-key passphrase. Upload the contents of the resulting `.lic` file under the plugin's Professional license setting. The signed file includes no printer address, GLPI database details, or API token.

Treat the license file as customer information because it contains that installation's ID, even though the signature is not a secret.
