# Offline license operations

The demo is automatic and starts at the timestamp recorded on first plugin installation. The Print Service persists that trial state independently. The Professional license is perpetual for major version 1 and is bound to the GLPI installation ID.

## Public source and one-time sale

The repository and both product components are licensed under **GPL-2.0-or-later**. The official source is public and users may modify and redistribute it under the GPL terms. The project may charge a one-time fee for the official Professional activation and official distribution. That fee cannot remove recipients' GPL rights, and the license check is not a technical barrier against modified forks. Do not market it as exclusive control over all copies or derivatives.

The planned Professional purchase is a one-time payment for perpetual use of the official Professional release within major version 1.x. License files are installation-bound and verified offline; there is no license server or recurring activation check. The exact price, refund terms, and transfer/reissue policy must be published before taking payment.

Editing and using a custom ZPL label template is a Professional feature. Demo users see a locked preview in GLPI; both the plugin settings controller and the Print Service enforce the license requirement. A recovery export includes the custom template, if configured, along with the installation identity and license.

## Verify or create the signing key

Before issuing a paid license, confirm that the matching public key is configured on the Print Service and that the encrypted private key has a tested, owner-controlled offline backup. If there is no existing key, run the key generator in a protected operator environment that has the Print Service Python dependencies installed:

```bash
python licensing/generate_keys.py --private-key /secure/offline/zebrashipping-ed25519.pem
```

The utility refuses to overwrite an existing key. It prompts for a passphrase, encrypts the private key, sets owner-only file permissions, and prints the public key in base64. Back up the private key securely. Do not place it in the product repository or on a customer's GLPI server.

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
