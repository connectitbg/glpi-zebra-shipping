# GLPI Zebra Shipping

Open-source shipping-label printing from GLPI 11 to administrator-configured Zebra network printers.

**Publisher:** Connect IT

The current tested code is `0.2.8`; the latest published archive is `0.2.8`. Physical printing has been demonstrated with a networked Zebra ZD421 using 93 × 71 mm media. Other network printers that accept ZPL over raw TCP are expected to work, but model-specific compatibility has not been verified for every printer. USB printer compatibility is unknown and unsupported; USB printing is outside the project scope and is not planned. See [printer compatibility](docs/PRINTER_COMPATIBILITY.md).

The plugin includes per-record GLPI access checks, validated printer selection and field sizes, encrypted license storage, complete uninstall cleanup, and hardened local-service URL validation. Labels include a large recipient/company heading and address plus asset name, type, inventory number, and serial number. Asset metadata is prefilled and editable. Administrators can export or restore a recovery file containing the license and installation identity. Demo labels keep the `DEMO` mark.

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

The one-time price, refund/transfer policy, signing-key readiness, payment process, and public support commitment remain open. See [commercial readiness](docs/COMMERCIAL_READINESS.md) and the [monetization runbook](docs/MONETIZATION_RUNBOOK.md).

## Development status

The stable source and versioned release are public. Publication in the GLPI plugins catalog is still pending Teclib's review; Marketplace availability requires a separate GLPI review and is not automatic. See [catalog and Marketplace status](docs/MARKETPLACE_SUBMISSION_DRAFT.md).
