# GLPI Zebra Shipping

Open-source shipping-label printing from GLPI 11 to administrator-configured Zebra network printers.

**Publisher:** Connect IT

The current `0.2.7` build is a beta. It includes per-record GLPI access checks, validated printer selection and field sizes, encrypted license storage, complete uninstall cleanup, and hardened local-service URL validation. The ZD421 test label uses 93 × 71 mm media, a large centered recipient or company heading, a larger address, and the asset name, type, inventory number, and serial number. These asset metadata fields are prefilled and editable before printing. Administrators can export or restore a recovery file containing the license and installation identity. Demo labels keep the `DEMO` mark. Do not use this build for production shipments.

## Product decisions

- Demo period: 14 days from first plugin installation.
- Demo limits: two enabled printers, 50 successful print jobs, and 14 days from first plugin installation.
- Trial state must live outside the plugin directory and survive upgrades/reinstalls.
- Professional license: perpetual for major version 1.x, verified offline with Ed25519.
- Professional includes multiple printers.
- The project is licensed under GPL-2.0-or-later. A one-time Professional sale may be offered for the official build; it cannot take away the GPL rights to modify and redistribute the source.
- No production printer IP, service token, private signing key, or customer data belongs in this repository.

## Install and configure

See [installation and configuration](docs/INSTALLATION.md). The GLPI plugin and Print Service are separate packages. Administrators configure printer addresses in the plugin; no printer address is preconfigured in the source.

## Components

- `plugin/`: GLPI plugin. It owns GLPI permissions, shipment form/history, printer selection, trial/license state, and print-service requests.
- `print-service/`: localhost service that validates requests and licenses, renders ZPL, and sends jobs to registered printers.
- `licensing/`: offline license format and operator-side signing utility. The signing key is never shipped to customers.
- `docs/`: installation, configuration, security, licensing, test reports, and publication preparation.

GLPI Zebra Shipping is licensed under GPL-2.0-or-later. The planned Professional option is a one-time purchase for perpetual use of the official major version 1.x release, verified offline. GPL recipients keep the right to modify and redistribute source under the GPL; a Professional key cannot restrict modified forks.

The physical label review, GLPI coding/security/upgrade reviews, one-time price, refund/transfer policy, and support commitment remain open. See [commercial readiness](docs/COMMERCIAL_READINESS.md) before treating the test build as a release.

## Development status

The source is being prepared for the GLPI plugins catalog. Marketplace availability requires a separate GLPI review and is not automatic.
